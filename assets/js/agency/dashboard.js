/**
 * Agency Dashboard JavaScript
 * Handles dashboard functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize refresh button
    const refreshButton = document.getElementById('refreshPage');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            location.reload();
        });
    }
    
    // Initialize tooltips if any
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length) {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
    
    // DO NOT initialize chart here - it's now handled by dashboard_chart.js
    // The old initProgramStatusChart function is kept for reference but not called
});

/**
 * This function is no longer used but kept for reference
 * Chart initialization is now handled by ProgramStatusChart in dashboard_chart.js
 */
function initProgramStatusChart() {
    // This function is deprecated - Chart initialization is now in dashboard_chart.js
    console.log("Chart initialization moved to dashboard_chart.js");
}
