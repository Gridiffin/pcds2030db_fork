/**
 * Agency Dashboard Chart
 * Enhanced chart visualization for the agency dashboard
 */

// Wrap everything in an IIFE to avoid global class declaration
(function() {
    class ChartManager {
        constructor(chartId) {
            this.chartId = chartId;
            this.chart = null;
            this.data = null;
            this.initialAnimation = true;
        }        /**
         * Initialize the chart with data
         * @param {Object} data - Chart data containing values and colors
         */
        init(data) {
            
            this.data = data;
            
            // Verify data integrity
            if (!data || !data.data || !Array.isArray(data.data)) {
                console.error("Invalid chart data format:", data);
                return this;
            }
            
            
            
            const containerElement = document.getElementById(this.chartId).parentElement;
            if (data.hasPeriodData === false) {
                
                this.showNoDataMessage(containerElement);
                return this;
            }
            
            const noDataMsg = containerElement.querySelector('.no-data-message');
            if (noDataMsg) {
                noDataMsg.remove();
            }
            
            this.createChart();
            return this;
        }
        
        /**
         * Show a message when no data is available for the period
         * @param {HTMLElement} container - The container element
         */
        showNoDataMessage(container) {
            // Check if no data message already exists
            if (container.querySelector('.no-data-message')) {
                return;
            }
            
            // Create a message with styling - improved for better vertical centering
            const message = document.createElement('div');
            message.className = 'no-data-message d-flex justify-content-center align-items-center';
            message.style.position = 'absolute';
            message.style.top = '0';
            message.style.left = '0';
            message.style.width = '100%';
            message.style.height = '100%';
            message.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-chart-pie fa-3x mb-3"></i>
                    <p class="mb-1">No program data available for this period</p>
                    <p class="small">Select a different reporting period or create new programs</p>
                </div>
            `;
            
            // Clear any existing chart
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
            
            // Make sure container has position relative for absolute positioning
            if (window.getComputedStyle(container).position === 'static') {
                container.style.position = 'relative';
            }
            
            // Add message to container
            container.appendChild(message);
        }        /**
         * Create the chart instance
         */
        createChart() {
            const ctx = document.getElementById(this.chartId);
            if (!ctx) {
                console.error(`Canvas element with ID ${this.chartId} not found`);
                return;
            }
            
            
            
            
            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }
            
            
            
            // Check for zero data - show empty message if all values are 0
            const hasData = this.data.data.some(value => value > 0);
            if (!hasData) {
                console.warn("All chart data values are zero");
                // Create a message element
                const container = ctx.parentElement;
                const message = document.createElement('div');
                message.className = 'text-center py-4 text-muted';
                message.innerHTML = '<i class="fas fa-chart-pie fa-3x mb-3"></i><p>No program data available</p>';
                container.appendChild(message);
                
                // Still render empty chart
            }
            
            // Check for and destroy existing chart instance
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                
                existingChart.destroy();
            }
            
            // Chart animation options
            const animationOptions = this.initialAnimation ? {
                animateScale: true,
                animateRotate: true
            } : false;
            
            // Create chart with correct status colors
            this.chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['On Track', 'Delayed', 'Monthly Target Achieved', 'Not Started'],
                    datasets: [{
                        data: this.data.data,
                        // Updated colors to match correct status colors
                        backgroundColor: [
                            '#ffc107', // on-track (yellow)
                            '#dc3545', // delayed (red)
                            '#28a745', // completed/monthly target achieved (green)
                            '#6c757d'  // not-started (grey)
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverBorderWidth: 4,
                        hoverBorderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: animationOptions,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: (tooltipItems) => tooltipItems[0].label,
                                label: (tooltipItem) => {
                                    const value = tooltipItem.raw;
                                    const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${value} programs (${percentage}%)`;
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            bodySpacing: 8,
                            boxPadding: 6
                        }
                    },
                    cutout: '70%'
                }
            });
            
            
            
            // After first render, disable initial animation
            this.initialAnimation = false;
        }
        
        /**
         * Update chart with new data
         * @param {Object} newData - New chart data
         */
        update(newData) {
            if (!this.chart) return;
            
            this.data = newData;
            this.chart.data.datasets[0].data = newData.data;
            this.chart.update();
        }
    }    /**
     * Initialize the dashboard chart with program rating data
     */
    function initializeDashboardChart(chartData) {
        // Check if we have a chart container
        const chartContainer = document.getElementById('programRatingChart');
        if (!chartContainer) return;
        
        // Define colors based on status meaning
        const chartColors = {
            onTrack: '#ffc107',       // Yellow - Still on track for the year
            delayed: '#dc3545',       // Red - Delayed
            completed: '#28a745',     // Green - Monthly target achieved
            notStarted: '#6c757d'     // Gray - Not started
        };
        
        // Setup data for the chart
        const data = {
            labels: ['On Track', 'Delayed', 'Target Achieved', 'Not Started'],
            datasets: [{
                data: chartData.data,
                backgroundColor: [
                    chartColors.onTrack,
                    chartColors.delayed,
                    chartColors.completed,
                    chartColors.notStarted
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        };

        // ...existing code...
    }

    // Expose these functions to global scope
    window.initializeDashboardChart = function(chartData) {        
        const chartInstance = new ChartManager('programRatingChart');
        chartInstance.init(chartData);
        
        // Make chart available globally for updates
        window.dashboardChart = chartInstance;
        return chartInstance;
    };
      // Make the updateChartByProgramType function globally accessible
    window.updateChartByProgramType = function(includeAssigned) {
        
        
        // Get current period ID from URL params or global variable
        const urlParams = new URLSearchParams(window.location.search);
        const periodId = urlParams.get('period_id') || (window.currentPeriodId || null);
        
        // Prepare form data for AJAX request
        const formData = new FormData();
        formData.append('period_id', periodId);
        formData.append('include_assigned', includeAssigned);
          // Make AJAX request to get updated chart data
        fetch('ajax/chart_data.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                
                
                // Update chart with new data
                if (window.dashboardChart) {
                    window.dashboardChart.update(data.chart_data);
                } else {
                    console.error("Dashboard chart not initialized");
                }
                
                // Update statistics cards if renderStatCards function exists
                if (typeof window.renderStatCards === 'function') {
                    window.renderStatCards(data.stats);
                }
            } else {
                console.error("Server error:", data.error);
            }
        })        .catch(error => {
            console.error("AJAX error:", error);
        });
    };    // Initialize the chart on page load
    document.addEventListener('DOMContentLoaded', function() {
        // DISABLED: Simple chart initialization is now handled directly in dashboard.php
        // to avoid conflicts and timing issues. The complex ChartManager approach was
        // causing conflicts with multiple chart initializers.
        
        // Note: Toggle handling is now consolidated in dashboard.js to avoid conflicts
        // The chart will be updated via window.dashboardChart.update() from dashboard.js
    });
})();
