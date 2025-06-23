/**
 * Admin Programs Functionality
 * Handles filtering and interactions on the admin programs page with separate sections
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete functionality
    initDeleteButtons();
    
    // Initialize table sorting for both tables
    const tables = ['unsubmittedProgramsTable', 'submittedProgramsTable'];
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
 * Initialize filtering functionality for both program sections
 */
function initializeFiltering() {    // Unsubmitted programs filters
    const unsubmittedSearch = document.getElementById('unsubmittedProgramSearch');
    const unsubmittedRating = document.getElementById('unsubmittedRatingFilter');
    const unsubmittedType = document.getElementById('unsubmittedTypeFilter');
    const unsubmittedSector = document.getElementById('unsubmittedSectorFilter');
    const unsubmittedAgency = document.getElementById('unsubmittedAgencyFilter');
    const unsubmittedInitiative = document.getElementById('unsubmittedInitiativeFilter');
    const resetUnsubmittedBtn = document.getElementById('resetUnsubmittedFilters');
    
    // Submitted programs filters
    const submittedSearch = document.getElementById('submittedProgramSearch');
    const submittedRating = document.getElementById('submittedRatingFilter');
    const submittedType = document.getElementById('submittedTypeFilter');
    const submittedSector = document.getElementById('submittedSectorFilter');
    const submittedAgency = document.getElementById('submittedAgencyFilter');
    const submittedInitiative = document.getElementById('submittedInitiativeFilter');
    const resetSubmittedBtn = document.getElementById('resetSubmittedFilters');
    
    // Add event listeners for unsubmitted programs
    [unsubmittedSearch, unsubmittedRating, unsubmittedType, unsubmittedSector, unsubmittedAgency, unsubmittedInitiative].forEach(element => {
        if (element) {
            const eventType = element.type === 'text' ? 'input' : 'change';
            element.addEventListener(eventType, () => filterPrograms('unsubmitted'));
        }
    });
    
    // Add event listeners for submitted programs
    [submittedSearch, submittedRating, submittedType, submittedSector, submittedAgency, submittedInitiative].forEach(element => {
        if (element) {
            const eventType = element.type === 'text' ? 'input' : 'change';
            element.addEventListener(eventType, () => filterPrograms('submitted'));
        }
    });
    
    // Reset button event listeners
    if (resetUnsubmittedBtn) {
        resetUnsubmittedBtn.addEventListener('click', () => resetFilters('unsubmitted'));
    }
    
    if (resetSubmittedBtn) {
        resetSubmittedBtn.addEventListener('click', () => resetFilters('submitted'));
    }
}

/**
 * Filter programs based on the section (unsubmitted or submitted)
 */
function filterPrograms(section) {
    const programs = section === 'unsubmitted' ? unsubmittedPrograms : submittedPrograms;
    const tableId = section === 'unsubmitted' ? 'unsubmittedProgramsTable' : 'submittedProgramsTable';
    const prefix = section === 'unsubmitted' ? 'unsubmitted' : 'submitted';
      // Get filter values
    const searchValue = document.getElementById(prefix + 'ProgramSearch')?.value.toLowerCase() || '';
    const ratingValue = document.getElementById(prefix + 'RatingFilter')?.value || '';
    const typeValue = document.getElementById(prefix + 'TypeFilter')?.value || '';
    const sectorValue = document.getElementById(prefix + 'SectorFilter')?.value || '';
    const agencyValue = document.getElementById(prefix + 'AgencyFilter')?.value || '';
    const initiativeValue = document.getElementById(prefix + 'InitiativeFilter')?.value || '';
      // Filter programs
    const filteredPrograms = programs.filter(program => {
        // Search filter - search in both program name and program number
        if (searchValue && 
            !program.program_name.toLowerCase().includes(searchValue) && 
            !(program.program_number && program.program_number.toLowerCase().includes(searchValue))) {
            return false;
        }
        
        // Rating filter
        if (ratingValue && program.rating !== ratingValue) {
            return false;
        }
        
        // Type filter
        if (typeValue) {
            const isAssigned = program.is_assigned == 1;
            if (typeValue === 'assigned' && !isAssigned) return false;
            if (typeValue === 'agency' && isAssigned) return false;
        }
        
        // Sector filter
        if (sectorValue && String(program.sector_id) !== sectorValue) {
            return false;
        }
        
        // Agency filter
        if (agencyValue && String(program.owner_agency_id) !== agencyValue) {
            return false;
        }
        
        // Initiative filter
        if (initiativeValue) {
            if (initiativeValue === 'no-initiative') {
                // Show only programs without initiatives
                if (program.initiative_id && program.initiative_id !== null) {
                    return false;
                }
            } else {
                // Show only programs with the specific initiative
                if (String(program.initiative_id) !== initiativeValue) {
                    return false;
                }
            }
        }
        
        return true;
    });
    
    // Update the table
    updateProgramTable(tableId, filteredPrograms, section);
      // Update filter badges
    updateFilterBadges(section, {
        search: searchValue,
        rating: ratingValue,
        type: typeValue,
        sector: sectorValue,
        agency: agencyValue,
        initiative: initiativeValue
    });
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
    const prefix = section === 'unsubmitted' ? 'unsubmitted' : 'submitted';
      // Reset all filter inputs
    const searchInput = document.getElementById(prefix + 'ProgramSearch');
    const ratingSelect = document.getElementById(prefix + 'RatingFilter');
    const typeSelect = document.getElementById(prefix + 'TypeFilter');
    const sectorSelect = document.getElementById(prefix + 'SectorFilter');
    const agencySelect = document.getElementById(prefix + 'AgencyFilter');
    const initiativeSelect = document.getElementById(prefix + 'InitiativeFilter');
    
    if (searchInput) searchInput.value = '';
    if (ratingSelect) ratingSelect.value = '';
    if (typeSelect) typeSelect.value = '';
    if (sectorSelect) sectorSelect.value = '';
    if (agencySelect) agencySelect.value = '';
    if (initiativeSelect) initiativeSelect.value = '';
    
    // Clear filter badges
    const badgesContainer = document.getElementById(prefix + 'FilterBadges');
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
                case 'sector':
                    const sectorSelect = document.getElementById(prefix + 'SectorFilter');
                    const sectorText = sectorSelect?.options[sectorSelect.selectedIndex]?.text || value;
                    label = `Sector: ${sectorText}`;
                    break;                case 'agency':
                    const agencySelect = document.getElementById(prefix + 'AgencyFilter');
                    const agencyText = agencySelect?.options[agencySelect.selectedIndex]?.text || value;
                    label = `Agency: ${agencyText}`;
                    break;
                case 'initiative':
                    const initiativeSelect = document.getElementById(prefix + 'InitiativeFilter');
                    const initiativeText = initiativeSelect?.options[initiativeSelect.selectedIndex]?.text || value;
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
    // This function can be expanded if needed for delete functionality
    console.log('Delete buttons initialized');
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
 * Provides immediate feedback to users without page reloads
 */
function showToast(message, type = 'info', duration = 5000) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        `;
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `alert alert-${type} alert-dismissible fade show mb-2`;
    toast.setAttribute('role', 'alert');
    toast.style.cssText = `
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    `;
    
    // Set icon based on type
    let icon;
    switch (type) {
        case 'success':
            icon = 'fa-check-circle';
            break;
        case 'danger':
        case 'error':
            icon = 'fa-exclamation-circle';
            break;
        case 'warning':
            icon = 'fa-exclamation-triangle';
            break;
        default:
            icon = 'fa-info-circle';
    }
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${icon} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Auto-remove after duration
    if (duration > 0) {
        setTimeout(() => {
            if (document.getElementById(toastId)) {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 150);
            }
        }, duration);
    }
    
    return toast;
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
