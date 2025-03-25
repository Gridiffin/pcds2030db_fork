/**
 * Admin Dashboard Charts
 * 
 * Generates charts for the admin dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Program Status Chart (Pie Chart)
    if (document.getElementById('programStatusChart') && typeof programStatusData !== 'undefined') {
        createPieChart('programStatusChart', programStatusData);
    }
    
    // Sector Programs Chart (Bar Chart)
    if (document.getElementById('sectorProgramsChart') && typeof sectorProgramsData !== 'undefined') {
        createBarChart('sectorProgramsChart', sectorProgramsData);
    }
});

/**
 * Create a pie chart
 * @param {string} elementId DOM element ID 
 * @param {object} data Chart data with labels, data, and backgroundColor
 */
function createPieChart(elementId, data) {
    const ctx = document.getElementById(elementId).getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.data || [],
                backgroundColor: data.backgroundColor || [],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            },
            plugins: {
                title: {
                    display: false
                }
            }
        }
    });
}

/**
 * Create a bar chart
 * @param {string} elementId DOM element ID
 * @param {object} data Chart data with labels, data, and backgroundColor
 */
function createBarChart(elementId, data) {
    const ctx = document.getElementById(elementId).getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Number of Programs',
                data: data.data || [],
                backgroundColor: data.backgroundColor || [],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
