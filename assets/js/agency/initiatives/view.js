/**
 * Initiative View DOM Module
 * Handles DOM manipulation and Chart.js interaction for initiative view
 */

import {
    parseRatingData,
    prepareChartData,
    generateChartConfig,
    validateChartDependencies,
    generateErrorHTML
} from './logic.js';

/**
 * Initialize rating distribution chart
 * @param {string} canvasId - ID of the canvas element
 * @param {string} dataElementId - ID of element containing rating data
 */
export function initializeRatingChart(canvasId = 'initiativeRatingChart', dataElementId = 'ratingData') {
    const canvas = document.getElementById(canvasId);
    const ratingDataElement = document.getElementById(dataElementId);
    
    // Validate required elements
    if (!canvas) {
        console.error(`Canvas element with ID "${canvasId}" not found`);
        return;
    }
    
    if (!ratingDataElement) {
        console.error(`Rating data element with ID "${dataElementId}" not found`);
        return;
    }
    
    // Validate dependencies
    const validation = validateChartDependencies();
    if (!validation.success) {
        console.error('Missing dependencies:', validation.missing);
        const chartContainer = canvas.parentElement;
        chartContainer.innerHTML = generateErrorHTML('dependencies', validation.missing.join(', '));
        return;
    }
    
    // Parse rating data
    const ratingData = parseRatingData(ratingDataElement);
    if (!ratingData) {
        const chartContainer = canvas.parentElement;
        chartContainer.innerHTML = generateErrorHTML('no-data');
        return;
    }
    
    // Prepare chart data
    const chartData = prepareChartData(ratingData);
    
    // Create chart if there's data
    if (chartData.data.length > 0) {
        try {
            const config = generateChartConfig(chartData);
            new Chart(canvas, config);
        } catch (error) {
            console.error('Error creating chart:', error);
            const chartContainer = canvas.parentElement;
            chartContainer.innerHTML = generateErrorHTML('chart-error', error.message);
        }
    } else {
        // Show message when no data is available
        const chartContainer = canvas.parentElement;
        chartContainer.innerHTML = generateErrorHTML('no-data');
    }
}

/**
 * Initialize search functionality for initiatives table
 */
export function initializeSearch() {
    const searchForm = document.querySelector('form[method="GET"]');
    if (!searchForm) return;
    
    const searchInput = searchForm.querySelector('input[name="search"]');
    const statusSelect = searchForm.querySelector('select[name="status"]');
    
    if (searchInput) {
        // Add search on enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchForm.submit();
            }
        });
    }
    
    if (statusSelect) {
        // Auto-submit on status change
        statusSelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }
}

/**
 * Initialize tooltips and popovers
 */
export function initializeTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Add loading states to buttons and forms
 */
export function addLoadingStates() {
    const buttons = document.querySelectorAll('button[type="submit"], .btn[href]');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.tagName === 'BUTTON') {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
                this.disabled = true;
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 5000);
            }
        });
    });
}

/**
 * Enhance table interactions
 */
export function enhanceTableInteractions() {
    const table = document.querySelector('.initiatives-table, .table');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        // Add click handler to make entire row clickable
        const viewLink = row.querySelector('a[href*="view_initiative"]');
        if (viewLink) {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on a button or link
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a, button')) {
                    return;
                }
                viewLink.click();
            });
        }
        
        // Add hover effects
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(13, 110, 253, 0.05)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}
