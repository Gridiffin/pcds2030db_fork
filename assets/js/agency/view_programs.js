/**
 * View Programs Functionality
 * Handles filtering and interactions on the programs list page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete functionality
    initDeleteButtons();
    
    // Initialize table sorting
    const tables = ['draftProgramsTable', 'finalizedProgramsTable'];
    tables.forEach(tableId => {
        const table = document.getElementById(tableId);
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
            });
        });
    });
    
    // Initialize draft table filters
    const draftSearchInput = document.getElementById('draftProgramSearch');
    const draftRatingFilter = document.getElementById('draftRatingFilter');
    const draftTypeFilter = document.getElementById('draftTypeFilter');
    const resetDraftFiltersBtn = document.getElementById('resetDraftFilters');
    
    if (draftSearchInput) draftSearchInput.addEventListener('keyup', function() { applyFilters('draft'); });
    if (draftRatingFilter) draftRatingFilter.addEventListener('change', function() { applyFilters('draft'); });
    if (draftTypeFilter) draftTypeFilter.addEventListener('change', function() { applyFilters('draft'); });
    
    if (resetDraftFiltersBtn) {
        resetDraftFiltersBtn.addEventListener('click', function() {
            if (draftSearchInput) draftSearchInput.value = '';
            if (draftRatingFilter) draftRatingFilter.value = '';
            if (draftTypeFilter) draftTypeFilter.value = '';
            applyFilters('draft');
        });
    }
    
    // Initialize finalized table filters
    const finalizedSearchInput = document.getElementById('finalizedProgramSearch');
    const finalizedRatingFilter = document.getElementById('finalizedRatingFilter');
    const finalizedTypeFilter = document.getElementById('finalizedTypeFilter');
    const resetFinalizedFiltersBtn = document.getElementById('resetFinalizedFilters');
    
    if (finalizedSearchInput) finalizedSearchInput.addEventListener('keyup', function() { applyFilters('finalized'); });
    if (finalizedRatingFilter) finalizedRatingFilter.addEventListener('change', function() { applyFilters('finalized'); });
    if (finalizedTypeFilter) finalizedTypeFilter.addEventListener('change', function() { applyFilters('finalized'); });
    
    if (resetFinalizedFiltersBtn) {
        resetFinalizedFiltersBtn.addEventListener('click', function() {
            if (finalizedSearchInput) finalizedSearchInput.value = '';
            if (finalizedRatingFilter) finalizedRatingFilter.value = '';
            if (finalizedTypeFilter) finalizedTypeFilter.value = '';
            applyFilters('finalized');
        });
    }
    
    // Initialize program submission buttons
    document.querySelectorAll('.submit-program').forEach(button => {
        button.addEventListener('click', function() {
            const programId = this.getAttribute('data-program-id');

            fetch('ajax/submit_program.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `program_id=${programId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the program.');
            });
        });
    });
});

// Handle filtering for specific table
function applyFilters(tableType) {
    const tableId = tableType === 'draft' ? 'draftProgramsTable' : 'finalizedProgramsTable';
    const filterBadgesId = tableType === 'draft' ? 'draftFilterBadges' : 'finalizedFilterBadges';
    
    const searchInput = document.getElementById(tableType + 'ProgramSearch');
    const ratingFilter = document.getElementById(tableType + 'RatingFilter');
    const typeFilter = document.getElementById(tableType + 'TypeFilter');
    
    const searchText = searchInput ? searchInput.value.toLowerCase() : '';
    const ratingValue = ratingFilter ? ratingFilter.value : '';
    const typeValue = typeFilter ? typeFilter.value : '';
    
    // Clear existing filter badges
    const filterBadgesContainer = document.getElementById(filterBadgesId);
    if (filterBadgesContainer) {
        filterBadgesContainer.innerHTML = '';
    }
    
    // Create filter badges if filters are applied
    if (searchText || ratingValue || typeValue) {
        let badgesHtml = '<span class="badge-label">Active filters:</span>';
        
        if (searchText) {
            badgesHtml += `<span class="filter-badge">"${searchText}" <i class="fas fa-times remove-filter" data-filter="search" data-table="${tableType}"></i></span>`;
        }
        
        if (ratingValue) {
            const ratingLabel = document.getElementById(tableType + 'RatingFilter').options[document.getElementById(tableType + 'RatingFilter').selectedIndex].text;
            badgesHtml += `<span class="filter-badge">${ratingLabel} <i class="fas fa-times remove-filter" data-filter="rating" data-table="${tableType}"></i></span>`;
        }
        
        if (typeValue) {
            const typeLabel = document.getElementById(tableType + 'TypeFilter').options[document.getElementById(tableType + 'TypeFilter').selectedIndex].text;
            badgesHtml += `<span class="filter-badge">${typeLabel} <i class="fas fa-times remove-filter" data-filter="type" data-table="${tableType}"></i></span>`;
        }
        
        if (filterBadgesContainer) {
            filterBadgesContainer.innerHTML = badgesHtml;
            
            // Add click handlers for filter badge removal
            filterBadgesContainer.querySelectorAll('.remove-filter').forEach(icon => {
                icon.addEventListener('click', function() {
                    const filterType = this.getAttribute('data-filter');
                    const tableType = this.getAttribute('data-table');
                    
                    if (filterType === 'search') {
                        document.getElementById(tableType + 'ProgramSearch').value = '';
                    } else if (filterType === 'rating') {
                        document.getElementById(tableType + 'RatingFilter').value = '';
                    } else if (filterType === 'type') {
                        document.getElementById(tableType + 'TypeFilter').value = '';
                    }
                    
                    applyFilters(tableType);
                });
            });
        }
    }
    
    // Apply filters to table rows
    const tableRows = document.querySelectorAll(`#${tableId} tbody tr`);
    tableRows.forEach(row => {
        const programName = row.querySelector('td:first-child .fw-medium')?.textContent.toLowerCase() || '';
        const ratingText = row.querySelector('td:nth-child(2) .badge')?.textContent.toLowerCase() || '';
        const programType = row.getAttribute('data-program-type') || '';
        
        // Map display text back to status values for comparison
        const ratingMap = {
            'monthly target achieved': 'target-achieved',
            'on track for year': 'on-track-yearly',
            'severe delays': 'severe-delay',
            'not started': 'not-started'
        };
        
        const normalizedRating = ratingMap[ratingText] || ratingText;
        
        // Apply all filters
        let showRow = true;
        
        // Text search filter
        if (searchText && !programName.includes(searchText)) {
            showRow = false;
        }
        
        // Rating filter
        if (ratingValue && normalizedRating !== ratingValue) {
            showRow = false;
        }
        
        // Type filter
        if (typeValue && programType !== typeValue) {
            showRow = false;
        }
        
        // Show or hide the row
        row.style.display = showRow ? '' : 'none';
    });
    
    // Update "no results" message if needed
    updateNoResultsMessage(tableId);
}

// Function to update "no results" message when all filtered rows are hidden
function updateNoResultsMessage(tableId) {
    const tbody = document.querySelector(`#${tableId} tbody`);
    if (!tbody) return;
    
    // Check if there are any visible rows
    const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => 
        row.style.display !== 'none'
    );
    
    // If all rows are either filtered out or there are no rows originally
    if (visibleRows.length === 0) {
        // Check if we already have a "no results" row
        let noResultsRow = tbody.querySelector('.no-results-row');
        
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = '<td colspan="4" class="text-center py-4">No matching programs found.</td>';
            tbody.appendChild(noResultsRow);
        }
    } else {
        // Remove any existing "no results" row if we have visible rows
        const noResultsRow = tbody.querySelector('.no-results-row');
        if (noResultsRow) {
            noResultsRow.remove();
        }
    }
}

/**
 * Initialize delete buttons functionality
 */
function initDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-program-btn');
    const modal = document.getElementById('deleteModal');
    
    if (!modal || !deleteButtons.length) return;
    
    const programNameDisplay = document.getElementById('program-name-display');
    const programIdInput = document.getElementById('program-id-input');
    
    if (!programNameDisplay || !programIdInput) return;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const programId = this.getAttribute('data-id');
            const programName = this.getAttribute('data-name');
            
            programNameDisplay.textContent = programName;
            programIdInput.value = programId;
            
            // Show the modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        });
    });
}
