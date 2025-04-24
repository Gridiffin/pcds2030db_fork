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
    
    // Initialize the dashboard-wide toggle for assigned programs
    initDashboardAssignedToggle();
    
    // Initialize the program table sorting
    initProgramTableSorting();
});

/**
 * Initialize the dashboard-wide toggle for assigned programs
 * This function handles the toggle behavior that affects all dashboard components
 */
function initDashboardAssignedToggle() {
    const toggle = document.getElementById('includeAssignedToggle');
    if (!toggle) return;
    
    // Load saved preference on page load
    const savedPreference = localStorage.getItem('includeAssignedPrograms');
    if (savedPreference !== null) {
        toggle.checked = savedPreference === 'true';
    } else {
        // Default to true if not previously set
        toggle.checked = true;
    }
    
    toggle.addEventListener('change', function() {
        const includeAssigned = this.checked;
        
        // Add loading indicators
        document.querySelectorAll('.stat-card').forEach(card => {
            card.classList.add('loading');
        });
        
        if (document.getElementById('programStatusChart')) {
            const chartContainer = document.getElementById('programStatusChart').closest('.card-body');
            if (chartContainer) chartContainer.classList.add('loading');
        }
        
        // Save preference to localStorage
        localStorage.setItem('includeAssignedPrograms', includeAssigned.toString());
        
        // Request new data from server
        fetchDashboardData(includeAssigned);
    });
    
    // Initial fetch based on toggle state
    fetchDashboardData(toggle.checked);
}

/**
 * Fetch filtered dashboard data from server
 */
function fetchDashboardData(includeAssigned) {
    const periodId = document.getElementById('periodSelector')?.value || '';
    
    fetch('/pcds2030_dashboard/ajax/dashboard_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `period_id=${periodId}&include_assigned=${includeAssigned}`
    })
    .then(response => response.json())
    .then(data => {
        // Update UI components with new data
        renderStatCards(data.stats);
        renderStatusChart(data.chart_data);
        
        // Remove loading indicators
        document.querySelectorAll('.stat-card, .card-body.loading').forEach(el => {
            el.classList.remove('loading');
        });
    })
    .catch(error => {
        console.error('Error fetching dashboard data:', error);
        // Remove loading indicators
        document.querySelectorAll('.stat-card, .card-body.loading').forEach(el => {
            el.classList.remove('loading');
        });
        
        // Show error message
        alert('There was an error fetching dashboard data. Please try again.');
    });
}

/**
 * Render stat cards with provided data
 */
function renderStatCards(stats) {
    // Update total programs card
    const totalCard = document.querySelector('.stat-card.primary .stat-value');
    if (totalCard) totalCard.textContent = stats.total;
    
    // Update on-track programs card
    const onTrackCard = document.querySelector('.stat-card.warning .stat-value');
    if (onTrackCard) onTrackCard.textContent = stats['on-track'];
    
    // Update delayed programs card
    const delayedCard = document.querySelector('.stat-card.danger .stat-value');
    if (delayedCard) delayedCard.textContent = stats['delayed'];
    
    // Update completed programs card
    const completedCard = document.querySelector('.stat-card.success .stat-value');
    if (completedCard) completedCard.textContent = stats['completed'];
    
    // Update percentage subtitles
    updateCardSubtitle('.stat-card.warning .stat-subtitle', stats['on-track'], stats.total);
    updateCardSubtitle('.stat-card.danger .stat-subtitle', stats['delayed'], stats.total);
    updateCardSubtitle('.stat-card.success .stat-subtitle', stats['completed'], stats.total);
    
    // Update total submission status
    const programsSubmitted = document.querySelector('.stat-card.primary .stat-subtitle');
    if (programsSubmitted) {
        programsSubmitted.innerHTML = `<i class="fas fa-check me-1"></i>${stats.total} Programs`;
    }
}

/**
 * Update a stat card subtitle with percentage
 */
function updateCardSubtitle(selector, value, total) {
    const subtitle = document.querySelector(selector);
    if (!subtitle) return;
    
    let percentage = 0;
    if (total > 0) {
        percentage = Math.round((value / total) * 100);
    }
    
    subtitle.innerHTML = `<i class="fas fa-chart-line me-1"></i>${percentage}% of total`;
}

/**
 * Render status chart with provided data
 */
function renderStatusChart(chartData) {
    const chartCanvas = document.getElementById('programStatusChart');
    if (!chartCanvas) return;
    
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Unable to render chart.');
        return;
    }
    
    // Clear any existing chart - with proper check to ensure it has destroy method
    if (window.programStatusChart && typeof window.programStatusChart.destroy === 'function') {
        window.programStatusChart.destroy();
    }
    
    // Define colors for the chart
    const chartColors = {
        onTrack: '#ffc107',    // Yellow - On track 
        delayed: '#dc3545',    // Red - Delayed
        completed: '#28a745',  // Green - Target achieved
        notStarted: '#6c757d'  // Gray - Not started
    };
    
    try {
        // Create new chart
        window.programStatusChart = new Chart(chartCanvas, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: [
                        chartColors.onTrack,
                        chartColors.delayed,
                        chartColors.completed, 
                        chartColors.notStarted
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    } catch (error) {
        console.error('Error creating chart:', error);
    }
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
 * Sort the programs table
 */
function sortProgramTable(table, sortBy, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.no-filter-results)'));
    
    // Skip if no rows or only one row
    if (rows.length <= 1) return;
    
    // Sort rows
    const sortedRows = rows.sort((a, b) => {
        if (sortBy === 'name') {
            const aText = a.querySelector('td:nth-child(1) .fw-medium')?.textContent.trim().toLowerCase() || '';
            const bText = b.querySelector('td:nth-child(1) .fw-medium')?.textContent.trim().toLowerCase() || '';
            return direction === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
        } else if (sortBy === 'status') {
            const aStatus = a.querySelector('td:nth-child(2) .badge')?.textContent.trim().toLowerCase() || '';
            const bStatus = b.querySelector('td:nth-child(2) .badge')?.textContent.trim().toLowerCase() || '';
            return direction === 'asc' ? aStatus.localeCompare(bStatus) : bStatus.localeCompare(aStatus);
        } else if (sortBy === 'date') {
            const aDate = new Date(a.querySelector('td:nth-child(3)')?.textContent.trim() || 0);
            const bDate = new Date(b.querySelector('td:nth-child(3)')?.textContent.trim() || 0);
            return direction === 'asc' ? aDate - bDate : bDate - aDate;
        }
        return 0;
    });
    
    // Reorder rows in the DOM
    sortedRows.forEach(row => tbody.appendChild(row));
}
