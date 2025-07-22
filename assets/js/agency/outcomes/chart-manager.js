/**
 * Chart Manager for Outcomes
 * Handles Chart.js visualizations for outcomes data
 */

export class ChartManager {
    constructor() {
        this.charts = new Map();
        this.defaultColors = [
            '#007bff', '#28a745', '#dc3545', '#ffc107', '#6f42c1',
            '#fd7e14', '#20c997', '#6c757d', '#343a40', '#f8f9fa'
        ];
    }

    /**
     * Create a new chart
     */
    createChart(canvas, options = {}) {
        if (!canvas || !window.Chart) {
            console.error('ChartManager: Canvas or Chart.js not available');
            return null;
        }

        try {
            const chartConfig = this.buildChartConfig(options);
            const chart = new Chart(canvas, chartConfig);
            
            // Store chart reference
            const chartId = canvas.id || 'chart_' + Date.now();
            this.charts.set(chartId, chart);
            
            
            return chart;
        } catch (error) {
            console.error('ChartManager: Error creating chart:', error);
            return null;
        }
    }

    /**
     * Build Chart.js configuration object
     */
    buildChartConfig(options) {
        const { type = 'line', data = {}, columns = [], rows = [] } = options;
        
        const chartData = this.prepareChartData(data, columns, rows);
        
        const config = {
            type: type,
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: options.title || 'Outcomes Data Visualization',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: columns.length > 1,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        cornerRadius: 6,
                        caretPadding: 10
                    }
                },
                scales: this.getScaleConfig(type),
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        };

        return config;
    }

    /**
     * Prepare data for Chart.js from outcomes structure
     */
    prepareChartData(data, columns, rows) {
        const labels = rows.map(row => row.label || row.month || '');
        const datasets = [];

        columns.forEach((column, index) => {
            const dataPoints = rows.map(row => {
                const value = row.data[column] || row.data[column.toLowerCase()] || '';
                return this.parseNumericValue(value);
            });

            datasets.push({
                label: column,
                data: dataPoints,
                borderColor: this.defaultColors[index % this.defaultColors.length],
                backgroundColor: this.defaultColors[index % this.defaultColors.length] + '20',
                borderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.3,
                fill: false
            });
        });

        return {
            labels: labels,
            datasets: datasets
        };
    }

    /**
     * Get scale configuration based on chart type
     */
    getScaleConfig(type) {
        const baseScales = {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Period'
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            y: {
                display: true,
                title: {
                    display: true,
                    text: 'Value'
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                beginAtZero: true
            }
        };

        // Adjust scales based on chart type
        if (type === 'bar') {
            baseScales.x.grid.display = false;
        } else if (type === 'pie' || type === 'doughnut') {
            return {}; // No scales for pie charts
        }

        return baseScales;
    }

    /**
     * Update existing chart with new data
     */
    updateChart(chart, options = {}) {
        if (!chart) {
            console.warn('ChartManager: No chart provided for update');
            return;
        }

        try {
            const { data = {}, columns = [], rows = [] } = options;
            const chartData = this.prepareChartData(data, columns, rows);
            
            chart.data = chartData;
            chart.update('resize');
            
            
        } catch (error) {
            console.error('ChartManager: Error updating chart:', error);
        }
    }

    /**
     * Change chart type
     */
    updateChartType(chart, newType, options = {}) {
        if (!chart) {
            console.warn('ChartManager: No chart provided for type change');
            return;
        }

        try {
            // Update chart type
            chart.config.type = newType;
            
            // Update scales for new type
            chart.config.options.scales = this.getScaleConfig(newType);
            
            // Update data if provided
            if (options.data || options.columns || options.rows) {
                const { data = {}, columns = [], rows = [] } = options;
                const chartData = this.prepareChartData(data, columns, rows);
                chart.data = chartData;
            }
            
            // Adjust dataset styling for new type
            this.adjustDatasetStyling(chart, newType);
            
            chart.update('resize');
            
            
        } catch (error) {
            console.error('ChartManager: Error changing chart type:', error);
        }
    }

    /**
     * Adjust dataset styling based on chart type
     */
    adjustDatasetStyling(chart, type) {
        chart.data.datasets.forEach((dataset, index) => {
            const color = this.defaultColors[index % this.defaultColors.length];
            
            switch (type) {
                case 'line':
                    dataset.borderColor = color;
                    dataset.backgroundColor = color + '20';
                    dataset.borderWidth = 2;
                    dataset.pointRadius = 4;
                    dataset.fill = false;
                    break;
                    
                case 'bar':
                    dataset.backgroundColor = color;
                    dataset.borderColor = color;
                    dataset.borderWidth = 1;
                    break;
                    
                case 'pie':
                case 'doughnut':
                    dataset.backgroundColor = this.defaultColors.slice(0, dataset.data.length);
                    dataset.borderColor = '#fff';
                    dataset.borderWidth = 2;
                    break;
            }
        });
    }

    /**
     * Parse numeric value from string, handling various formats
     */
    parseNumericValue(value) {
        if (typeof value === 'number') {
            return value;
        }
        
        if (typeof value === 'string') {
            // Remove common non-numeric characters
            const cleaned = value.replace(/[,%$]/g, '');
            const parsed = parseFloat(cleaned);
            return isNaN(parsed) ? 0 : parsed;
        }
        
        return 0;
    }

    /**
     * Export chart as image
     */
    exportChart(chart, filename = 'chart') {
        if (!chart) {
            console.warn('ChartManager: No chart provided for export');
            return;
        }

        try {
            const link = document.createElement('a');
            link.download = filename + '.png';
            link.href = chart.toBase64Image();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            
        } catch (error) {
            console.error('ChartManager: Error exporting chart:', error);
        }
    }

    /**
     * Get chart by ID
     */
    getChart(chartId) {
        return this.charts.get(chartId);
    }

    /**
     * Destroy specific chart
     */
    destroyChart(chartId) {
        const chart = this.charts.get(chartId);
        if (chart) {
            chart.destroy();
            this.charts.delete(chartId);
            
        }
    }

    /**
     * Destroy all charts
     */
    destroy() {
        this.charts.forEach((chart, chartId) => {
            chart.destroy();
            
        });
        this.charts.clear();
    }
}
