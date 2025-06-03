/**
 * All Sectors View Functionality
 * 
 * Handles searching and filtering for the All Sectors view
 */
document.addEventListener('DOMContentLoaded', function() {
    // Disable client-side filtering to avoid conflict with server-side filtering
    // initializeFiltering();
});

/**
 * Initialize search and filtering functionality
 */
function initializeFiltering() {
    // Fix: Changed element IDs to match what's in the HTML
    const searchInput = document.getElementById('search');
    const ratingFilter = document.getElementById('rating') || document.getElementById('status'); // Support both for backward compatibility
    const sectorFilter = document.getElementById('sector_id');
    const resetButton = document.getElementById('resetFilters');
    const filterIndicators = document.getElementById('activeFilters');
    const filterBadges = document.getElementById('filterBadges');
    const noResultsMessage = document.getElementById('noResultsMessage');
    const programCount = document.getElementById('programCount');
    
    // Skip initialization if elements don't exist on this page
    if (!searchInput && !ratingFilter && !sectorFilter) {
        console.log('Filtering elements not found - might be on a different view');
        return;
    }
    
    // Function to apply all filters
    function applyFilters() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const ratingValue = ratingFilter ? ratingFilter.value.toLowerCase() : '';
        const sectorValue = sectorFilter ? sectorFilter.value : '';
        
        // Check if any filters are active
        const hasActiveFilters = searchTerm || ratingValue || sectorValue;
        
        // Update filter indicators visibility
        if (filterIndicators) {
            filterIndicators.style.display = hasActiveFilters ? 'block' : 'none';
        }
        
        // Update filter badges
        if (filterBadges) {
            filterBadges.innerHTML = '';
            
            if (searchTerm) {
                addFilterBadge('Search', searchTerm, () => {
                    searchInput.value = '';
                    applyFilters();
                });
            }
            
            if (ratingValue) {
                const ratingText = ratingFilter.options[ratingFilter.selectedIndex].text;
                addFilterBadge('Rating', ratingText, () => {
                    ratingFilter.value = '';
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
        }
        
        // Apply filters to all rows in the table
        const programsTable = document.getElementById('programsTable');
        if (!programsTable) return;
        
        const allRows = programsTable.querySelectorAll('tbody tr');
        
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
            
            const programName = row.querySelector('td:first-child .fw-medium')?.textContent.toLowerCase() || '';
            const programDesc = row.querySelector('td:first-child .small')?.textContent.toLowerCase() || '';
            const rating = row.querySelector('td:nth-child(4) .badge')?.textContent.toLowerCase() || '';
            const sectorText = row.querySelector('td:nth-child(3) .badge')?.textContent.toLowerCase() || '';
            
            // Check if row matches all filters
            const matchesSearch = !searchTerm || 
                programName.includes(searchTerm) || 
                programDesc.includes(searchTerm);
                
            const matchesRating = !ratingValue || rating.toLowerCase().includes(ratingValue.toLowerCase());
            
            const matchesSector = !sectorValue || row.classList.contains(`sector-${sectorValue}`);
            
            // Show/hide row based on filters
            if (matchesSearch && matchesRating && matchesSector) {
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
        const tableBody = programsTable.querySelector('tbody');
        
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
        if (!filterBadges) return;
        
        const badge = document.createElement('span');
        badge.className = 'badge rounded-pill bg-light text-dark border';
        badge.innerHTML = `${type}: ${value} <button type="button" class="btn-close" aria-label="Remove filter"></button>`;
        
        // Add click handler to clear this filter
        badge.querySelector('.btn-close').addEventListener('click', clearFn);
        
        filterBadges.appendChild(badge);
    }
    
    // Add event listeners for filters
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (ratingFilter) ratingFilter.addEventListener('change', applyFilters);
    if (sectorFilter) sectorFilter.addEventListener('change', applyFilters);
    
    // Reset all filters
    if (resetButton) {
        resetButton.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (ratingFilter) ratingFilter.value = '';
            if (sectorFilter) sectorFilter.value = '';
            applyFilters();
        });
    }
    
    // Only apply initial filters if explicit selections have been made
    // This prevents auto-filtering to current sector on page load
    if ((searchInput && searchInput.value) || 
        (ratingFilter && ratingFilter.value) || 
        (sectorFilter && sectorFilter.value !== '')) {
        applyFilters();
    } else {
        // Just update the program count
        if (programCount) {
            const programsTable = document.getElementById('programsTable');
            if (programsTable) {
                const totalPrograms = programsTable.querySelectorAll('tbody tr:not([style*="display: none"])').length;
                programCount.textContent = `${totalPrograms} Program${totalPrograms !== 1 ? 's' : ''}`;
            }
        }
    }
}