/**
 * User Table Manager
 * Handles user table interactions, refreshing, and updates
 */
function UserTableManager(formManagerParam) {
    // Use provided managers or create new ones if needed
    const formManager = formManagerParam || (window.UserFormManager ? UserFormManager() : null);
    
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
        
        // Now attach listeners to delete buttons as backup
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            // Check if this button already has our listener
            if (button.hasAttribute('data-listener-attached')) {
                return; // Skip if already has listener
            }
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const userId = this.getAttribute('data-user-id');
                const username = this.getAttribute('data-username');
                
                // Try to use the manage_users.js function first
                if (typeof showDeleteConfirmationModal === 'function') {
                    showDeleteConfirmationModal(username, userId, this);
                } else if (formManager) {
                    formManager.showDeleteForm(userId, username);
                } else {
                    console.error('No delete modal function available');
                    alert('Error: Could not initialize delete form');
                }
            });
            
            // Mark as having listener attached
            button.setAttribute('data-listener-attached', 'true');
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
                if (typeof window.showToast === 'function') {
                    window.showToast('Success', `User "${username}" ${statusText} successfully.`, 'success');
                } else {
                    alert(`User "${username}" ${statusText} successfully.`);
                }
                
                // Refresh the table to reflect the changes
                refreshTable();
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast('Error', data.error || 'Failed to update user status', 'danger');
                } else {
                    alert(data.error || 'Failed to update user status');
                }
            }
        })
        .catch(error => {
            console.error('Toggle active error:', error);
            if (typeof window.showToast === 'function') {
                window.showToast('Error', 'An unexpected error occurred while updating status', 'danger');
            } else {
                alert('An unexpected error occurred while updating status');
            }
        });
    }
    
    // Refresh the users table without reloading the page
    function refreshTable(page = 1, perPage = 20) {
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

        fetch(window.APP_URL + `/app/ajax/admin_user_tables.php?page=${page}&per_page=${perPage}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, statusText: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                if (html.trim() === '') {
                    console.warn('Empty response from server for table refresh. This might indicate an issue if tables were expected.');
                }
                tableWrapper.innerHTML = html;
                listenersAttached = false; // Reset flag to allow re-attachment
                attachEventListeners(); 
            })
            .catch(error => {
                console.error('Table refresh error:', error);
                tableWrapper.innerHTML = originalContent; // Restore original content on error
                listenersAttached = false; 
                attachEventListeners();
                if (typeof window.showToast === 'function') {
                    window.showToast('Error', `Failed to refresh user list: ${error.message}. Please try again.`, 'danger');
                } else {
                    alert(`Failed to refresh user list: ${error.message}. Please try again.`);
                }
            });
    }

    // Event delegation for pagination controls
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('user-table-page-link')) {
            e.preventDefault();
            const page = parseInt(e.target.getAttribute('data-page'));
            if (!isNaN(page) && page > 0) {
                refreshTable(page);
            }
        }
    });
    
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
        const tableManager = UserTableManager(formManager);
        
        // Attach event listeners once
        tableManager.attachEventListeners();
        
        // Make the table manager available globally for other scripts
        window.tableManager = tableManager;
    }
});
