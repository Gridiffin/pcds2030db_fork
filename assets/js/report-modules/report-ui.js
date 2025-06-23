/**
 * Report UI Module
 * 
 * Handles UI interactions for the report generator
 */

// Prevent multiple instantiations
if (typeof window.ReportUI !== 'undefined') {
    console.log('ReportUI module already loaded, skipping redeclaration');
} else {
    window.ReportUI = (function() {
    // Store DOM element references
    let elements = {};

    /**
     * Initialize the UI components
     */
    function initUI() {
        // Store references to DOM elements
        elements = {
            generateBtn: document.getElementById('generatePptxBtn'),
            generationForm: document.getElementById('reportGenerationForm'),
            statusCard: document.getElementById('generationStatus'),
            statusMessage: document.getElementById('statusMessage'),
            successMessage: document.getElementById('successMessage'),
            errorMessage: document.getElementById('errorMessage'),
            errorText: document.getElementById('errorText'),
            downloadLink: document.getElementById('downloadLink'),
            sectorSelect: document.getElementById('sectorSelect'),
            periodSelect: document.getElementById('periodSelect'),
            reportNameInput: document.getElementById('reportName'),
            reportDescInput: document.getElementById('reportDescription'),
            isPublicCheckbox: document.getElementById('isPublic'),
            deleteReportModal: document.getElementById('deleteReportModal'),
            reportNameToDelete: document.getElementById('reportNameToDelete'),
            confirmDeleteBtn: document.getElementById('confirmDeleteBtn'),
            programSelector: document.getElementById('programSelector')
        };
        
        // Set up event listeners
        setupEventListeners();
    }

    /**
     * Set up all event listeners
     */
    function setupEventListeners() {
        // Auto-update report name when period or sector changes
        if (elements.sectorSelect && elements.periodSelect) {
            elements.sectorSelect.addEventListener('change', updateReportName);
            elements.periodSelect.addEventListener('change', updateReportName);
        }
        
        // Generate button
        if (elements.generateBtn) {
            // Ensure we don't add duplicate listeners
            const newGenerateBtn = elements.generateBtn.cloneNode(true);
            if (elements.generateBtn.parentNode) {
                elements.generateBtn.parentNode.replaceChild(newGenerateBtn, elements.generateBtn);
            }
            
            // Update reference to the new button
            elements.generateBtn = document.getElementById('generatePptxBtn');
            
            if (elements.generateBtn) {
                elements.generateBtn.addEventListener('click', handleGenerateClick);
            }
        }
        
        // Delete report modal
        setupDeleteModal();
    }

    /**
     * Set up the delete report modal functionality
     */
    function setupDeleteModal() {
        if (elements.deleteReportModal) {
            // Clean up any existing event listeners to prevent duplicates
            const newDeleteReportModal = elements.deleteReportModal.cloneNode(true);
            if (elements.deleteReportModal.parentNode) {
                elements.deleteReportModal.parentNode.replaceChild(newDeleteReportModal, elements.deleteReportModal);
            }
            
            // Re-assign the modal reference after replacing with clone
            const refreshedDeleteReportModal = document.getElementById('deleteReportModal');
            const refreshedReportNameToDelete = document.getElementById('reportNameToDelete');
            const refreshedConfirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            
            // Update references
            elements.deleteReportModal = refreshedDeleteReportModal;
            elements.reportNameToDelete = refreshedReportNameToDelete;
            elements.confirmDeleteBtn = refreshedConfirmDeleteBtn;
            
            let reportIdToDelete = null;
            
            // Set up modal show event
            if (elements.deleteReportModal) {
                elements.deleteReportModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    reportIdToDelete = button.getAttribute('data-report-id');
                    const reportName = button.getAttribute('data-report-name');
                    
                    if (elements.reportNameToDelete) {
                        elements.reportNameToDelete.textContent = reportName;
                    }
                });
            }
            
            // Set up delete confirmation button
            if (elements.confirmDeleteBtn) {
                elements.confirmDeleteBtn.addEventListener('click', function() {
                    if (!reportIdToDelete) return;
                      // Store reference to the button that triggered the modal
                    const triggerButton = document.querySelector(`.delete-report-btn[data-report-id="${reportIdToDelete}"]`);
                    
                    // Disable delete button and show loading state
                    elements.confirmDeleteBtn.disabled = true;
                    elements.confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
                    
                    // Delete the report
                    ReportAPI.deleteReport(reportIdToDelete, triggerButton)
                        .then(() => {
                            // Show success toast
                            showToast('Success', 'Report deleted successfully', 'success');
                            
                            // Reset button state
                            elements.confirmDeleteBtn.disabled = false;
                            elements.confirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete Report';
                            reportIdToDelete = null;
                            
                            // Hide modal
                            const modalInstance = bootstrap.Modal.getInstance(elements.deleteReportModal);
                            modalInstance.hide();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Error', error.message || 'An unexpected error occurred', 'danger');
                            
                            // Reset button state
                            elements.confirmDeleteBtn.disabled = false;
                            elements.confirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete Report';
                            reportIdToDelete = null;
                            
                            // Hide modal
                            const modalInstance = bootstrap.Modal.getInstance(elements.deleteReportModal);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                        });
                });
            }
        }
    }

    /**
     * Update report name based on selected sector and period
     */
    function updateReportName() {
        if (elements.sectorSelect && elements.periodSelect && elements.sectorSelect.value && elements.periodSelect.value) {
            const sectorText = elements.sectorSelect.options[elements.sectorSelect.selectedIndex].text;
            const periodText = elements.periodSelect.options[elements.periodSelect.selectedIndex].text;
            elements.reportNameInput.value = `${sectorText} Report - ${periodText}`;
        }
    }

    /**
     * Handle click on the generate button
     * @param {Event} e - The click event
     */
    function handleGenerateClick(e) {
        e.preventDefault();
        
        // Validate form
        if (!elements.periodSelect.value || !elements.sectorSelect.value || !elements.reportNameInput.value) {
            alert('Please fill out all required fields.');
            return;
        }
        
        const periodId = elements.periodSelect.value;
        const sectorId = elements.sectorSelect.value;
        const reportName = elements.reportNameInput.value;
        const reportDescription = elements.reportDescInput.value || '';
        const isPublic = elements.isPublicCheckbox.checked ? 1 : 0;        // Get selected program IDs with their order from global state
        const selectedProgramIds = [];
        const programOrders = {};
        
        // Check if globalProgramSelections is available (from report-generator.js)
        if (typeof window.globalProgramSelections !== 'undefined' && window.globalProgramSelections) {
            // Use global state for multi-agency support
            window.globalProgramSelections.forEach((data, programId) => {
                if (data.selected) {
                    selectedProgramIds.push(programId);
                    if (data.order) {
                        programOrders[programId] = data.order;
                    }
                }
            });
            
            console.log('Using global state - Selected programs:', selectedProgramIds);
            console.log('Program orders:', programOrders);
        } else {
            // Fallback to DOM-based collection for backward compatibility
            console.log('Global state not available, falling back to DOM collection');
            const programCheckboxes = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]:checked');
            
            programCheckboxes.forEach(checkbox => {
                const programId = checkbox.value;
                selectedProgramIds.push(programId);
                
                // Get the order value for this program
                const orderInput = document.getElementById(`order_${programId}`);
                if (orderInput && orderInput.value) {
                    programOrders[programId] = parseInt(orderInput.value);
                }            });
        }
        
        // Validate that we have selected programs
        if (selectedProgramIds.length === 0) {
            alert('Please select at least one program to include in the report.');
            return;
        }
        
        console.log('Final selected programs for report generation:', selectedProgramIds);
        console.log('Final program orders:', programOrders);
        
        // Hide existing messages and show status
        elements.successMessage.classList.add('d-none');
        elements.errorMessage.classList.add('d-none');
        elements.statusCard.classList.remove('d-none');
        
        // Disable generate button
        elements.generateBtn.disabled = true;
        
        // Step 1: Fetch data from API
        elements.statusMessage.textContent = 'Fetching data...';
              // Pass selectedProgramIds and programOrders to fetchReportData
        ReportAPI.fetchReportData(periodId, sectorId, [], selectedProgramIds, programOrders)
            .then(data => {
                console.log('Received API data:', data);
                console.log('Programs in API data:', data.programs);
                console.log('Sector info:', data.reportTitle, 'sector_id:', data.sector_id);
                elements.statusMessage.textContent = 'Generating PPTX...';
                return ReportPopulator.generatePresentation(data, elements.statusMessage);
            })
            .then(blob => {
                elements.statusMessage.textContent = 'Saving report...';
                return ReportAPI.uploadPresentation(blob, periodId, sectorId, reportName, reportDescription, isPublic);            })
            .then(result => {
                // Hide status and show success
                elements.statusCard.classList.add('d-none');
                elements.successMessage.classList.remove('d-none');
                
                // Set download link
                if (result.pptx_path) {
                    elements.downloadLink.href = `../../download.php?type=report&file=${result.pptx_path}`;
                }
                  // Re-enable generate button
                elements.generateBtn.disabled = false;
                
                // Track the newly generated report for NEW badge
                if (result.report_id && typeof window.addNewReportToTracker === 'function') {
                    window.addNewReportToTracker(result.report_id);
                    console.log('Tracked new report for badge:', result.report_id);
                }

                // Update recent reports table with enhanced feedback
                console.log('Refreshing recent reports table...');
                const refreshPromise = ReportAPI.refreshReportsTable();
                
                refreshPromise
                    .then(() => {
                        console.log('Reports table refreshed successfully');
                        // Optionally show a subtle success notification
                        if (typeof showToast !== 'undefined') {
                            showToast('Success', 'Report generated and list updated!', 'success');
                        }
                    })
                    .catch(refreshError => {
                        console.warn('Failed to refresh reports table:', refreshError);
                        // Show informative message instead of generic warning
                        if (typeof showToast !== 'undefined') {
                            showToast('Notice', 'Report generated successfully! The list will update when you refresh the page.', 'info');
                        }
                    });
                
                return result; // Return the original result so the promise chain is not broken
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide status and show error
                elements.statusCard.classList.add('d-none');
                elements.errorMessage.classList.remove('d-none');
                elements.errorText.textContent = error.message || 'Error generating report. Please try again.';
                
                // Re-enable generate button
                elements.generateBtn.disabled = false;
            });
    }

    /**
     * Show a toast notification
     * @param {string} title - The toast title
     * @param {string} message - The toast message
     * @param {string} type - The toast type (success, info, warning, danger)
     */
    function showToast(title, message, type = 'info') {
        // Check if toast container exists, if not create it
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.id = toastId;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        // Add toast content
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong>: ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 5000 });
        bsToast.show();
        
        // Remove toast from DOM after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }    // Expose public methods
    return {
        initUI,
        showToast,
        updateReportName,
        setupDeleteModal // Make this function publicly accessible so it can be called after table refresh
    };
})();

} // End ReportUI guard