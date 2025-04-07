/**
 * Program Details Page JavaScript
 * Handles visualization of program progress data
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize progress visualization
    initProgressChart();
});

/**
 * Initialize and render the progress chart
 */
function initProgressChart() {
    if (!submissionData || submissionData.length === 0) {
        // Show "no data" message if no submissions
        document.getElementById('chartNoData')?.classList.remove('d-none');
        return;
    }
    
    // Reverse data to show oldest to newest
    const chartData = [...submissionData].reverse();
    
    // Extract periods and values
    const periods = chartData.map(item => item.period);
    const targets = chartData.map(item => item.target);
    const achievements = chartData.map(item => item.achievement);
    
    // Get canvas context
    const ctx = document.getElementById('progressChart').getContext('2d');
    
    // Create the chart
    const progressChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: periods,
            datasets: [
                {
                    label: 'Target',
                    data: targets,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Achievement',
                    data: achievements,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: `Program Progress: ${programName}`,
                    font: {
                        size: 16
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    callbacks: {
                        // Format values in tooltip
                        afterTitle: function(context) {
                            const dataIndex = context[0].dataIndex;
                            const datasetIndex = context[0].datasetIndex;
                            const status = chartData[dataIndex].status;
                            let statusText = 'Status: ';
                            
                            switch(status) {
                                case 'on-track':
                                    statusText += '🟢 On Track';
                                    break;
                                case 'delayed':
                                    statusText += '🟠 Delayed';
                                    break;
                                case 'completed':
                                    statusText += '🔵 Completed';
                                    break;
                                default:
                                    statusText += '⚪ Not Started';
                            }
                            
                            return statusText;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
    
    // Add program timeline annotation if dates are available
    if (programDates.startDate && programDates.endDate) {
        const now = new Date();
        const start = new Date(programDates.startDate);
        const end = new Date(programDates.endDate);
        
        // Calculate progress percentage
        const total = end - start;
        const elapsed = now - start;
        const progress = Math.min(100, Math.max(0, (elapsed / total) * 100));
        
        // Display progress indicator
        const progressElement = document.createElement('div');
        progressElement.className = 'program-timeline mt-3 pt-3 border-top';
        progressElement.innerHTML = `
            <div class="small text-muted mb-2">Program Timeline Progress</div>
            <div class="progress">
                <div class="progress-bar ${progress >= 100 ? 'bg-primary' : 'bg-success'}" 
                     role="progressbar" style="width: ${progress}%" 
                     aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                    ${Math.round(progress)}%
                </div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <small class="text-muted">${start.toLocaleDateString()}</small>
                <small class="text-muted">${end.toLocaleDateString()}</small>
            </div>
        `;
        
        // Add to chart container
        document.querySelector('.chart-container').after(progressElement);
    }
}
