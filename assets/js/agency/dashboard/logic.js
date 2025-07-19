/**
 * Agency Dashboard - Logic Component
 * 
 * Handles dashboard interactions, AJAX calls, and general functionality
 */

export class DashboardLogic {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeToggles();
    }
    
    setupEventListeners() {
        // Refresh button functionality
        this.setupRefreshButton();
        
        // Assigned programs toggle
        this.setupAssignedToggle();
        
        // Card interactions
        this.setupCardInteractions();
    }
    
    setupRefreshButton() {
        const refreshButton = document.getElementById('refreshDashboard');
        if (refreshButton) {
            refreshButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleRefresh(refreshButton);
            });
        }
        
        // Also handle legacy refresh button
        const legacyRefreshButton = document.getElementById('refreshPage');
        if (legacyRefreshButton) {
            legacyRefreshButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleRefresh(legacyRefreshButton);
            });
        }
    }
    
    handleRefresh(button) {
        // Add loading state
        button.classList.add('loading');
        
        // Store original content
        const originalIcon = button.querySelector('i')?.className || 'fas fa-sync-alt';
        const originalText = button.querySelector('span')?.innerText || 'Refresh Data';
        
        // Update button to show loading
        button.innerHTML = `<i class="fas fa-sync-alt fa-spin"></i> <span>Refreshing...</span>`;
        button.disabled = true;
        
        // Reload the page after a short delay
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
    
    setupAssignedToggle() {
        const toggle = document.getElementById('includeAssignedToggle');
        if (!toggle) return;
        
        // Load saved preference
        const savedPreference = localStorage.getItem('includeAssignedPrograms');
        if (savedPreference !== null) {
            toggle.checked = savedPreference === 'true';
        } else {
            toggle.checked = false; // Default to OFF
        }
        
        // Handle toggle changes
        toggle.addEventListener('change', (e) => {
            this.handleAssignedToggle(e.target.checked);
        });
    }
    
    handleAssignedToggle(includeAssigned) {
        // Save preference
        localStorage.setItem('includeAssignedPrograms', includeAssigned.toString());
        
        // Update dashboard data via AJAX
        this.refreshDashboardData(includeAssigned);
        
        console.log(`🔄 Assigned programs toggle: ${includeAssigned ? 'ON' : 'OFF'}`);
    }
    
    refreshDashboardData(includeAssigned) {
        // Show loading state
        this.showLoadingState();
        
        // Get current period from PHP or default
        const currentPeriod = window.currentPeriodId || null;
        
        // Make AJAX request to refresh dashboard data
        fetch('ajax/agency_dashboard_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                period_id: currentPeriod,
                include_assigned: includeAssigned
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateDashboardWithData(data);
            } else {
                console.error('❌ Dashboard data refresh failed:', data.error);
                this.showErrorState();
            }
        })
        .catch(error => {
            console.error('❌ AJAX error:', error);
            this.showErrorState();
        })
        .finally(() => {
            this.hideLoadingState();
        });
    }
    
    updateDashboardWithData(data) {
        // Update stats cards
        if (data.stats) {
            this.updateStatsCards(data.stats);
        }
        
        // Update chart
        if (data.chart_data && window.AgencyDashboard?.chart) {
            window.AgencyDashboard.chart.update(data.chart_data);
        }
        
        // Update programs table
        if (data.recent_updates && window.AgencyDashboard?.programsTable) {
            window.AgencyDashboard.programsTable.updateTable(data.recent_updates);
        }
        
        console.log('✅ Dashboard updated with new data');
    }
    
    updateStatsCards(stats) {
        // Update total programs
        const totalElement = document.querySelector('.bento-card.primary .display-4');
        if (totalElement) totalElement.textContent = stats.total || 0;
        
        // Update on track programs
        const onTrackElement = document.querySelector('.bento-card.success .display-4');
        if (onTrackElement) onTrackElement.textContent = stats['on-track'] || 0;
        
        // Update delayed programs
        const delayedElement = document.querySelector('.bento-card.warning .display-4');
        if (delayedElement) delayedElement.textContent = stats.delayed || 0;
        
        // Update completed programs
        const completedElement = document.querySelector('.bento-card.info .display-4');
        if (completedElement) completedElement.textContent = stats.completed || 0;
        
        // Update percentages
        const total = stats.total || 0;
        if (total > 0) {
            const onTrackPercent = Math.round(((stats['on-track'] || 0) / total) * 100);
            const delayedPercent = Math.round(((stats.delayed || 0) / total) * 100);
            const completedPercent = Math.round(((stats.completed || 0) / total) * 100);
            
            const percentageElements = document.querySelectorAll('.opacity-75');
            if (percentageElements[0]) percentageElements[0].textContent = `${onTrackPercent}% of total`;
            if (percentageElements[1]) percentageElements[1].textContent = `${delayedPercent}% of total`;
            if (percentageElements[2]) percentageElements[2].textContent = `${completedPercent}% of total`;
        }
    }
    
    setupCardInteractions() {
        // Add hover effects and click handlers for bento cards
        document.querySelectorAll('.bento-card').forEach(card => {
            // Add smooth hover animations
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    }
    
    showLoadingState() {
        // Add loading indicators to dashboard
        document.querySelectorAll('.bento-card .display-4').forEach(element => {
            element.style.opacity = '0.5';
        });
    }
    
    hideLoadingState() {
        // Remove loading indicators
        document.querySelectorAll('.bento-card .display-4').forEach(element => {
            element.style.opacity = '1';
        });
    }
    
    showErrorState() {
        // Show error message
        console.error('❌ Failed to refresh dashboard data');
        // Could add toast notification here
    }
    
    initializeToggles() {
        // Initialize any other toggles or controls
        console.log('🔧 Dashboard controls initialized');
    }
    
    refresh() {
        // Refresh dashboard data
        const toggle = document.getElementById('includeAssignedToggle');
        const includeAssigned = toggle ? toggle.checked : false;
        this.refreshDashboardData(includeAssigned);
    }
}
