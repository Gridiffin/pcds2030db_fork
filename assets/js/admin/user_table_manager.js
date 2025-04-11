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
        
        // First remove any existing listeners (if possible)
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            // Create new cloned button to remove all event listeners
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
        
        // Edit user buttons can be handled here too
        
        // Mark listeners as attached
        listenersAttached = true;
    }
    
    // Refresh the users table without reloading the page
    function refreshTable() {
        // Show a loading spinner in the table area
        const mainContent = document.querySelector('.card-body');
        if (mainContent) {
            const originalContent = mainContent.innerHTML;
            mainContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Refreshing user list...</p>
                </div>
            `;
            
            // Fetch updated content from the server
            fetch(window.location.href + '?ajax_table=1')  // Add a parameter to avoid POST processing
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Extract the users table
                    const newTable = doc.querySelector('.table-responsive');
                    const currentTable = document.querySelector('.table-responsive');
                    
                    if (newTable && currentTable) {
                        currentTable.parentNode.replaceChild(newTable, currentTable);
                    } else {
                        window.location.reload();
                        return;
                    }
                    
                    // Update the user count badge
                    const userCountBadge = doc.querySelector('.card-header .badge');
                    const currentBadge = document.querySelector('.card-header .badge');
                    if (userCountBadge && currentBadge) {
                        currentBadge.textContent = userCountBadge.textContent;
                    }
                    
                    // Re-attach event listeners to new elements
                    attachEventListeners();
                    
                    // Apply highlight effect to indicate fresh content
                    highlightTableRows();
                })
                .catch(error => {
                    // Restore original content on error
                    mainContent.innerHTML = originalContent;
                    toastManager.show('Error', 'Failed to refresh content. Please try again.', 'danger');
                    console.error('Table refresh error:', error);
                });
        } else {
            window.location.reload();
        }
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
        animateRowDeletion
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
    }
});
