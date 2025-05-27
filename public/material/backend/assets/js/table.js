function loadTable(config) {
    const container = $(config.container);
    const searchInput = $(config.searchInput);
    const exportBtns = $(config.exportButtons);
    let currentPage = 1;

    function fetchData(page = 1, search = '', perPageOverride = null) {
        $('#table-overlay-loader').fadeIn();
        startDotAnimation();

        const perPage = perPageOverride || $(`select[id$='-perPage']`).val() || 50;

        $.ajax({
            url: '/table/fetch',
            method: 'GET',
            data: {
                table: config.table,
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
                    const imageUrl = val || 'https://hjadmin.itiffyconsultants.xyz/public/uploads/no-image.jpg';
                    html += `<td>
                        <a href="${imageUrl}" data-lightbox="table-images" data-title="${row.name ?? ''}">
                            <img src="${imageUrl}" alt="Image" class="img-thumbnail mt-3" style="width: 75px; height: 50px; cursor: zoom-in;">
                        </a>
                    </td>`;
                } else {
                    html += `<td>${val}</td>`;
                }
            });

            if (config.showActions) {
                const status = row[config.statusColumn];
                const encodedId = row.encoded_id;
                const base = '/' + config.routePrefix;

                html += `<td>
                    <a href="${base}/edit/${encodedId}" class="btn btn-sm btn-primary me-1" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>`;

                if (status == 1) {
                    html += `<a href="${base}/change-status/${encodedId}" class="btn btn-sm btn-success me-1" title="Deactivate">
                        <i class="fa-solid fa-check"></i>
                    </a>`;
                } else {
                    html += `<a href="${base}/change-status/${encodedId}" class="btn btn-sm btn-warning me-1" title="Activate">
                        <i class="fas fa-times"></i>
                    </a>`;
                }

                html += `<a href="${base}/delete/${encodedId}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </a></td>`;
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

        // Jump to input
        // html += `
        //     <span class="ms-2">Jump to page:</span>
        //     <input type="number" id="jumpPage" min="1" max="${totalPages}" style="width: 60px;" class="form-control form-control-sm d-inline-block">
        //     <button id="jumpPageBtn" class="btn btn-sm btn-secondary">Go</button>
        // `;

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