/**
 * Report API Module
 * 
 * Handles all API calls and server communication for the report generator
 */

const ReportAPI = (function() {
    /**
     * Fetches report data from the API
     * @param {number} periodId - The reporting period ID
     * @param {number} sectorId - The sector ID
     * @param {number[]} [selectedKpiIds] - Optional array of selected KPI IDs
     * @returns {Promise<Object>} - A promise that resolves to the report data
     */
    function fetchReportData(periodId, sectorId, selectedKpiIds = []) {
        return new Promise((resolve, reject) => {
            let apiUrl = `../../api/report_data.php?period_id=${periodId}&sector_id=${sectorId}`;
            if (selectedKpiIds && selectedKpiIds.length > 0) {
                apiUrl += `&selected_kpi_ids=${selectedKpiIds.join(',')}`;
                console.log('Selected KPI IDs:', selectedKpiIds);
            } else {
                console.log('No KPI IDs selected, will use default KPIs');
            }
            console.log('Fetching from URL:', apiUrl);
            
            fetch(apiUrl)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
                    }
                    return response.text(); // Get the raw text first to see what's actually being returned
                })
                .then(rawText => {
                    console.log('Raw response length:', rawText.length);
                    // Check if it starts with <?php or has PHP error indicators
                    if (rawText.trim().startsWith('<?php') || 
                        rawText.includes('Warning:') || 
                        rawText.includes('Notice:') || 
                        rawText.includes('Fatal error:') ||
                        rawText.includes('Parse error:')) {
                        console.error('PHP code or error in response:', rawText.substring(0, 1000));
                        throw new Error('PHP error detected in response. Check console for details.');
                    }
                    
                    if (rawText.length > 0) {
                        console.log('First 500 chars of response:', rawText.substring(0, 500));
                        // Try to parse the JSON
                        try {
                            const data = JSON.parse(rawText);
                            console.log('JSON parsed successfully');
                            return data;
                        } catch (error) {
                            console.error('JSON parsing error:', error);
                            console.error('Raw response causing JSON parse error:', rawText.substring(0, 1000));
                            throw new Error('API returned invalid JSON. See console for details.');
                        }
                    } else {
                        console.error('Empty response received from API');
                        throw new Error('API returned empty response');
                    }
                })
                .then(data => {
                    console.log('API request successful');
                    resolve(data);
                })
                .catch(error => {
                    console.error('API request failed:', error);
                    reject(error);
                });
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
    function uploadPresentation(blob, periodId, sectorId, reportName, description, isPublic) {
        return new Promise((resolve, reject) => {
            // Get period and sector text for filename
            const periodSelect = document.getElementById('periodSelect');
            const sectorSelect = document.getElementById('sectorSelect');
            
            if (!periodSelect || !sectorSelect) {
                reject(new Error('Could not find period or sector selects'));
                return;
            }
            
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
     * Refresh the reports table by fetching the latest data
     * @returns {Promise<boolean>} - A promise that resolves to true if successful
     */
    function refreshReportsTable() {
        return new Promise((resolve, reject) => {
            // Look for the table body to update
            const tableBody = document.querySelector('.reports-table tbody');
            const tableContainer = document.querySelector('.table-responsive');
            
            if (!tableBody || !tableContainer) {
                console.error('Could not find reports table elements');
                reject(new Error('Could not find reports table elements'));
                return;
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
                            resolve(true);
                        } else {
                            // No reports found
                            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No reports generated yet.</td></tr>';
                            resolve(true);
                        }
                    } else {
                        // Error handling
                        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Could not load reports.</td></tr>';
                        console.error('API error:', data);
                        reject(new Error('API error: ' + JSON.stringify(data)));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading reports.</td></tr>';
                    reject(error);
                });
        });
    }

    /**
     * Delete a report
     * @param {number} reportId - The ID of the report to delete
     * @param {Element} button - The button that was clicked to trigger the delete
     * @returns {Promise<boolean>} - A promise that resolves to true if successful
     */
    function deleteReport(reportId, button) {
        return new Promise((resolve, reject) => {
            if (!reportId) {
                reject(new Error('No report ID provided'));
                return;
            }
            
            // Find row to delete
            const rowToDelete = button ? button.closest('tr') : null;
            
            // Create form data for the request
            const formData = new FormData();
            formData.append('report_id', reportId);
            
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
                
                if (data.success) {
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
                        // If we couldn't find the row, reload the table
                        refreshReportsTable();
                    }
                    resolve(true);
                } else {
                    reject(new Error(data.error || 'Failed to delete report'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                reject(error);
            });
        });
    }

    // Expose public methods
    return {
        fetchReportData,
        uploadPresentation,
        refreshReportsTable,
        deleteReport
    };
})();