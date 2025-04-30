/**
 * Report Generator
 * 
 * Client-side JavaScript for generating PPTX reports using PptxGenJS
 * This script handles:
 * 1. Fetching data from the API
 * 2. Generating the PPTX with a master slide
 * 3. Uploading the generated file to the server
 */

document.addEventListener('DOMContentLoaded', function() {
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
    
    sectorSelect.addEventListener('change', updateReportName);
    periodSelect.addEventListener('change', updateReportName);
    
    // Setup delete report modal
    if (deleteReportModal) {
        deleteReportModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            reportIdToDelete = button.getAttribute('data-report-id');
            const reportName = button.getAttribute('data-report-name');
            
            reportNameToDelete.textContent = reportName;
        });
        
        // Handle delete confirmation
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (!reportIdToDelete) return;
                
                // Disable delete button and show loading state
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
                
                // Send delete request
                const formData = new FormData();
                formData.append('report_id', reportIdToDelete);
                
                fetch('../../api/delete_report.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide modal
                    const modalInstance = bootstrap.Modal.getInstance(deleteReportModal);
                    modalInstance.hide();
                    
                    if (data.success) {
                        // Show success toast or message
                        showToast('Success', 'Report deleted successfully', 'success');
                        
                        // Remove the row from the table
                        const reportRow = document.querySelector(`button[data-report-id="${reportIdToDelete}"]`).closest('tr');
                        if (reportRow) {
                            reportRow.remove();
                            
                            // If no more reports, show empty state
                            const tbody = document.querySelector('.reports-table tbody');
                            if (tbody && tbody.children.length === 0) {
                                const tableContainer = document.querySelector('.table-responsive');
                                tableContainer.innerHTML = '<div class="reports-empty-state"><p class="text-muted">No reports generated yet.</p></div>';
                            }
                        }
                    } else {
                        // Show error message
                        showToast('Error', data.error || 'Failed to delete report', 'danger');
                    }
                    
                    // Reset button state
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Report';
                    reportIdToDelete = null;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'An unexpected error occurred', 'danger');
                    
                    // Reset button state
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Report';
                    reportIdToDelete = null;
                    
                    // Hide modal
                    const modalInstance = bootstrap.Modal.getInstance(deleteReportModal);
                    modalInstance.hide();
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
    generateBtn.addEventListener('click', generateReport);
    
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
        generateBtn.disabled = true;
        
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
                generateBtn.disabled = false;

                // Update recent reports table
                updateRecentReportsTable(periodId);
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide status and show error
                statusCard.classList.add('d-none');
                errorMessage.classList.remove('d-none');
                errorText.textContent = error.message || 'Error generating report. Please try again.';
                
                // Re-enable generate button
                generateBtn.disabled = false;
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
                
                // Chart areas with subtle borders
                { 'timberChartBg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 0.8, w: 5.2, h: 2.7,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 1, dashType: 'dash' }
                    } 
                }},
                { 'timberChartArea': { 
                    type: 'chart', 
                    options: { 
                        x: 7.5, y: 1.0, w: 4.8, h: 2.3
                    } 
                }},
                
                { 'areaRestoredChartBg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 5.0, w: 5.2, h: 2.0,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.secondary, width: 1, dashType: 'dash' }
                    } 
                }},
                { 'areaRestoredChartArea': { 
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
                { 'kpiTPABg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 4.1, w: 2.1, h: 0.8,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 0.75 }
                    } 
                }},
                { 'kpiTPAArea': { 
                    type: 'body', 
                    options: { 
                        x: 7.5, y: 4.2, w: 1.9, h: 0.6,
                        fontFace: defaultFont
                    } 
                }},
                { 'kpiCertBg': { 
                    type: 'rect', 
                    options: { 
                        x: 9.5, y: 4.1, w: 3.0, h: 0.8,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 0.75 }
                    } 
                }},
                { 'kpiCertArea': { 
                    type: 'body', 
                    options: { 
                        x: 9.7, y: 4.2, w: 2.6, h: 0.6,
                        fontFace: defaultFont
                    } 
                }},
                { 'kpiRecogBg': { 
                    type: 'rect', 
                    options: { 
                        x: 7.3, y: 7.1, w: 5.2, h: 0.6,
                        fill: { color: 'FFFFFF' },
                        line: { color: themeColors.primary, width: 0.75 }
                    } 
                }},
                { 'kpiRecogArea': { 
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
        
        // Add titles
        slide.addText(data.reportTitle, { 
            placeholder: 'title',
            fontFace: defaultFont,
            color: themeColors.primary
        });
        
        slide.addText(data.sectorLeads, { 
            placeholder: 'subtitle',
            fontFace: defaultFont,
            color: themeColors.lightText
        });
        
        slide.addText(data.quarter, { 
            placeholder: 'quarterTitle',
            fontFace: defaultFont,
            color: themeColors.accent1
        });
        
        // Add projects title
        slide.addText('Projects / Programmes', { 
            placeholder: 'projectsTitle',
            fontFace: defaultFont,
            color: themeColors.primary
        });
        
        // Add projects with alternating row background for readability
        let yPos = 1.2; // Starting Y position (updated to match new projectsArea y-coord)
        
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
                x: 4.5, y: yPos + 0.05, w: 2.4, h: 0.3, 
                fontSize: 9, 
                fontFace: defaultFont,
                color: themeColors.lightText,
                valign: 'middle'
            });
            
            // Move Y position down for next project
            yPos += 0.45; // Slightly more space between rows
        });
        
        // Add KPI section title
        slide.addText('Key Performance Indicators', { 
            placeholder: 'kpiTitleArea',
            fontFace: defaultFont,
            color: themeColors.primary
        });
        
        // Add Timber Export chart with enhanced styling
        slide.addChart(
            pptx.charts.LINE, 
            [
                { name: '2023', labels: data.timberExportChart.labels, values: data.timberExportChart.data2023 },
                { name: '2024', labels: data.timberExportChart.labels, values: data.timberExportChart.data2024 }
            ],
            { 
                placeholder: 'timberChartArea',
                title: 'Timber Export Value (RM)',
                titleFontSize: 11,
                titleColor: themeColors.primary,
                titleBold: true,
                titleFontFace: defaultFont,
                showTitle: true,
                showLegend: true,
                legendPos: 'b',
                legendFontFace: defaultFont,
                legendFontSize: 8,
                
                // Enhanced styling for chart elements
                dataLabelFontSize: 8,
                dataLabelFontFace: defaultFont,
                chartColors: [themeColors.primary, themeColors.accent1],
                
                // Line styling
                lineWidth: 2.5,
                lineDataSymbol: 'circle',
                lineDataSymbolSize: 8,
                lineDataSymbolLineColor: 'FFFFFF',
                
                // Axis styling
                valAxisMaxVal: Math.max(...data.timberExportChart.data2023, ...data.timberExportChart.data2024) * 1.1,
                valAxisLabelFontSize: 8,
                valAxisLabelFontFace: defaultFont,
                catAxisLabelFontSize: 8,
                catAxisLabelFontFace: defaultFont,
                
                // Border and background
                border: { pt: 1, color: themeColors.lightText },
                chartArea: { fill: { color: 'FFFFFF' } },
                plotArea: { fill: { color: 'FFFFFF' } }
            }
        );
        
        // Add timber chart totals with improved styling
        slide.addText(`TOTAL 2023 = ${data.timberExportChart.total2023}`, { 
            x: 7.6, y: 3.5, w: 2.4, h: 0.25, 
            fontSize: 10, bold: true, 
            fontFace: defaultFont,
            color: themeColors.primary,
            valign: 'middle' 
        });
        
        slide.addText(`TOTAL 2024 (Jan-Sept) = ${data.timberExportChart.total2024}`, { 
            x: 10.0, y: 3.5, w: 2.5, h: 0.25, 
            fontSize: 10, bold: true,
            fontFace: defaultFont,
            color: themeColors.accent1,
            valign: 'middle' 
        });
        
        // Add TPA/Biodiversity KPI with improved styling
        slide.addText(data.kpiTPA.value.toString(), { 
            x: 7.6, y: 4.2, w: 0.6, h: 0.5, 
            fontSize: 28, bold: true, 
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center',
            valign: 'middle'
        });
        
        slide.addText('TPA Protection & Biodiversity Conserved', { 
            x: 7.6, y: 4.6, w: 2.0, h: 0.2, 
            fontSize: 9, bold: true,
            fontFace: defaultFont,
            color: themeColors.text
        });
        
        slide.addText(data.kpiTPA.description, { 
            x: 8.3, y: 4.25, w: 1.0, h: 0.4, 
            fontSize: 8, 
            fontFace: defaultFont,
            color: themeColors.lightText,
            valign: 'middle'
        });
        
        // Add Certification KPIs with improved styling
        // FMU Certification
        slide.addText(data.kpiCertification.fmu_percent + '%', { 
            x: 9.7, y: 4.2, w: 0.8, h: 0.4, 
            fontSize: 20, bold: true, 
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center',
            valign: 'middle'
        });
        
        slide.addText('Forest Management Unit (FMU)', { 
            x: 9.7, y: 4.6, w: 2.0, h: 0.2, 
            fontSize: 9, bold: true,
            fontFace: defaultFont,
            color: themeColors.text
        });
        
        slide.addText(data.kpiCertification.fmu_value, { 
            x: 10.5, y: 4.2, w: 1.0, h: 0.4, 
            fontSize: 9, 
            fontFace: defaultFont,
            color: themeColors.lightText,
            valign: 'middle'
        });
        
        // FPMU Certification
        slide.addText(data.kpiCertification.fpmu_percent + '%', { 
            x: 11.7, y: 4.2, w: 0.8, h: 0.4, 
            fontSize: 20, bold: true, 
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center',
            valign: 'middle'
        });
        
        slide.addText('Forest Plantation Management Unit', { 
            x: 11.7, y: 4.6, w: 2.0, h: 0.2, 
            fontSize: 9, bold: true,
            fontFace: defaultFont,
            color: themeColors.text
        });
        
        slide.addText(data.kpiCertification.fpmu_value, { 
            x: 12.5, y: 4.2, w: 1.0, h: 0.4, 
            fontSize: 9, 
            fontFace: defaultFont,
            color: themeColors.lightText,
            valign: 'middle'
        });
        
        // Add Degraded Area Restored chart with enhanced styling
        slide.addChart(
            pptx.charts.LINE,
            [
                { name: '2022', labels: data.areaRestoredChart.labels, values: data.areaRestoredChart.data2022 },
                { name: '2023', labels: data.areaRestoredChart.labels, values: data.areaRestoredChart.data2023 },
                { name: '2024', labels: data.areaRestoredChart.labels, values: data.areaRestoredChart.data2024 }
            ],
            {
                placeholder: 'areaRestoredChartArea',
                title: 'Total Degraded Area Restored (Ha)',
                titleFontSize: 11,
                titleColor: themeColors.secondary,
                titleBold: true,
                titleFontFace: defaultFont,
                showTitle: true,
                showLegend: true,
                legendPos: 'b',
                legendFontFace: defaultFont,
                legendFontSize: 8,
                
                // Enhanced styling for chart elements
                dataLabelFontSize: 8,
                dataLabelFontFace: defaultFont,
                chartColors: [themeColors.primary, themeColors.accent2, themeColors.secondary],
                
                // Line styling
                lineWidth: 2.5,
                lineDataSymbol: 'circle',
                lineDataSymbolSize: 8,
                lineDataSymbolLineColor: 'FFFFFF',
                
                // Axis styling
                valAxisMaxVal: Math.max(
                    ...data.areaRestoredChart.data2022, 
                    ...data.areaRestoredChart.data2023, 
                    ...data.areaRestoredChart.data2024
                ) * 1.1,
                valAxisLabelFontSize: 8,
                valAxisLabelFontFace: defaultFont,
                catAxisLabelFontSize: 8,
                catAxisLabelFontFace: defaultFont,
                
                // Border and background
                border: { pt: 1, color: themeColors.lightText },
                chartArea: { fill: { color: 'FFFFFF' } },
                plotArea: { fill: { color: 'FFFFFF' } }
            }
        );
        
        // Add area restored total with improved styling
        slide.addText(`TOTAL 2024 = ${data.areaRestoredChart.total2024}`, { 
            x: 10.0, y: 7.0, w: 2.5, h: 0.25, 
            fontSize: 10, bold: true, 
            fontFace: defaultFont,
            color: themeColors.secondary,
            valign: 'middle'
        });
        
        // Add World Recognition KPIs with improved styling
        // SDGP Recognition
        slide.addText(data.kpiRecognition.sdgp_percent + '%', { 
            x: 7.7, y: 7.2, w: 0.8, h: 0.4, 
            fontSize: 20, bold: true, 
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center',
            valign: 'middle'
        });
        
        slide.addText('SDGP Endorsed Initiative', { 
            x: 8.5, y: 7.25, w: 2.0, h: 0.3, 
            fontSize: 9, bold: true,
            fontFace: defaultFont,
            color: themeColors.text,
            valign: 'middle'
        });
        
        // Niah National Park
        slide.addText(data.kpiRecognition.niah_percent + '%', { 
            x: 10.7, y: 7.2, w: 0.8, h: 0.4, 
            fontSize: 20, bold: true, 
            fontFace: defaultFont,
            color: themeColors.primary,
            align: 'center',
            valign: 'middle'
        });
        
        slide.addText('Niah NP World Heritage', { 
            x: 11.5, y: 7.25, w: 2.0, h: 0.3, 
            fontSize: 9, bold: true,
            fontFace: defaultFont,
            color: themeColors.text,
            valign: 'middle'
        });
        
        // Add Status Legend with improved styling
        // Legend title is added in the master slide
        
        // Status indicators with improved styling
        let legendY = 6.7; // Updated position to match new master slide
        
        // Green status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 0.7, y: legendY, w: 0.3, h: 0.3, 
            fill: { color: themeColors.greenStatus },
            line: { color: themeColors.text, width: 1 },
            shadow: { type: 'outer', angle: 45, blur: 3, color: 'CFCFCF', offset: 1 }
        });
        
        slide.addText('Target Achieved / On Track', { 
            x: 1.1, y: legendY, w: 2.5, h: 0.3, 
            fontSize: 9,
            fontFace: defaultFont, 
            color: themeColors.text,
            valign: 'middle'
        });
        
        // Yellow status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 3.7, y: legendY, w: 0.3, h: 0.3, 
            fill: { color: themeColors.yellowStatus },
            line: { color: themeColors.text, width: 1 },
            shadow: { type: 'outer', angle: 45, blur: 3, color: 'CFCFCF', offset: 1 }
        });
        
        slide.addText('Minor Issues / Delayed', { 
            x: 4.1, y: legendY, w: 2.5, h: 0.3, 
            fontSize: 9,
            fontFace: defaultFont,
            color: themeColors.text,
            valign: 'middle'
        });
        
        // Red status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 0.7, y: legendY + 0.4, w: 0.3, h: 0.3, 
            fill: { color: themeColors.redStatus },
            line: { color: themeColors.text, width: 1 },
            shadow: { type: 'outer', angle: 45, blur: 3, color: 'CFCFCF', offset: 1 }
        });
        
        slide.addText('Major Issues / At Risk', { 
            x: 1.1, y: legendY + 0.4, w: 2.5, h: 0.3, 
            fontSize: 9,
            fontFace: defaultFont,
            color: themeColors.text,
            valign: 'middle'
        });
        
        // Grey status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 3.7, y: legendY + 0.4, w: 0.3, h: 0.3, 
            fill: { color: themeColors.greyStatus },
            line: { color: themeColors.text, width: 1 },
            shadow: { type: 'outer', angle: 45, blur: 3, color: 'CFCFCF', offset: 1 }
        });
        
        slide.addText('Not Started / No Data', { 
            x: 4.1, y: legendY + 0.4, w: 2.5, h: 0.3, 
            fontSize: 9,
            fontFace: defaultFont,
            color: themeColors.text,
            valign: 'middle'
        });
        
        // Add Draft Date
        slide.addText(data.draftDate, { 
            placeholder: 'draftDateArea',
            fontFace: defaultFont,
            color: themeColors.lightText,
            italic: true
        });
        
        // Optionally add a logo to the footer
        // slide.addImage({ path: '../../assets/images/logo.png', placeholder: 'footerLogo' });
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
     * Update the recent reports table with the latest reports
     * @param {number} periodId - The current period ID
     */
    function updateRecentReportsTable(periodId) {
        const recentReportsTable = document.getElementById('recentReportsTable');
        if (!recentReportsTable) return;
        
        const recentReportsSection = document.getElementById('recentReportsSection');
        if (!recentReportsSection) return;
        
        // If there's a loading indicator, show it
        const tableBody = recentReportsTable.querySelector('tbody');
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading recent reports...</td></tr>';
        }
        
        // Fetch the updated reports list for the current period
        fetch(`../../api/get_recent_reports.php?period_id=${periodId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.reports && tableBody) {
                    // If we have reports, update the table
                    if (data.reports.length > 0) {
                        let tableHtml = '';
                        
                        data.reports.forEach(report => {
                            const reportTypeClass = report.report_type === 'program' ? 'primary' : 'info';
                            const generatedDate = new Date(report.generated_at).toLocaleDateString('en-US', { 
                                month: 'short', day: 'numeric', year: 'numeric' 
                            });
                            
                            tableHtml += `
                            <tr>
                                <td>${report.report_name}</td>
                                <td>${report.description || '<em>No description</em>'}</td>
                                <td>
                                    <span class="badge bg-${reportTypeClass}">
                                        ${report.report_type.charAt(0).toUpperCase() + report.report_type.slice(1)}
                                    </span>
                                </td>
                                <td>${generatedDate}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="../../reports/${report.file_path}" class="btn btn-outline-primary" target="_blank" title="View Report">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="../../reports/${report.file_path}" class="btn btn-outline-success" download title="Download Report">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            `;
                        });
                        
                        tableBody.innerHTML = tableHtml;
                        recentReportsSection.classList.remove('d-none');
                    } else {
                        // No reports found
                        tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No reports found for this period.</td></tr>';
                    }
                } else {
                    // Error or no data
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Could not load reports. Please refresh the page.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error fetching reports:', error);
                if (tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading reports.</td></tr>';
                }
            });
    }
});