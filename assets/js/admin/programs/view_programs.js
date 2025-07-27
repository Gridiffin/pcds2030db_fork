/**
 * Admin Programs - View Programs JavaScript
 * Handles functionality for the admin programs listing page
 */

// Import CSS for admin view programs
import '../../../css/admin/programs/view_programs.css';

// Import essential utilities
import '../../utilities/initialization.js';
import '../../utilities/dropdown_init.js';

// Import admin modern box dropdown functionality
import '../admin-modern-box-dropdown.js';

// Import main utilities including showToast
import '../../main.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize page components
    initializeFilters();
    initializeProgramBoxes();
    initializeBulkActions();
    initializeQuickActions();
    initializeDeleteModal();
    
    console.log('Admin view programs page with modern boxes initialized');
});

/**
 * Initialize filter functionality
 */
function initializeFilters() {
    const filterForm = document.getElementById('filterForm');
    const resetFiltersBtn = document.getElementById('resetFilters');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            resetFilters();
        });
    }
    
    // Auto-apply filters on change
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        input.addEventListener('change', debounce(applyFilters, 300));
    });
}

/**
 * Apply current filter settings
 */
function applyFilters() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Update URL and reload page with filters
    const currentUrl = new URL(window.location);
    currentUrl.search = params.toString();
    
    showLoadingState();
    window.location.href = currentUrl.toString();
}

/**
 * Reset all filters
 */
function resetFilters() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.reset();
        
        // Clear URL parameters
        const currentUrl = new URL(window.location);
        currentUrl.search = '';
        window.location.href = currentUrl.toString();
    }
}

/**
 * Initialize program boxes functionality
 */
function initializeProgramBoxes() {
    // Initialize modern box interactions
    initializeProgramBoxFiltering();
    initializeProgramBoxSorting();
    
    // Initialize program selection (if needed for bulk actions)
    initializeProgramSelection();
    
    console.log('Program boxes initialized');
}

/**
 * Initialize program box filtering
 */
function initializeProgramBoxFiltering() {
    const searchInput = document.getElementById('finalizedProgramSearch');
    const ratingFilter = document.getElementById('finalizedRatingFilter');
    const typeFilter = document.getElementById('finalizedTypeFilter');
    const agencyFilter = document.getElementById('finalizedAgencyFilter');
    const initiativeFilter = document.getElementById('finalizedInitiativeFilter');
    const resetButton = document.getElementById('resetFinalizedFilters');
    
    // Add event listeners for real-time filtering
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterProgramBoxes, 300));
    }
    
    [ratingFilter, typeFilter, agencyFilter, initiativeFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', filterProgramBoxes);
        }
    });
    
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            // Reset all filters
            if (searchInput) searchInput.value = '';
            [ratingFilter, typeFilter, agencyFilter, initiativeFilter].forEach(filter => {
                if (filter) filter.value = '';
            });
            filterProgramBoxes();
        });
    }
}

/**
 * Filter program boxes based on current filter values
 */
function filterProgramBoxes() {
    const searchValue = document.getElementById('finalizedProgramSearch')?.value.toLowerCase() || '';
    const ratingValue = document.getElementById('finalizedRatingFilter')?.value || '';
    const typeValue = document.getElementById('finalizedTypeFilter')?.value || '';
    const agencyValue = document.getElementById('finalizedAgencyFilter')?.value || '';
    const initiativeValue = document.getElementById('finalizedInitiativeFilter')?.value || '';
    
    const programBoxes = document.querySelectorAll('.admin-program-box');
    let visibleCount = 0;
    
    programBoxes.forEach(box => {
        let isVisible = true;
        
        // Text search
        if (searchValue) {
            const programName = box.querySelector('.admin-program-name')?.textContent.toLowerCase() || '';
            const programNumber = box.querySelector('.admin-program-number')?.textContent.toLowerCase() || '';
            const searchText = programName + ' ' + programNumber;
            
            if (!searchText.includes(searchValue)) {
                isVisible = false;
            }
        }
        
        // Rating filter
        if (ratingValue) {
            const boxRating = box.getAttribute('data-status') || '';
            const ratingMap = {
                'target-achieved': 'monthly_target_achieved',
                'on-track-yearly': 'on_track_for_year',
                'severe-delay': 'severe_delay',
                'not-started': 'not_started'
            };
            
            if (ratingMap[ratingValue] !== boxRating) {
                isVisible = false;
            }
        }
        
        // Type filter
        if (typeValue) {
            const boxType = box.getAttribute('data-program-type') || '';
            if (boxType !== typeValue) {
                isVisible = false;
            }
        }
        
        // Agency filter
        if (agencyValue) {
            const boxAgency = box.getAttribute('data-agency-id') || '';
            if (boxAgency !== agencyValue) {
                isVisible = false;
            }
        }
        
        // Initiative filter
        if (initiativeValue) {
            if (initiativeValue === 'no-initiative') {
                const boxInitiative = box.getAttribute('data-initiative-id') || '0';
                if (boxInitiative !== '0') {
                    isVisible = false;
                }
            } else {
                const boxInitiative = box.getAttribute('data-initiative-id') || '0';
                if (boxInitiative !== initiativeValue) {
                    isVisible = false;
                }
            }
        }
        
        // Show/hide box
        box.style.display = isVisible ? 'block' : 'none';
        if (isVisible) visibleCount++;
    });
    
    // Update count
    const countElement = document.getElementById('finalized-count');
    if (countElement) {
        countElement.textContent = visibleCount;
    }
    
    // Show empty state if no results
    updateEmptyState(visibleCount === 0);
}

/**
 * Initialize program box sorting
 */
function initializeProgramBoxSorting() {
    // This would be implemented if sorting functionality is needed
    // For now, programs are sorted by the server query
    console.log('Program box sorting initialized (server-side sorted)');
}

/**
 * Update empty state display
 */
function updateEmptyState(isEmpty) {
    const container = document.getElementById('finalizedProgramsContainer');
    if (!container) return;
    
    let emptyState = container.querySelector('.admin-programs-empty-state');
    
    if (isEmpty && !emptyState) {
        // Create empty state
        emptyState = document.createElement('div');
        emptyState.className = 'admin-programs-empty-state';
        emptyState.innerHTML = `
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="empty-title">No Programs Found</h3>
            <p class="empty-description">No programs match your current filter criteria. Try adjusting your filters or search terms.</p>
        `;
        container.appendChild(emptyState);
    } else if (!isEmpty && emptyState) {
        // Remove empty state
        emptyState.remove();
    }
}

/**
 * Initialize program selection checkboxes
 */
function initializeProgramSelection() {
    const selectAllCheckbox = document.getElementById('selectAllPrograms');
    const programCheckboxes = document.querySelectorAll('.program-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            programCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsVisibility();
        });
    }
    
    programCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateBulkActionsVisibility();
        });
    });
}

/**
 * Update select all checkbox state
 */
function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('selectAllPrograms');
    const programCheckboxes = document.querySelectorAll('.program-checkbox');
    
    if (selectAllCheckbox && programCheckboxes.length > 0) {
        const checkedCount = Array.from(programCheckboxes).filter(cb => cb.checked).length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < programCheckboxes.length;
        selectAllCheckbox.checked = checkedCount === programCheckboxes.length;
    }
}

/**
 * Initialize bulk actions
 */
function initializeBulkActions() {
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkAssignBtn = document.getElementById('bulkAssignBtn');
    
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const selectedPrograms = getSelectedPrograms();
            if (selectedPrograms.length > 0) {
                confirmBulkDelete(selectedPrograms);
            }
        });
    }
    
    if (bulkAssignBtn) {
        bulkAssignBtn.addEventListener('click', function() {
            const selectedPrograms = getSelectedPrograms();
            if (selectedPrograms.length > 0) {
                openBulkAssignModal(selectedPrograms);
            }
        });
    }
}

/**
 * Update bulk actions visibility
 */
function updateBulkActionsVisibility() {
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = getSelectedPrograms().length;
    
    if (bulkActionsBar) {
        if (selectedCount > 0) {
            bulkActionsBar.classList.add('show');
            updateBulkActionsCount(selectedCount);
        } else {
            bulkActionsBar.classList.remove('show');
        }
    }
}

/**
 * Update bulk actions count display
 */
function updateBulkActionsCount(count) {
    const countElement = document.getElementById('selectedCount');
    if (countElement) {
        countElement.textContent = count;
    }
}

/**
 * Get selected program IDs
 */
function getSelectedPrograms() {
    const programCheckboxes = document.querySelectorAll('.program-checkbox:checked');
    return Array.from(programCheckboxes).map(checkbox => ({
        id: checkbox.value,
        name: checkbox.dataset.programName
    }));
}

/**
 * Initialize quick actions dropdowns
 */
function initializeQuickActions() {
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const programId = this.dataset.programId;
            const programName = this.dataset.programName;
            
            handleQuickAction(action, programId, programName);
        });
    });
}

/**
 * Handle quick action click
 */
function handleQuickAction(action, programId, programName) {
    switch (action) {
        case 'view':
            window.location.href = `view_program.php?id=${programId}`;
            break;
        case 'edit':
            window.location.href = `edit_program.php?id=${programId}`;
            break;
        case 'delete':
            confirmSingleDelete(programId, programName);
            break;
        case 'clone':
            cloneProgram(programId);
            break;
        default:
            console.warn('Unknown action:', action);
    }
}

/**
 * Initialize delete modal functionality
 */
function initializeDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteProgramForm');
    
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
            submitBtn.disabled = true;
            
            // Submit form
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    // Close modal and refresh page
                    const modal = bootstrap.Modal.getInstance(deleteModal);
                    modal.hide();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error', data.error || 'Delete failed', 'error');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showToast('Error', 'An error occurred while deleting', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}

/**
 * Confirm single program deletion
 */
function confirmSingleDelete(programId, programName) {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        // Update modal content
        const programNameDisplay = deleteModal.querySelector('#programNameDisplay');
        const programIdInput = deleteModal.querySelector('#programIdInput');
        
        if (programNameDisplay) {
            programNameDisplay.textContent = programName;
        }
        if (programIdInput) {
            programIdInput.value = programId;
        }
        
        // Show modal
        const modal = new bootstrap.Modal(deleteModal);
        modal.show();
    }
}

/**
 * Confirm bulk program deletion
 */
function confirmBulkDelete(selectedPrograms) {
    const confirmMessage = `Are you sure you want to delete ${selectedPrograms.length} selected program(s)? This action cannot be undone.`;
    
    if (confirm(confirmMessage)) {
        performBulkDelete(selectedPrograms);
    }
}

/**
 * Perform bulk deletion
 */
function performBulkDelete(selectedPrograms) {
    const programIds = selectedPrograms.map(p => p.id);
    
    showLoadingState();
    
    fetch('bulk_delete_programs.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ program_ids: programIds })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            showToast('Success', `${data.deleted_count} program(s) deleted successfully`, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error', data.error || 'Bulk delete failed', 'error');
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('Bulk delete error:', error);
        showToast('Error', 'An error occurred during bulk deletion', 'error');
    });
}

/**
 * Clone a program
 */
function cloneProgram(programId) {
    showLoadingState();
    
    fetch('clone_program.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ program_id: programId })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            showToast('Success', 'Program cloned successfully', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error', data.error || 'Clone failed', 'error');
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('Clone error:', error);
        showToast('Error', 'An error occurred while cloning', 'error');
    });
}

/**
 * Show loading state
 */
function showLoadingState() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    } else {
        // Create loading overlay if it doesn't exist
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-spinner"></div>
        `;
        document.body.appendChild(overlay);
    }
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    // Use existing toast function if available, otherwise create simple alert
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        alert(`${title}: ${message}`);
    }
}

// Export functions for global access
window.AdminViewPrograms = {
    applyFilters,
    resetFilters,
    filterProgramBoxes,
    confirmSingleDelete,
    confirmBulkDelete,
    cloneProgram,
    showLoadingState,
    hideLoadingState
};