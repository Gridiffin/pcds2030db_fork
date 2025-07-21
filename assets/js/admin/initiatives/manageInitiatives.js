import '../../../css/admin/initiatives/manage_initiatives.css';

function formatDate(dateStr) {
    const d = new Date(dateStr);
    if (isNaN(d)) return '';
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function renderTable(initiatives, columns) {
    if (!initiatives.length) {
        return `<div class="text-center py-5">
            <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No initiatives found</h5>
            <p class="text-muted">No initiatives match your search criteria.</p>
            <a href="initiatives.php" class="btn btn-outline-primary">
                <i class="fas fa-undo me-1"></i>Clear Filters
            </a>
        </div>`;
    }
    let rows = initiatives.map(initiative => {
        return `<tr data-initiative-id="${initiative[columns.id]}">
            <td>
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1">
                            ${initiative[columns.number] ? `<span class="badge bg-primary me-2">${initiative[columns.number]}</span>` : ''}
                            ${initiative[columns.name] || ''}
                        </div>
                        ${initiative[columns.description] ? `<div class="text-muted small" style="line-height: 1.4;">${initiative[columns.description].length > 120 ? initiative[columns.description].substring(0, 120) + '...' : initiative[columns.description]}</div>` : ''}
                    </div>
                </div>
            </td>
            <td class="text-center"><span class="badge bg-secondary">${initiative.program_count || 0} total</span></td>
            <td>${(initiative[columns.start_date] || initiative[columns.end_date]) ? `<div class="small">${initiative[columns.start_date] && initiative[columns.end_date] ? `<i class='fas fa-calendar-alt me-1 text-muted'></i>${formatDate(initiative[columns.start_date])} - ${formatDate(initiative[columns.end_date])}` : initiative[columns.start_date] ? `<i class='fas fa-play me-1 text-success'></i>Started: ${formatDate(initiative[columns.start_date])}` : `<i class='fas fa-flag-checkered me-1 text-warning'></i>Due: ${formatDate(initiative[columns.end_date])}`}</div>` : `<span class="text-muted small"><i class="fas fa-calendar-times me-1"></i>No timeline</span>`}</td>
            <td>${initiative[columns.is_active] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td class="text-center"><a href="edit.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm me-1" title="Edit Initiative"><i class="fas fa-edit"></i></a><a href="view_initiative.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm" title="View Initiative Details"><i class="fas fa-eye"></i></a></td>
        </tr>`;
    }).join('');
    return `<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Initiative</th><th class="text-center">Total Programs</th><th>Timeline</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>${rows}</tbody></table></div>`;
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const tableContainer = document.querySelector('.card-body.p-0');
    const loader = document.createElement('div');
    loader.className = 'text-center py-4';
    loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

    function getColumns() {
        // These should match the PHP column names
        return {
            id: document.body.dataset.initiativeIdCol || 'id',
            name: document.body.dataset.initiativeNameCol || 'name',
            number: document.body.dataset.initiativeNumberCol || 'number',
            description: document.body.dataset.initiativeDescriptionCol || 'description',
            start_date: document.body.dataset.startDateCol || 'start_date',
            end_date: document.body.dataset.endDateCol || 'end_date',
            is_active: document.body.dataset.isActiveCol || 'is_active',
        };
    }

    function fetchInitiatives(params) {
        tableContainer.innerHTML = '';
        tableContainer.appendChild(loader);
        const url = new URL('/app/ajax/admin_manage_initiatives.php', window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        fetch(url)
            .then(res => res.json())
            .then(data => {
                tableContainer.innerHTML = '';
                if (data.success) {
                    tableContainer.innerHTML = renderTable(data.initiatives, getColumns());
                } else {
                    tableContainer.innerHTML = `<div class='alert alert-danger'>${data.error || 'Failed to load initiatives.'}</div>`;
                }
            })
            .catch(() => {
                tableContainer.innerHTML = `<div class='alert alert-danger'>Server error. Please try again.';</div>`;
            });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            const params = {};
            for (const [key, value] of formData.entries()) {
                params[key] = value;
            }
            fetchInitiatives(params);
        });
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { formatDate, renderTable };
} 