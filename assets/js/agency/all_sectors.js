/**
 * All Sectors View Functionality
 * 
 * Handles searching and filtering for the All Sectors view
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search and filtering
    initializeFiltering();
    
    // Remove export functionality initialization since export button is removed
});

/**
 * Initialize search and filtering functionality
 */
function initializeFiltering() {
    const searchInput = document.getElementById('searchPrograms');
    const statusFilter = document.getElementById('statusFilter');
    const sectorFilter = document.getElementById('sectorFilter');
    const resetButton = document.getElementById('resetFilters');
    const filterIndicators = document.getElementById('activeFilters');
    const filterBadges = document.getElementById('filterBadges');
    const noResultsMessage = document.getElementById('noResultsMessage');
    const programCount = document.getElementById('programCount');
    
    // Function to apply all filters
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusValue = statusFilter.value.toLowerCase();
        const sectorValue = sectorFilter.value;
        
        // Check if any filters are active
        const hasActiveFilters = searchTerm || statusValue || sectorValue;
        
        // Update filter indicators visibility
        filterIndicators.style.display = hasActiveFilters ? 'block' : 'none';
        
        // Update filter badges
        filterBadges.innerHTML = '';
        
        if (searchTerm) {
            addFilterBadge('Search', searchTerm, () => {
                searchInput.value = '';
                applyFilters();
            });
        }
        
        if (statusValue) {
            const statusText = statusFilter.options[statusFilter.selectedIndex].text;
            addFilterBadge('Status', statusText, () => {
                statusFilter.value = '';
                applyFilters();
            });
        }
        
        if (sectorValue) {
            const sectorText = sectorFilter.options[sectorFilter.selectedIndex].text;
            addFilterBadge('Sector', sectorText.replace(' (Your Sector)', ''), () => {
                sectorFilter.value = '';
                applyFilters();
            });
        }
        
        // Apply filters to all rows in the table
        const allRows = document.querySelectorAll('#programsTable tbody tr');
        
        // Skip filtering if there's a "No programs found" row
        if (allRows.length === 1 && allRows[0].querySelector('td[colspan]')) {
            return;
        }
        
        let visibleCount = 0;
        
        allRows.forEach(row => {
            // Skip rows that are just "No programs found" messages
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const programName = row.getAttribute('data-program-name')?.toLowerCase() || '';
            const programDesc = row.querySelector('td:first-child .small')?.textContent.toLowerCase() || '';
            const status = row.getAttribute('data-status')?.toLowerCase() || '';
            const sectorId = row.getAttribute('data-sector-id') || '';
            
            // Check if row matches all filters
            const matchesSearch = !searchTerm || 
                programName.includes(searchTerm) || 
                programDesc.includes(searchTerm);
                
            const matchesStatus = !statusValue || status === statusValue;
            
            const matchesSector = !sectorValue || sectorId === sectorValue;
            
            // Show/hide row based on filters
            if (matchesSearch && matchesStatus && matchesSector) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update the count display
        if (programCount) {
            programCount.textContent = `${visibleCount} Program${visibleCount !== 1 ? 's' : ''}`;
        }
        
        // Show/hide no results message
        if (noResultsMessage) {
            noResultsMessage.style.display = (visibleCount === 0 && hasActiveFilters) ? 'block' : 'none';
        }
        
        // If we have a normal table (not a "no data" message) but no visible rows after filtering
        const tableBody = document.querySelector('#programsTable tbody');
        
        // Check for existing "No results from filter" message row
        let noFilterResultsRow = tableBody.querySelector('tr.no-filter-results');
        
        if (visibleCount === 0 && hasActiveFilters) {
            // Add a "no filter results" message if it doesn't exist
            if (!noFilterResultsRow) {
                const colSpan = document.querySelector('#programsTable thead tr').children.length;
                noFilterResultsRow = document.createElement('tr');
                noFilterResultsRow.className = 'no-filter-results';
                noFilterResultsRow.innerHTML = `
                    <td colspan="${colSpan}" class="text-center py-4">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-filter me-2"></i>
                            No programs match your filter criteria. Try adjusting your filters.
                        </div>
                    </td>
                `;
                tableBody.appendChild(noFilterResultsRow);
            } else {
                noFilterResultsRow.style.display = '';
            }
        } else if (noFilterResultsRow) {
            // Hide the "no filter results" message if it exists but we have results
            noFilterResultsRow.style.display = 'none';
        }
    }
    
    // Helper function to add filter badge
    function addFilterBadge(type, value, clearFn) {
        const badge = document.createElement('span');
        badge.className = 'badge rounded-pill bg-light text-dark border';
        badge.innerHTML = `${type}: ${value} <button type="button" class="btn-close" aria-label="Remove filter"></button>`;
        
        // Add click handler to clear this filter
        badge.querySelector('.btn-close').addEventListener('click', clearFn);
        
        filterBadges.appendChild(badge);
    }
    
    // Add event listeners for filters
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (sectorFilter) sectorFilter.addEventListener('change', applyFilters);
    
    // Reset all filters
    if (resetButton) {
        resetButton.addEventListener('click', () => {
            searchInput.value = '';
            statusFilter.value = '';
            sectorFilter.value = '';
            applyFilters();
        });
    }
    
    // Only apply initial filters if explicit selections have been made
    // This prevents auto-filtering to current sector on page load
    if (searchInput.value || statusFilter.value || sectorFilter.value !== '') {
        applyFilters();
    } else {
        // Just update the program count
        if (programCount) {
            const totalPrograms = document.querySelectorAll('#programsTable tbody tr:not([style*="display: none"])').length;
            programCount.textContent = `${totalPrograms} Program${totalPrograms !== 1 ? 's' : ''}`;
        }
    }
}

// Remove the initializeExport function and other export-related functionality since it's no longer needed
