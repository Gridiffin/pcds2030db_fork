/**
 * Admin Programs List
 * 
 * Handles interactions for the programs listing page
 */

// Show loading spinner
function showLoading() {
    const loadingOverlay = document.querySelector('.loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }
}

// Hide loading spinner
function hideLoading() {
    const loadingOverlay = document.querySelector('.loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

// Function to update the programs table via AJAX
function updateProgramsList(formData) {
    const tableContainer = document.querySelector('#programsTable_wrapper') || document.querySelector('#programsTable').parentNode;
    
    // Show loading indicator
    showLoading();
    
    // If there's an ongoing request, abort it
    if (window.ajaxRequest) {
        window.ajaxRequest.abort();
    }
    
    window.ajaxRequest = fetch('/admin/programs.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Parse the response to extract just the table content
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTableContent = doc.querySelector('#programsTable');
        
        if (newTableContent) {
            // Replace the current table with the new one
            const currentTable = document.querySelector('#programsTable');
            if (currentTable) {
                currentTable.innerHTML = newTableContent.innerHTML;
            }
        }
        
        // Reinitialize tooltips after content update
        reinitializeTooltips();
        
        // Hide loading indicator
        hideLoading();
    })
    .catch(error => {
        console.error('Error updating programs list:', error);
        hideLoading();
        if (error.name !== 'AbortError') {
            showToast('Error', 'Failed to update programs list. Please try again.', 'error');
        }
    });
}

// Clear all toast notifications
function clearToasts() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        const bsToast = bootstrap.Toast.getInstance(toast);
        if (bsToast) {
            bsToast.hide();
        }
    });
}

// Show toast notification
function showToast(title, message, type = 'info') {
    clearToasts();
    
    const toastContainer = document.querySelector('.toast-container') || document.body;
    const toastElement = document.createElement('div');
    toastElement.className = `toast align-items-center text-bg-${type} border-0`;
    toastElement.setAttribute('role', 'alert');
    toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong>: ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastElement);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

// Reinitialize tooltips after content changes
function reinitializeTooltips() {
    // Dispose of existing tooltips
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltips.forEach(el => {
        const tooltip = bootstrap.Tooltip.getInstance(el);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Initialize new tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize datatable if available
    let dataTable;
    const programsTable = document.getElementById('programsTable');
    if (programsTable && typeof $.fn.DataTable === 'function') {
        dataTable = $(programsTable).DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthChange": true,
            "searching": false, // We have our own search
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "emptyTable": "No programs found matching your criteria."
            }
        });
    }

    // Handle filter form interactions
    const filterForm = document.getElementById('filterForm');
    
    if (filterForm) {
        // Track if an AJAX request is in progress
        let ajaxRequest = null;
        
        // Variable to store the current URL state for browser history
        let currentUrlParams = new URLSearchParams(window.location.search);
        
        // Add loading spinner to the table container
        const tableContainer = document.querySelector('.table-responsive');
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        tableContainer.style.position = 'relative';
        tableContainer.appendChild(loadingOverlay);
        
        
        // Hide loading overlay initially
        hideLoading();
        
        
        // Handle browser back/forward navigation
        window.addEventListener('popstate', function(event) {
            // If we have state data, use it to update the form and table
            if (event.state && event.state.formData) {
                // Update form fields with values from the state
                const formData = event.state.formData;
                for (const [key, value] of Object.entries(formData)) {
                    const field = filterForm.elements[key];
                    if (field) {
                        field.value = value;
                    }
                }
                
                // Update the programs list with the form data
                updateProgramsList(formData);
            } else {
                // If no state, use the current URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const formData = {};
                
                // Populate form fields with URL parameters
                for (const [key, value] of urlParams.entries()) {
                    formData[key] = value;
                    const field = filterForm.elements[key];
                    if (field) {
                        field.value = value;
                    }
                }
                
                // Update the programs list
                updateProgramsList(formData);
            }
        });
          // Handle form submission via AJAX
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(filterForm);
            const serializedData = {};
            
            // Convert FormData to plain object
            for (const [key, value] of formData.entries()) {
                serializedData[key] = value;
            }
            
            // Update programs list via AJAX
            updateProgramsList(serializedData);
        });
        
        // Handle the Reset button click
        const resetButton = filterForm.querySelector('#resetFilters');
        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Reset all form fields
                filterForm.reset();
                  // Prepare minimal data (just period_id if it exists)
                const minimalData = {};
                const periodInput = filterForm.querySelector('input[name="period_id"]');
                if (periodInput) {
                    minimalData.period_id = periodInput.value;
                } else {
                    // Check for period_id in URL params
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('period_id')) {
                        minimalData.period_id = urlParams.get('period_id');
                    }
                }
                
                // Update with reset filters
                updateProgramsList(minimalData);
            });
        }
          // Handle dropdown changes for auto-submit
        const dropdownFilters = filterForm.querySelectorAll('select');
        dropdownFilters.forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                // Get form data with current values
                const formData = new FormData(filterForm);
                const serializedData = {};
                
                // Convert FormData to plain object
                for (const [key, value] of formData.entries()) {
                    serializedData[key] = value;
                }
                
                // Update programs list via AJAX
                updateProgramsList(serializedData);
            });
        });
          // Handle search input with debouncing
        const searchInput = filterForm.querySelector('#programSearch');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Get form data with current values
                    const formData = new FormData(filterForm);
                    const serializedData = {};
                    
                    // Convert FormData to plain object
                    for (const [key, value] of formData.entries()) {
                        serializedData[key] = value;
                    }
                    
                    // Update programs list via AJAX
                    updateProgramsList(serializedData);
                }, 500); // 500ms delay
            });
        }    }

    // Handle refresh button
    const refreshButton = document.getElementById('refreshTable');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            // Get current form data
            const formData = new FormData(filterForm);
            const serializedData = {};
            
            // Convert FormData to plain object
            for (const [key, value] of formData.entries()) {
                serializedData[key] = value;
            }
            
            // Update programs list via AJAX
            updateProgramsList(serializedData);
        });
    }

    // Initialize tooltips for action buttons
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
