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

    // Initialize the program table sorting
    initProgramTableSorting();
    
    // Initialize the dashboard-wide toggle for assigned programs
    initDashboardAssignedToggle();
});

/**
 * Initialize the dashboard-wide toggle for assigned programs
 * This function handles the toggle behavior that affects all dashboard components
 */
function initDashboardAssignedToggle() {
    const toggle = document.getElementById('includeAssignedToggle');
    if (!toggle) return;
    
    // Set initial state (unchecked by default)
    toggle.checked = false;
    
    toggle.addEventListener('change', function() {
        // Store the toggle state
        const includeAssigned = this.checked;
        
        // Update all dashboard components except Program Updates section
        updateDashboardComponents(includeAssigned);
        
        // Update chart if the chart-specific function exists
        if (typeof window.updateChartByProgramType === 'function') {
            window.updateChartByProgramType(includeAssigned);
        }
        
        // Save preference to localStorage for persistence
        localStorage.setItem('includeAssignedPrograms', includeAssigned ? 'true' : 'false');
    });
    
    // Load saved preference on page load
    const savedPreference = localStorage.getItem('includeAssignedPrograms');
    if (savedPreference === 'true') {
        toggle.checked = true;
        
        // Trigger the change event to update the dashboard
        toggle.dispatchEvent(new Event('change'));
    }
}

/**
 * Update all dashboard components based on the toggle state
 * 
 * @param {boolean} includeAssigned - Whether to include assigned programs
 */
function updateDashboardComponents(includeAssigned) {
    console.log(`Updating dashboard components - Include assigned: ${includeAssigned}`);
    
    // Update stat cards
    updateStatCards(includeAssigned);
    
    // Note: We no longer update the Programs Updates table here
    // as it should always show all programs regardless of toggle state
}

/**
 * Update stat cards based on toggle state
 * 
 * @param {boolean} includeAssigned - Whether to include assigned programs
 */
function updateStatCards(includeAssigned) {
    // Get all program rows, including hidden ones
    const allProgramRows = document.querySelectorAll('#dashboardProgramsTable tr[data-program-type]');
    
    // Skip if no program rows found
    if (!allProgramRows.length) return;
    
    // Reset counters
    const counts = {
        total: 0,
        submitted: 0,
        'on-track': 0,
        'delayed': 0,
        'completed': 0,
        'not-started': 0,
        'assigned-drafts': 0  // Track assigned drafts separately
    };
    
    // Count programs by status
    allProgramRows.forEach(row => {
        const programType = row.getAttribute('data-program-type');
        const isDraft = row.classList.contains('draft-program');
        
        // Skip assigned programs if toggle is off
        if (!includeAssigned && programType === 'assigned') return;
        
        // Track assigned drafts separately but don't count them in stats
        if (programType === 'assigned' && isDraft) {
            counts['assigned-drafts']++;
            // Skip counting assigned drafts in other stats
            return;
        }
        
        // Skip regular draft programs from stats
        if (isDraft) return;
        
        // Increment total counter (only counts submitted programs)
        counts.total++;
        counts.submitted++; // All counted programs are submitted since we skip drafts
        
        // Get status badge text
        const statusBadge = row.querySelector('td:nth-child(2) .badge');
        if (!statusBadge) return;
        
        const statusText = statusBadge.textContent.trim().toLowerCase();
        
        // Map status text to status key
        if (statusText.includes('on track')) {
            counts['on-track']++;
        } else if (statusText.includes('delayed') || statusText.includes('delay')) {
            counts['delayed']++;
        } else if (statusText.includes('target achieved') || 
                  statusText.includes('achieved') || 
                  statusText.includes('completed')) {
            counts['completed']++;
        } else if (statusText.includes('not started') || statusText === '') {
            counts['not-started']++;
        }
    });
    
    // Log counts for debugging
    console.log('Program counts:', counts);
    
    // Update the stat cards with new counts
    const totalCard = document.querySelector('.stat-card.primary .stat-value');
    const onTrackCard = document.querySelector('.stat-card.warning .stat-value');
    const delayedCard = document.querySelector('.stat-card.danger .stat-value');
    const completedCard = document.querySelector('.stat-card.success .stat-value');
    
    if (totalCard) totalCard.textContent = counts.total;
    if (onTrackCard) onTrackCard.textContent = counts['on-track'];
    if (delayedCard) delayedCard.textContent = counts['delayed'];
    if (completedCard) completedCard.textContent = counts['completed'];
    
    // Update the stat card subtitles with percentages
    updateCardSubtitle('.stat-card.primary .stat-subtitle', counts.submitted, 'Programs Submitted');
    updateCardSubtitle('.stat-card.warning .stat-subtitle', counts['on-track'], counts.total);
    updateCardSubtitle('.stat-card.danger .stat-subtitle', counts['delayed'], counts.total);
    updateCardSubtitle('.stat-card.success .stat-subtitle', counts['completed'], counts.total);
    
    // Update draft counter if it exists
    const draftCounter = document.getElementById('draftProgramCounter');
    if (draftCounter) {
        // Count both regular drafts and assigned drafts if toggle is on
        let draftCount = document.querySelectorAll('#dashboardProgramsTable tr.draft-program:not([data-program-type="assigned"]), ' + 
                                                 (includeAssigned ? '#dashboardProgramsTable tr.draft-program[data-program-type="assigned"]' : '')).length;
        
        draftCounter.textContent = draftCount;
        draftCounter.closest('.badge').classList.toggle('d-none', draftCount === 0);
    }
}

/**
 * Update a stat card subtitle with percentage
 * 
 * @param {string} selector - CSS selector for the subtitle element
 * @param {number} value - Value to display
 * @param {number|string} total - Total for percentage calculation or custom text
 */
function updateCardSubtitle(selector, value, total) {
    const element = document.querySelector(selector);
    if (!element) return;
    
    if (typeof total === 'number') {
        // For percentage calculations
        if (total > 0) {
            const percentage = Math.round((value / total) * 100);
            element.innerHTML = `<i class="fas fa-chart-line me-1"></i> ${percentage}% of total`;
            element.classList.remove('text-muted');
        } else {
            element.innerHTML = `<i class="fas fa-info-circle me-1"></i> No data for this period`;
            element.classList.add('text-muted');
        }
    } else if (typeof total === 'string') {
        // For custom text (like "Programs Submitted")
        element.innerHTML = `<i class="fas fa-check me-1"></i> ${value} ${total}`;
    }
}

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
            
            // Program Updates section always shows all programs
            // regardless of the dashboard-wide toggle state
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

/**
 * Initialize the program table sorting functionality
 */
function initProgramTableSorting() {
    const programTable = document.getElementById('dashboardProgramsTable');
    const sortableHeaders = document.querySelectorAll('th.sortable');
    
    if (!programTable || !sortableHeaders.length) return;
    
    // Current sort state
    let currentSort = {
        column: null,
        direction: 'asc'
    };
    
    // Add click handlers to sortable headers
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.getAttribute('data-sort');
            
            // Update sort direction
            if (currentSort.column === sortBy) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = sortBy;
                currentSort.direction = 'asc';
            }
            
            // Update header icons
            sortableHeaders.forEach(h => {
                const icon = h.querySelector('i');
                if (h === this) {
                    icon.className = currentSort.direction === 'asc' 
                        ? 'fas fa-sort-up ms-1' 
                        : 'fas fa-sort-down ms-1';
                } else {
                    icon.className = 'fas fa-sort ms-1';
                }
            });
            
            // Sort the table
            sortProgramTable(programTable, sortBy, currentSort.direction);
        });
    });
}

/**
 * Sort the program table rows based on column and direction
 */
function sortProgramTable(table, column, direction) {
    const rows = Array.from(table.querySelectorAll('tr:not(.no-filter-results)'));
    if (!rows.length) return;
    
    // Sort the rows
    const sortedRows = rows.sort((a, b) => {
        let aValue, bValue;
        
        switch(column) {
            case 'name':
                // Get program name text
                aValue = a.querySelector('td:first-child .fw-medium').textContent.trim().toLowerCase();
                bValue = b.querySelector('td:first-child .fw-medium').textContent.trim().toLowerCase();
                break;
                
            case 'status':
                // Get status text
                aValue = a.querySelector('td:nth-child(2) .badge').textContent.trim().toLowerCase();
                bValue = b.querySelector('td:nth-child(2) .badge').textContent.trim().toLowerCase();
                break;
                
            case 'date':
                // Get date as timestamp for comparison
                const aDate = a.querySelector('td:nth-child(3)').textContent.trim();
                const bDate = b.querySelector('td:nth-child(3)').textContent.trim();
                
                if (aDate === 'Not set' && bDate === 'Not set') return 0;
                if (aDate === 'Not set') return 1;
                if (bDate === 'Not set') return -1;
                
                aValue = new Date(aDate).getTime();
                bValue = new Date(bDate).getTime();
                break;
                
            default:
                return 0;
        }
        
        // Compare values
        if (aValue < bValue) return direction === 'asc' ? -1 : 1;
        if (aValue > bValue) return direction === 'asc' ? 1 : -1;
        return 0;
    });
    
    // Re-append rows in sorted order
    sortedRows.forEach(row => table.appendChild(row));
}
