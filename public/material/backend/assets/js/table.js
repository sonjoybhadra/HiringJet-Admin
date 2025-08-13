function loadTable(config) {
    const container = $(config.container);
    const searchInput = $(config.searchInput);
    const exportBtns = $(config.exportButtons);
    let currentPage = 1;
    let baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');
    let frontendUrl = document.querySelector('meta[name="front-url"]').getAttribute('content');

    function fetchData(page = 1, search = '', perPageOverride = null) {
        $('#table-overlay-loader').fadeIn();
        startDotAnimation();

        const perPage = perPageOverride || $(`select[id$='-perPage']`).val() || 50;

        $.ajax({
            url: '/table/fetch',
            method: 'GET',
            data: {
                table: config.table,
                routes: config.routePrefix,
                columns: config.columns.join(','),
                page: page,
                perPage: perPage,
                search: search,
                orderBy: config.orderBy,
                orderType: config.orderType,
                conditions: JSON.stringify(config.conditions || []),
                joins: JSON.stringify(config.joins || [])
            },
            success: function (res) {
                renderTable(res.data, res.page, perPage);
                renderPagination(res.pages, res.page);
            },
            error: function (err) {
                console.error('Fetch failed:', err);
            },
            complete: function () {
                stopDotAnimation();
                $('#table-overlay-loader').fadeOut();
            }
        });
    }

    function renderTable(data, currentPage = 1, perPage = 20) {
        let html = '<table class="table table-striped"><thead><tr>';

        config.headers.forEach(header => {
            html += `<th>${header}</th>`;
        });

        if (config.showActions) {
            html += '<th>Actions</th>';
        }

        html += '</tr></thead><tbody>';

        if (data.length <= 0) {
            const colsCount = (config.columns.length + (config.showActions ? 2 : 1));
            html += `<tr><td style="color:red; text-align:center;" colspan="${colsCount}">No records available</td></tr>`;
        }

        data.forEach((row, index) => {
            html += '<tr>';

            const slno = ((currentPage - 1) * perPage) + index + 1;
            html += `<td>${slno}</td>`;

            const visibleCols = config.visibleColumns ?? config.columns;
            visibleCols.forEach(col => {
                const val = row[col] ?? '';

                if (config.imageColumns && config.imageColumns.includes(col)) {
                    const imageUrl = ((val != '')?baseUrl + val:'https://hjadmin.itiffyconsultants.xyz/public/uploads/no-image.jpg');
                    html += `<td>
                        <a href="${imageUrl}" data-lightbox="table-images" data-title="${row.name ?? ''}">
                            <img src="${imageUrl}" alt="Image" class="img-thumbnail mt-3" style="width: 75px; height: 50px; cursor: zoom-in;">
                        </a>
                    </td>`;
                } else {
                    if(config.routePrefix == 'post-job'){
                        var job_no = row['job_no'];
                        var job_id = row['id'];
                        if(col == 'position_name'){
                            html += `<td>
                                        ${val}
                                        <span id="textToCopy${job_id}" style="display:none;">${frontendUrl}job-details/${job_no}</span>
                                        <span class="badge bg-secondary" style="margin-left:10px;" onclick="copyLink(${job_id});"><i class="fa-regular fa-copy" id="copyBtn" style="cursor:pointer;"></i></span>
                                        <h6 class="text-success" id="copyStatus${job_id}"></h6>
                                    </td>`;
                        } else {
                            html += `<td>${val}</td>`;
                        }
                    } else {
                        html += `<td>${val}</td>`;
                    }
                }
            });

            if (config.showActions) {
                const status = row[config.statusColumn];
                const encodedId = row.encoded_id;
                const base = '/' + config.routePrefix;

                html += `<td>`;
                
                if(config.routePrefix != 'jobseeker' && config.routePrefix != 'employer-user'){
                    html += `<a href="${base}/edit/${encodedId}" class="btn btn-primary btn-sm me-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>`;

                    if (status == 1) {
                        html += `<a href="${base}/change-status/${encodedId}" class="btn btn-success btn-sm me-1" title="Deactivate">
                            <i class="fa-solid fa-check"></i>
                        </a>`;
                    } else {
                        html += `<a href="${base}/change-status/${encodedId}" class="btn btn-warning btn-sm me-1" title="Activate">
                            <i class="fas fa-times"></i>
                        </a>`;
                    }
                }                

                html += `<a href="${base}/delete/${encodedId}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </a>`;

                if(config.routePrefix == 'post-job'){
                    var job_no = row['job_no'];
                    html += `<br><br><a href="${frontendUrl}job-details/${job_no}" class="btn btn-warning btn-sm me-1" title="View Jobs" target="_blank">
                                    <i class="fa-solid fa-eye"></i>&nbsp;&nbsp;View</a>`;

                    html += `<br><br><a href="${base}/applications/${encodedId}" class="btn btn-info btn-sm" title="Applications" target="_blank">
                                    <i class="fa-solid fa-briefcase"></i>&nbsp;&nbsp;Applications
                                </a>`;
                }

                if(config.routePrefix == 'job'){
                    var job_no = row['job_no'];
                    html += `<br><br><a href="${base}/view-details/${encodedId}" class="btn btn-warning btn-sm me-1" title="View Jobs">
                                    <i class="fa-solid fa-info-circle"></i>&nbsp;&nbsp;View Details</a>`;
                }

                if(config.routePrefix == 'jobseeker'){
                    html += `<br><br><a href="${base}/profile/${encodedId}" class="btn btn-info btn-sm" title="Profile" target="_blank">
                                    <i class="fa-solid fa-briefcase"></i>&nbsp;&nbsp;Profile
                                </a>`;
                }

                if(config.routePrefix == 'employer-user'){
                    const completed_steps = row['completed_steps'];
                    if(completed_steps < 2){
                        html += `<br><br><a href="${base}/create-business/${encodedId}" class="btn btn-sm btn-primary btn-sm" title="Business" target="_blank">
                                    <i class="fa-solid fa-briefcase" style="margin-right:3px;"></i>Add Business
                                </a>`;
                    } else {
                        html += `<br><br><a href="${base}/create-business/${encodedId}" class="btn btn-sm btn-primary btn-sm" title="Business" target="_blank">
                                    <i class="fa-solid fa-briefcase" style="margin-right:3px;"></i>Edit Business
                                </a>`;
                        html += `<br><br><a href="${base}/profile/${encodedId}" class="btn btn-sm btn-info btn-sm" title="Profile" target="_blank">
                                    <i class="fa-solid fa-info-circle" style="margin-right:3px;"></i>View Profile
                                </a>`;
                    }
                    if(status == 0){
                        html += `<br><br><a href="${base}/verify-otp/${encodedId}" class="btn btn-sm btn-warning btn-sm" title="Verify OTP">
                                    <i class="fa-solid fa-key" style="margin-right:3px;"></i>Verify OTP
                                </a>`;
                    }
                }

                html += `</td>`;
            }

            html += '</tr>';
        });

        html += '</tbody></table>';
        container.html(html);
    }

    function renderPagination(totalPages, current) {
        let html = '<div class="d-flex flex-wrap align-items-center gap-2 mt-3" style="float:right;">';

        if (totalPages > 1) {
            if (current > 1) {
                html += `<button class="btn btn-sm btn-light page-btn" data-page="1" style="background-color: #092b61;color: #FFF;">First</button>`;
                html += `<button class="btn btn-sm btn-light page-btn" data-page="${current - 1}" style="background-color: #092b61;color: #FFF;">&laquo; Prev</button>`;
            }

            const pageWindow = 3;
            let start = Math.max(1, current - 1);
            let end = Math.min(totalPages, start + pageWindow - 1);

            if (start > 1) {
                html += `<span class="mx-1">...</span>`;
            }

            for (let i = start; i <= end; i++) {
                html += `<button class="btn btn-sm page-btn ${i === current ? 'btn-primary' : 'btn-light'}" data-page="${i}" style="background-color: #092b61;color: #FFF;">${i}</button>`;
            }

            if (end < totalPages) {
                html += `<span class="mx-1">...</span>`;
            }

            if (current < totalPages) {
                html += `<button class="btn btn-sm btn-light page-btn" data-page="${current + 1}" style="background-color: #092b61;color: #FFF;">Next &raquo;</button>`;
                html += `<button class="btn btn-sm btn-light page-btn" data-page="${totalPages}" style="background-color: #092b61;color: #FFF;">Last</button>`;
            }
        }

        html += '</div>';
        container.append(html);
    }

    container.on('click', '.page-btn', function () {
        currentPage = $(this).data('page');
        fetchData(currentPage, searchInput.val());
    });

    container.on('click', '#jumpPageBtn', function () {
        const page = parseInt($('#jumpPage').val());
        if (page > 0) {
            fetchData(page, searchInput.val());
        }
    });

    searchInput.on('keyup', function () {
        fetchData(1, $(this).val());
    });

    $(document).on('change', `select[id$='-perPage']`, function () {
        const newPerPage = this.value;
        console.log('PerPage dropdown changed to:', newPerPage);
        fetchData(1, searchInput.val(), newPerPage);
    });

    exportBtns.on('click', function () {
        const format = $(this).data('format');
        const url = new URL('/table/export', window.location.origin);
        url.searchParams.set('table', config.table);
        url.searchParams.set('columns', config.columns.join(','));
        url.searchParams.set('headers', config.headers.join(','));
        url.searchParams.set('format', format);
        url.searchParams.set('search', $(config.searchInput).val());

        let conditionsRaw = $(this).data('conditions');
        let conditions = [];

        if (conditionsRaw) {
            try {
                conditions = typeof conditionsRaw === 'string'
                    ? JSON.parse(conditionsRaw)
                    : conditionsRaw;
            } catch (e) {
                console.error('Invalid conditions JSON', e);
            }
        }

        url.searchParams.set('conditions', encodeURIComponent(JSON.stringify(conditions)));
        url.searchParams.set('orderBy', config.orderBy);
        url.searchParams.set('orderType', config.orderType);
        url.searchParams.set('filename', $(this).data('filename'));
        window.open(url.toString(), '_blank');
    });

    fetchData();
}

function copyLink(jobID){
    const text = document.getElementById('textToCopy' + jobID).innerText;
    navigator.clipboard.writeText(text)
        .then(() => {
            document.getElementById('copyStatus' + jobID).innerText = 'Copied!';
            setTimeout(() => {
                document.getElementById('copyStatus' + jobID).innerText = '';
            }, 2000);
        })
        .catch(err => {
            console.error('Failed to copy: ', err);
        });
}