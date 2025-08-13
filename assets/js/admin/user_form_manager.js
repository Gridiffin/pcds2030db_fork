/**
 * User Form Manager
 * Handles dynamic user forms for add, edit, delete operations
 */
function UserFormManager() {
    // Private variables - moved inside function scope to prevent redeclarations
    const formContainer = document.getElementById('formContainer');
    
    // Flag for tracking initialization - moved inside the closure
    let initialized = false;
    let deleteModalInstance = null; // Store the modal instance to prevent multiple instances

    /**
     * Initialize the form manager
     */
    function initialize() {
        if (initialized) return; // Prevent multiple initializations
        
        // Initial setup tasks
        console.log('User form manager initialized');
        initialized = true;
    }

    /**
     * Show modal to delete a user (Fallback implementation)
     * @param {string} userId User ID
     * @param {string} username Username
     */
    function showDeleteForm(userId, username) {
        // Check if manage_users.js modal already exists
        const existingModal = document.getElementById('deleteUserModal');
        if (existingModal) {
            return;
        }
        
        // Create fallback modal
        const deleteModal = document.createElement('div');
        deleteModal.className = 'modal fade';
        deleteModal.id = 'deleteUserModal';
        deleteModal.setAttribute('tabindex', '-1');
        deleteModal.setAttribute('aria-labelledby', 'deleteUserModalLabel');
        deleteModal.setAttribute('aria-hidden', 'true');
        
        deleteModal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the user <strong>${username}</strong>?</p>
                        <p>This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete User</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(deleteModal);
        
        // Create Bootstrap modal instance
        deleteModalInstance = new bootstrap.Modal(deleteModal);
        
        // Handle confirm button
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            // Simple form submission fallback
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_user';
            
            const userIdInput = document.createElement('input');
            userIdInput.type = 'hidden';
            userIdInput.name = 'user_id';
            userIdInput.value = userId;
            
            form.appendChild(actionInput);
            form.appendChild(userIdInput);
            document.body.appendChild(form);
            form.submit();
        });
        
        // Cleanup when hidden
        deleteModal.addEventListener('hidden.bs.modal', function () {
            deleteModal.remove();
            document.body.classList.remove('modal-open');
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        });
        
        deleteModalInstance.show();
    }

    // Other form methods can go here...
    
    // Initialize on creation
    initialize();

    /**
     * Force cleanup of any modal artifacts - safety net function
     */
    function forceCleanup() {
        // Remove modal-open class
        document.body.classList.remove('modal-open');
        
        // Remove all modal backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Reset body styles that Bootstrap modals might have set
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // Hide any visible modals
        const visibleModals = document.querySelectorAll('.modal.show');
        visibleModals.forEach(modal => {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
        });
    }

    /**
     * Hide any open forms/modals
     */
    function hideForm() {
        // Hide any open Bootstrap modals properly
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
        
        // Clean up our specific modal instance
        if (deleteModalInstance) {
            deleteModalInstance.hide();
        }
        
        // Force cleanup after a short delay
        setTimeout(() => {
            forceCleanup();
        }, 300);
    }

    // Public API
    return {
        showDeleteForm,
        hideForm,
        forceCleanup // Expose cleanup function for emergency use
        // Other public methods...
    };
}

// Create a single global instance
window.UserFormManager = UserFormManager;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // This will be initialized later when needed, not automatically
    console.log('User form manager script loaded');
});
