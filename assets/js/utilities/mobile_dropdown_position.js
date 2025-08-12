/**
 * Mobile Notification Dropdown Positioning
 * Ensures notification dropdown stays within viewport bounds on mobile devices
 */

/**
 * Adjust dropdown position to stay within viewport bounds
 */
function adjustDropdownPosition() {
    const notificationDropdown = document.getElementById('notificationsDropdown');
    const dropdownMenu = document.querySelector('.notification-dropdown');
    
    if (!notificationDropdown || !dropdownMenu) return;
    
    // Only apply on mobile devices
    if (window.innerWidth <= 767) {
        const rect = notificationDropdown.getBoundingClientRect();
        const dropdownWidth = 280; // Min width from CSS
        const margin = 20; // Minimum margin from edges
        
        // Calculate if dropdown would overflow
        const wouldOverflow = (rect.right - dropdownWidth) < margin;
        
        if (wouldOverflow) {
            // Calculate how much to move left
            const adjustment = margin - (rect.right - dropdownWidth);
            dropdownMenu.style.transform = `translateX(-${10 + adjustment}px)`;
        } else {
            // Reset to default
            dropdownMenu.style.transform = 'translateX(-10px)';
        }
    } else {
        // Reset for desktop
        dropdownMenu.style.transform = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const notificationDropdown = document.getElementById('notificationsDropdown');
    const dropdownMenu = document.querySelector('.notification-dropdown');
    
    if (notificationDropdown && dropdownMenu) {
        // Adjust position when dropdown is shown
        notificationDropdown.addEventListener('shown.bs.dropdown', adjustDropdownPosition);
        
        // Adjust position on window resize
        window.addEventListener('resize', function() {
            if (notificationDropdown.getAttribute('aria-expanded') === 'true') {
                adjustDropdownPosition();
            }
        });
        
        // Prevent dropdown from closing when clicking inside on mobile
        if (window.innerWidth <= 767) {
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
});

// Additional helper for notification interactions on mobile
document.addEventListener('DOMContentLoaded', function() {
    // Improve touch interactions for notification items on mobile
    const notificationItems = document.querySelectorAll('.notification-item');
    
    notificationItems.forEach(item => {
        // Add better touch feedback
        item.addEventListener('touchstart', function() {
            this.style.backgroundColor = 'rgba(var(--forest-light-rgb), 0.1)';
        });
        
        item.addEventListener('touchend', function() {
            setTimeout(() => {
                this.style.backgroundColor = '';
            }, 150);
        });
    });
});
