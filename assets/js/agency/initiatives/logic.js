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

/**
 * Calculate health score based on program statuses
 * @param {Array} programs - Array of program objects with status property
 * @returns {number} Health score percentage (0-100)
 */
export function calculateHealthScore(programs) {
    if (!programs || !Array.isArray(programs) || programs.length === 0) {
        return 50; // Default neutral score
    }
    
    const statusScores = {
        'completed': 100,
        'active': 75,
        'on_hold': 50,
        'delayed': 25,
        'cancelled': 10
    };
    
    // Status normalization mapping
    const statusNormalization = {
        'not-started': 'active',
        'not_started': 'active',
        'on-hold': 'on_hold',
        'canceled': 'cancelled'
    };
    
    let totalScore = 0;
    
    programs.forEach(program => {
        let status = program.status ? String(program.status).toLowerCase() : 'active';
        
        // Normalize status
        if (statusNormalization[status]) {
            status = statusNormalization[status];
        }
        
        // Get score for status (default to 50 for unknown)
        const score = statusScores[status] || 50;
        totalScore += score;
    });
    
    return Math.round(totalScore / programs.length);
}

/**
 * Format timeline text from start and end dates
 * @param {string} startDate - Start date string
 * @param {string} endDate - End date string
 * @returns {string} Formatted timeline text
 */
export function formatTimelineText(startDate, endDate) {
    if (!startDate || !endDate) {
        return 'No timeline data available';
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
        return 'Invalid dates provided';
    }
    
    // Calculate the difference in milliseconds and convert to years
    const timeDiff = end.getTime() - start.getTime();
    const yearsDiff = Math.round(timeDiff / (1000 * 60 * 60 * 24 * 365.25)); // Account for leap years
    
    let yearText;
    if (yearsDiff <= 0) {
        yearText = '0 years';
    } else if (yearsDiff === 1) {
        yearText = '1 year';
    } else {
        yearText = `${yearsDiff} years`;
    }
    
    return `${startDate} to ${endDate} (${yearText})`;
}

/**
 * Validate initiative data structure
 * @param {Object} initiative - Initiative object to validate
 * @returns {boolean} True if valid, false otherwise
 */
export function validateInitiativeData(initiative) {
    if (!initiative || typeof initiative !== 'object') {
        return false;
    }
    
    // Required fields
    const requiredFields = ['initiative_id', 'initiative_name', 'start_date', 'end_date'];
    
    for (const field of requiredFields) {
        if (!initiative[field]) {
            return false;
        }
    }
    
    // Validate dates
    const startDate = new Date(initiative.start_date);
    const endDate = new Date(initiative.end_date);
    
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        return false;
    }
    
    // End date should be after start date
    if (endDate <= startDate) {
        return false;
    }
    
    return true;
}

/**
 * Get color for a given status
 * @param {string} status - Program status
 * @returns {string} Hex color code
 */
export function getStatusColor(status) {
    if (!status) return '#6c757d';
    
    const statusColors = {
        'completed': '#28a745',
        'active': '#17a2b8',
        'on_hold': '#ffc107',
        'delayed': '#fd7e14',
        'cancelled': '#dc3545'
    };
    
    // Status normalization
    const normalizedStatus = status.toLowerCase().replace('-', '_');
    const statusNormalization = {
        'not_started': 'active',
        'on_hold': 'on_hold',
        'canceled': 'cancelled'
    };
    
    const finalStatus = statusNormalization[normalizedStatus] || normalizedStatus;
    
    return statusColors[finalStatus] || '#6c757d';
}

/**
 * Format program count with proper pluralization
 * @param {number} count - Number of programs
 * @returns {string} Formatted count text
 */
export function formatProgramCount(count) {
    const num = parseInt(count);
    
    if (isNaN(num) || num < 0) {
        return '0 programs';
    }
    
    return num === 1 ? '1 program' : `${num} programs`;
}
