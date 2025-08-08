/**
 * Admin Dashboard JavaScript Entry Point - Modernized
 * Combines original functionality with modern enhancements
 * Maintains backward compatibility while improving UX
 */

// Import dashboard-specific CSS bundle (now includes modern components)
import '../../css/admin/dashboard/dashboard.css';

// Import dashboard functionality (preserved)
import './dashboard.js';

// Import modern enhancements
import './admin-dashboard-modern.js';

// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Admin Dashboard loaded');
    
    // Initialize dashboard-specific features (preserved functionality)
    initializeDashboard();
    
    // Initialize modern enhancements
    if (typeof ModernAdminDashboard !== 'undefined') {
        new ModernAdminDashboard();
    }
});

function initializeDashboard() {
    // Preserve all original dashboard functionality
    console.log('Dashboard module initialized with modern enhancements');
    
    // Quick actions functionality (preserved)
    const quickActionButtons = document.querySelectorAll('.admin-action-card-modern, .quick-action-btn');
    quickActionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Add loading state for better UX
            const icon = this.querySelector('i');
            if (icon && !icon.classList.contains('fa-spin')) {
                const originalClass = icon.className;
                icon.className = icon.className.replace(/fa-[\w-]+$/, 'fa-spinner fa-spin');
                
                // Restore original icon after delay (simulated loading)
                setTimeout(() => {
                    icon.className = originalClass;
                }, 1000);
            }
        });
    });

    // Stats cards interactions (enhanced)
    const statCards = document.querySelectorAll('.admin-stat-card-modern, .stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            const title = this.querySelector('.admin-stat-title-modern, .stat-card-title');
            if (title) {
                console.log('Stat card clicked:', title.textContent);
                
                // Add visual feedback
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });
    });

    // Programs table functionality (preserved and enhanced)
    const programLinks = document.querySelectorAll('a[href*="view_program"], a[href*="programs"]');
    programLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading visual feedback
            this.style.opacity = '0.7';
            const icon = document.createElement('i');
            icon.className = 'fas fa-spinner fa-spin ms-2';
            icon.style.fontSize = '0.8em';
            this.appendChild(icon);
            
            // Cleanup after navigation starts
            setTimeout(() => {
                this.style.opacity = '';
                if (icon.parentNode) {
                    icon.remove();
                }
            }, 2000);
        });
    });

    // Period selector functionality preservation
    const periodSelectors = document.querySelectorAll('[data-period-content]');
    if (periodSelectors.length > 0) {
        // Maintain original period switching functionality
        window.switchPeriod = function(periodId) {
            console.log('Switching to period:', periodId);
            const url = new URL(window.location);
            url.searchParams.set('period_id', periodId);
            window.location = url.toString();
        };
    }

    // Chart rendering compatibility (enhanced)
    if (typeof Chart !== 'undefined') {
        // Apply modern chart styling while preserving functionality
        Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 12;
        
        // Modern color palette that maintains accessibility
        const modernColors = [
            '#11998e', '#2f80ed', '#f2994a', 
            '#eb5757', '#9b51e0', '#27ae60',
            '#4facfe', '#f093fb', '#43e97b'
        ];
        
        window.getModernChartColor = function(index) {
            return modernColors[index % modernColors.length];
        };

        // Override default chart options for modern look
        Chart.defaults.elements.point.radius = 4;
        Chart.defaults.elements.point.hoverRadius = 6;
        Chart.defaults.elements.line.tension = 0.3;
        Chart.defaults.scales.linear.grid.color = 'rgba(0,0,0,0.05)';
        Chart.defaults.scales.linear.ticks.color = '#6b7280';
    }

    // Error handling and graceful degradation
    window.addEventListener('error', function(e) {
        console.error('Admin Dashboard Error:', e.error);
        
        // Graceful fallback for modern features
        const modernElements = document.querySelectorAll('.admin-fade-in, .admin-card-modern');
        modernElements.forEach(el => {
            if (el.style.opacity === '0') {
                el.style.opacity = '1';
            }
            if (el.style.transform) {
                el.style.transform = 'none';
            }
        });
    });

    // Performance monitoring (non-blocking)
    if (window.performance && window.performance.mark) {
        try {
            window.performance.mark('admin-dashboard-init-end');
            
            window.addEventListener('load', function() {
                window.performance.mark('admin-dashboard-load-complete');
                const measures = window.performance.getEntriesByType('measure');
                console.log('Dashboard performance metrics available:', measures.length);
            });
        } catch (e) {
            // Ignore performance API errors
        }
    }
}