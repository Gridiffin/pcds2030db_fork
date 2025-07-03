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
    const draftInitiativeFilter = document.getElementById('draftInitiativeFilter');
    const resetDraftFiltersBtn = document.getElementById('resetDraftFilters');
    
    if (draftSearchInput) draftSearchInput.addEventListener('keyup', function() { applyFilters('draft'); });
    if (draftRatingFilter) draftRatingFilter.addEventListener('change', function() { applyFilters('draft'); });
    if (draftTypeFilter) draftTypeFilter.addEventListener('change', function() { applyFilters('draft'); });
    if (draftInitiativeFilter) draftInitiativeFilter.addEventListener('change', function() { applyFilters('draft'); });
    
    if (resetDraftFiltersBtn) {
        resetDraftFiltersBtn.addEventListener('click', function() {
            if (draftSearchInput) draftSearchInput.value = '';
            if (draftRatingFilter) draftRatingFilter.value = '';
            if (draftTypeFilter) draftTypeFilter.value = '';
            if (draftInitiativeFilter) draftInitiativeFilter.value = '';
            applyFilters('draft');
        });
    }
      // Initialize finalized table filters
    const finalizedSearchInput = document.getElementById('finalizedProgramSearch');
    const finalizedRatingFilter = document.getElementById('finalizedRatingFilter');
    const finalizedTypeFilter = document.getElementById('finalizedTypeFilter');
    const finalizedInitiativeFilter = document.getElementById('finalizedInitiativeFilter');
    const resetFinalizedFiltersBtn = document.getElementById('resetFinalizedFilters');
    
    if (finalizedSearchInput) finalizedSearchInput.addEventListener('keyup', function() { applyFilters('finalized'); });
    if (finalizedRatingFilter) finalizedRatingFilter.addEventListener('change', function() { applyFilters('finalized'); });
    if (finalizedTypeFilter) finalizedTypeFilter.addEventListener('change', function() { applyFilters('finalized'); });
    if (finalizedInitiativeFilter) finalizedInitiativeFilter.addEventListener('change', function() { applyFilters('finalized'); });
    
    if (resetFinalizedFiltersBtn) {
        resetFinalizedFiltersBtn.addEventListener('click', function() {
            if (finalizedSearchInput) finalizedSearchInput.value = '';
            if (finalizedRatingFilter) finalizedRatingFilter.value = '';
            if (finalizedTypeFilter) finalizedTypeFilter.value = '';
            if (finalizedInitiativeFilter) finalizedInitiativeFilter.value = '';
            applyFilters('finalized');
        });
    }
    
    // Initialize program submission buttons
    document.querySelectorAll('.submit-program').forEach(button => {
        button.addEventListener('click', function() {
            const programId = this.getAttribute('data-program-id');

            fetch('../ajax/submit_program.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `program_id=${programId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else if (data.status === 'info') {
                    showToast('Info', data.message, 'info');
                } else {
                    showToast('Error', data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred while submitting the program.', 'danger');
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
    const initiativeFilter = document.getElementById(tableType + 'InitiativeFilter');
    
    const searchText = searchInput ? searchInput.value.toLowerCase() : '';
    const ratingValue = ratingFilter ? ratingFilter.value : '';
    const typeValue = typeFilter ? typeFilter.value : '';
    const initiativeValue = initiativeFilter ? initiativeFilter.value : '';
    
    // Clear existing filter badges
    const filterBadgesContainer = document.getElementById(filterBadgesId);
    if (filterBadgesContainer) {
        filterBadgesContainer.innerHTML = '';
    }
      // Create filter badges if filters are applied
    if (searchText || ratingValue || typeValue || initiativeValue) {
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
        
        if (initiativeValue) {
            const initiativeLabel = document.getElementById(tableType + 'InitiativeFilter').options[document.getElementById(tableType + 'InitiativeFilter').selectedIndex].text;
            badgesHtml += `<span class="filter-badge">${initiativeLabel} <i class="fas fa-times remove-filter" data-filter="initiative" data-table="${tableType}"></i></span>`;
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
                    } else if (filterType === 'initiative') {
                        document.getElementById(tableType + 'InitiativeFilter').value = '';
                    }
                    
                    applyFilters(tableType);
                });            });
        }
    }
    
    // Apply filters to table rows
    const tableRows = document.querySelectorAll(`#${tableId} tbody tr`);
    
    tableRows.forEach((row, index) => {
        // Skip "no programs found" rows
        if (row.querySelector('td[colspan]')) {
            return;
        }
        
        // Get program data from the allPrograms array
        // We need to determine which program this row represents
        const programNameElement = row.querySelector('td:first-child .fw-medium .program-name');
        if (!programNameElement) return;
        
        const programNameInRow = programNameElement.textContent.trim();
        
        // Find matching program in allPrograms array
        let currentProgram = null;
        if (typeof allPrograms !== 'undefined') {
            currentProgram = allPrograms.find(p => {
                const programDisplayName = (p.program_number ? p.program_number + ' ' : '') + p.program_name;
                return programDisplayName === programNameInRow || p.program_name === programNameInRow;
            });
        }
        
        // Fallback to DOM parsing if program not found in data
        if (!currentProgram) {
            const programNameElement = row.querySelector('td:first-child .fw-medium');
            const programName = programNameElement?.textContent.toLowerCase() || '';
              // Extract program number from the badge if it exists
            const programNumberBadge = programNameElement?.querySelector('.badge.bg-info');
            const programNumber = programNumberBadge ? programNumberBadge.textContent.toLowerCase() : '';
            
            // Get initiative information - 2nd column now
            const initiativeElement = row.querySelector('td:nth-child(2)');
            const initiativeText = initiativeElement?.textContent.trim().toLowerCase() || '';
            const hasInitiative = initiativeElement?.querySelector('.badge.bg-primary') !== null;
            
            // Rating is now in 3rd column
            const ratingText = row.querySelector('td:nth-child(3) .badge')?.textContent.trim().toLowerCase() || '';
            const programType = row.getAttribute('data-program-type') || '';
            
            // Map display text back to rating values for comparison
            const ratingMap = {
                'monthly target achieved': 'target-achieved',
                'on track for year': 'on-track-yearly',
                'on track': 'on-track',
                'severe delays': 'severe-delay',
                'delayed': 'delayed',
                'completed': 'completed',
                'not started': 'not-started'
            };
            
            const normalizedRating = ratingMap[ratingText] || ratingText;
            
            // Apply all filters using DOM data
            let showRow = true;
            
            // Text search filter - search in both program name and program number
            if (searchText && !programName.includes(searchText) && !programNumber.includes(searchText)) {
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
            
            // Initiative filter (fallback DOM method)
            if (initiativeValue) {
                if (initiativeValue === 'no-initiative') {
                    // Show only programs without initiatives
                    if (hasInitiative) {
                        showRow = false;
                    }
                } else {
                    // Show only programs with the specific initiative
                    // Check if the initiative text contains the selected initiative name
                    const selectedInitiativeElement = document.querySelector(`#${tableType}InitiativeFilter option[value="${initiativeValue}"]`);
                    const selectedInitiativeName = selectedInitiativeElement ? selectedInitiativeElement.textContent.toLowerCase() : '';
                    
                    if (!hasInitiative || !initiativeText.includes(selectedInitiativeName)) {
                        showRow = false;
                    }
                }
            }
            
            // Show or hide the row
            row.style.display = showRow ? '' : 'none';
            return;
        }
        
        // Use program data for filtering (preferred method)
        let showRow = true;
        
        // Text search filter - search in both program name and program number
        if (searchText) {
            const searchInName = currentProgram.program_name.toLowerCase().includes(searchText);
            const searchInNumber = currentProgram.program_number ? currentProgram.program_number.toLowerCase().includes(searchText) : false;
            if (!searchInName && !searchInNumber) {
                showRow = false;
            }
        }
        
        // Rating filter
        if (ratingValue && currentProgram.rating !== ratingValue) {
            showRow = false;
        }
          // Type filter
        if (typeValue) {
            const isAssigned = currentProgram.is_assigned == 1;
            const programType = isAssigned ? 'assigned' : 'created';
            if (programType !== typeValue) {
                showRow = false;
            }
        }
        
        // Initiative filter using initiative_id
        if (initiativeValue) {
            if (initiativeValue === 'no-initiative') {
                // Show only programs without initiatives
                if (currentProgram.initiative_id && currentProgram.initiative_id !== null) {
                    showRow = false;
                }
            } else {
                // Show only programs with the specific initiative ID
                if (!currentProgram.initiative_id || currentProgram.initiative_id != initiativeValue) {
                    showRow = false;
                }
            }
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
            noResultsRow.innerHTML = '<td colspan="5" class="text-center py-4">No matching programs found.</td>';
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