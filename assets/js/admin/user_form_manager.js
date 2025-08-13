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
     * Show modal to delete a user
     * @param {string} userId User ID
     * @param {string} username Username
     */
    function showDeleteForm(userId, username) {
        // Create or get the delete form modal
        let deleteModal = document.getElementById('deleteUserModal');
        
        if (!deleteModal) {
            deleteModal = document.createElement('div');
            deleteModal.className = 'modal fade';
            deleteModal.id = 'deleteUserModal';
            deleteModal.setAttribute('tabindex', '-1');
            deleteModal.setAttribute('aria-labelledby', 'deleteUserModalLabel');
            deleteModal.setAttribute('aria-hidden', 'true');
            
            deleteModal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the user <strong id="delete-username"></strong>?</p>
                            <p>This action cannot be undone.</p>
                            <form id="deleteUserForm" method="post">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" id="delete-user-id">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete User</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(deleteModal);
            
            // Create Bootstrap modal instance only once
            deleteModalInstance = new bootstrap.Modal(deleteModal, {
                backdrop: 'static', // Prevent backdrop click issues
                keyboard: true
            });
            
            // Add event listener to the confirm button - only once
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                document.getElementById('deleteUserForm').submit();
            });
            
            // Add cleanup event listeners to prevent backdrop issues
            deleteModal.addEventListener('hidden.bs.modal', function () {
                // Ensure proper cleanup when modal is hidden
                document.body.classList.remove('modal-open');
                
                // Remove any remaining backdrops
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                
                // Reset modal state
                deleteModal.style.display = 'none';
                deleteModal.setAttribute('aria-hidden', 'true');
                deleteModal.removeAttribute('aria-modal');
                deleteModal.removeAttribute('role');
            });
        }
        
        // Set the values
        document.getElementById('delete-username').textContent = username;
        document.getElementById('delete-user-id').value = userId;
        
        // Show the modal using the stored instance or create new one if needed
        if (deleteModalInstance) {
            deleteModalInstance.show();
        } else {
            // Fallback: create instance if it doesn't exist
            deleteModalInstance = new bootstrap.Modal(deleteModal);
            deleteModalInstance.show();
        }
    }

    // Other form methods can go here...
    
    // Initialize on creation
    initialize();

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
        
        // Force cleanup of any remaining modal artifacts
        setTimeout(() => {
            document.body.classList.remove('modal-open');
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        }, 300);
    }

    // Public API
    return {
        showDeleteForm,
        hideForm
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
