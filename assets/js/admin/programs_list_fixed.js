/**
 * Admin Programs List
 * 
 * Handles interactions for the programs listing page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize datatable if available
    let dataTable;
    const programsTable = document.getElementById('programsTable');
    if (programsTable && typeof $.fn.DataTable === 'function') {
        dataTable = $(programsTable).DataTable({
            "paging": true,
            "pageLength": 25,
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
        loadingOverlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 10;
            display: none;
        `;
        tableContainer.style.position = 'relative';
        tableContainer.appendChild(loadingOverlay);
        
        // Show loading spinner
        function showLoading() {
            loadingOverlay.style.display = 'flex';
        }
        
        // Hide loading spinner
        function hideLoading() {
            loadingOverlay.style.display = 'none';
        }
        
        // Create or get toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Function to update the programs table via AJAX
        function updateProgramsList(formData) {
            // If there's an ongoing request, abort it
            if (ajaxRequest) {
                ajaxRequest.abort();
            }
            
            // Show loading indicator
            showLoading();
            
            // Update URL with current filters for bookmarking/sharing
            const params = new URLSearchParams(formData);
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            history.pushState({ formData: formData }, '', newUrl);
            currentUrlParams = params;
            
            // Make the AJAX request
            ajaxRequest = $.ajax({
                url: '../ajax/get_programs_list.php',
                method: 'GET',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    // Always hide loading indicator first
                    hideLoading();
                    
                    if (response.status === 'success') {
                        // Update the table data
                        const tableBody = document.querySelector('table tbody');
                        
                        // Destroy existing DataTable if it exists
                        if (dataTable) {
                            try {
                                dataTable.destroy();
                            } catch (e) {
                                console.warn("Error destroying DataTable:", e);
                            }
                        }
                        
                        // Update HTML
                        tableBody.innerHTML = response.tableHtml;
                        
                        // Update program count in header if exists
                        const countElement = document.querySelector('.card-header .badge');
                        if (countElement) {
                            countElement.textContent = response.count + ' Programs';
                        }
                        
                        // Try to reinitialize DataTable with safety checks
                        try {
                            if (typeof $.fn.DataTable === 'function') {
                                // Small delay to ensure DOM is ready
                                setTimeout(function() {
                                    dataTable = $('#programsTable').DataTable({
                                        "paging": true,
                                        "pageLength": 25,
                                        "lengthChange": true,
                                        "searching": false,
                                        "ordering": true,
                                        "info": true, 
                                        "autoWidth": false,
                                        "responsive": true,
                                        "language": {
                                            "emptyTable": "No programs found matching your criteria."
                                        }
                                    });
                                }, 100);
                            } else {
                                console.warn("DataTable plugin not available for reinitialization");
                            }
                        } catch (e) {
                            console.error("Failed to reinitialize DataTable:", e);
                        }
                        
                        // Reinitialize tooltips for new action buttons
                        reinitializeTooltips();
                        
                        // Clear existing toasts before showing a new one to avoid duplicates
                        clearToasts();
                        
                        // Show success toast notification with shorter text
                        showToast('Success', 'Programs updated', 'success');
                        
                    } else {
                        console.error('Error fetching programs:', response.error);
                        
                        // Clear existing toasts before showing a new one
                        clearToasts();
                        showToast('Error', 'Failed to update list', 'danger');
                    }
                    
                    // Clear the request reference
                    ajaxRequest = null;
                },
                error: function(xhr, status, error) {
                    // Always hide loading indicator
                    hideLoading();
                    
                    // Only show error if not aborted
                    if (status !== 'abort') {
                        console.error('AJAX error:', error);
                        
                        // Clear existing toasts before showing a new one
                        clearToasts();
                        showToast('Error', 'Failed to update program list: ' + error, 'danger');
                    }
                    
                    // Clear the request reference
                    ajaxRequest = null;
                }
            });
        }
        
        // Function to clear all toasts
        function clearToasts() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                const bsToast = bootstrap.Toast.getInstance(toast);
                if (bsToast) {
                    bsToast.hide();
                }
                toast.remove();
            });
        }
        
        // Function to show toast notifications
        function showToast(title, message, type = 'info') {
            if (typeof window.showToast === 'function') {
                window.showToast(title, message, type);
            } else {
                // Fallback if global showToast isn't loaded
                alert(`${title}: ${message}`);
            }
        }
        
        // Function to reinitialize tooltips for dynamically added elements
        function reinitializeTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
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
        
        // Handle refresh button
        const refreshButton = document.getElementById('refreshTable');
        if (refreshButton) {
            refreshButton.addEventListener('click', function(e) {
                e.preventDefault();
                
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
    }

    // Initialize tooltips for action buttons
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
