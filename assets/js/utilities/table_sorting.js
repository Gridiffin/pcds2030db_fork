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
                    // Find the sort icon specifically (the one with fa-sort, fa-sort-up, or fa-sort-down)
                    const sortIcon = h.querySelector('i[class*="fa-sort"]');
                    if (sortIcon && h === this) {
                        sortIcon.className = currentSort.direction === 'asc' 
                            ? 'fas fa-sort-up ms-1' 
                            : 'fas fa-sort-down ms-1';
                    } else if (sortIcon) {
                        sortIcon.className = 'fas fa-sort ms-1';
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

    const rows = Array.from(tbody.querySelectorAll('tr:not(.no-filter-results):not(.no-results-row)'));
    if (rows.length <= 1) return;

    const sortedRows = rows.sort((a, b) => {
        if (sortBy === 'name') {
            const aText = a.querySelector('td:first-child .fw-medium')?.textContent.trim().toLowerCase() || '';
            const bText = b.querySelector('td:first-child .fw-medium')?.textContent.trim().toLowerCase() || '';
            return direction === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
        } 
        else if (sortBy === 'initiative') {
            // Sort by initiative name (column 2) using data attributes
            const aInitiativeElement = a.querySelector('td:nth-child(2)');
            const bInitiativeElement = b.querySelector('td:nth-child(2)');
            
            const aInitiative = aInitiativeElement?.getAttribute('data-initiative') || 'zzz_no_initiative';
            const bInitiative = bInitiativeElement?.getAttribute('data-initiative') || 'zzz_no_initiative';
            
            return direction === 'asc' ? aInitiative.localeCompare(bInitiative) : bInitiative.localeCompare(aInitiative);
        }
        else if (sortBy === 'status') {
            // Sort by status (column 3) using data attributes
            const aStatusElement = a.querySelector('td:nth-child(3)');
            const bStatusElement = b.querySelector('td:nth-child(3)');
            
            const aOrder = parseInt(aStatusElement?.getAttribute('data-status-order') || '999');
            const bOrder = parseInt(bStatusElement?.getAttribute('data-status-order') || '999');
            
            return direction === 'asc' ? aOrder - bOrder : bOrder - aOrder;
        }
        else if (sortBy === 'date') {
            // Sort by date (column 4)
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
