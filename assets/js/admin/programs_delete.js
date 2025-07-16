/**
 * Admin Programs Delete Functionality
 * Simple implementation focused on making delete buttons work with Bootstrap modal
 */
document.addEventListener('DOMContentLoaded', function() {
    initDeleteButtons();
    initMoreActionsButtons();
    initModalEventHandlers();
});

/**
 * Initialize delete buttons functionality
 */
function initDeleteButtons() {
    // Get all delete buttons
    const deleteButtons = document.querySelectorAll('.delete-program-btn');
    
    deleteButtons.forEach((button, index) => {
        // Add Bootstrap modal data attributes for reliable modal triggering
        button.setAttribute('data-bs-toggle', 'modal');
        button.setAttribute('data-bs-target', '#deleteModal');
        
        button.addEventListener('click', function(e) {
            const programId = this.getAttribute('data-id');
            const programName = this.getAttribute('data-name');
            
            // Set the program details in the modal
            const programNameDisplay = document.getElementById('program-name-display');
            const programIdInput = document.getElementById('program-id-input');
            const deleteModal = document.getElementById('deleteModal');
            
            if (programNameDisplay && programIdInput) {
                programNameDisplay.textContent = programName;
                programIdInput.value = programId;
            }
            
            // Manual modal trigger as fallback
            if (deleteModal && typeof bootstrap !== 'undefined') {
                try {
                    const modal = new bootstrap.Modal(deleteModal);
                    modal.show();
                } catch (error) {
                    console.error('Manual modal trigger failed:', error);
                }
            }
        });
    });
}

/**
 * Initialize modal event handlers
 */
function initModalEventHandlers() {
    const deleteModal = document.getElementById('deleteModal');
    
    if (deleteModal) {
        // Handle modal show event
        deleteModal.addEventListener('show.bs.modal', function(event) {
            // Modal is about to show
        });
        
        // Handle modal shown event
        deleteModal.addEventListener('shown.bs.modal', function(event) {
            // Modal is now visible
        });
        
        // Handle modal hide event
        deleteModal.addEventListener('hide.bs.modal', function(event) {
            // Modal is about to hide
        });
        
        // Handle modal hidden event
        deleteModal.addEventListener('hidden.bs.modal', function(event) {
            // Modal is now hidden
        });
    }
}

/**
 * Initialize more actions buttons functionality
 */
function initMoreActionsButtons() {
    const moreActionsButtons = document.querySelectorAll('.more-actions-btn');
    
    moreActionsButtons.forEach((button, index) => {
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
    try {
        if (typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    } catch (error) {
        console.error('Error showing more actions modal:', error);
    }
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
                        <!-- Actions will be populated dynamically -->
                    </div>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

/**
 * Update more actions modal content with program-specific actions
 */
function updateMoreActionsModalContent(modal, programId, programName, programType) {
    // Update program info
    const programNameDisplay = modal.querySelector('.program-name-display');
    const programTypeDisplay = modal.querySelector('.program-type-display');
    const actionsList = modal.querySelector('.actions-list');
    
    if (programNameDisplay) {
        programNameDisplay.textContent = programName;
    }
    
    if (programTypeDisplay) {
        programTypeDisplay.textContent = programType === 'assigned' ? 'Assigned Program' : 'Agency-Created Program';
    }
    
    // Create action buttons
    if (actionsList) {
        actionsList.innerHTML = `
            <div class="d-grid gap-2">
                <a href="view_program.php?id=${programId}" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-2"></i>View Program Details
                </a>
                <a href="edit_program.php?id=${programId}" class="btn btn-outline-secondary">
                    <i class="fas fa-edit me-2"></i>Edit Program
                </a>
                <hr>
                <button type="button" class="btn btn-outline-danger" onclick="triggerDeleteFromModal(${programId}, '${programName.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash me-2"></i>Delete Program
                </button>
            </div>
        `;
    }
}

/**
 * Trigger delete from more actions modal
 */
function triggerDeleteFromModal(programId, programName) {
    // Close the more actions modal first
    const moreActionsModal = bootstrap.Modal.getInstance(document.getElementById('moreActionsModal'));
    if (moreActionsModal) {
        moreActionsModal.hide();
    }
    
    // Trigger the delete modal
    setTimeout(() => {
        const programNameDisplay = document.getElementById('program-name-display');
        const programIdInput = document.getElementById('program-id-input');
        
        if (programNameDisplay && programIdInput) {
            programNameDisplay.textContent = programName;
            programIdInput.value = programId;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    }, 300); // Small delay to allow more actions modal to close
}
