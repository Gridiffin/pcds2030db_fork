/**
 * Period Selector JavaScript
 * 
 * Handles period selection and content updating based on selected period
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the period selector on load
    initPeriodSelector();
});

/**
 * Initialize the period selector
 */
function initPeriodSelector() {
    const periodSelector = document.getElementById('periodSelector');
    
    if (periodSelector) {
        // Handle period change
        periodSelector.addEventListener('change', function() {
            const selectedPeriodId = this.value;
            
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Update period_id parameter
            urlParams.set('period_id', selectedPeriodId);
            
            // Redirect to the current page with updated parameters
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        });
    }
}

/**
 * Update page content via AJAX based on the selected period
 * 
 * @param {string} periodId - The selected period ID
 */
function updatePageContent(periodId) {
    // Show loading indicators for all dynamic content sections
    const dynamicSections = document.querySelectorAll('[data-period-content]');
    dynamicSections.forEach(section => {
        section.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading data...</p></div>';
    });
    
    // Get the current page path to determine which API endpoint to call
    const pagePath = window.location.pathname;
    
    // Get CSRF token if available (for POST requests)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Define the endpoint URL based on the current page
    let endpoint = '';
    
    if (pagePath.includes('/agency/dashboard.php')) {
        endpoint = '../ajax/dashboard_data.php';
    } else if (pagePath.includes('/agency/view_all_sectors.php')) {
        endpoint = '../ajax/sectors_data.php';
    } else if (pagePath.includes('/admin/dashboard.php')) {
        endpoint = '../ajax/admin_dashboard_data.php';
    } else if (pagePath.includes('/admin/manage_programs.php')) {
        endpoint = '../ajax/programs_data.php';
    } else if (pagePath.includes('/admin/manage_metrics.php')) {
        endpoint = '../ajax/metrics_data.php';
    }
    
    // If we have a valid endpoint, make the AJAX request
    if (endpoint) {
        fetch(`${endpoint}?period_id=${periodId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Update each dynamic section with its corresponding data
            dynamicSections.forEach(section => {
                const sectionId = section.getAttribute('data-period-content');
                if (data[sectionId]) {
                    section.innerHTML = data[sectionId];
                }
            });
            
            // Re-initialize any JS components that need it
            reinitializeComponents();
        })
        .catch(error => {
            console.error('Error fetching period data:', error);
            dynamicSections.forEach(section => {
                section.innerHTML = '<div class="alert alert-danger">Failed to load data. Please try refreshing the page.</div>';
            });
        });
    } else {
        // Fallback to full page reload if we don't have a specific endpoint
        window.location.reload();
    }
}

/**
 * Reinitialize JavaScript components after AJAX updates
 */
function reinitializeComponents() {
    // Re-initialize charts if they exist
    if (typeof initCharts === 'function') {
        initCharts();
    }
    
    // Re-initialize datatables if they exist
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.datatable').DataTable();
    }
    
    // Re-attach event listeners for dynamic content
    document.dispatchEvent(new CustomEvent('contentUpdated'));
}
