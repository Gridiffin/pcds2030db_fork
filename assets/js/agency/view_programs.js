/**
 * View Programs Functionality
 * Handles filtering and interactions on the programs list page
 */

// Import CSS for programs view
import '../../css/agency/programs/view_programs.css';

// Import essential utilities
import '../utilities/initialization.js';
import '../utilities/dropdown_init.js';

// Import main utilities including showToast
import '../main.js';

// Global toggle dropdown function for custom dropdowns
window.toggleDropdown = function(button) {
    const dropdown = button.nextElementSibling;
    if (!dropdown || !dropdown.classList.contains('dropdown-menu-custom')) {
        return;
    }
    
    // Find the program box container
    const programBox = button.closest('.program-box');
    
    // Close all other dropdowns first and remove dropdown-active class
    document.querySelectorAll('.dropdown-menu-custom.show').forEach(menu => {
        if (menu !== dropdown) {
            menu.classList.remove('show');
            const otherProgramBox = menu.closest('.program-box');
            if (otherProgramBox) {
                otherProgramBox.classList.remove('dropdown-active');
            }
        }
    });
    
    // Toggle current dropdown
    const isShowing = dropdown.classList.contains('show');
    dropdown.classList.toggle('show');
    
    // Add/remove dropdown-active class for z-index management
    if (programBox) {
        if (isShowing) {
            programBox.classList.remove('dropdown-active');
            // Remove body class if no dropdowns are open
            if (!document.querySelector('.dropdown-menu-custom.show')) {
                document.body.classList.remove('dropdown-open');
            }
        } else {
            programBox.classList.add('dropdown-active');
            // Add body class when dropdown opens
            document.body.classList.add('dropdown-open');
        }
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!button.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
            if (programBox) {
                programBox.classList.remove('dropdown-active');
                // Remove body class if no dropdowns are open
                if (!document.querySelector('.dropdown-menu-custom.show')) {
                    document.body.classList.remove('dropdown-open');
                }
            }
            document.removeEventListener('click', closeDropdown);
        }
    });
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete functionality
    initDeleteButtons();
    
    // Initialize more actions modal functionality
    initMoreActionsModal();
    
    // Initialize tooltips for all buttons
    initTooltips();
    
    // Initialize pagination for both tables (wait for TablePagination to be available)
    if (typeof TablePagination !== 'undefined') {
        initializePagination();
    } else {
        // Wait for TablePagination to be loaded with timeout to prevent infinite loops
        let attempts = 0;
        const maxAttempts = 50; // 5 seconds maximum wait time
        const checkForTablePagination = setInterval(() => {
            attempts++;
            if (typeof TablePagination !== 'undefined') {
                clearInterval(checkForTablePagination);
                initializePagination();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkForTablePagination);
                console.error('TablePagination not found after 5 seconds. Pagination will not be initialized.');
            }
        }, 100);
    }
    
    // Initialize box sorting (sorting will be implemented differently for horizontal boxes)
    // TODO: Implement sorting for horizontal box layout if needed
    // For now, we'll comment this out as the sorting logic needs to be redesigned for boxes
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

// Handle filtering for specific container
function applyFilters(containerType) {
    const containerId = containerType === 'draft' ? 'draftProgramsContainer' : 
                       (containerType === 'finalized' ? 'finalizedProgramsContainer' : 'emptyProgramsContainer');
    const filterBadgesId = containerType === 'draft' ? 'draftFilterBadges' : 
                          (containerType === 'finalized' ? 'finalizedFilterBadges' : 'emptyFilterBadges');
    
    const searchInput = document.getElementById(containerType + 'ProgramSearch');
    const ratingFilter = document.getElementById(containerType + 'RatingFilter');
    const typeFilter = document.getElementById(containerType + 'TypeFilter');
    const initiativeFilter = document.getElementById(containerType + 'InitiativeFilter');
    
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
            badgesHtml += `<span class="filter-badge">"${searchText}" <i class="fas fa-times remove-filter" data-filter="search" data-container="${containerType}"></i></span>`;
        }
        
        if (ratingValue) {
            const ratingLabel = document.getElementById(containerType + 'RatingFilter').options[document.getElementById(containerType + 'RatingFilter').selectedIndex].text;
            badgesHtml += `<span class="filter-badge">${ratingLabel} <i class="fas fa-times remove-filter" data-filter="rating" data-container="${containerType}"></i></span>`;
        }
        
        if (typeValue) {
            const typeLabel = document.getElementById(containerType + 'TypeFilter').options[document.getElementById(containerType + 'TypeFilter').selectedIndex].text;
            badgesHtml += `<span class="filter-badge">${typeLabel} <i class="fas fa-times remove-filter" data-filter="type" data-container="${containerType}"></i></span>`;
        }
        
        if (initiativeValue) {
            const initiativeLabel = document.getElementById(containerType + 'InitiativeFilter').options[document.getElementById(containerType + 'InitiativeFilter').selectedIndex].text;
            badgesHtml += `<span class="filter-badge">${initiativeLabel} <i class="fas fa-times remove-filter" data-filter="initiative" data-container="${containerType}"></i></span>`;
        }
        
        if (filterBadgesContainer) {
            filterBadgesContainer.innerHTML = badgesHtml;
            
            // Add click handlers for filter badge removal
            filterBadgesContainer.querySelectorAll('.remove-filter').forEach(icon => {
                icon.addEventListener('click', function() {
                    const filterType = this.getAttribute('data-filter');
                    const containerType = this.getAttribute('data-container');
                      if (filterType === 'search') {
                        document.getElementById(containerType + 'ProgramSearch').value = '';
                    } else if (filterType === 'rating') {
                        document.getElementById(containerType + 'RatingFilter').value = '';
                    } else if (filterType === 'type') {
                        document.getElementById(containerType + 'TypeFilter').value = '';
                    } else if (filterType === 'initiative') {
                        document.getElementById(containerType + 'InitiativeFilter').value = '';
                    }
                    
                    applyFilters(containerType);
                });            });
        }
    }
    
    // Apply filters to program boxes
    const programBoxes = document.querySelectorAll(`#${containerId} .program-box`);
    
    programBoxes.forEach((box, index) => {
        // Get program data from the program box data attributes
        const programNameElement = box.querySelector('.program-name');
        if (!programNameElement) return;
        
        const programNameInBox = programNameElement.textContent.trim();
        
        // Find matching program in allPrograms array
        let currentProgram = null;
        if (typeof allPrograms !== 'undefined') {
            currentProgram = allPrograms.find(p => {
                const programDisplayName = (p.program_number ? p.program_number + ' ' : '') + p.program_name;
                return programDisplayName === programNameInBox || p.program_name === programNameInBox;
            });
        }
        
        // Fallback to DOM parsing if program not found in data
        if (!currentProgram) {
            const programNameElement = box.querySelector('.program-name');
            const programName = programNameElement?.textContent.toLowerCase() || '';
              // Extract program number from the number badge if it exists
            const programNumberBadge = box.querySelector('.program-number');
            const programNumber = programNumberBadge ? programNumberBadge.textContent.toLowerCase() : '';
            
            // Get initiative information from the initiative section
            const initiativeElement = box.querySelector('.initiative-info');
            const initiativeText = initiativeElement?.textContent.trim().toLowerCase() || '';
            const hasInitiative = initiativeElement?.querySelector('.initiative-icon, .initiative-badge') !== null;
            
            // Get rating from status section
            const ratingElement = box.querySelector('.status-info .status-text');
            const ratingText = ratingElement?.textContent.trim().toLowerCase() || '';
            const programType = box.getAttribute('data-program-type') || '';
            
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
            
            // Show or hide the box by adding/removing d-none class
            // This is compatible with the pagination utility
            if (showRow) {
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
            }
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
        
        // Show or hide the box by adding/removing d-none class
        // This is compatible with the pagination utility
        if (showRow) {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    });
    
    // Update "no results" message if needed
    updateNoResultsMessage(containerId);
    
    // Update pagination after filtering
    if (window.tablePaginations[containerId]) {
        window.tablePaginations[containerId].onFilterChange();
    }
}

/**
 * Initialize pagination for program containers
 */
function initializePagination() {
    // Check if TablePagination is available
    if (typeof TablePagination === 'undefined') {
        console.error('TablePagination class is not available. Make sure pagination.js is loaded first.');
        return;
    }
    
    // Initialize pagination for draft programs container
    const draftContainer = document.getElementById('draftProgramsContainer');
    if (draftContainer) {
        window.tablePaginations = window.tablePaginations || {};
        window.tablePaginations['draftProgramsContainer'] = new TablePagination('draftProgramsContainer', {
            itemsPerPage: 5,
            paginationContainerId: 'draftProgramsPagination',
            counterElementId: 'draftProgramsCounter',
            itemSelector: '.program-box' // Use program boxes instead of table rows
        });
    }
    
    // Initialize pagination for finalized programs container
    const finalizedContainer = document.getElementById('finalizedProgramsContainer');
    if (finalizedContainer) {
        window.tablePaginations = window.tablePaginations || {};
        window.tablePaginations['finalizedProgramsContainer'] = new TablePagination('finalizedProgramsContainer', {
            itemsPerPage: 5,
            paginationContainerId: 'finalizedProgramsPagination',
            counterElementId: 'finalizedProgramsCounter',
            itemSelector: '.program-box' // Use program boxes instead of table rows
        });
    }
    
    // Initialize pagination for empty programs container  
    const emptyContainer = document.getElementById('emptyProgramsContainer');
    if (emptyContainer) {
        window.tablePaginations = window.tablePaginations || {};
        window.tablePaginations['emptyProgramsContainer'] = new TablePagination('emptyProgramsContainer', {
            itemsPerPage: 5,
            paginationContainerId: 'emptyProgramsPagination',
            counterElementId: 'emptyProgramsCounter',
            itemSelector: '.program-box' // Use program boxes instead of table rows
        });
    }
}

/**
 * Initialize delete buttons functionality
 */
function initDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.trigger-delete-modal');
    const modal = document.getElementById('deleteModal');
    
    if (!modal || !deleteButtons.length) return;
    
    const programNameDisplay = document.getElementById('program-name-display');
    const programIdInput = document.getElementById('program-id-input');
    const continueBtn = document.getElementById('delete-continue-btn');
    const confirmBtn = document.getElementById('delete-confirm-btn');
    const deleteStep1 = document.getElementById('deleteStep1');
    const deleteStep2 = document.getElementById('deleteStep2');
    const deleteForm = document.getElementById('delete-program-form');
    
    if (!programNameDisplay || !programIdInput || !continueBtn || !confirmBtn || !deleteStep1 || !deleteStep2 || !deleteForm) return;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('[DEBUG] Delete button clicked', {
                programId: this.getAttribute('data-id'),
                programName: this.getAttribute('data-name'),
                button: this
            });
            const programId = this.getAttribute('data-id');
            const programName = this.getAttribute('data-name');
            
            programNameDisplay.textContent = programName;
            programIdInput.value = programId;
            
            // Reset modal to step 1
            deleteStep1.style.display = 'block';
            deleteStep2.style.display = 'none';
            continueBtn.style.display = 'inline-block';
            confirmBtn.style.display = 'none';
            
            // Show the modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        });
    });
    
    // Handle Continue button click
    continueBtn.addEventListener('click', function() {
        // Move to step 2
        deleteStep1.style.display = 'none';
        deleteStep2.style.display = 'block';
        continueBtn.style.display = 'none';
        confirmBtn.style.display = 'inline-block';
    });
    
    // Handle final confirmation
    confirmBtn.addEventListener('click', function() {
        // Submit the delete form
        deleteForm.submit();
    });
}

/**
 * Update "no results" message when all filtered boxes are hidden
 */
function updateNoResultsMessage(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    // Check if there are any visible boxes (not hidden by filters)
    const visibleBoxes = Array.from(container.querySelectorAll('.program-box:not(.d-none)'));
    
    // If all boxes are filtered out
    if (visibleBoxes.length === 0) {
        // Check if we already have a "no results" message
        let noResultsMessage = container.querySelector('.no-filter-results');
          if (!noResultsMessage) {
            noResultsMessage = document.createElement('div');
            noResultsMessage.className = 'no-filter-results programs-empty-state';
            noResultsMessage.innerHTML = `
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="empty-title">No Matching Programs Found</div>
                <div class="empty-description">Try adjusting your filters to see more results.</div>
            `;
            container.appendChild(noResultsMessage);
        }
    } else {
        // Remove any existing "no results" message if we have visible boxes
        const noResultsMessage = container.querySelector('.no-filter-results');
        if (noResultsMessage) {
            noResultsMessage.remove();
        }
    }
}

/**
 * Initialize tooltips for all buttons in the tables
 */
function initTooltips() {
    // Initialize tooltips for all elements with data-bs-toggle="tooltip"
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover focus',
            delay: { show: 300, hide: 100 }
        });
    });
    
    // Also initialize tooltips for any dynamically added content
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const newTooltips = node.querySelectorAll ? node.querySelectorAll('[data-bs-toggle="tooltip"]') : [];
                        newTooltips.forEach(function(tooltipEl) {
                            new bootstrap.Tooltip(tooltipEl, {
                                trigger: 'hover focus',
                                delay: { show: 300, hide: 100 }
                            });
                        });
                    }
                });
            }
        });
    });
    
    // Observe changes to the document body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

/**
 * Initialize more actions modal functionality for table action buttons
 */
function initMoreActionsModal() {
    // Find all "More Actions" buttons in table action columns
    const moreActionsButtons = document.querySelectorAll('.more-actions-btn');
    
    moreActionsButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const programId = this.getAttribute('data-program-id');
            const programName = this.getAttribute('data-program-name');
            const programType = this.getAttribute('data-program-type');
            
            // Show the more actions modal
            showMoreActionsModal(programId, programName, programType);
        });
    });
}

/**
 * Show the more actions modal with program-specific actions
 */
function showMoreActionsModal(programId, programName, programType) {
    // Create modal HTML if it doesn't exist
    let modal = document.getElementById('moreActionsModal');
    if (!modal) {
        modal = createMoreActionsModal();
        document.body.appendChild(modal);
    }
    
    // Update modal content with program-specific actions
    updateMoreActionsModalContent(modal, programId, programName, programType);
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Create the more actions modal HTML structure
 */
function createMoreActionsModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'moreActionsModal';
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-labelledby', 'moreActionsModalLabel');
    modal.setAttribute('aria-hidden', 'true');
    
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="moreActionsModalLabel">
                        <i class="fas fa-ellipsis-v me-2"></i>Additional Actions
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="program-info mb-3">
                        <h6 class="program-name-display"></h6>
                        <small class="text-muted program-type-display"></small>
                    </div>
                    <div class="actions-list">
                        <!-- Additional actions will be populated dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

/**
 * Update modal content with program-specific actions
 */
function updateMoreActionsModalContent(modal, programId, programName, programType) {
    // Update program info
    const nameDisplay = modal.querySelector('.program-name-display');
    const typeDisplay = modal.querySelector('.program-type-display');
    
    nameDisplay.textContent = programName;
    typeDisplay.textContent = programType === 'assigned' ? 'Assigned Program' : 'Agency-Created Program';
    
    // Create action buttons
    const actionsList = modal.querySelector('.actions-list');
    actionsList.innerHTML = '';
    
    const actions = [
        {
            icon: 'fas fa-edit',
            text: 'Edit Submission',
            url: `edit_submission.php?program_id=${programId}`,
            class: 'btn-outline-success',
            tooltip: 'Edit submissions for this program by selecting a reporting period'
        },
        {
            icon: 'fas fa-paper-plane',
            text: 'Submit Submission',
            action: 'submit_submission',
            class: 'btn-outline-info',
            tooltip: 'Select and submit an existing draft submission'
        },
        {
            icon: 'fas fa-edit',
            text: 'Edit Program',
            url: `edit_program.php?id=${programId}`,
            class: 'btn-outline-warning',
            tooltip: 'Modify program details, targets, and basic information'
        }
    ];
    
    actions.forEach(action => {
        if (action.url) {
            // Create link button
            const actionButton = document.createElement('a');
            actionButton.className = `btn ${action.class} w-100 mb-2`;
            actionButton.href = action.url;
            actionButton.setAttribute('title', action.tooltip);
            actionButton.setAttribute('data-bs-toggle', 'tooltip');
            actionButton.setAttribute('data-bs-placement', 'left');
            actionButton.innerHTML = `<i class="${action.icon} me-2"></i>${action.text}`;
            actionsList.appendChild(actionButton);
        } else if (action.action === 'submit_submission') {
            // Create submit submission button with click handler
            const actionButton = document.createElement('button');
            actionButton.className = `btn ${action.class} w-100 mb-2 submit-submission-btn`;
            actionButton.setAttribute('data-program-id', programId);
            actionButton.setAttribute('data-program-name', programName);
            actionButton.setAttribute('title', action.tooltip);
            actionButton.setAttribute('data-bs-toggle', 'tooltip');
            actionButton.setAttribute('data-bs-placement', 'left');
            actionButton.innerHTML = `<i class="${action.icon} me-2"></i>${action.text}`;
            
            // Add click handler for submit submission functionality
            actionButton.addEventListener('click', function() {
                const programId = this.getAttribute('data-program-id');
                const programName = this.getAttribute('data-program-name');
                
                // Close the current modal first
                const currentModal = bootstrap.Modal.getInstance(document.getElementById('moreActionsModal'));
                if (currentModal) {
                    currentModal.hide();
                }
                
                // Show submission selection modal
                showSubmissionSelectionModal(programId, programName);
            });
            
            actionsList.appendChild(actionButton);
        }
    });

    // Remove focal-only finalize/revert controls from modal
    // (No focalSection or AJAX for submissions here)

    // Initialize tooltips for the new buttons
    const tooltipTriggerList = [].slice.call(actionsList.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Show submission selection modal
 */
function showSubmissionSelectionModal(programId, programName) {
    // Create modal HTML if it doesn't exist
    let modal = document.getElementById('submissionSelectionModal');
    if (!modal) {
        modal = createSubmissionSelectionModal();
        document.body.appendChild(modal);
    }
    
    // Update modal content
    updateSubmissionSelectionModalContent(modal, programId, programName);
    
    // Show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Create the submission selection modal HTML structure
 */
function createSubmissionSelectionModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'submissionSelectionModal';
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-labelledby', 'submissionSelectionModalLabel');
    modal.setAttribute('aria-hidden', 'true');
    
    modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submissionSelectionModalLabel">
                        <i class="fas fa-paper-plane me-2"></i>Select Submission to Submit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="program-info mb-3">
                        <h6 class="program-name-display"></h6>
                        <small class="text-muted">Select a draft submission to submit and finalize</small>
                    </div>
                    <div id="submissionsList">
                        <!-- Submissions will be loaded here -->
                    </div>
                    <div id="loadingSubmissions" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading submissions...</p>
                    </div>
                    <div id="noSubmissions" class="text-center py-4" style="display: none;">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No Draft Submissions Found</h6>
                        <p class="text-muted">This program has no draft submissions available for submission.</p>
                        <a href="edit_submission.php?program_id=" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Create New Submission
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

/**
 * Update submission selection modal content
 */
function updateSubmissionSelectionModalContent(modal, programId, programName) {
    // Update program info
    const nameDisplay = modal.querySelector('.program-name-display');
    const submissionsList = modal.querySelector('#submissionsList');
    const loadingDiv = modal.querySelector('#loadingSubmissions');
    const noSubmissionsDiv = modal.querySelector('#noSubmissions');
    const createNewLink = modal.querySelector('#noSubmissions a');
    
    nameDisplay.textContent = programName;
    createNewLink.href = `edit_submission.php?program_id=${programId}`;
    
    // Show loading state
    submissionsList.style.display = 'none';
    noSubmissionsDiv.style.display = 'none';
    loadingDiv.style.display = 'block';
    
    // Load submissions
    loadProgramSubmissions(programId, modal);
}

/**
 * Load program submissions via AJAX
 */
function loadProgramSubmissions(programId, modal) {
    const submissionsList = modal.querySelector('#submissionsList');
    const loadingDiv = modal.querySelector('#loadingSubmissions');
    const noSubmissionsDiv = modal.querySelector('#noSubmissions');
    
    fetch(`../ajax/get_program_submissions_list.php?program_id=${programId}`)
        .then(response => response.json())
        .then(data => {
            // Hide loading
            loadingDiv.style.display = 'none';
            
            if (data.success && data.submissions && data.submissions.length > 0) {
                // Show submissions
                submissionsList.innerHTML = '';
                data.submissions.forEach(submission => {
                    const submissionCard = createSubmissionCard(submission, programId);
                    submissionsList.appendChild(submissionCard);
                });
                submissionsList.style.display = 'block';
            } else {
                // Show no submissions message
                noSubmissionsDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading submissions:', error);
            loadingDiv.style.display = 'none';
            noSubmissionsDiv.style.display = 'block';
            
            // Update error message
            const errorMessage = noSubmissionsDiv.querySelector('h6');
            errorMessage.textContent = 'Error Loading Submissions';
            const errorDesc = noSubmissionsDiv.querySelector('p');
            errorDesc.textContent = 'An error occurred while loading submissions. Please try again.';
        });
}

/**
 * Create submission card element
 */
function createSubmissionCard(submission, programId) {
    const card = document.createElement('div');
    card.className = 'card mb-3 submission-card';
    card.style.cursor = 'pointer';
    card.setAttribute('data-submission-id', submission.submission_id);
    card.setAttribute('data-period-id', submission.period_id);
    
    // Add hover effect
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
    });
    
    // Add click handler
    card.addEventListener('click', function() {
        selectSubmission(submission.submission_id, submission.period_id, programId);
    });
    
    const statusBadge = submission.period_status === 'open' ? 
        '<span class="badge bg-success me-2">Open</span>' : 
        '<span class="badge bg-secondary me-2">Closed</span>';
    
    card.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="card-title mb-2">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        ${submission.period_display}
                        ${statusBadge}
                    </h6>
                    <p class="card-text text-muted mb-2">
                        <i class="fas fa-file-alt me-1"></i>
                        ${submission.description}
                    </p>
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Last updated: ${submission.submitted_at || 'Not specified'}
                    </small>
                </div>
                <div class="text-end">
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </div>
        </div>
    `;
    
    return card;
}

/**
 * Handle submission selection and redirect
 */
function selectSubmission(submissionId, periodId, programId) {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('submissionSelectionModal'));
    if (modal) {
        modal.hide();
    }
    
    // Redirect to edit_submission.php with the selected submission
    window.location.href = `edit_submission.php?program_id=${programId}&period_id=${periodId}`;
}