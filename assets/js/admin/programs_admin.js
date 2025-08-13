/**
 * Admin Programs Functionality
 * Handles filtering and interactions on the admin programs page
 * Uses modular CSS import: programs.css (~100kB vs 352kB main.css)
 */

// Import programs-specific CSS bundle
import '../../css/admin/programs/programs.css';

/**
 * Filter programs based on the section (draft, finalized, or empty)
 */
function filterPrograms(section) {
    let tableId, searchId, ratingId, typeId, agencyId, initiativeId, badgeContainerId, countId;
    
    if (section === 'draft') {
        tableId = 'draftProgramsTable';
        searchId = 'draftProgramSearch';
        ratingId = 'draftRatingFilter';
        typeId = 'draftTypeFilter';
        agencyId = 'draftAgencyFilter';
        initiativeId = 'draftInitiativeFilter';
        badgeContainerId = 'draftFilterBadges';
        countId = 'draft-count';
    } else if (section === 'finalized') {
        tableId = 'finalizedProgramsTable';
        searchId = 'finalizedProgramSearch';
        ratingId = 'finalizedRatingFilter';
        typeId = 'finalizedTypeFilter';
        agencyId = 'finalizedAgencyFilter';
        initiativeId = 'finalizedInitiativeFilter';
        badgeContainerId = 'finalizedFilterBadges';
        countId = 'finalized-count';
    } else if (section === 'empty') {
        tableId = 'emptyProgramsTable';
        searchId = 'emptyProgramSearch';
        ratingId = null; // Empty programs don't have rating filter
        typeId = 'emptyTypeFilter';
        agencyId = 'emptyAgencyFilter';
        initiativeId = 'emptyInitiativeFilter';
        badgeContainerId = 'emptyFilterBadges';
        countId = 'empty-count';
    }
    
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Get filter values
    const searchValue = document.getElementById(searchId)?.value.toLowerCase() || '';
    const ratingValue = ratingId ? document.getElementById(ratingId)?.value || '' : '';
    const typeValue = document.getElementById(typeId)?.value || '';
    const agencyValue = document.getElementById(agencyId)?.value || '';
    const initiativeValue = document.getElementById(initiativeId)?.value || '';
    
    // Get all rows in the table body
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        // Skip if this is the "no results" row
        if (row.children.length === 1 && row.children[0].getAttribute('colspan')) {
            row.style.display = 'none';
            return;
        }
        
        let showRow = true;
        
        // Search filter - check program name and number
        if (searchValue) {
            const programNameElement = row.querySelector('.program-name');
            const programName = programNameElement ? programNameElement.textContent.toLowerCase() : '';
            if (!programName.includes(searchValue)) {
                showRow = false;
            }
        }
        
        // Rating filter (only for draft and finalized sections)
        if (ratingValue && ratingId) {
            const ratingData = row.getAttribute('data-rating');
            const ratingMap = {
                'target-achieved': 'monthly_target_achieved',
                'on-track-yearly': 'on_track_for_year', 
                'severe-delay': 'severe_delay',
                'not-started': 'not_started'
            };
            if (ratingMap[ratingValue] && ratingData !== ratingMap[ratingValue]) {
                showRow = false;
            }
        }
        
        // Type filter
        if (typeValue) {
            const programType = row.getAttribute('data-program-type');
            if (programType !== typeValue) {
                showRow = false;
            }
        }
        
        // Agency filter
        if (agencyValue) {
            const agencyData = row.getAttribute('data-agency-id');
            if (agencyData !== agencyValue) {
                showRow = false;
            }
        }
        
        // Initiative filter
        if (initiativeValue) {
            const initiativeData = row.getAttribute('data-initiative-id') || '0';
            if (initiativeValue === 'no-initiative') {
                if (initiativeData !== '0' && initiativeData !== '') {
                    showRow = false;
                }
            } else {
                if (initiativeData !== initiativeValue) {
                    showRow = false;
                }
            }
        }
        
        // Show/hide row
        row.style.display = showRow ? '' : 'none';
        if (showRow) visibleCount++;
    });
    
    // Update count badge
    const countElement = document.getElementById(countId);
    if (countElement) {
        countElement.textContent = visibleCount;
    }
    
    // Show "no results" message if needed
    if (visibleCount === 0) {
        const tbody = table.querySelector('tbody');
        const colspan = section === 'empty' ? 5 : 6; // Empty section has fewer columns
        const noResultsRow = document.createElement('tr');
        noResultsRow.innerHTML = `<td colspan="${colspan}" class="text-center py-4">No programs found matching the current filters.</td>`;
        tbody.appendChild(noResultsRow);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete functionality
    initDeleteButtons();
    
    // Initialize more actions modal functionality
    initMoreActionsModal();
    
    // Initialize table sorting for all tables
    const tables = ['draftProgramsTable', 'finalizedProgramsTable', 'emptyProgramsTable'];
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
                        h.setAttribute('data-direction', newDirection);
                    } else {
                        icon.className = 'fas fa-sort ms-1';
                        h.removeAttribute('data-direction');
                    }
                });
                
                // Sort the table
                sortTable(tableId, sortBy, newDirection);
            });
        });
    });
    
    // Initialize filtering for both sections
    initializeFiltering();
    
    // Apply initial filters from URL parameters
    applyInitialFilters();
});

/**
 * Initialize filtering functionality for all program sections
 */
function initializeFiltering() {
    // Draft programs filters
    const draftSearch = document.getElementById('draftProgramSearch');
    const draftRating = document.getElementById('draftRatingFilter');
    const draftType = document.getElementById('draftTypeFilter');
    const draftAgency = document.getElementById('draftAgencyFilter');
    const draftInitiative = document.getElementById('draftInitiativeFilter');
    const resetDraftBtn = document.getElementById('resetDraftFilters');
    
    // Finalized programs filters
    const finalizedSearch = document.getElementById('finalizedProgramSearch');
    const finalizedRating = document.getElementById('finalizedRatingFilter');
    const finalizedType = document.getElementById('finalizedTypeFilter');
    const finalizedAgency = document.getElementById('finalizedAgencyFilter');
    const finalizedInitiative = document.getElementById('finalizedInitiativeFilter');
    const resetFinalizedBtn = document.getElementById('resetFinalizedFilters');
    
    // Empty programs filters
    const emptySearch = document.getElementById('emptyProgramSearch');
    const emptyType = document.getElementById('emptyTypeFilter');
    const emptyAgency = document.getElementById('emptyAgencyFilter');
    const emptyInitiative = document.getElementById('emptyInitiativeFilter');
    const resetEmptyBtn = document.getElementById('resetEmptyFilters');
    
    // Add event listeners for draft programs
    [draftSearch, draftRating, draftType, draftAgency, draftInitiative].forEach(element => {
        if (element) {
            const eventType = element.type === 'text' ? 'input' : 'change';
            element.addEventListener(eventType, () => filterPrograms('draft'));
        }
    });
    
    // Add event listeners for finalized programs
    [finalizedSearch, finalizedRating, finalizedType, finalizedAgency, finalizedInitiative].forEach(element => {
        if (element) {
            const eventType = element.type === 'text' ? 'input' : 'change';
            element.addEventListener(eventType, () => filterPrograms('finalized'));
        }
    });
    
    // Add event listeners for empty programs  
    [emptySearch, emptyType, emptyAgency, emptyInitiative].forEach(element => {
        if (element) {
            const eventType = element.type === 'text' ? 'input' : 'change';
            element.addEventListener(eventType, () => filterPrograms('empty'));
        }
    });
    
    // Reset button event listeners
    if (resetDraftBtn) {
        resetDraftBtn.addEventListener('click', () => resetFilters('draft'));
    }
    
    if (resetFinalizedBtn) {
        resetFinalizedBtn.addEventListener('click', () => resetFilters('finalized'));
    }
    
    if (resetEmptyBtn) {
        resetEmptyBtn.addEventListener('click', () => resetFilters('empty'));
    }
}

/**
 * Update the program table with filtered results
 */
function updateProgramTable(tableId, programs, section) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    tbody.innerHTML = '';
    
    // Determine colspan based on section (unsubmitted and submitted both have initiative column now)
    const colspan = 7;
    
    if (programs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="text-center py-4">No ${section} programs found.</td>
            </tr>
        `;
        return;
    }
    
    programs.forEach(program => {
        const row = createProgramRow(program, section);
        tbody.appendChild(row);
    });
}

/**
 * Create a table row for a program
 */
function createProgramRow(program, section) {
    const row = document.createElement('tr');
    row.setAttribute('data-program-type', program.is_assigned ? 'assigned' : 'agency');
    row.setAttribute('data-sector-id', program.sector_id);
    row.setAttribute('data-agency-id', program.owner_agency_id);
    row.setAttribute('data-initiative-id', program.initiative_id || '');
    row.setAttribute('data-rating', program.rating || 'not-started');
    
    const ratingMap = {
        'on-track': { label: 'On Track', class: 'warning' },
        'on-track-yearly': { label: 'On Track for Year', class: 'warning' },
        'target-achieved': { label: 'Monthly Target Achieved', class: 'success' },
        'delayed': { label: 'Delayed', class: 'danger' },
        'severe-delay': { label: 'Severe Delays', class: 'danger' },
        'completed': { label: 'Completed', class: 'primary' },
        'not-started': { label: 'Not Started', class: 'secondary' }
    };
    
    const currentRating = program.rating || 'not-started';
    const rating = ratingMap[currentRating] || ratingMap['not-started'];
    
    // Format date
    let formattedDate = 'Not set';
    if (program.updated_at && program.updated_at !== '0000-00-00 00:00:00') {
        formattedDate = new Date(program.updated_at).toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    } else if (program.submission_date && program.submission_date !== '0000-00-00 00:00:00') {
        formattedDate = new Date(program.submission_date).toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric'
        });
    }
    
    // Create action button based on section
    let actionButton = '';
    if (program.submission_id) {
        if (section === 'unsubmitted') {
            actionButton = `
                <a href="resubmit.php?program_id=${program.program_id}&period_id=${periodId}" 
                   class="btn btn-outline-success btn-sm w-100" 
                   title="Submit Program for this Period"
                   onclick="return confirm('Are you sure you want to submit this program for the period? This will mark it as officially submitted.');">
                    <i class="fas fa-check-circle"></i> Submit
                </a>
            `;
        } else {
            actionButton = `
                <a href="unsubmit.php?program_id=${program.program_id}&period_id=${periodId}" 
                   class="btn btn-outline-warning btn-sm w-100" 
                   title="Unsubmit Program for this Period"
                   onclick="return confirm('Are you sure you want to unsubmit this program for the period? This will revert its status and allow the agency to edit it again.');">
                    <i class="fas fa-undo"></i> Unsubmit
                </a>
            `;
        }
    } else {
        actionButton = '<small class="text-muted">No submissions</small>';
    }    // Create initiative column based on section (only for unsubmitted or when data contains initiative info)
    let initiativeColumn = '';
    if (section === 'unsubmitted' || program.initiative_name) {
        if (program.initiative_name) {
            initiativeColumn = `
                <td>
                    <span class="badge bg-primary initiative-badge" title="Initiative">
                        <i class="fas fa-lightbulb me-1"></i>
                        ${escapeHtml(program.initiative_name)}
                    </span>
                </td>
            `;
        } else {
            initiativeColumn = `
                <td>
                    <span class="text-muted small">
                        <i class="fas fa-minus me-1"></i>Not Linked
                    </span>
                </td>
            `;
        }
    }
    
    row.innerHTML = `
        <td>
            <div class="fw-medium">
                <a href="view_program.php?id=${program.program_id}&period_id=${periodId}">
                    ${program.program_number ? `<span class="badge bg-info me-2" title="Program Number">${escapeHtml(program.program_number)}</span>` : ''}
                    ${escapeHtml(program.program_name)}
                </a>
                ${section === 'unsubmitted' ? '<span class="badge bg-light text-dark ms-1">Unsubmitted</span>' : ''}
            </div>
            <div class="small text-muted program-type-indicator">
                <i class="fas fa-${program.is_assigned ? 'tasks' : 'folder-plus'} me-1"></i>
                ${program.is_assigned ? 'Assigned' : 'Agency-Created'}
            </div>
        </td>
        ${initiativeColumn}
        <td>${escapeHtml(program.sector_name)}</td>
        <td>${escapeHtml(program.agency_name)}</td>
        <td>
            <span class="badge bg-${rating.class}">
                ${rating.label}
            </span>
        </td>
        <td>${formattedDate}</td>
        <td>
            <div class="btn-group btn-group-sm" role="group" aria-label="Program actions">
                <a href="view_program.php?id=${program.program_id}&period_id=${periodId}" class="btn btn-outline-primary" title="View Program Details">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="edit_program.php?id=${program.program_id}" class="btn btn-outline-secondary" title="Edit Program">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="#" 
                   class="btn btn-outline-danger" 
                   title="Delete Program"
                   onclick="confirmDeleteProgram(${program.program_id}, ${periodId}); return false;">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
            <div class="mt-1 d-grid">
                ${actionButton}
            </div>
        </td>
    `;
    
    return row;
}

/**
 * Reset filters for a specific section
 */
function resetFilters(section) {
    let searchId, ratingId, typeId, agencyId, initiativeId, badgeContainerId;
    
    if (section === 'draft') {
        searchId = 'draftProgramSearch';
        ratingId = 'draftRatingFilter';
        typeId = 'draftTypeFilter';
        agencyId = 'draftAgencyFilter';
        initiativeId = 'draftInitiativeFilter';
        badgeContainerId = 'draftFilterBadges';
    } else if (section === 'finalized') {
        searchId = 'finalizedProgramSearch';
        ratingId = 'finalizedRatingFilter';
        typeId = 'finalizedTypeFilter';
        agencyId = 'finalizedAgencyFilter';
        initiativeId = 'finalizedInitiativeFilter';
        badgeContainerId = 'finalizedFilterBadges';
    } else if (section === 'empty') {
        searchId = 'emptyProgramSearch';
        ratingId = null; // Empty section doesn't have rating filter
        typeId = 'emptyTypeFilter';
        agencyId = 'emptyAgencyFilter';
        initiativeId = 'emptyInitiativeFilter';
        badgeContainerId = 'emptyFilterBadges';
    }
    
    // Reset all filter inputs
    const searchInput = document.getElementById(searchId);
    const ratingSelect = ratingId ? document.getElementById(ratingId) : null;
    const typeSelect = document.getElementById(typeId);
    const agencySelect = document.getElementById(agencyId);
    const initiativeSelect = document.getElementById(initiativeId);
    
    if (searchInput) searchInput.value = '';
    if (ratingSelect) ratingSelect.value = '';
    if (typeSelect) typeSelect.value = '';
    if (agencySelect) agencySelect.value = '';
    if (initiativeSelect) initiativeSelect.value = '';
    
    // Clear filter badges
    const badgesContainer = document.getElementById(badgeContainerId);
    if (badgesContainer) {
        badgesContainer.innerHTML = '';
    }
    
    // Refilter to show all programs
    filterPrograms(section);
}

/**
 * Update filter badges to show active filters
 */
function updateFilterBadges(section, filters) {
    const prefix = section === 'unsubmitted' ? 'unsubmitted' : 'submitted';
    const badgesContainer = document.getElementById(prefix + 'FilterBadges');
    if (!badgesContainer) return;
    
    badgesContainer.innerHTML = '';
    
    // Add badges for active filters
    Object.entries(filters).forEach(([key, value]) => {
        if (value) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary me-2 mb-1';
            let label = '';
            let agencySelect, agencyText, initiativeSelect, initiativeText;
            
            switch (key) {
                case 'search':
                    label = `Search: ${value}`;
                    break;
                case 'rating':
                    label = `Rating: ${value}`;
                    break;
                case 'type':
                    label = `Type: ${value}`;
                    break;
                case 'agency':
                    agencySelect = document.getElementById(prefix + 'AgencyFilter');
                    agencyText = agencySelect?.options[agencySelect.selectedIndex]?.text || value;
                    label = `Agency: ${agencyText}`;
                    break;
                case 'initiative':
                    initiativeSelect = document.getElementById(prefix + 'InitiativeFilter');
                    initiativeText = initiativeSelect?.options[initiativeSelect.selectedIndex]?.text || value;
                    label = `Initiative: ${initiativeText}`;
                    break;
            }
            
            badge.innerHTML = `${label} <i class="fas fa-times ms-1" style="cursor: pointer;"></i>`;
            
            // Add click handler to remove filter
            badge.querySelector('i').addEventListener('click', () => {
                // Clear the specific filter
                const inputId = prefix + (key.charAt(0).toUpperCase() + key.slice(1)) + 
                    (key === 'search' ? 'ProgramSearch' : 'Filter');
                const input = document.getElementById(inputId);
                if (input) {
                    input.value = '';
                }
                filterPrograms(section);
            });
            
            badgesContainer.appendChild(badge);
        }
    });
}

/**
 * Sort table by column
 */
function sortTable(tableId, sortBy, direction) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let aValue, bValue;
        
        switch (sortBy) {
            case 'name':
                aValue = a.cells[0].querySelector('.fw-medium a')?.textContent || '';
                bValue = b.cells[0].querySelector('.fw-medium a')?.textContent || '';
                break;
            case 'sector':
                aValue = a.cells[1].textContent;
                bValue = b.cells[1].textContent;
                break;
            case 'agency':
                aValue = a.cells[2].textContent;
                bValue = b.cells[2].textContent;
                break;
            case 'rating':
                aValue = a.cells[3].textContent;
                bValue = b.cells[3].textContent;
                break;
            case 'date':
                aValue = a.cells[4].textContent;
                bValue = b.cells[4].textContent;
                // Convert to date for proper sorting
                if (aValue !== 'Not set') aValue = new Date(aValue);
                if (bValue !== 'Not set') bValue = new Date(bValue);
                break;
            default:
                return 0;
        }
        
        if (sortBy === 'date') {
            // Handle date sorting
            if (aValue === 'Not set' && bValue === 'Not set') return 0;
            if (aValue === 'Not set') return direction === 'asc' ? 1 : -1;
            if (bValue === 'Not set') return direction === 'asc' ? -1 : 1;
            return direction === 'asc' ? aValue - bValue : bValue - aValue;
        } else {
            // Handle text sorting
            const result = aValue.localeCompare(bValue);
            return direction === 'asc' ? result : -result;
        }
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Initialize delete buttons functionality
 */
function initDeleteButtons() {
    // Get all delete buttons
    const deleteButtons = document.querySelectorAll('.delete-program-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const programId = this.getAttribute('data-id');
            const programName = this.getAttribute('data-name');
            
            // Set the program details in the modal
            const programNameDisplay = document.getElementById('program-name-display');
            const programIdInput = document.getElementById('program-id-input');
            
            if (programNameDisplay && programIdInput) {
                programNameDisplay.textContent = programName;
                programIdInput.value = programId;
                
                // Show the delete modal
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            } else {
                // Fallback: show browser confirm dialog
                if (confirm(`Are you sure you want to delete the program "${programName}"? This action cannot be undone.`)) {
                    // Create and submit form programmatically
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_program.php';
                    
                    const programIdField = document.createElement('input');
                    programIdField.type = 'hidden';
                    programIdField.name = 'program_id';
                    programIdField.value = programId;
                    form.appendChild(programIdField);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });
    });
    
    console.log(`Delete buttons initialized for ${deleteButtons.length} buttons`);
}

/**
 * Confirm program deletion
 */
function confirmDeleteProgram(programId, periodId) {
    if (confirm('Are you sure you want to delete this program? This action cannot be undone.')) {
        // Find and disable the delete button to prevent double-clicks
        const deleteButton = document.querySelector(`a[onclick*="confirmDeleteProgram(${programId}"]`);
        const programRow = deleteButton ? deleteButton.closest('tr') : null;
          if (deleteButton) {
            deleteButton.classList.add('btn-deleting');
            deleteButton.classList.remove('btn-danger');
            deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
            deleteButton.title = 'Deleting program...';
        }
        
        // Highlight the row being deleted with smooth animation
        if (programRow) {
            programRow.classList.add('row-deleting');
        }
        
        // Show toast notification for immediate feedback
        showToast('Deleting program...', 'info', 3000);
        
        // Create a form to submit the delete request via POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_program.php';
        
        // Add program_id field
        const programIdField = document.createElement('input');
        programIdField.type = 'hidden';
        programIdField.name = 'program_id';
        programIdField.value = programId;
        form.appendChild(programIdField);
        
        // Add period_id field if provided
        if (periodId && periodId !== 'null') {
            const periodIdField = document.createElement('input');
            periodIdField.type = 'hidden';
            periodIdField.name = 'period_id';
            periodIdField.value = periodId;
            form.appendChild(periodIdField);
        }
        
        // Add confirm_delete field
        const confirmField = document.createElement('input');
        confirmField.type = 'hidden';
        confirmField.name = 'confirm_delete';
        confirmField.value = '1';
        form.appendChild(confirmField);
        
        // Append to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

/**
 * Apply initial filters based on URL parameters
 */
function applyInitialFilters() {
    // Check if initialProgramType is defined and has a valid value
    if (typeof initialProgramType !== 'undefined' && initialProgramType) {
        if (initialProgramType === 'assigned') {
            // Apply "assigned" filter to both sections
            const unsubmittedTypeFilter = document.getElementById('unsubmittedTypeFilter');
            const submittedTypeFilter = document.getElementById('submittedTypeFilter');
            
            if (unsubmittedTypeFilter) {
                unsubmittedTypeFilter.value = 'assigned';
                filterPrograms('unsubmitted');
            }
            
            if (submittedTypeFilter) {
                submittedTypeFilter.value = 'assigned';
                filterPrograms('submitted');
            }
        } else if (initialProgramType === 'agency') {
            // Apply "agency" filter to both sections
            const unsubmittedTypeFilter = document.getElementById('unsubmittedTypeFilter');
            const submittedTypeFilter = document.getElementById('submittedTypeFilter');
            
            if (unsubmittedTypeFilter) {
                unsubmittedTypeFilter.value = 'agency';
                filterPrograms('unsubmitted');
            }
            
            if (submittedTypeFilter) {
                submittedTypeFilter.value = 'agency';
                filterPrograms('submitted');
            }
        }
    }
}

/**
 * Toast Notification System
 * Uses the global showToast function for consistency
 */
function showToast(message, type = 'info', duration = 5000) {
    if (typeof window.showToast === 'function') {
        window.showToast('Notification', message, type, duration);
    } else {
        // Fallback if global showToast isn't loaded
        alert(message);
    }
}

/**
 * Enhanced error handling for form submissions
 */
function handleFormError(message) {
    showToast(message, 'danger', 8000);
    console.error('Form submission error:', message);
}

/**
 * Show loading state for long operations
 */
function showLoadingOverlay(message = 'Processing...') {
    let overlay = document.getElementById('loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        `;
        
        const spinner = document.createElement('div');
        spinner.className = 'text-center text-white';
        spinner.innerHTML = `
            <div class="spinner-border mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div id="loading-message">${message}</div>
        `;
        
        overlay.appendChild(spinner);
        document.body.appendChild(overlay);
    } else {
        document.getElementById('loading-message').textContent = message;
        overlay.style.display = 'flex';
    }
    
    return overlay;
}

/**
 * Hide loading overlay
 */
function hideLoadingOverlay() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
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
                        <i class="fas fa-ellipsis-v me-2"></i>Program Actions
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
 * Update modal content with program-specific actions for admin users
 */
function updateMoreActionsModalContent(modal, programId, programName, programType) {
    // Update program info
    const nameDisplay = modal.querySelector('.program-name-display');
    const typeDisplay = modal.querySelector('.program-type-display');
    
    nameDisplay.textContent = programName;
    typeDisplay.textContent = programType === 'assigned' ? 'Assigned Program' : 'Agency-Created Program';
    
    // Create action buttons for admin users
    const actionsList = modal.querySelector('.actions-list');
    actionsList.innerHTML = '';
    
    const actions = [
        {
            icon: 'fas fa-list-alt',
            text: 'View Submissions',
            url: `list_program_submissions.php?program_id=${programId}`,
            class: 'btn-outline-info',
            tooltip: 'View all submissions for this program across reporting periods'
        },
        {
            icon: 'fas fa-edit',
            text: 'Edit Submissions',
            url: `edit_submission.php?program_id=${programId}`,
            class: 'btn-outline-success',
            tooltip: 'Edit submissions for this program by selecting a reporting period'
        },
        {
            icon: 'fas fa-plus-circle',
            text: 'Add Submission',
            url: `add_submission.php?program_id=${programId}`,
            class: 'btn-outline-primary',
            tooltip: 'Add a new submission for this program in a specific reporting period'
        },
        {
            icon: 'fas fa-cog',
            text: 'Edit Program Details',
            url: `edit_program.php?id=${programId}`,
            class: 'btn-outline-warning',
            tooltip: 'Modify program details, targets, and basic information'
        }
    ];
    
    actions.forEach(action => {
        const actionButton = document.createElement('a');
        actionButton.className = `btn ${action.class} w-100 mb-2`;
        actionButton.href = action.url;
        actionButton.setAttribute('title', action.tooltip);
        actionButton.setAttribute('data-bs-toggle', 'tooltip');
        actionButton.setAttribute('data-bs-placement', 'left');
        actionButton.innerHTML = `<i class="${action.icon} me-2"></i>${action.text}`;
        actionsList.appendChild(actionButton);
    });

    // Initialize tooltips for the new buttons
    const tooltipTriggerList = [].slice.call(actionsList.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}
