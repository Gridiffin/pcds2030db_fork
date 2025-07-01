/**
 * Initiative View - Program Rating Distribution Chart
 * Handles the rating distribution chart for the initiative view page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we have the chart canvas and rating data
    const canvas = document.getElementById('initiativeRatingChart');
    const ratingDataElement = document.getElementById('ratingData');
    
    if (!canvas) {
        console.error('Canvas element with ID "initiativeRatingChart" not found');
        return;
    }
    
    if (!ratingDataElement) {
        console.error('Rating data element with ID "ratingData" not found');
        return;
    }
    
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        return;
    }
    
    // Parse rating distribution data from the hidden element
    let ratingData;
    try {
        const textContent = ratingDataElement.textContent || ratingDataElement.innerText;
        ratingData = JSON.parse(textContent);
    } catch (e) {
        console.error('Failed to parse rating data:', e);
        return;
    }
    
    // Filter out zero values and prepare chart data
    const chartLabels = [];
    const chartData = [];
    const chartColors = [];
    
    const colorMap = {
        'target-achieved': '#28a745',
        'completed': '#28a745',
        'on-track': '#ffc107',
        'on-track-yearly': '#ffc107',
        'delayed': '#dc3545',
        'severe-delay': '#dc3545',
        'not-started': '#6c757d'
    };
    
    const labelMap = {
        'target-achieved': 'Target Achieved',
        'completed': 'Completed',
        'on-track': 'On Track',
        'on-track-yearly': 'On Track (Yearly)',
        'delayed': 'Delayed',
        'severe-delay': 'Severe Delay',
        'not-started': 'Not Started'
    };
    
    // Include ALL status types with count > 0 (including not-started)
    for (const [status, count] of Object.entries(ratingData)) {
        if (count > 0) {
            const label = labelMap[status] || status;
            const color = colorMap[status] || '#6c757d';
            
            chartLabels.push(label);
            chartData.push(count);
            chartColors.push(color);
        }
    }
    
    // Create chart if there's data
    if (chartData.length > 0) {
        try {
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        data: chartData,
                        backgroundColor: chartColors,
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
            });
        } catch (error) {
            console.error('Error creating chart:', error);
            
            // Fallback to show error message
            const chartContainer = canvas.parentElement;
            chartContainer.innerHTML = `
                <div class="text-muted text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3 text-warning"></i>
                    <div>Error loading chart. Please refresh the page.</div>
                </div>
            `;
        }
    } else {
        // Show message when no data is available
        const chartContainer = canvas.parentElement;
        chartContainer.innerHTML = `
            <div class="text-muted text-center py-4">
                <i class="fas fa-chart-pie fa-2x mb-3"></i>
                <div>No program rating data available for this initiative.</div>
            </div>
        `;
    }
});
