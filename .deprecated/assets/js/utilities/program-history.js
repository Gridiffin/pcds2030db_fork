/**
 * Program History JavaScript
 * Handles the program edit history feature interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize history tooltips
    initHistoryTooltips();
    
    // Initialize history panels
    initHistoryPanels();
    
    // Handle targets history display
    initTargetsHistory();
    
    // Initialize field history toggles
    initFieldHistoryToggles();
});

/**
 * Initialize tooltip functionality for history indicators
 */
function initHistoryTooltips() {
    const historyIndicators = document.querySelectorAll('.history-indicator');
    
    historyIndicators.forEach(indicator => {
        indicator.addEventListener('mouseenter', function(e) {
            const tooltipId = this.getAttribute('data-tooltip');
            const tooltip = document.getElementById(tooltipId);
            
            if (tooltip) {
                // Position tooltip
                const rect = this.getBoundingClientRect();
                tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
                tooltip.style.left = `${rect.left + window.scrollX - 100}px`;
                
                // Show tooltip
                tooltip.classList.add('show');
            }
        });
        
        indicator.addEventListener('mouseleave', function(e) {
            const tooltipId = this.getAttribute('data-tooltip');
            const tooltip = document.getElementById(tooltipId);
            
            if (tooltip) {
                // Hide tooltip after a small delay (to handle gap between tooltip and indicator)
                setTimeout(() => {
                    if (!tooltip.matches(':hover')) {
                        tooltip.classList.remove('show');
                    }
                }, 300);
            }
        });
    });
    
    // Handle tooltip hover
    document.querySelectorAll('.history-tooltip').forEach(tooltip => {
        tooltip.addEventListener('mouseleave', function() {
            this.classList.remove('show');
        });
    });
}

/**
 * Initialize collapsible history panels
 */
function initHistoryPanels() {
    const historyToggleBtns = document.querySelectorAll('.history-toggle-btn');
    
    historyToggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const panelId = this.getAttribute('data-target');
            const panel = document.getElementById(panelId);
            
            if (panel) {
                const isVisible = panel.style.display !== 'none';
                panel.style.display = isVisible ? 'none' : 'block';
                this.innerHTML = isVisible ? '<i class="fas fa-history"></i> Show History' : '<i class="fas fa-times"></i> Hide History';
            }
        });
    });
}

/**
 * Initialize targets history display functionality
 */
function initTargetsHistory() {
    const targetHistoryBtns = document.querySelectorAll('.target-history-btn');
    
    targetHistoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const historyContainer = document.getElementById(targetId);
            
            if (historyContainer) {
                const isVisible = historyContainer.style.display !== 'none';
                historyContainer.style.display = isVisible ? 'none' : 'block';
                this.innerHTML = isVisible ? '<i class="fas fa-history"></i>' : '<i class="fas fa-times"></i>';
            }
        });
    });
}

/**
 * Toggle field history visibility
 * @param {string} fieldId ID of the field's history container to toggle
 */
function toggleFieldHistory(fieldId) {
    const historyContainer = document.getElementById(fieldId);
    if (historyContainer) {
        const isVisible = historyContainer.style.display !== 'none';
        historyContainer.style.display = isVisible ? 'none' : 'block';
        
        // Update the toggle button text/icon if it exists
        const toggleBtn = document.querySelector(`[data-history-target="${fieldId}"]`);
        if (toggleBtn) {
            toggleBtn.innerHTML = isVisible ? 
                '<i class="fas fa-history"></i> Show History' : 
                '<i class="fas fa-times"></i> Hide History';
        }
    }
}

/**
 * Initialize field history toggling
 */
function initFieldHistoryToggles() {
    document.querySelectorAll('.field-history-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-history-target');
            toggleFieldHistory(targetId);
        });
    });
}
