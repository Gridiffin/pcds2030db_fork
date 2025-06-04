/**
 * Dropdown Initialization Script
 * Ensures that Bootstrap 5 dropdowns work properly throughout the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if Bootstrap is available
    if (typeof bootstrap === 'undefined') {
        console.warn('Bootstrap is not loaded. Dropdowns may not work properly.');
        return;
    }

    // Add necessary event listeners to dropdown toggles.
    // Bootstrap's data-bs-toggle="dropdown" should handle the core initialization.
    // This script focuses on ensuring consistent behavior like preventDefault for href="#"
    // and adding accessibility features like keyboard navigation.
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        // Prevent default action on dropdown toggle click if href="#"
        dropdown.addEventListener('click', function(e) {
            if (dropdown.getAttribute('href') === '#') {
                e.preventDefault();
            }
            // Note: We are not calling new bootstrap.Dropdown(dropdown) here.
            // We rely on Bootstrap's own data-attribute based initialization.
            // If a dropdown instance is needed for bsDropdown.toggle(),
            // it should be retrieved using bootstrap.Dropdown.getInstance(dropdown).
        });

        // Handle keyboard navigation for accessibility
        dropdown.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const bsDropdownInstance = bootstrap.Dropdown.getInstance(dropdown);
                if (bsDropdownInstance) {
                    bsDropdownInstance.toggle();
                } else {
                    // Fallback or if Bootstrap didn't auto-initialize (e.g. dynamically added content)
                    // This case should be rare if using data-bs-toggle properly.
                    try {
                        const newBsDropdown = new bootstrap.Dropdown(dropdown);
                        newBsDropdown.toggle();
                    } catch (err) {
                        console.warn('Failed to toggle dropdown on keydown for element:', dropdown, err);
                    }
                }
            }
        });
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
