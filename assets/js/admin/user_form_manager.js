/**
 * User Form Manager
 * Handles user form interactions for admin user management
 */

// Make sure event handlers are only attached once
let deleteButtonsInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log("User Form Manager initialized");
    
    // Initialize delete buttons (only once)
    if (!deleteButtonsInitialized) {
        initializeDeleteButtons();
        deleteButtonsInitialized = true;
    }
});

/**
 * Initialize delete buttons with confirmation dialog
 */
function initializeDeleteButtons() {
    // Use event delegation to handle delete buttons
    document.body.addEventListener('click', function(e) {
        // Check if clicked element or its parent is a delete button
        const deleteButton = e.target.closest('.delete-user-btn');
        if (!deleteButton) return;
        
        e.preventDefault();
        
        const userId = deleteButton.getAttribute('data-user-id');
        const username = deleteButton.getAttribute('data-username');
        
        // Only show a single confirmation dialog
        if (confirm(`Are you sure you want to delete the user "${username}"?`)) {
            deleteUser(userId, username);
        }
    });
}

/**
 * Delete a user
 */
function deleteUser(userId, username) {
    // Create form data
    const formData = new FormData();
    formData.append('action', 'delete_user');
    formData.append('user_id', userId);
    
    // Send the request
    fetch(`${APP_URL}/admin/process_user.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('User deleted successfully');
            // Reload the page to update the user list
            window.location.reload();
        } else {
            // Show error message
            alert('Error: ' + (data.error || 'Failed to delete user'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the user');
    });
}
