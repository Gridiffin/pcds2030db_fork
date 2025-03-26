/**
 * Agency Dashboard Functionality
 * Handles interactions on the agency dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    // Refresh button functionality
    const refreshBtn = document.getElementById('refreshPage');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Add spinner to indicate loading
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';
            this.disabled = true;
            
            // Reload the page
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
    }
    
    // Add animation to cards
    document.querySelectorAll('.card').forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
    
    // Initialize charts if data exists
    if (typeof programStatusChartData !== 'undefined') {
        initProgramStatusChart();
    }
});

/**
 * Initialize the program status chart
 */
function initProgramStatusChart() {
    const ctx = document.getElementById('programStatusChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['On Track', 'Delayed', 'Completed', 'Not Started'],
            datasets: [{
                data: programStatusChartData.data,
                backgroundColor: programStatusChartData.colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}
