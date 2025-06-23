/**
 * Report API Module
 * 
 * Handles all API calls and server communication for the report generator
 */

// Prevent multiple instantiations
if (typeof window.ReportAPI !== 'undefined') {
    console.log('ReportAPI module already loaded, skipping redeclaration');
} else {
    window.ReportAPI = (function() {/**
     * Fetches report data from the API
     * @param {number} periodId - The reporting period ID
     * @param {number} sectorId - The sector ID
     * @param {number[]} [selectedKpiIds] - Optional array of selected KPI IDs (this will be an empty array)
     * @param {number[]} [selectedProgramIds] - Optional array of selected program IDs
     * @param {Object} [programOrders] - Optional object mapping program IDs to their display order
     * @returns {Promise<Object>} - A promise that resolves to the report data
     */
    function fetchReportData(periodId, sectorId, selectedKpiIds = [], selectedProgramIds = [], programOrders = {}) {
        return new Promise((resolve, reject) => {
            // Build parameters object
            const params = {
                'period_id': periodId,
                'sector_id': sectorId
            };
            
            // Add selected program IDs to the API call if provided
            if (selectedProgramIds && selectedProgramIds.length > 0) {
                params.selected_program_ids = selectedProgramIds.join(',');
                console.log('Selected Program IDs:', selectedProgramIds);
                  // Add program orders if provided
                if (programOrders && Object.keys(programOrders).length > 0) {
                    params.program_orders = JSON.stringify(programOrders);
                    console.log('Program Orders:', programOrders);
                }
            } else {
                console.log('No Program IDs selected, will include all programs for the sector');
            }
              // Generate API URL using the helper function
            const apiEndpointUrl = apiUrl('report_data.php', params);
            console.log('Fetching from URL:', apiEndpointUrl);
            
            fetch(apiEndpointUrl)
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
            fetch(apiUrl("save_report.php"), {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Upload response status:', response.status);
                console.log('Upload response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                // Get raw text first to debug potential JSON issues
                return response.text();
            })
            .then(rawText => {
                console.log('Upload raw response length:', rawText.length);
                console.log('Upload raw response first 500 chars:', rawText.substring(0, 500));
                
                // Check for PHP errors or non-JSON content
                if (rawText.trim().startsWith('<?php') || 
                    rawText.includes('Warning:') || 
                    rawText.includes('Notice:') || 
                    rawText.includes('Fatal error:') ||
                    rawText.includes('Parse error:')) {
                    console.error('PHP code or error in upload response:', rawText.substring(0, 1000));
                    throw new Error('PHP error detected in upload response. Check console for details.');
                }
                
                // Try to parse JSON
                try {
                    const result = JSON.parse(rawText);
                    console.log('Upload JSON parsed successfully:', result);
                    return result;
                } catch (error) {
                    console.error('Upload JSON parsing error:', error);
                    console.error('Raw upload response causing JSON parse error:', rawText.substring(0, 1000));
                    throw new Error('Upload API returned invalid JSON. See console for details.');
                }
            })
            .then(result => {
                if (result.success) {
                    resolve(result);
                } else {
                    reject(new Error(result.error || 'Unknown error saving the report'));
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                reject(error);
            });
        });
    }    /**
     * Refresh the reports table by fetching the latest data
     * @returns {Promise<boolean>} - A promise that resolves to true if successful
     */
    function refreshReportsTable() {
        return new Promise((resolve, reject) => {
            // Find the container where the table should be displayed
            const reportsTableContainer = document.getElementById('recentReportsContainer');
            
            if (!reportsTableContainer) {
                console.error('Could not find reports container element (ID: recentReportsContainer)');
                reject(new Error('Could not find reports container element'));
                return;
            }
            
            // Show loading indicator with better styling
            reportsTableContainer.innerHTML = `
                <div class="d-flex justify-content-center align-items-center py-4">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fas fa-sync fa-spin me-1"></i>
                            Refreshing report list...
                        </p>
                    </div>
                </div>
            `;
            
            // Get the correct path - we need to go up from the current report generator page location
            const ajaxPath = `${window.APP_URL || ''}/app/views/admin/ajax/recent_reports_table.php`;
            
            // Fetch the table HTML directly from the dedicated endpoint
            fetch(ajaxPath)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
                    }
                    return response.text(); // We're expecting HTML, not JSON
                })
                .then(html => {
                    // Simple fade-out effect before updating
                    reportsTableContainer.style.opacity = '0.5';
                      setTimeout(() => {
                        // Insert the HTML into the container
                        reportsTableContainer.innerHTML = html;
                        
                        // Fade back in
                        reportsTableContainer.style.opacity = '1';
                        
                        // Re-setup delete modal functionality for the new buttons
                        if (typeof ReportUI !== 'undefined' && ReportUI.setupDeleteModal) {
                            ReportUI.setupDeleteModal();
                        }
                          // Re-setup generate report toggle buttons (for empty state button)
                        if (typeof window.setupGenerateReportToggle === 'function') {
                            window.setupGenerateReportToggle();
                        }
                          // Re-initialize NEW badge system
                        if (typeof window.initNewReportBadges === 'function') {
                            window.initNewReportBadges();
                        }
                        
                        // Re-initialize search functionality
                        if (typeof window.initReportSearch === 'function') {
                            window.initReportSearch();
                        }
                        
                        // Show a brief success indicator
                        const refreshIndicator = document.getElementById('refreshIndicator');
                        if (refreshIndicator) {
                            refreshIndicator.style.display = 'block';
                            refreshIndicator.innerHTML = `
                                <small class="text-success">
                                    <i class="fas fa-check-circle"></i> Updated
                                </small>
                            `;
                            
                            // Hide the indicator after 2 seconds
                            setTimeout(() => {
                                refreshIndicator.style.display = 'none';
                            }, 2000);
                        }
                        
                        resolve(true);
                    }, 300); // Small delay for smoother transition
                })
                .catch(error => {
                    console.error('Error refreshing reports table:', error);
                    reportsTableContainer.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Unable to refresh reports list.</strong>
                            <p class="mb-2">Your report was generated successfully, but we couldn't update the list automatically.</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">
                                <i class="fas fa-refresh me-1"></i>Refresh Page
                            </button>
                        </div>
                    `;
                    reportsTableContainer.style.opacity = '1';
                    reject(error);
                });
        });
    }

    /**
     * Delete a report
     * @param {number} reportId - The ID of the report to delete
     * @param {Element} button - The button that was clicked to trigger the delete
     * @returns {Promise<boolean>} - A promise that resolves to true if successful
     */    function deleteReport(reportId, button) {
        return new Promise((resolve, reject) => {
            if (!reportId) {
                reject(new Error('No report ID provided'));
                return;
            }
            
            // Find the element to delete (either table row or report card)
            let elementToDelete = null;
            if (button) {
                // Try to find table row first (old layout)
                elementToDelete = button.closest('tr');
                // If not found, try to find report card (new layout)
                if (!elementToDelete) {
                    elementToDelete = button.closest('.report-card');
                }
            }
            
            // Create form data for the request
            const formData = new FormData();
            formData.append('report_id', reportId);
            
            fetch(apiUrl("delete_report.php"), {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error ${response.status}: ${response.statusText}. Response: ${text.substring(0, 200)}`);
                    });
                }
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.error("Delete report response was not JSON. Received:", text.substring(0, 500));
                        throw new Error('Server did not return JSON for delete operation. Check console for the raw response.');
                    });
                }
            })
            .then(data => { // 'data' is already parsed JSON here if successful
                if (data.success) {
                    // Remove the element from the DOM if it exists
                    if (elementToDelete) {
                        // Add fade-out animation before removal
                        elementToDelete.style.transition = 'opacity 0.3s';
                        elementToDelete.style.opacity = '0';
                        
                        setTimeout(() => {
                            elementToDelete.remove();
                            
                            // Check if we need to show empty state
                            const container = document.getElementById('recentReportsContainer');
                            if (container) {
                                const remainingCards = container.querySelectorAll('.report-card');
                                const remainingRows = container.querySelectorAll('tbody tr');
                                
                                if (remainingCards.length === 0 && remainingRows.length === 0) {
                                    // Show empty state for card layout
                                    container.innerHTML = `
                                        <div class="empty-state text-center py-5">
                                            <i class="fas fa-file-powerpoint fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No reports generated yet</h5>
                                            <p class="text-muted mb-3">Get started by generating your first report below.</p>
                                            <button type="button" class="btn btn-primary" id="generateReportToggleEmpty">
                                                <i class="fas fa-plus me-1"></i>Generate First Report
                                            </button>                                        </div>
                                    `;
                                    
                                    // Re-setup the toggle button for the empty state
                                    if (typeof window.setupGenerateReportToggle === 'function') {
                                        window.setupGenerateReportToggle();
                                    }
                                }
                            }
                        }, 300);
                    } else {
                        // If we couldn't find the element, reload the entire container
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
    }    // Expose public methods
    return {
        fetchReportData,
        uploadPresentation,
        refreshReportsTable,
        deleteReport
    };
})();

} // End ReportAPI guard
