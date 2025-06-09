/**
 * All Sectors Programs Filtering and Sorting
 * Client-side filtering and sorting for the view_all_sectors.php page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sorting for programs table if present
    const programsTable = document.getElementById('programsTable');
    if (programsTable) {
        const sortableHeaders = programsTable.querySelectorAll('th.sortable');
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

                sortTableRows(programsTable, sortBy, newDirection);
            });
        });
    }

    // Initialize filters
    const searchInput = document.getElementById('searchFilter');
    const ratingFilter = document.getElementById('ratingFilter');
    const agencyFilter = document.getElementById('agencyFilter');
    const resetFiltersBtn = document.getElementById('resetFilters');

    if (searchInput) {
        searchInput.addEventListener('keyup', applyFilters);
        searchInput.addEventListener('input', applyFilters);
    }
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
        // Determine active tab from URL parameter 'tab'
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'programs';

        console.log('Active tab:', activeTab);
        console.log('Search input value:', searchInput ? searchInput.value : '');

        if (activeTab === 'programs') {
            const searchText = searchInput ? searchInput.value.toLowerCase() : '';
            const ratingValue = ratingFilter ? ratingFilter.value : '';
            const agencyValue = agencyFilter ? agencyFilter.value : '';

            const tbody = document.querySelector('#programsTable tbody');
            if (!tbody) return;
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

            updateNoResultsMessage(tbody, 7, 'No matching programs found.');
        } else if (activeTab === 'outcomes') {
            const searchText = searchInput ? searchInput.value.toLowerCase() : '';
            const statusValue = ratingFilter ? ratingFilter.value.toLowerCase() : '';
            const createdByValue = agencyFilter ? agencyFilter.value.toLowerCase() : '';

            const tbody = document.querySelector('#outcomesTable tbody');
            if (!tbody) return;
            const rows = tbody.querySelectorAll('tr');

            rows.forEach(row => {
                const tableName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                const createdBy = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const status = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';

                let showRow = true;

                if (searchText && !tableName.includes(searchText)) {
                    showRow = false;
                }

                if (statusValue && !status.includes(statusValue)) {
                    showRow = false;
                }

                if (createdByValue && !createdBy.includes(createdByValue)) {
                    showRow = false;
                }

                row.style.display = showRow ? '' : 'none';
            });

            updateNoResultsMessage(tbody, 5, 'No matching outcomes found.');
        }
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
    function updateNoResultsMessage(tbody, colspan, message) {
        const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
        let noResultsRow = tbody.querySelector('.no-results-row');

        if (visibleRows.length === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = '<td colspan="' + colspan + '" class="text-center py-4">' + message + '</td>';
                tbody.appendChild(noResultsRow);
            }
        } else {
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    }
});
