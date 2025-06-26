/**
 * Table Sorting Utility
 * Adds sorting functionality to tables with sortable headers
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find all tables with sortable headers
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const sortableHeaders = table.querySelectorAll('th.sortable');
        if (!sortableHeaders.length) return;

        // Current sort state for this table
        const currentSort = {
            column: null,
            direction: 'asc'
        };

        // Add click handlers to sortable headers
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sortBy = this.getAttribute('data-sort');
                
                // Update sort direction
                if (currentSort.column === sortBy) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = sortBy;
                    currentSort.direction = 'asc';
                }
                
                // Update header icons
                sortableHeaders.forEach(h => {
                    const icon = h.querySelector('i');
                    if (h === this) {
                        icon.className = currentSort.direction === 'asc' 
                            ? 'fas fa-sort-up ms-1' 
                            : 'fas fa-sort-down ms-1';
                    } else {
                        icon.className = 'fas fa-sort ms-1';
                    }
                });
                
                // Sort the table
                sortTable(table, sortBy, currentSort.direction);
            });
        });
    });
});

/**
 * Sort a table by the specified column and direction
 */
function sortTable(table, sortBy, direction) {
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr:not(.no-filter-results)'));
    if (rows.length <= 1) return;

    const sortedRows = rows.sort((a, b) => {
        if (sortBy === 'name') {
            const aText = a.querySelector('td:first-child .fw-medium')?.textContent.trim().toLowerCase() || '';
            const bText = b.querySelector('td:first-child .fw-medium')?.textContent.trim().toLowerCase() || '';
            return direction === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
        } 
        else if (sortBy === 'rating') {
            const statusOrder = {
                'monthly target achieved': 1,
                'on track for year': 2,
                'severe delays': 3,
                'not started': 4
            };
            const aStatus = a.querySelector('td:nth-child(2) .badge')?.textContent.trim().toLowerCase() || '';
            const bStatus = b.querySelector('td:nth-child(2) .badge')?.textContent.trim().toLowerCase() || '';
            const aRank = statusOrder[aStatus] || 999;
            const bRank = statusOrder[bStatus] || 999;
            return direction === 'asc' ? aRank - bRank : bRank - aRank;
        }
        else if (sortBy === 'date') {
            // Use data-date attribute if available for reliable sorting
            const aDateCell = a.querySelector('td:nth-child(4) span[data-date]');
            const bDateCell = b.querySelector('td:nth-child(4) span[data-date]');
            const aDate = aDateCell ? new Date(aDateCell.getAttribute('data-date')) : new Date(0);
            const bDate = bDateCell ? new Date(bDateCell.getAttribute('data-date')) : new Date(0);
            return direction === 'asc' ? aDate - bDate : bDate - aDate;
        }
        return 0;
    });

    // Reorder rows in the DOM
    sortedRows.forEach(row => tbody.appendChild(row));
}
