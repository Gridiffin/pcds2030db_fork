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
                defineReportMaster(pptx);
                
                // Create slide using the master
                let slide = pptx.addSlide({ masterName: 'REPORT_MASTER_SLIDE' });
                
                // Populate slide with data
                populateSlide(slide, data, pptx);
                
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
        pptx.defineSlideMaster({
            title: 'REPORT_MASTER_SLIDE',
            background: { color: 'FFFFFF' }, // White background
            objects: [
                // Title placeholders
                { 'title': { options: { x: 0.5, y: 0.1, w: 7.0, h: 0.5, fontSize: 24, bold: true } } }, // Main title (sector name)
                { 'subtitle': { options: { x: 0.5, y: 0.4, w: 7.0, h: 0.2, fontSize: 10 } } }, // Subtitle (sector leads)
                { 'quarterTitle': { options: { x: 8.0, y: 0.1, w: 4.5, h: 0.5, fontSize: 28, bold: true, align: 'right' } } }, // Quarter title
                
                // Project area
                { 'projectsTitle': { options: { x: 0.5, y: 0.8, w: 12.0, h: 0.3, fontSize: 10, bold: true } } }, // Projects section title
                { 'projectsArea': { type: 'body', options: { x: 0.5, y: 1.0, w: 12.0, h: 2.5 } } }, // Area for project list
                
                // Chart areas
                { 'timberChartArea': { type: 'chart', options: { x: 7.5, y: 1.0, w: 5.0, h: 2.5 } } }, // Area for timber export chart
                { 'areaRestoredChartArea': { type: 'chart', options: { x: 7.5, y: 5.0, w: 5.0, h: 2.0 } } }, // Area for area restored chart
                
                // KPI sections
                { 'kpiTitleArea': { options: { x: 7.5, y: 3.7, w: 5.0, h: 0.3, fontSize: 10, bold: true } } }, // KPI section title
                { 'kpiTPAArea': { type: 'body', options: { x: 7.5, y: 4.0, w: 2.0, h: 0.8 } } }, // Area for TPA KPI
                { 'kpiCertArea': { type: 'body', options: { x: 9.5, y: 4.0, w: 3.0, h: 0.8 } } }, // Area for Certification KPI
                { 'kpiRecogArea': { type: 'body', options: { x: 7.5, y: 7.0, w: 5.0, h: 0.5 } } }, // Area for Recognition KPI
                
                // Legend and Draft date
                { 'legendArea': { type: 'body', options: { x: 0.5, y: 6.5, w: 6.0, h: 0.5 } } }, // Area for legend
                { 'draftDateArea': { type: 'body', options: { x: 0.5, y: 7.2, w: 3.0, h: 0.3, fontSize: 10 } } }, // Area for draft date
            ],
        });
    }
    
    /**
     * Populate the slide with data from the API
     * @param {Object} slide - The slide to populate
     * @param {Object} data - The data from the API
     * @param {Object} pptx - The PptxGenJS instance
     */
    function populateSlide(slide, data, pptx) {
        // Add titles
        slide.addText(data.reportTitle, { placeholder: 'title' });
        slide.addText(data.sectorLeads, { placeholder: 'subtitle' });
        slide.addText(data.quarter, { placeholder: 'quarterTitle' });
        
        // Add projects title
        slide.addText('Projects / Programmes', { placeholder: 'projectsTitle' });
        
        // Add projects
        let yPos = 1.0; // Starting Y position (should match projectsArea y-coord in master)
        data.projects.forEach((proj, index) => {
            // Status color indicator
            let statusColor = '';
            switch (proj.rating) {
                case 'green': statusColor = '00B050'; break; // Green
                case 'yellow': statusColor = 'FFFF00'; break; // Yellow
                case 'red': statusColor = 'FF0000'; break; // Red
                default: statusColor = 'D9D9D9'; // Grey
            }
            
            // Add a colored rectangle for status
            slide.addShape(pptx.shapes.RECTANGLE, { 
                x: 4.0, y: yPos, w: 0.3, h: 0.3, 
                fill: { color: statusColor },
                line: { color: '000000', width: 1 }
            });
            
            // Add text for project name, target, and status
            slide.addText(proj.name, { x: 0.5, y: yPos, w: 3.2, h: 0.3, fontSize: 9 });
            slide.addText(proj.target, { x: 4.5, y: yPos, w: 3.0, h: 0.5, fontSize: 9 });
            slide.addText(proj.status, { x: 8.0, y: yPos, w: 4.5, h: 0.5, fontSize: 9 });
            
            // Move Y position down for next project
            yPos += 0.5;
        });
        
        // Add KPI section title
        slide.addText('Key Performance Indicators', { placeholder: 'kpiTitleArea' });
        
        // Add Timber Export chart
        slide.addChart(
            pptx.charts.LINE, 
            [
                { name: '2023', labels: data.timberExportChart.labels, values: data.timberExportChart.data2023 },
                { name: '2024', labels: data.timberExportChart.labels, values: data.timberExportChart.data2024 }
            ],
            { 
                placeholder: 'timberChartArea',
                title: 'Timber Export Value (RM)',
                titleFontSize: 10,
                titleColor: '000000',
                showTitle: true,
                showLegend: true,
                legendPos: 'b',
                dataLabelFontSize: 8,
                chartColors: ['4472C4', 'ED7D31'],
                lineWidth: 2,
                lineDataSymbol: 'circle',
                lineDataSymbolSize: 6,
                valAxisMaxVal: Math.max(...data.timberExportChart.data2023, ...data.timberExportChart.data2024) * 1.1,
                valAxisLabelFontSize: 8,
                catAxisLabelFontSize: 8
            }
        );
        
        // Add timber chart totals
        slide.addText(`TOTAL 2023 = ${data.timberExportChart.total2023}`, { 
            x: 7.6, y: 3.6, w: 2.4, h: 0.2, fontSize: 9, color: '4472C4' 
        });
        slide.addText(`TOTAL 2024 (Jan-Sept) = ${data.timberExportChart.total2024}`, { 
            x: 10.0, y: 3.6, w: 2.5, h: 0.2, fontSize: 9, color: 'ED7D31' 
        });
        
        // Add TPA/Biodiversity KPI
        slide.addText(data.kpiTPA.value.toString(), { 
            x: 7.6, y: 4.1, w: 0.5, h: 0.5, fontSize: 24, bold: true, color: '4472C4'
        });
        slide.addText('TPA Protection & Biodiversity Conserved', { 
            x: 7.6, y: 4.5, w: 2.0, h: 0.3, fontSize: 9, bold: true 
        });
        slide.addText(data.kpiTPA.description, { 
            x: 8.2, y: 4.1, w: 1.3, h: 0.4, fontSize: 8 
        });
        
        // Add Certification KPIs
        // FMU Certification
        slide.addText(data.kpiCertification.fmu_percent + '%', { 
            x: 9.6, y: 4.1, w: 0.8, h: 0.4, fontSize: 18, bold: true, color: '4472C4'
        });
        slide.addText('Forest Management Unit (FMU)', { 
            x: 9.6, y: 4.5, w: 2.0, h: 0.2, fontSize: 9, bold: true 
        });
        slide.addText(data.kpiCertification.fmu_value, { 
            x: 10.4, y: 4.1, w: 1.0, h: 0.4, fontSize: 9, color: '000000'
        });
        
        // FPMU Certification  
        slide.addText(data.kpiCertification.fpmu_percent + '%', { 
            x: 11.6, y: 4.1, w: 0.8, h: 0.4, fontSize: 18, bold: true, color: '4472C4'
        });
        slide.addText('Forest Plantation Management Unit', { 
            x: 11.6, y: 4.5, w: 2.0, h: 0.2, fontSize: 9, bold: true 
        });
        slide.addText(data.kpiCertification.fpmu_value, { 
            x: 12.4, y: 4.1, w: 1.0, h: 0.4, fontSize: 9, color: '000000'
        });
        
        // Add Degraded Area Restored chart
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
                titleFontSize: 10,
                titleColor: '000000',
                showTitle: true,
                showLegend: true,
                legendPos: 'b',
                dataLabelFontSize: 8,
                chartColors: ['4472C4', '5B9BD5', '70AD47'],
                lineWidth: 2,
                lineDataSymbol: 'circle',
                lineDataSymbolSize: 6,
                valAxisMaxVal: Math.max(
                    ...data.areaRestoredChart.data2022, 
                    ...data.areaRestoredChart.data2023, 
                    ...data.areaRestoredChart.data2024
                ) * 1.1,
                valAxisLabelFontSize: 8,
                catAxisLabelFontSize: 8
            }
        );
        
        // Add area restored total
        slide.addText(`TOTAL 2024 = ${data.areaRestoredChart.total2024}`, { 
            x: 10.0, y: 7.0, w: 2.5, h: 0.2, fontSize: 9, bold: true, color: '70AD47' 
        });
        
        // Add World Recognition KPIs
        // SDGP Recognition
        slide.addText(data.kpiRecognition.sdgp_percent + '%', { 
            x: 7.6, y: 7.1, w: 0.8, h: 0.4, fontSize: 18, bold: true, color: '4472C4'
        });
        slide.addText('SDGP Endorsed Initiative', { 
            x: 7.6, y: 7.4, w: 2.0, h: 0.2, fontSize: 9, bold: true 
        });
        
        // Niah National Park
        slide.addText(data.kpiRecognition.niah_percent + '%', { 
            x: 9.6, y: 7.1, w: 0.8, h: 0.4, fontSize: 18, bold: true, color: '4472C4'
        });
        slide.addText('Niah NP World Heritage', { 
            x: 9.6, y: 7.4, w: 2.0, h: 0.2, fontSize: 9, bold: true 
        });
        
        // Add Legend
        let legendY = 6.5;
        
        // Green status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 0.5, y: legendY, w: 0.3, h: 0.3, 
            fill: { color: '00B050' },
            line: { color: '000000', width: 1 }
        });
        slide.addText('Target Achieved / On Track', { 
            x: 0.9, y: legendY, w: 2.5, h: 0.3, fontSize: 9 
        });
        
        // Yellow status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 3.5, y: legendY, w: 0.3, h: 0.3, 
            fill: { color: 'FFFF00' },
            line: { color: '000000', width: 1 }
        });
        slide.addText('Minor Issues / Delayed', { 
            x: 3.9, y: legendY, w: 2.5, h: 0.3, fontSize: 9 
        });
        
        // Red status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 0.5, y: legendY + 0.4, w: 0.3, h: 0.3, 
            fill: { color: 'FF0000' },
            line: { color: '000000', width: 1 }
        });
        slide.addText('Major Issues / At Risk', { 
            x: 0.9, y: legendY + 0.4, w: 2.5, h: 0.3, fontSize: 9 
        });
        
        // Grey status
        slide.addShape(pptx.shapes.RECTANGLE, { 
            x: 3.5, y: legendY + 0.4, w: 0.3, h: 0.3, 
            fill: { color: 'D9D9D9' },
            line: { color: '000000', width: 1 }
        });
        slide.addText('Not Started / No Data', { 
            x: 3.9, y: legendY + 0.4, w: 2.5, h: 0.3, fontSize: 9 
        });
        
        // Add Draft Date
        slide.addText(data.draftDate, { placeholder: 'draftDateArea' });
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