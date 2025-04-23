/**
 * Agency Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle refresh button click
    const refreshButton = document.getElementById('refreshPage');
    if (refreshButton) {
        refreshButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add loading state
            this.classList.add('loading');
            
            // Change button text
            const originalIcon = this.querySelector('i').className;
            const originalText = this.querySelector('span')?.innerText || 'Refresh Data';
            
            // Update button
            this.innerHTML = `<i class="fas fa-sync-alt fa-spin"></i> <span>Refreshing...</span>`;
            
            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
    }
    
    // Initialize the program tabs
    const tabElements = document.querySelectorAll('.program-table-tabs .nav-link');
    if (tabElements.length > 0) {
        tabElements.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs and hide all content
                tabElements.forEach(t => t.classList.remove('active'));
                const tabContents = document.querySelectorAll('.tab-pane');
                tabContents.forEach(content => content.classList.remove('show', 'active'));
                
                // Add active class to clicked tab and show its content
                this.classList.add('active');
                const targetId = this.getAttribute('href').substring(1);
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('show', 'active');
                }
            });
        });
    }
    
    // Handle notification actions
    const notificationItems = document.querySelectorAll('.notification-item');
    if (notificationItems.length > 0) {
        notificationItems.forEach(item => {
            // Mark notification as read when clicked
            item.addEventListener('click', function() {
                if (this.classList.contains('unread')) {
                    this.classList.remove('unread');
                    
                    // Optional: Send AJAX request to mark as read in the database
                    const notificationId = this.getAttribute('data-id');
                    if (notificationId) {
                        markNotificationAsRead(notificationId);
                    }
                }
            });
        });
    }
    
    // Function to mark notification as read via AJAX
    function markNotificationAsRead(notificationId) {
        // This is a placeholder for the AJAX call
        // Implement this when you have the backend endpoint ready
        console.log(`Marking notification ${notificationId} as read`);
        
        // Example AJAX implementation:
        /*
        fetch('ajax/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            updateNotificationCounter();
        })
        .catch((error) => {
            console.error('Error:', error);
        });
        */
    }
    
    // Function to update notification counter
    function updateNotificationCounter() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        const badge = document.querySelector('.notification-badge');
        
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Initialize the program type filter
    initProgramTypeFilter();
});

/**
 * Initialize the program type filter functionality
 */
function initProgramTypeFilter() {
    const filterSelect = document.getElementById('dashboardProgramTypeFilter');
    const programTable = document.getElementById('dashboardProgramsTable');
    const programCountBadge = document.getElementById('programCount');
    
    if (!filterSelect || !programTable) return;
    
    filterSelect.addEventListener('change', function() {
        const filterValue = this.value;
        const rows = programTable.querySelectorAll('tr[data-program-type]');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const programType = row.getAttribute('data-program-type');
            
            if (filterValue === 'all' || programType === filterValue) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update the program count badge
        if (programCountBadge) {
            programCountBadge.textContent = visibleCount;
        }
        
        // Show a message if no programs match the filter
        let noResultsRow = programTable.querySelector('.no-filter-results');
        
        if (visibleCount === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-filter-results';
                noResultsRow.innerHTML = `
                    <td colspan="3" class="text-center py-4">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-filter me-2"></i>
                            No programs match your filter criteria.
                        </div>
                    </td>
                `;
                programTable.appendChild(noResultsRow);
            } else {
                noResultsRow.style.display = '';
            }
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    });
}
