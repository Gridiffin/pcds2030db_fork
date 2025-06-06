/**
 * All Sectors Programs Filtering and Sorting
 * Client-side filtering and sorting for the view_all_sectors.php page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sorting
    const table = document.getElementById('programsTable');
    if (!table) return;

    const sortableHeaders = table.querySelectorAll('th.sortable');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const sortBy = this.getAttribute('data-sort');
            const currentDirection = this.getAttribute('data-direction') || 'asc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';

            // Update all icons in this table
            sortableHeaders.forEach(h => {
                const icon = h.querySelector('i');
                if (h === this) {
                    icon.className = newDirection === 'asc' 
                        ? 'fas fa-sort-up ms-1' 
                        : 'fas fa-sort-down ms-1';
                } else {
                    icon.className = 'fas fa-sort ms-1';
                    h.removeAttribute('data-direction');
                }
            });

            // Update direction attribute
            this.setAttribute('data-direction', newDirection);

            sortTableRows(table, sortBy, newDirection);
        });
    });

    // Initialize filters
    const searchInput = document.getElementById('searchFilter');
    const ratingFilter = document.getElementById('ratingFilter');
    const agencyFilter = document.getElementById('agencyFilter');
    const resetFiltersBtn = document.getElementById('resetFilters');

    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
    if (ratingFilter) ratingFilter.addEventListener('change', applyFilters);
    if (agencyFilter) agencyFilter.addEventListener('change', applyFilters);

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (ratingFilter) ratingFilter.value = '';
            if (agencyFilter) agencyFilter.value = '';
            applyFilters();
        });
    }

    // Initial filter application
    applyFilters();

    // Function to apply filters to table rows
    function applyFilters() {
        const searchText = searchInput ? searchInput.value.toLowerCase() : '';
        const ratingValue = ratingFilter ? ratingFilter.value : '';
        const agencyValue = agencyFilter ? agencyFilter.value : '';

        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const programName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
            const rating = row.getAttribute('data-rating') || '';
            const agency = row.getAttribute('data-agency') || '';

            let showRow = true;

            if (searchText && !programName.includes(searchText)) {
                showRow = false;
            }

            if (ratingValue && rating !== ratingValue) {
                showRow = false;
            }

            if (agencyValue && agency !== agencyValue) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });

        updateNoResultsMessage(tbody);
    }

    // Function to sort table rows
    function sortTableRows(table, sortBy, direction) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');

        rows.sort((a, b) => {
            let aValue = '';
            let bValue = '';

            switch (sortBy) {
                case 'name':
                    aValue = a.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                    bValue = b.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                    break;
                case 'agency':
                    aValue = a.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    bValue = b.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    break;
                case 'sector':
                    aValue = a.querySelector('td:nth-child(3) .badge')?.textContent.toLowerCase() || '';
                    bValue = b.querySelector('td:nth-child(3) .badge')?.textContent.toLowerCase() || '';
                    break;
                case 'rating':
                    aValue = a.querySelector('td:nth-child(4) .badge')?.textContent.toLowerCase() || '';
                    bValue = b.querySelector('td:nth-child(4) .badge')?.textContent.toLowerCase() || '';
                    break;
                case 'date':
                    aValue = a.querySelector('td:nth-child(6)')?.textContent.toLowerCase() || '';
                    bValue = b.querySelector('td:nth-child(6)')?.textContent.toLowerCase() || '';
                    break;
                default:
                    break;
            }

            if (aValue < bValue) return direction === 'asc' ? -1 : 1;
            if (aValue > bValue) return direction === 'asc' ? 1 : -1;
            return 0;
        });

            // Append sorted rows back to tbody
            rows.forEach(row => tbody.appendChild(row));
            
            // Initialize tooltips for newly sorted rows
            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

    }

    // Function to update "no results" message
    function updateNoResultsMessage(tbody) {
        const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
        let noResultsRow = tbody.querySelector('.no-results-row');

        if (visibleRows.length === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = '<td colspan="7" class="text-center py-4">No matching programs found.</td>';
                tbody.appendChild(noResultsRow);
            }
        } else {
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    }
});
