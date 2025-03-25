/**
 * Program Details Page
 * Creates visualizations for program progress over time
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if we have program data and chart element
    if (typeof programData !== 'undefined' && document.getElementById('progressChart')) {
        createProgressChart();
    }
    
    function createProgressChart() {
        // Extract data from programData
        const submissions = programData.submissions || [];
        
        // If no submissions, don't attempt to draw chart
        if (submissions.length === 0) return;
        
        // Prepare data for chart
        const periods = [];
        const achievements = [];
        const targets = [];
        const statuses = [];
        const statusColors = {
            'on-track': '#28a745',
            'delayed': '#ffc107',
            'completed': '#17a2b8',
            'not-started': '#6c757d'
        };
        
        // Process submissions in chronological order (oldest first)
        [...submissions].reverse().forEach(submission => {
            periods.push(`Q${submission.quarter}-${submission.year}`);
            
            // Extract numeric values for achievements and targets if possible
            let achievement = parseFloat(submission.achievement);
            let target = parseFloat(submission.target);
            
            // Handle cases where values are not numeric
            if (isNaN(achievement)) {
                // Try to extract percentage
                const percentMatch = submission.achievement.match(/(\d+)%/);
                achievement = percentMatch ? parseFloat(percentMatch[1]) : 0;
            }
            
            if (isNaN(target)) {
                const percentMatch = submission.target.match(/(\d+)%/);
                target = percentMatch ? parseFloat(percentMatch[1]) : 100;
            }
            
            achievements.push(achievement);
            targets.push(target);
            statuses.push(statusColors[submission.status] || statusColors['not-started']);
        });
        
        // Create the chart
        const ctx = document.getElementById('progressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: periods,
                datasets: [
                    {
                        label: 'Achievement',
                        data: achievements,
                        borderColor: '#8591a4',
                        backgroundColor: 'rgba(133, 145, 164, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: statuses,
                        pointBorderColor: '#fff',
                        pointRadius: 6,
                        pointHoverRadius: 8
                    },
                    {
                        label: 'Target',
                        data: targets,
                        borderColor: 'rgba(164, 152, 133, 0.7)',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const status = [...submissions].reverse()[index].status.replace('-', ' ');
                                return `Status: ${status.charAt(0).toUpperCase() + status.slice(1)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Value'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Reporting Period'
                        }
                    }
                }
            }
        });
    }
});
