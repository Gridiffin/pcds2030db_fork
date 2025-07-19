/**
 * Initiatives Logic Module
 * Pure functions for data processing and chart configuration - testable
 */

/**
 * Color mapping for different rating statuses
 */
export const RATING_COLORS = {
    'target-achieved': '#28a745',
    'completed': '#28a745',
    'on-track': '#ffc107',
    'on-track-yearly': '#ffc107',
    'delayed': '#dc3545',
    'severe-delay': '#dc3545',
    'not-started': '#6c757d'
};

/**
 * Label mapping for different rating statuses
 */
export const RATING_LABELS = {
    'target-achieved': 'Target Achieved',
    'completed': 'Completed',
    'on-track': 'On Track',
    'on-track-yearly': 'On Track (Yearly)',
    'delayed': 'Delayed',
    'severe-delay': 'Severe Delay',
    'not-started': 'Not Started'
};

/**
 * Parse rating data from HTML element text content
 * @param {HTMLElement} element - Element containing JSON data
 * @returns {Object|null} Parsed rating data or null if failed
 */
export function parseRatingData(element) {
    if (!element) {
        console.error('No element provided for parsing rating data');
        return null;
    }
    
    try {
        const textContent = element.textContent || element.innerText;
        return JSON.parse(textContent);
    } catch (e) {
        console.error('Failed to parse rating data:', e);
        return null;
    }
}

/**
 * Prepare chart data from rating data object
 * @param {Object} ratingData - Raw rating data
 * @returns {Object} Chart data with labels, values, and colors
 */
export function prepareChartData(ratingData) {
    const chartLabels = [];
    const chartData = [];
    const chartColors = [];
    
    if (!ratingData || typeof ratingData !== 'object') {
        return { labels: chartLabels, data: chartData, colors: chartColors };
    }
    
    // Include ALL status types with count > 0 (including not-started)
    for (const [status, count] of Object.entries(ratingData)) {
        if (count > 0) {
            const label = RATING_LABELS[status] || status;
            const color = RATING_COLORS[status] || '#6c757d';
            
            chartLabels.push(label);
            chartData.push(count);
            chartColors.push(color);
        }
    }
    
    return {
        labels: chartLabels,
        data: chartData,
        colors: chartColors
    };
}

/**
 * Generate Chart.js configuration for rating distribution
 * @param {Object} chartData - Prepared chart data
 * @returns {Object} Chart.js configuration object
 */
export function generateChartConfig(chartData) {
    return {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: chartData.colors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
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
    };
}

/**
 * Validate required dependencies for chart creation
 * @returns {Object} Validation result with success flag and missing dependencies
 */
export function validateChartDependencies() {
    const missing = [];
    
    if (typeof Chart === 'undefined') {
        missing.push('Chart.js library');
    }
    
    return {
        success: missing.length === 0,
        missing: missing
    };
}

/**
 * Generate HTML for error messages
 * @param {string} type - Type of error ('chart-error', 'no-data', 'dependencies')
 * @param {string} message - Custom error message
 * @returns {string} HTML string for error display
 */
export function generateErrorHTML(type, message = '') {
    const templates = {
        'chart-error': `
            <div class="text-muted text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-3 text-warning"></i>
                <div>Error loading chart. Please refresh the page.</div>
                ${message ? `<div class="small mt-2">${message}</div>` : ''}
            </div>
        `,
        'no-data': `
            <div class="text-muted text-center py-4">
                <i class="fas fa-chart-pie fa-2x mb-3"></i>
                <div>No program rating data available for this initiative.</div>
            </div>
        `,
        'dependencies': `
            <div class="text-muted text-center py-4">
                <i class="fas fa-exclamation-circle fa-2x mb-3 text-danger"></i>
                <div>Required dependencies not loaded.</div>
                ${message ? `<div class="small mt-2">${message}</div>` : ''}
            </div>
        `
    };
    
    return templates[type] || templates['chart-error'];
}
