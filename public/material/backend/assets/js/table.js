function loadTable(config) {
    const container = $(config.container);
    const searchInput = $(config.searchInput);
    const exportBtns = $(config.exportButtons);
    let currentPage = 1;

    function fetchData(page = 1, search = '') {
        $.get('/table/fetch', {
            table: config.table,
            columns: config.columns.join(','),
            page: page,
            search: search,
            orderBy: config.orderBy,
            orderType: config.orderType,
            conditions: JSON.stringify(config.conditions || [])
        }, function (res) {
            renderTable(res.data, res.page, res.perPage);
            renderPagination(res.pages, res.page);
        });
    }

    function renderTable(data, currentPage = 1, perPage = 20) {
        let html = '<table class="table table-striped"><thead><tr>';

        config.headers.forEach(header => {
            html += `<th>${header}</th>`;
        });

        html += '</tr></thead><tbody>';

        if(data.length <= 0){
            html += '<tr>';
                var colsCount = (config.columns.length + 2);
                html += '<td style="color:red; text-align:center;" colspan="' + colsCount + '">No records available</td>';
            html += '</tr>';
        }
        
        data.forEach((row, index) => {
            html += '<tr>';

            // Sl. No.
            const slno = ((currentPage - 1) * perPage) + index + 1;
            html += `<td>${slno}</td>`;

            // Data columns
            const visibleCols = config.visibleColumns ?? config.columns;
            // Now use:
            visibleCols.forEach(col => {
                html += `<td>${row[col] ?? ''}</td>`;
            });

            // Actions
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

        $(config.container).html(html);
    }

    function renderPagination(totalPages, current) {
        let html = '<div class="pagination">';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="btn btn-sm page-btn" data-page="${i}">${i}</button>`;
        }
        html += '</div>';
        container.append(html);
    }

    container.on('click', '.page-btn', function () {
        currentPage = $(this).data('page');
        fetchData(currentPage, searchInput.val());
    });

    searchInput.on('keyup', function () {
        fetchData(1, $(this).val());
    });
    
    exportBtns.on('click', function () {
        const format = $(this).data('format');
        const url = new URL('/table/export', window.location.origin);
        url.searchParams.set('table', config.table);
        url.searchParams.set('columns', config.columns.join(','));
        url.searchParams.set('format', format);
        url.searchParams.set('search', $(config.searchInput).val());

        // ✅ NEW: Get conditions from button’s data attribute
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