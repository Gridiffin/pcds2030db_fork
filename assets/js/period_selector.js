/**
 * Period Selector Component
 * 
 * Handles the reporting period selection and persistence with seamless AJAX updates.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get period selector elements
    const periodSelector = document.getElementById('periodSelector');
    const currentPeriodLabel = document.getElementById('currentPeriodLabel');
    
    if (!periodSelector) return;
    
    // Handle period change
    periodSelector.addEventListener('change', function() {
        const selectedPeriod = this.value;
        const selectedPeriodText = this.options[this.selectedIndex].text;
        
        // Set session storage for selected period
        sessionStorage.setItem('selectedPeriodId', selectedPeriod);
        
        // Update URL with selected period
        const url = new URL(window.location.href);
        url.searchParams.set('period_id', selectedPeriod);
        window.history.pushState({}, '', url);
        
        // Update period display
        if (currentPeriodLabel) {
            currentPeriodLabel.textContent = selectedPeriodText;
            
            // Add or remove historical badge
            const historicalBadge = currentPeriodLabel.querySelector('.badge');
            const isHistorical = !selectedPeriodText.includes('(Current)');
            
            if (isHistorical && !historicalBadge) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-info ms-2';
                badge.textContent = 'Historical';
                currentPeriodLabel.appendChild(badge);
            } else if (!isHistorical && historicalBadge) {
                historicalBadge.remove();
            }
        }
        
        // AJAX update for different content sections
        updatePageContent(selectedPeriod);
    });
    
    // Initialize selected period from URL or session storage
    const urlParams = new URLSearchParams(window.location.search);
    const urlPeriod = urlParams.get('period_id');
    const storedPeriod = sessionStorage.getItem('selectedPeriodId');
    
    if (urlPeriod) {
        periodSelector.value = urlPeriod;
    } else if (storedPeriod) {
        periodSelector.value = storedPeriod;
        
        // Update URL with stored period
        const url = new URL(window.location.href);
        url.searchParams.set('period_id', storedPeriod);
        window.history.replaceState({}, '', url);
    }
});

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
