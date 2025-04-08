/**
 * Agency Dashboard Chart
 * Enhanced chart visualization for the agency dashboard
 */

// Wrap everything in an IIFE to avoid global class declaration
(function() {
    // Private class not exposed to global scope
    class ChartManager {
        constructor(chartId, legendId) {
            this.chartId = chartId;
            this.legendId = legendId;
            this.chart = null;
            this.data = null;
            this.initialAnimation = true;
            console.log("ChartManager created for", chartId);
        }

        /**
         * Initialize the chart with data
         * @param {Object} data - Chart data containing values and colors
         */
        init(data) {
            console.log("ChartManager.init called with data:", data);
            this.data = data;
            
            // Verify data integrity
            if (!data || !data.data || !Array.isArray(data.data)) {
                console.error("Invalid chart data format:", data);
                return this;
            }
            
            // Create chart
            this.createChart();
            
            // Set up legend interactivity
            this.setupLegend();
            
            return this;
        }
        
        /**
         * Create the chart instance
         */
        createChart() {
            const ctx = document.getElementById(this.chartId);
            if (!ctx) {
                console.error(`Canvas element with ID ${this.chartId} not found`);
                return;
            }
            
            console.log("Creating chart with data:", this.data.data);
            
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
                console.log("Destroying existing chart instance");
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
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: (tooltipItems) => {
                                    return tooltipItems[0].label;
                                },
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
                    cutout: '70%',
                    elements: {
                        arc: {
                            borderWidth: 0
                        }
                    }
                }
            });
            
            console.log("Chart created successfully");
            
            // After first render, disable initial animation
            this.initialAnimation = false;
        }
        
        /**
         * Set up interactive legend
         */
        setupLegend() {
            const legendElement = document.getElementById(this.legendId);
            if (!legendElement) return;
            
            // Get all legend items
            const legendItems = legendElement.querySelectorAll('.chart-legend-item');
            
            legendItems.forEach((item, index) => {
                // Add click event to toggle visibility
                item.addEventListener('click', () => {
                    // Toggle visibility in the chart
                    const meta = this.chart.getDatasetMeta(0);
                    const dataVisible = meta.data[index].hidden ? true : false;
                    meta.data[index].hidden = !dataVisible;
                    
                    // Update legend item style
                    if (!dataVisible) {
                        item.classList.add('disabled');
                    } else {
                        item.classList.remove('disabled');
                    }
                    
                    // Update chart
                    this.chart.update();
                });
                
                // Add hover effect to highlight corresponding chart section
                item.addEventListener('mouseenter', () => {
                    this.chart.setActiveElements([{datasetIndex: 0, index: index}]);
                    this.chart.update();
                });
                
                item.addEventListener('mouseleave', () => {
                    this.chart.setActiveElements([]);
                    this.chart.update();
                });
            });
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
    }

    // Only expose the initialization function to global scope
    window.initializeDashboardChart = function(chartData) {
        console.log("initializeDashboardChart called with data:", chartData);
        const chartInstance = new ChartManager('programStatusChart', 'programStatusLegend');
        chartInstance.init(chartData);
        
        // Make chart available globally for updates
        window.dashboardChart = chartInstance;
        return chartInstance;
    };
})();
