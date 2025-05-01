/**
 * Report Generator
 * 
 * Client-side JavaScript for generating PPTX reports using PptxGenJS
 * This script handles:
 * 1. Fetching data from the API
 * 2. Generating the PPTX with a master slide
 * 3. Uploading the generated file to the server
 */

// Global initialization flag to prevent duplicate initialization
if (typeof reportGeneratorInitialized === 'undefined') {
    var reportGeneratorInitialized = false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations
    if (reportGeneratorInitialized) {
        console.log('Report generator already initialized, skipping duplicate initialization.');
        return;
    }
    reportGeneratorInitialized = true;
    console.log('Initializing report generator...');
    
    // Get DOM elements
    const generateBtn = document.getElementById('generatePptxBtn');
    const generationForm = document.getElementById('reportGenerationForm');
    const statusCard = document.getElementById('generationStatus');
    const statusMessage = document.getElementById('statusMessage');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const downloadLink = document.getElementById('downloadLink');
    
    // Delete report modal elements
    const deleteReportModal = document.getElementById('deleteReportModal');
    const reportNameToDelete = document.getElementById('reportNameToDelete');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let reportIdToDelete = null;
    
    // Set default report name based on sector and period selection
    const sectorSelect = document.getElementById('sectorSelect');
    const periodSelect = document.getElementById('periodSelect');
    const reportNameInput = document.getElementById('reportName');
    
    // When sector or period is changed, update report name suggestion
    function updateReportName() {
        if (sectorSelect.value && periodSelect.value) {
            const sectorText = sectorSelect.options[sectorSelect.selectedIndex].text;
            const periodText = periodSelect.options[periodSelect.selectedIndex].text;
            reportNameInput.value = `${sectorText} Report - ${periodText}`;
        }
    }
    
    if (sectorSelect && periodSelect) {
        sectorSelect.addEventListener('change', updateReportName);
        periodSelect.addEventListener('change', updateReportName);
    }
    
    // Setup delete report modal
    if (deleteReportModal) {
        // Clean up any existing event listeners to prevent duplicates
        const newDeleteReportModal = deleteReportModal.cloneNode(true);
        if (deleteReportModal.parentNode) {
            deleteReportModal.parentNode.replaceChild(newDeleteReportModal, deleteReportModal);
        }
        
        // Re-assign the modal reference after replacing with clone
        const refreshedDeleteReportModal = document.getElementById('deleteReportModal');
        const refreshedReportNameToDelete = document.getElementById('reportNameToDelete');
        const refreshedConfirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        refreshedDeleteReportModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            reportIdToDelete = button.getAttribute('data-report-id');
            const reportName = button.getAttribute('data-report-name');
            
            refreshedReportNameToDelete.textContent = reportName;
        });
        
        // Handle delete confirmation
        if (refreshedConfirmDeleteBtn) {
            refreshedConfirmDeleteBtn.addEventListener('click', function() {
                if (!reportIdToDelete) return;
                
                // Store reference to the button that triggered the modal
                const triggerButton = document.querySelector(`.action-btn-delete[data-report-id="${reportIdToDelete}"]`);
                const rowToDelete = triggerButton ? triggerButton.closest('tr') : null;
                
                // Disable delete button and show loading state
                refreshedConfirmDeleteBtn.disabled = true;
                refreshedConfirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
                
                // Send delete request
                const formData = new FormData();
                formData.append('report_id', reportIdToDelete);
                
                fetch('../../api/delete_report.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // First check if the response is ok
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    
                    // Get the raw text first to see what's actually being returned
                    return response.text();
                })
                .then(rawText => {
                    console.log('Raw API response:', rawText);
                    
                    // Try to parse as JSON, with error handling
                    let data;
                    try {
                        data = JSON.parse(rawText);
                    } catch (e) {
                        console.error('JSON parsing error:', e);
                        throw new Error('Invalid JSON response from server. See console for details.');
                    }
                    
                    // Hide modal
                    const modalInstance = bootstrap.Modal.getInstance(refreshedDeleteReportModal);
                    modalInstance.hide();
                    
                    if (data.success) {
                        // Show success toast or message
                        showToast('Success', 'Report deleted successfully', 'success');
                        
                        // Remove the row from the table if it exists
                        if (rowToDelete) {
                            rowToDelete.remove();
                            
                            // If no more reports, show empty state
                            const tbody = document.querySelector('.reports-table tbody');
                            if (tbody && tbody.children.length === 0) {
                                const tableContainer = document.querySelector('.table-responsive');
                                if (tableContainer) {
                                    tableContainer.innerHTML = '<div class="reports-empty-state"><p class="text-muted">No reports generated yet.</p></div>';
                                }
                            }
                        } else {
                            // If we couldn't find the row, reload the page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    } else {
                        // Show error message
                        showToast('Error', data.error || 'Failed to delete report', 'danger');
                    }
                    
                    // Reset button state
                    refreshedConfirmDeleteBtn.disabled = false;
                    refreshedConfirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete Report';
                    reportIdToDelete = null;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', error.message || 'An unexpected error occurred', 'danger');
                    
                    // Reset button state
                    refreshedConfirmDeleteBtn.disabled = false;
                    refreshedConfirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete Report';
                    reportIdToDelete = null;
                    
                    // Hide modal
                    const modalInstance = bootstrap.Modal.getInstance(refreshedDeleteReportModal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
            });
        }
    }
    
    // Helper function to show toast notifications
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
    }
    
    // Add event listener to the generate button
    if (generateBtn) {
        // Ensure we remove any existing listeners first to prevent duplicates
        const newGenerateBtn = generateBtn.cloneNode(true);
        generateBtn.parentNode.replaceChild(newGenerateBtn, generateBtn);
        
        // Re-get the button after replacing with clone
        const refreshedGenerateBtn = document.getElementById('generatePptxBtn');
        if (refreshedGenerateBtn) {
            refreshedGenerateBtn.addEventListener('click', generateReport);
        }
    }
    
    /**
     * Main function to generate the report
     */
    function generateReport() {
        // Validate form
        if (!generationForm.checkValidity()) {
            generationForm.reportValidity();
            return;
        }
        
        // Get form values
        const periodId = periodSelect.value;
        const sectorId = sectorSelect.value;
        const reportName = reportNameInput.value;
        const description = document.getElementById('reportDescription').value;
        const isPublic = document.getElementById('isPublic').checked ? 1 : 0;
        
        // Hide existing messages and show status
        successMessage.classList.add('d-none');
        errorMessage.classList.add('d-none');
        statusCard.classList.remove('d-none');
        
        // Disable generate button
        refreshedGenerateBtn = document.getElementById('generatePptxBtn');
        if (refreshedGenerateBtn) {
            refreshedGenerateBtn.disabled = true;
        }
        
        // Step 1: Fetch data from API
        statusMessage.textContent = 'Fetching data...';
        
        const apiUrl = `../../api/report_data.php?period_id=${periodId}&sector_id=${sectorId}`;
        console.log('Fetching from URL:', apiUrl);
        
        fetch(apiUrl)
            .then(response => {
                console.log('Response status:', response.status);
                return response.text(); // Get the raw text first to see what's actually being returned
            })
            .then(rawText => {
                console.log('Raw response:', rawText);
                // Try to parse the JSON
                try {
                    const data = JSON.parse(rawText);
                    statusMessage.textContent = 'Generating PPTX...';
                    return generatePresentation(data);
                } catch (error) {
                    console.error('JSON parsing error:', error);
                    throw new Error('API returned invalid JSON. See console for details.');
                }
            })
            .then(blob => {
                statusMessage.textContent = 'Saving report...';
                return uploadPresentationToServer(blob, periodId, sectorId, reportName, description, isPublic);
            })
            .then(result => {
                // Hide status and show success
                statusCard.classList.add('d-none');
                successMessage.classList.remove('d-none');
                
                // Set download link
                if (result.pptx_path) {
                    downloadLink.href = `../../download.php?type=report&file=${result.pptx_path}`;
                }
                
                // Re-enable generate button
                refreshedGenerateBtn = document.getElementById('generatePptxBtn');
                if (refreshedGenerateBtn) {
                    refreshedGenerateBtn.disabled = false;
                }

                // Update recent reports table directly
                refreshReportsTable();
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide status and show error
                statusCard.classList.add('d-none');
                errorMessage.classList.remove('d-none');
                errorText.textContent = error.message || 'Error generating report. Please try again.';
                
                // Re-enable generate button
                refreshedGenerateBtn = document.getElementById('generatePptxBtn');
                if (refreshedGenerateBtn) {
                    refreshedGenerateBtn.disabled = false;
                }
            });
    }
    
    /**
     * Generate the PPTX presentation using PptxGenJS
     * @param {Object} data - The data from the API
     * @returns {Promise<Blob>} - A promise that resolves to a Blob containing the PPTX file
     */
    function generatePresentation(data) {
        return new Promise((resolve, reject) => {
            try {
                // Create new presentation
                let pptx = new PptxGenJS();
                
                // Define report master slide
                const themeColors = defineReportMaster(pptx);
                
                // Create slide using the master
                let slide = pptx.addSlide({ masterName: 'REPORT_MASTER_SLIDE' });
                
                // Populate slide with data
                populateSlide(slide, data, pptx, themeColors);
                
                // Get PPTX as blob
                pptx.write('blob')
                    .then(blob => {
                        resolve(blob);
                    })
                    .catch(error => {
                        reject(new Error('Error generating PPTX: ' + error.message));
                    });
            } catch (error) {
                reject(new Error('Error in presentation generation: ' + error.message));
            }
        });
    }

    /**
     * Define the master slide layout
     * @param {Object} pptx - The PptxGenJS instance
     */
    function defineReportMaster(pptx) {
        // Define a common font for consistency
        const defaultFont = 'Calibri';
        
        // Define theme colors for consistent styling
        const themeColors = {
            primary: '4472C4',     // Blue for primary elements
            secondary: '70AD47',   // Green for secondary elements  
            accent1: 'ED7D31',     // Orange for accent
            accent2: '5B9BD5',     // Light blue for accent
            text: '000000',        // Black for main text
            lightText: '666666',   // Grey for secondary text
            headerBg: 'F2F2F2',    // Light grey for section headers
            greenStatus: '00B050', // Green for on-track status
            yellowStatus: 'FFFF00', // Yellow for minor issues
            redStatus: 'FF0000',   // Red for major issues
            greyStatus: 'D9D9D9'   // Grey for no data
        };
        
        // Create slide master with enhanced styling
        pptx.defineSlideMaster({
            title: 'REPORT_MASTER_SLIDE',
            background: { color: 'FFFFFF' }, // White background
            slideNumber: { x: 0.3, y: 7.0, color: themeColors.lightText, fontFace: defaultFont, fontSize: 8 },
            objects: [
                // Title placeholders with professional formatting
                { 'title': { 
                    options: { 
                        x: 0.5, y: 0.1, w: 7.0, h: 0.6, 
                        fontSize: 28, bold: true, 
                        fontFace: defaultFont,
                        color: themeColors.primary,
                        shadow: { type: 'outer', angle: 45, blur: 3, color: 'CFCFCF', offset: 1 }
                    } 
                }},
                { 'subtitle': { 
                    options: { 
                        x: 0.5, y: 0.5, w: 7.0, h: 0.25, 
                        fontSize: 11, 
                        fontFace: defaultFont,
                        color: themeColors.lightText,
                        italic: true
                    } 
                }},
                { 'quarterTitle': { 
                    options: { 
                        x: 8.0, y: 0.1, w: 4.5, h: 0.6, 
                        fontSize: 32, bold: true, 
                        fontFace: defaultFont,
                        color: themeColors.accent1,
                        align: 'right'
                    } 
                }},
                
                // Project area with section background
                { 'projectsHeader': { 
                    type: 'rect', 
                    options: { 
                        x: 0.5, y: 0.8, w: 6.5, h: 0.3, 
                        fill: { color: themeColors.headerBg },
                        line: { color: themeColors.primary, width: 1 }
                    } 
                }},
                { 'projectsTitle': { 
                    options: { 
                        x: 0.6, y: 0.825, w: 6.0, h: 0.25, 
                        fontSize: 12, bold: true,
                        fontFace: defaultFont,
                        color: themeColors.primary
                    } 
                }},
                { 'projectsArea': { 
                    type: 'body', 
                    options: { 
                        x: 0.5, y: 1.2, w: 6.5, h: 5.0,
                        fontFace: defaultFont
                    } 
                }},
                
                // Chart areas with subtle borders - Main chart
                { 'mainChartBg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 0.8, w: 5.2, h: 2.7,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 1, dashType: 'dash' }
                    } 
                }},
                { 'mainChartArea': { 
                    type: 'chart', 
                    options: { 
                        x: 7.5, y: 1.0, w: 4.8, h: 2.3
                    } 
                }},
                
                // Secondary chart area
                { 'secondaryChartBg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 5.0, w: 5.2, h: 2.0,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.secondary, width: 1, dashType: 'dash' }
                    } 
                }},
                { 'secondaryChartArea': { 
                    type: 'chart', 
                    options: { 
                        x: 7.5, y: 5.1, w: 4.8, h: 1.8
                    } 
                }},
                
                // KPI sections with background styling
                { 'kpiHeader': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 3.7, w: 5.2, h: 0.3,
                        fill: { color: themeColors.headerBg },
                        line: { color: themeColors.primary, width: 1 }
                    } 
                }},
                { 'kpiTitleArea': { 
                    options: { 
                        x: 7.4, y: 3.725, w: 5.0, h: 0.25, 
                        fontSize: 12, bold: true,
                        fontFace: defaultFont,
                        color: themeColors.primary
                    } 
                }},
                
                // KPI 1 (Left)
                { 'kpi1Bg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 4.1, w: 2.1, h: 0.8,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 0.75 }
                    } 
                }},
                { 'kpi1Area': { 
                    type: 'body', 
                    options: { 
                        x: 7.5, y: 4.2, w: 1.9, h: 0.6,
                        fontFace: defaultFont
                    } 
                }},
                
                // KPI 2 (Middle)
                { 'kpi2Bg': { 
                    type: 'rect', 
                    options: { 
                        x: 9.5, y: 4.1, w: 3.0, h: 0.8,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 0.75 }
                    } 
                }},
                { 'kpi2Area': { 
                    type: 'body', 
                    options: { 
                        x: 9.7, y: 4.2, w: 2.6, h: 0.6,
                        fontFace: defaultFont
                    } 
                }},
                
                // KPI 3 (Bottom)
                { 'kpi3Bg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 7.1, w: 5.2, h: 0.6,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 0.75 }
                    } 
                }},
                { 'kpi3Area': { 
                    type: 'body', 
                    options: { 
                        x: 7.5, y: 7.2, w: 5.0, h: 0.4,
                        fontFace: defaultFont
                    } 
                }},
                
                // Legend section with styling
                { 'legendHeader': { 
                    type: 'rect', 
                    options: { 
                        x: 0.5, y: 6.3, w: 6.5, h: 0.3,
                        fill: { color: themeColors.headerBg },
                        line: { color: themeColors.primary, width: 1 }
                    } 
                }},
                { 'legendTitle': { 
                    options: { 
                        x: 0.6, y: 6.325, w: 6.0, h: 0.25, 
                        fontSize: 12, bold: true,
                        fontFace: defaultFont,
                        color: themeColors.primary,
                        text: 'Status Legend'
                    } 
                }},
                { 'legendArea': { 
                    type: 'body', 
                    options: { 
                        x: 0.5, y: 6.7, w: 6.5, h: 0.7,
                        fontFace: defaultFont
                    } 
                }},
                
                // Footer with draft date
                { 'footerLine': { 
                    type: 'line', 
                    options: { 
                        x: 0.5, y: 7.4, w: 12.0, h: 0, 
                        line: { color: themeColors.lightText, width: 0.75, dashType: 'dash' }
                    } 
                }},
                { 'draftDateArea': { 
                    type: 'body', 
                    options: { 
                        x: 0.5, y: 7.5, w: 3.0, h: 0.3, 
                        fontSize: 10, italic: true,
                        fontFace: defaultFont,
                        color: themeColors.lightText
                    } 
                }},
                { 'footerLogo': { 
                    type: 'placeholder', 
                    options: { 
                        x: 11.0, y: 7.4, w: 1.5, h: 0.4
                        // Will be populated with agency logo if available
                    } 
                }}
            ],
        });
        
        // Return theme colors for use in populateSlide
        return themeColors;
    }

    /**
     * Populate the slide with data from the API
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     * @param {Object} themeColors - The theme colors for styling
     */
    function populateSlide(slide, data, pptx, themeColors) {
        // Define common font for consistency
        const defaultFont = 'Calibri';

        // Add titles - Using EXPLICIT coordinates from master definition
        slide.addText(data.reportTitle || 'Default Title', { 
            x: 0.5, y: 0.1, w: 7.0, h: 0.6, 
            fontSize: 28, bold: true, 
            fontFace: defaultFont,
            color: themeColors.primary,
            shadow: { type: 'outer', angle: 45, blur: 3, color: 'CFCFCF', offset: 1 }
        });

        slide.addText(data.sectorLeads, { 
            x: 0.5, y: 0.5, w: 7.0, h: 0.25, 
            fontSize: 11, 
            fontFace: defaultFont,
            color: themeColors.lightText,
            italic: true
        });

        slide.addText(data.quarter, { 
            x: 8.0, y: 0.1, w: 4.5, h: 0.6, 
            fontSize: 32, bold: true, 
            fontFace: defaultFont,
            color: themeColors.accent1,
            align: 'right'
        });

        // Add projects title
        slide.addText('Projects / Programmes', { 
            x: 0.6, y: 0.825, w: 6.0, h: 0.25, 
            fontSize: 12, bold: true,
            fontFace: defaultFont,
            color: themeColors.primary
        });

        // Add projects with alternating row background for readability
        let yPos = 1.2; // Starting Y position (updated to match new projectsArea y-coord)
        
        if (data.projects && Array.isArray(data.projects)) {
            data.projects.forEach((proj, index) => {
                // Add alternating row background for better readability
                const rowBgColor = index % 2 === 0 ? 'FFFFFF' : 'F5F5F5';
                
                slide.addShape(pptx.shapes.RECTANGLE, {
                    x: 0.5, y: yPos, w: 6.5, h: 0.4,
                    fill: { color: rowBgColor },
                    line: { color: 'EEEEEE', width: 0.5 }
                });
                
                // Status color indicator
                let statusColor = '';
                switch (proj.rating) {
                    case 'green': statusColor = themeColors.greenStatus; break; // Green
                    case 'yellow': statusColor = themeColors.yellowStatus; break; // Yellow
                    case 'red': statusColor = themeColors.redStatus; break; // Red
                    default: statusColor = themeColors.greyStatus; // Grey
                }
                
                // Add a colored rectangle for status with shadow for emphasis
                slide.addShape(pptx.shapes.RECTANGLE, { 
                    x: 4.0, y: yPos + 0.05, w: 0.3, h: 0.3, 
                    fill: { color: statusColor },
                    line: { color: themeColors.text, width: 1 },
                    shadow: { type: 'outer', angle: 45, blur: 3, color: 'CFCFCF', offset: 1 }
                });
                
                // Add text for project name with improved styling
                slide.addText(proj.name, { 
                    x: 0.6, y: yPos + 0.05, w: 3.2, h: 0.3, 
                    fontSize: 10, bold: true, 
                    fontFace: defaultFont,
                    color: themeColors.text,
                    valign: 'middle'
                });
                
                // Add target text with improved styling - lighter color
                slide.addText(proj.target, { 
                    x: 4.5, y: yPos + 0.05, w: 2.4, h: 0.3, // Explicit coordinates
                    fontSize: 9, 
                    fontFace: defaultFont,
                    color: themeColors.lightText,
                    valign: 'middle'
                });
                
                // Move Y position down for next project
                yPos += 0.45; // Slightly more space between rows
            });
        }

        // Add KPI section title
        slide.addText('Key Performance Indicators', { 
            x: 7.4, y: 3.725, w: 5.0, h: 0.25, 
            fontSize: 12, bold: true,
            fontFace: defaultFont,
            color: themeColors.primary
        });

        // Rest of the slide population code...
        // Add Draft Date
        slide.addText(data.draftDate || new Date().toLocaleDateString(), { 
            x: 0.5, y: 7.5, w: 3.0, h: 0.3, 
            fontSize: 10, italic: true,
            fontFace: defaultFont,
            color: themeColors.lightText
        });
    }
    
    /**
     * Upload the generated presentation to the server
     * @param {Blob} blob - The generated PPTX as a Blob
     * @param {number} periodId - The reporting period ID
     * @param {number} sectorId - The sector ID
     * @param {string} reportName - The name of the report
     * @param {string} description - The report description
     * @param {number} isPublic - Whether the report should be public (1) or not (0)
     * @returns {Promise<Object>} - A promise that resolves to the response from the server
     */
    function uploadPresentationToServer(blob, periodId, sectorId, reportName, description, isPublic) {
        return new Promise((resolve, reject) => {
            // Create a file name (will be ignored by server but needed for FormData)
            const periodText = periodSelect.options[periodSelect.selectedIndex].text;
            const sectorText = sectorSelect.options[sectorSelect.selectedIndex].text;
            const filename = `${sectorText}_${periodText.replace(' ', '_')}.pptx`;
            
            // Create FormData
            const formData = new FormData();
            formData.append('report_file', blob, filename);
            formData.append('period_id', periodId);
            formData.append('sector_id', sectorId);
            formData.append('report_name', reportName);
            formData.append('description', description);
            formData.append('is_public', isPublic);
            
            // Send to server
            fetch('../../api/save_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    resolve(result);
                } else {
                    reject(new Error(result.error || 'Unknown error saving the report'));
                }
            })
            .catch(error => {
                reject(error);
            });
        });
    }
    
    /**
     * Directly refresh the reports table without a full page reload
     * This is a new function that replaces the previous updateRecentReportsTable
     */
    function refreshReportsTable() {
        // Look for the table body to update
        const tableBody = document.querySelector('.reports-table tbody');
        const tableContainer = document.querySelector('.table-responsive');
        
        if (!tableBody || !tableContainer) {
            console.error('Could not find reports table elements');
            return; // Table elements not found
        }
        
        // Show loading indicator
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Refreshing report list...</td></tr>';
        
        // Fetch the latest reports
        fetch('../../api/get_recent_reports.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.reports)) {
                    // Clear existing table content
                    tableBody.innerHTML = '';
                    
                    if (data.reports.length > 0) {
                        // Add each report to the table
                        data.reports.forEach(report => {
                            const row = document.createElement('tr');
                            
                            // Format the date
                            const generatedDate = new Date(report.generated_at).toLocaleDateString('en-US', { 
                                month: 'short', day: 'numeric', year: 'numeric' 
                            });
                            
                            // Create row content
                            row.innerHTML = `
                                <td>${report.report_name}</td>
                                <td>Q${report.quarter} ${report.year}</td>
                                <td>${generatedDate}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="../../download.php?type=report&file=${report.pptx_path}" class="btn btn-sm btn-outline-secondary action-btn action-btn-download" title="Download Report">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary action-btn action-btn-delete" title="Delete Report" 
                                                data-bs-toggle="modal" data-bs-target="#deleteReportModal" 
                                                data-report-id="${report.report_id}" 
                                                data-report-name="${report.report_name}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            
                            // Add the row to the table
                            tableBody.appendChild(row);
                        });
                    } else {
                        // No reports found
                        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No reports generated yet.</td></tr>';
                    }
                } else {
                    // Error handling
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Could not load reports.</td></tr>';
                    console.error('API error:', data);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading reports.</td></tr>';
            });
    }
});