/**
 * Admin Modern Box Dropdown Handler
 * Based on agency view programs dropdown implementation
 */

// Global toggle dropdown function for admin custom dropdowns
window.toggleAdminDropdown = function(button) {
    const dropdown = button.nextElementSibling;
    if (!dropdown || !dropdown.classList.contains('admin-dropdown-menu-custom')) {
        return;
    }
    
    // Find the program box container
    const programBox = button.closest('.admin-program-box');
    
    // Close all other dropdowns first and remove dropdown-active class
    document.querySelectorAll('.admin-dropdown-menu-custom.show').forEach(menu => {
        if (menu !== dropdown) {
            menu.classList.remove('show');
            const otherProgramBox = menu.closest('.admin-program-box');
            if (otherProgramBox) {
                otherProgramBox.classList.remove('admin-dropdown-active');
            }
        }
    });
    
    // Toggle current dropdown
    const isShowing = dropdown.classList.contains('show');
    dropdown.classList.toggle('show');
    
    // Update program box state
    if (programBox) {
        if (isShowing) {
            programBox.classList.remove('admin-dropdown-active');
            // Remove body class if no dropdowns are open
            if (!document.querySelector('.admin-dropdown-menu-custom.show')) {
                document.body.classList.remove('admin-dropdown-open');
            }
        } else {
            programBox.classList.add('admin-dropdown-active');
            // Add body class when dropdown opens
            document.body.classList.add('admin-dropdown-open');
        }
    }
    
    // Close dropdown when clicking outside
    setTimeout(() => {
        document.addEventListener('click', function closeDropdown(event) {
            if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
                if (programBox) {
                    programBox.classList.remove('admin-dropdown-active');
                    // Remove body class if no dropdowns are open
                    if (!document.querySelector('.admin-dropdown-menu-custom.show')) {
                        document.body.classList.remove('admin-dropdown-open');
                    }
                }
                document.removeEventListener('click', closeDropdown);
            }
        });
    }, 0);
};

// Initialize dropdown functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Modern Box Dropdown functionality initialized');
});