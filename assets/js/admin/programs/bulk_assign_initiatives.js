/**
 * Admin Programs - Bulk Assign Initiatives JavaScript
 * Handles functionality for the bulk assignment page
 */

// Import CSS for admin bulk assign initiatives
import '../../../css/admin/programs/bulk_assign_initiatives.css';

// Import essential utilities
import '../../utilities/initialization.js';
import '../../utilities/dropdown_init.js';

// Import main utilities including showToast
import '../../main.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize page components
    initializeInitiativeSelector();
    initializeProgramSelection();
    initializeBulkActions();
    initializeFilters();
    
    console.log('Admin bulk assign initiatives page initialized');
});

/**
 * Initialize initiative selector
 */
function initializeInitiativeSelector() {
    const initiativeSelect = document.getElementById('initiative_id');
    
    if (initiativeSelect) {
        initiativeSelect.addEventListener('change', function() {
            const initiativeId = this.value;
            
            if (initiativeId) {
                loadInitiativeDetails(initiativeId);
                enableProgramSelection();
            } else {
                clearInitiativeDetails();
                disableProgramSelection();
            }
            
            updateBulkActionsState();
        });
        
        // Load details if initiative is pre-selected
        const selectedInitiative = initiativeSelect.value;
        if (selectedInitiative) {
            loadInitiativeDetails(selectedInitiative);
            enableProgramSelection();
        }
    }
}

/**
 * Load initiative details
 */
function loadInitiativeDetails(initiativeId) {
    showLoadingState('initiative-details');
    
    fetch(`get_initiative_details.php?initiative_id=${initiativeId}`)
        .then(response => response.json())
        .then(data => {
            hideLoadingState('initiative-details');
            
            if (data.success) {
                displayInitiativeDetails(data.initiative);
            } else {
                showToast('Error', data.error || 'Failed to load initiative details', 'error');
                clearInitiativeDetails();
            }
        })
        .catch(error => {
            hideLoadingState('initiative-details');
            console.error('Error loading initiative details:', error);
            clearInitiativeDetails();
        });
}

/**
 * Display initiative details
 */
function displayInitiativeDetails(initiative) {
    const detailsContainer = document.getElementById('initiativeDetails');
    
    if (detailsContainer) {
        detailsContainer.innerHTML = `
            <div class="initiative-info">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">${escapeHtml(initiative.initiative_name)}</h6>
                        ${initiative.initiative_number ? `<span class="initiative-badge">${escapeHtml(initiative.initiative_number)}</span>` : ''}
                    </div>
                    <span class="badge bg-${initiative.is_active ? 'success' : 'secondary'}">
                        ${initiative.is_active ? 'Active' : 'Inactive'}
                    </span>
                </div>
                
                ${initiative.description ? `
                    <p class="text-muted mb-3">${escapeHtml(initiative.description)}</p>
                ` : ''}
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Current Programs:</strong><br>
                        <span class="text-muted">${initiative.program_count || 0} assigned</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Category:</strong><br>
                        <span class="text-muted">${initiative.category || 'Not specified'}</span>
                    </div>
                </div>
            </div>
        `;
        
        detailsContainer.style.display = 'block';
    }
}

/**
 * Clear initiative details
 */
function clearInitiativeDetails() {
    const detailsContainer = document.getElementById('initiativeDetails');
    if (detailsContainer) {
        detailsContainer.style.display = 'none';
        detailsContainer.innerHTML = '';
    }
}

/**
 * Enable program selection
 */
function enableProgramSelection() {
    const programSection = document.getElementById('programSelectionSection');
    if (programSection) {
        programSection.style.opacity = '1';
        programSection.style.pointerEvents = 'auto';
        
        // Enable all checkboxes
        const checkboxes = programSection.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.disabled = false);
    }
}

/**
 * Disable program selection
 */
function disableProgramSelection() {
    const programSection = document.getElementById('programSelectionSection');
    if (programSection) {
        programSection.style.opacity = '0.6';
        programSection.style.pointerEvents = 'none';
        
        // Disable and uncheck all checkboxes
        const checkboxes = programSection.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.disabled = true;
            checkbox.checked = false;
        });
        
        updateSelectionSummary();
    }
}

/**
 * Initialize program selection functionality
 */
function initializeProgramSelection() {
    const selectAllBtn = document.getElementById('selectAllPrograms');
    const selectNoneBtn = document.getElementById('selectNonePrograms');
    const programCheckboxes = document.querySelectorAll('.program-checkbox');
    
    // Select all button
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            programCheckboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = true;
                }
            });
            updateSelectionSummary();
            updateBulkActionsState();
        });
    }
    
    // Select none button
    if (selectNoneBtn) {
        selectNoneBtn.addEventListener('click', function() {
            programCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectionSummary();
            updateBulkActionsState();
        });
    }
    
    // Individual checkbox listeners
    programCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectionSummary();
            updateBulkActionsState();
        });
    });
    
    // Initialize with disabled state
    disableProgramSelection();
}

/**
 * Update selection summary
 */
function updateSelectionSummary() {
    const selectedPrograms = getSelectedPrograms();
    const totalPrograms = document.querySelectorAll('.program-checkbox').length;
    
    // Update selected count
    const selectedCountElement = document.getElementById('selectedCount');
    if (selectedCountElement) {
        selectedCountElement.textContent = selectedPrograms.length;
    }
    
    // Update total count
    const totalCountElement = document.getElementById('totalCount');
    if (totalCountElement) {
        totalCountElement.textContent = totalPrograms;
    }
    
    // Update initiative info
    const initiativeSelect = document.getElementById('initiative_id');
    const initiativeNameElement = document.getElementById('selectedInitiativeName');
    if (initiativeNameElement && initiativeSelect) {
        const selectedOption = initiativeSelect.selectedOptions[0];
        initiativeNameElement.textContent = selectedOption ? selectedOption.textContent : 'None selected';
    }
}

/**
 * Get selected program data
 */
function getSelectedPrograms() {
    const selectedCheckboxes = document.querySelectorAll('.program-checkbox:checked');
    return Array.from(selectedCheckboxes).map(checkbox => ({
        id: checkbox.value,
        name: checkbox.dataset.programName,
        agency: checkbox.dataset.agencyName
    }));
}

/**
 * Initialize bulk actions
 */
function initializeBulkActions() {
    const assignBtn = document.getElementById('bulkAssignBtn');
    const clearBtn = document.getElementById('clearSelectionBtn');
    
    if (assignBtn) {
        assignBtn.addEventListener('click', function() {
            performBulkAssignment();
        });
    }
    
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            clearAllSelections();
        });
    }
}

/**
 * Update bulk actions state
 */
function updateBulkActionsState() {
    const assignBtn = document.getElementById('bulkAssignBtn');
    const selectedPrograms = getSelectedPrograms();
    const initiativeSelect = document.getElementById('initiative_id');
    
    const canAssign = selectedPrograms.length > 0 && initiativeSelect.value;
    
    if (assignBtn) {
        assignBtn.disabled = !canAssign;
    }
}

/**
 * Perform bulk assignment
 */
function performBulkAssignment() {
    const selectedPrograms = getSelectedPrograms();
    const initiativeSelect = document.getElementById('initiative_id');
    
    if (selectedPrograms.length === 0) {
        showToast('Error', 'Please select at least one program', 'error');
        return;
    }
    
    if (!initiativeSelect.value) {
        showToast('Error', 'Please select an initiative', 'error');
        return;
    }
    
    // Confirm action
    const confirmMessage = `Are you sure you want to assign ${selectedPrograms.length} program(s) to the selected initiative?`;
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Show progress
    showProgressSection();
    
    const requestData = {
        initiative_id: initiativeSelect.value,
        program_ids: selectedPrograms.map(p => p.id)
    };
    
    fetch('process_bulk_assignment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        hideProgressSection();
        
        if (data.success) {
            showResultsSection(data.results);
            showToast('Success', `${data.success_count} program(s) assigned successfully`, 'success');
            
            // Clear selections after successful assignment
            setTimeout(clearAllSelections, 2000);
        } else {
            showToast('Error', data.error || 'Bulk assignment failed', 'error');
        }
    })
    .catch(error => {
        hideProgressSection();
        console.error('Bulk assignment error:', error);
        showToast('Error', 'An error occurred during bulk assignment', 'error');
    });
}

/**
 * Clear all selections
 */
function clearAllSelections() {
    // Clear initiative selection
    const initiativeSelect = document.getElementById('initiative_id');
    if (initiativeSelect) {
        initiativeSelect.value = '';
        initiativeSelect.dispatchEvent(new Event('change'));
    }
    
    // Clear program selections
    const programCheckboxes = document.querySelectorAll('.program-checkbox');
    programCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    updateSelectionSummary();
    updateBulkActionsState();
    hideResultsSection();
}

/**
 * Initialize filters
 */
function initializeFilters() {
    const filterForm = document.getElementById('filterForm');
    const resetFiltersBtn = document.getElementById('resetFilters');
    
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('input, select');
        
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                applyFilters();
            });
        });
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            resetFilters();
        });
    }
}

/**
 * Apply filters
 */
function applyFilters() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    
    const formData = new FormData(filterForm);
    const filters = Object.fromEntries(formData.entries());
    
    // Apply filters to program list
    const programItems = document.querySelectorAll('.program-item');
    
    programItems.forEach(item => {
        let shouldShow = true;
        
        // Apply agency filter
        if (filters.agency_filter && filters.agency_filter !== item.dataset.agencyId) {
            shouldShow = false;
        }
        
        // Apply status filter
        if (filters.status_filter && filters.status_filter !== item.dataset.status) {
            shouldShow = false;
        }
        
        // Apply search filter
        if (filters.search_filter) {
            const searchTerm = filters.search_filter.toLowerCase();
            const programName = item.dataset.programName.toLowerCase();
            const programNumber = item.dataset.programNumber?.toLowerCase() || '';
            
            if (!programName.includes(searchTerm) && !programNumber.includes(searchTerm)) {
                shouldShow = false;
            }
        }
        
        item.style.display = shouldShow ? 'flex' : 'none';
    });
    
    updateVisibleProgramsCount();
}

/**
 * Reset filters
 */
function resetFilters() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.reset();
        applyFilters();
    }
}

/**
 * Update visible programs count
 */
function updateVisibleProgramsCount() {
    const visiblePrograms = document.querySelectorAll('.program-item[style*="flex"], .program-item:not([style*="none"])');
    const countElement = document.getElementById('visibleProgramsCount');
    
    if (countElement) {
        countElement.textContent = visiblePrograms.length;
    }
}

/**
 * Show progress section
 */
function showProgressSection() {
    const progressSection = document.getElementById('progressSection');
    if (progressSection) {
        progressSection.classList.add('active');
        
        // Animate progress bar
        const progressBar = progressSection.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = '100%';
            }, 100);
        }
    }
}

/**
 * Hide progress section
 */
function hideProgressSection() {
    const progressSection = document.getElementById('progressSection');
    if (progressSection) {
        progressSection.classList.remove('active');
    }
}

/**
 * Show results section
 */
function showResultsSection(results) {
    const resultsSection = document.getElementById('resultsSection');
    const resultsList = document.getElementById('resultsList');
    
    if (resultsSection && resultsList) {
        // Clear previous results
        resultsList.innerHTML = '';
        
        // Add results
        results.forEach(result => {
            const resultItem = document.createElement('div');
            resultItem.className = 'result-item';
            resultItem.innerHTML = `
                <div class="result-status ${result.success ? 'success' : 'error'}"></div>
                <div class="result-text">
                    <strong>${escapeHtml(result.program_name)}</strong>
                    <br><small class="text-muted">${escapeHtml(result.message)}</small>
                </div>
            `;
            resultsList.appendChild(resultItem);
        });
        
        resultsSection.classList.add('active');
    }
}

/**
 * Hide results section
 */
function hideResultsSection() {
    const resultsSection = document.getElementById('resultsSection');
    if (resultsSection) {
        resultsSection.classList.remove('active');
    }
}

/**
 * Show loading state for specific section
 */
function showLoadingState(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.opacity = '0.6';
        section.style.pointerEvents = 'none';
    }
}

/**
 * Hide loading state for specific section
 */
function hideLoadingState(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.opacity = '1';
        section.style.pointerEvents = 'auto';
    }
}

/**
 * Utility function to escape HTML
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        alert(`${title}: ${message}`);
    }
}

// Export functions for global access
window.AdminBulkAssign = {
    loadInitiativeDetails,
    performBulkAssignment,
    clearAllSelections,
    applyFilters,
    resetFilters
};