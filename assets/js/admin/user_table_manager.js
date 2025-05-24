/**
 * User Table Manager
 * Handles user table interactions, refreshing, and updates
 */
function UserTableManager(formManagerParam, toastManagerParam) {
    // Use provided managers or create new ones if needed
    const formManager = formManagerParam || (window.UserFormManager ? UserFormManager() : null);
    const toastManager = toastManagerParam || (window.ToastManager ? ToastManager() : null);
    
    // Flag to track if event listeners have been attached
    let listenersAttached = false;
    
    // Attach event listeners to table actions
    function attachEventListeners() {
        // Prevent attaching listeners multiple times
        if (listenersAttached) {
            console.log('Event listeners already attached, skipping');
            return;
        }
        
        // Remove existing listeners from delete buttons by cloning and replacing them
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });
        
        // Remove existing listeners from toggle buttons by cloning and replacing them
        document.querySelectorAll('.toggle-active-btn').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });
        
        // Now attach listeners to delete buttons
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const username = this.getAttribute('data-username');
                
                if (formManager) {
                    formManager.showDeleteForm(userId, username);
                } else {
                    console.error('Form manager not available');
                    alert('Error: Could not initialize delete form');
                }
            });
        });
        
        // Add event listeners for status toggle buttons
        document.querySelectorAll('.toggle-active-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const username = this.getAttribute('data-username');
                const currentStatus = parseInt(this.getAttribute('data-status'));
                const newStatus = currentStatus === 1 ? 0 : 1;
                
                // Confirm before changing status
                const statusText = newStatus === 1 ? 'activate' : 'deactivate';
                if (confirm(`Are you sure you want to ${statusText} the user "${username}"?`)) {
                    toggleUserActive(userId, newStatus, username);
                }
            });
        });
        
        // Mark listeners as attached
        listenersAttached = true;
    }
      // Toggle user active status
    function toggleUserActive(userId, isActive, username) {
        const formData = new FormData();
        formData.append('action', 'toggle_active');
        formData.append('user_id', userId);
        formData.append('is_active', isActive);
        
        fetch(`${window.APP_URL}/app/handlers/admin/process_user.php`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const statusText = isActive === 1 ? 'activated' : 'deactivated';
                toastManager.show('Success', `User "${username}" ${statusText} successfully.`, 'success');
                
                // Refresh the table to reflect the changes
                refreshTable();
            } else {
                toastManager.show('Error', data.error || 'Failed to update user status', 'danger');
            }
        })
        .catch(error => {
            console.error('Toggle active error:', error);
            toastManager.show('Error', 'An unexpected error occurred while updating status', 'danger');
        });
    }
    
    // Refresh the users table without reloading the page
    function refreshTable() {
        const tableWrapper = document.getElementById('userTablesWrapper');
        if (!tableWrapper) {
            console.error('User tables wrapper (#userTablesWrapper) not found. Reloading page.');
            window.location.reload();
            return;
        }

        const originalContent = tableWrapper.innerHTML;
        tableWrapper.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="ms-2 mb-0 text-muted">Refreshing user list...</p>
            </div>
        `;

        fetch(window.APP_URL + '/app/views/admin/manage_users.php?ajax_table=1')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, statusText: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                if (html.trim() === '') {
                    console.warn('Empty response from server for table refresh. This might indicate an issue if tables were expected.');
                    // Potentially display a 'No users found' message or handle as appropriate
                    // For now, we'll still replace the content, which might clear the tables if the response is truly empty.
                }
                tableWrapper.innerHTML = html;
                
                listenersAttached = false; // Reset flag to allow re-attachment
                attachEventListeners(); 
                
                // highlightTableRows(); // Optional: uncomment if you want this visual effect
            })
            .catch(error => {
                console.error('Table refresh error:', error);
                tableWrapper.innerHTML = originalContent; // Restore original content on error
                
                // Re-attach listeners to the restored content
                listenersAttached = false; 
                attachEventListeners();

                if (toastManager && typeof toastManager.show === 'function') {
                    toastManager.show('Error', `Failed to refresh user list: ${error.message}. Please try again.`, 'danger');
                } else {
                    alert(`Failed to refresh user list: ${error.message}. Please try again.`);
                }
            });
    }
    
    // Visual effect for newly loaded content
    function highlightTableRows() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.backgroundColor = '#f0f7ff';
            row.style.transition = 'background-color 1s ease';
            
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 1000 + (index * 100));
        });
    }
    
    // Animated row deletion effect
    function animateRowDeletion(userId) {
        const userRows = document.querySelectorAll('tbody tr');
        const deletedRow = Array.from(userRows).find(row => {
            const deleteBtn = row.querySelector('.delete-user-btn');
            return deleteBtn && deleteBtn.getAttribute('data-user-id') === userId;
        });
        
        if (deletedRow) {
            deletedRow.style.backgroundColor = '#ffe6e6';
            deletedRow.style.transition = 'all 0.5s';
            
            setTimeout(() => {
                deletedRow.style.opacity = '0';
                deletedRow.style.transform = 'translateX(20px)';
                
                setTimeout(() => refreshTable(), 500);
            }, 300);
        } else {
            refreshTable();
        }
    }
    
    // Return public API
    return {
        attachEventListeners,
        refreshTable,
        animateRowDeletion,
        toggleUserActive // Expose the toggle function
    };
}

// Create a single instance when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize only if we're on the user management page (check for table presence)
    if (document.querySelector('.table') && typeof UserFormManager === 'function') {
        console.log('Initializing user table manager');
        
        // Create instances with proper dependency injection
        const formManager = UserFormManager();
        const toastManager = window.ToastManager ? ToastManager() : null;
        const tableManager = UserTableManager(formManager, toastManager);
        
        // Attach event listeners once
        tableManager.attachEventListeners();
        
        // Make the table manager available globally for other scripts
        window.tableManager = tableManager;
    }
});
