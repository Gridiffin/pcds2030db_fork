/**
 * Agency Dashboard - Chart Component
 * 
 * Handles Chart.js initialization and updates for the program rating chart
 */

export class DashboardChart {
    constructor() {
        this.chart = null;
        this.chartData = null;
        this.init();
    }
    
    init() {
        // Get chart data from global variable (set by PHP)
        this.chartData = window.programRatingChartData || null;
        
        if (!this.chartData) {
            console.warn('⚠️ No chart data found');
            return;
        }
        
        this.createChart();
    }
    
    createChart() {
        const canvas = document.getElementById('programRatingChart');
        if (!canvas) {
            console.error('❌ Chart canvas not found');
            return;
        }
        
        if (typeof Chart === 'undefined') {
            console.error('❌ Chart.js not loaded');
            return;
        }
        
        // Destroy existing chart if it exists
        if (this.chart) {
            this.chart.destroy();
        }
        
        try {
            this.chart = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: this.chartData.labels,
                    datasets: [{
                        data: this.chartData.data,
                        backgroundColor: ['#ffc107', '#dc3545', '#28a745', '#6c757d'],
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
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
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
                    cutout: '70%',
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000
                    }
                }
            });
            
            // Make chart globally accessible for updates
            window.programRatingChart = this.chart;
            
            console.log('✅ Program rating chart created successfully');
            
        } catch (error) {
            console.error('❌ Error creating chart:', error);
            this.showChartError();
        }
    }
    
    showChartError() {
        const canvas = document.getElementById('programRatingChart');
        if (canvas) {
            const container = canvas.parentElement;
            container.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error loading chart</p>
                </div>
            `;
        }
    }
    
    showLoading() {
        const canvas = document.getElementById('programRatingChart');
        if (canvas) {
            const container = canvas.parentElement;
            container.innerHTML = `
                <div class="chart-loading">
                    <i class="fas fa-spinner"></i>
                    Loading chart...
                </div>
            `;
        }
    }
    
    update(newData) {
        if (this.chart && newData) {
            this.chart.data.datasets[0].data = newData.data;
            this.chart.data.labels = newData.labels;
            this.chart.update('active');
            console.log('✅ Chart updated with new data');
        }
    }
    
    refresh() {
        // Refresh chart by recreating it
        this.createChart();
    }
    
    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    }
}
