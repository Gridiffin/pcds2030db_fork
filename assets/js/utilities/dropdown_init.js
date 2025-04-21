/**
 * Dropdown Initialization Script
 * Ensures that Bootstrap 5 dropdowns work properly throughout the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Basic dropdown initialization for all dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        new bootstrap.Dropdown(dropdown);
    });

    // Special handling for notifications dropdown
    const notificationDropdown = document.getElementById('notificationsDropdown');
    if (notificationDropdown) {
        // Prevent dropdown from closing when clicking inside notification items
        const notificationMenu = document.querySelector('.notification-dropdown');
        if (notificationMenu) {
            notificationMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
});