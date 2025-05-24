// Make sure this script only runs once
if (!window.reportingPeriodsInitialized) {
    window.reportingPeriodsInitialized = true;
    
    // Wait for DOM to be fully loaded before accessing elements
    document.addEventListener('DOMContentLoaded', function() {
        // Create a safe selector function that returns null instead of throwing errors
        const safeSelect = function(selector) {
            try {
                return document.querySelector(selector);
            } catch (e) {
                console.error('Error selecting element:', selector, e);
                return null;
            }
        };

        // Create a safe event attacher function
        const safeAttachEvent = function(element, eventType, handler) {
            if (element && typeof element.addEventListener === 'function') {
                element.addEventListener(eventType, handler);
                return true;
            }
            return false;
        };
        
        // Initialize year toggle accordion functionality
        function initializeYearToggle() {
            document.querySelectorAll('.year-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    // const year = this.getAttribute('data-year'); // Not strictly needed if we target relatively
                    const content = this.nextElementSibling; // Assumes .year-content is the immediate next sibling
                    const icon = this.querySelector('.toggle-indicator i'); // Find the icon element
                    const isExpanded = this.classList.contains('expanded');
                    
                    if (isExpanded) {
                        this.classList.remove('expanded');
                        this.classList.add('collapsed');
                        this.setAttribute('aria-expanded', 'false');
                        if (content) {
                            content.classList.remove('show');
                            content.classList.add('hide');
                        }
                        if (icon) {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                        }
                    } else {
                        this.classList.add('expanded');
                        this.classList.remove('collapsed');
                        this.setAttribute('aria-expanded', 'true');
                        if (content) {
                            content.classList.add('show');
                            content.classList.remove('hide');
                        }
                        if (icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                        }
                    }
                });
            });
        }
        
        // Initialize search functionality
        function initializeSearchFunctionality() {
            const searchInput = document.getElementById('periodSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const yearGroups = document.querySelectorAll('.year-group');
                    let hasResults = false;
                    
                    yearGroups.forEach(yearGroup => {
                        const periodRows = yearGroup.querySelectorAll('.period-row');
                        let yearHasMatches = false;
                        
                        periodRows.forEach(row => {
                            const yearText = row.getAttribute('data-year');
                            const quarterText = `Q${row.getAttribute('data-quarter')}`;
                            const statusText = row.querySelector('.badge').textContent.trim().toLowerCase();
                            
                            const matches = yearText.includes(searchTerm) || 
                                         quarterText.toLowerCase().includes(searchTerm) ||
                                         statusText.includes(searchTerm);
                                         
                            if (matches) {
                                row.style.display = '';
                                yearHasMatches = true;
                                hasResults = true;
                            } else {
                                row.style.display = 'none';
                            }
                        });
                        
                        if (yearHasMatches) {
                            yearGroup.style.display = '';
                            // Expand the year group that has matches
                            const yearToggle = yearGroup.querySelector('.year-toggle');
                            const yearContent = yearGroup.querySelector('.year-content');
                            if (yearToggle && yearContent && searchTerm) {
                                yearToggle.classList.add('expanded');
                                yearToggle.classList.remove('collapsed');
                                yearToggle.setAttribute('aria-expanded', 'true');
                                yearContent.classList.add('show');
                                yearContent.classList.remove('hide');
                            }
                        } else {
                            yearGroup.style.display = 'none';
                        }
                    });
                    
                    // Show/hide no results message
                    const noResultsMessage = document.getElementById('noPeriodsFound');
                    if (noResultsMessage) {
                        noResultsMessage.classList.toggle('d-none', hasResults || !searchTerm);
                    }
                });
            }
        }

        // Handler for both Add Period buttons
        const periodModal = safeSelect('#periodModal');
        const addPeriodModal = periodModal ? new bootstrap.Modal(periodModal) : null;
        
        // Handle both buttons with different IDs
        document.querySelectorAll('#addPeriodBtn, #addPeriodBtnAlt').forEach(button => {
            safeAttachEvent(button, 'click', function() {
                // Check if we have all required elements before proceeding
                const periodForm = safeSelect('#periodForm');
                const periodIdField = safeSelect('#period_id');
                const periodModalLabel = safeSelect('#periodModalLabel');
                
                if (!periodForm || !periodIdField || !periodModalLabel || !addPeriodModal) {
                    console.error('Missing required elements for Add Period modal');
                    return;
                }
                
                // Reset form for new period
                periodForm.reset();
                periodIdField.value = '';
                periodModalLabel.textContent = 'Add Period';
                
                // Set current year as default
                const currentYear = new Date().getFullYear();
                const yearField = safeSelect('#year');
                if (yearField) yearField.value = currentYear;
                
                // Set default quarter based on current date
                const currentMonth = new Date().getMonth() + 1;
                const currentQuarter = Math.ceil(currentMonth / 3);
                const quarterField = safeSelect('#quarter');
                if (quarterField) quarterField.value = currentQuarter;
                
                // Standard dates mode is checked by default
                const useStandardDates = safeSelect('#useStandardDates');
                const datesModeText = safeSelect('#datesModeText');
                if (useStandardDates) useStandardDates.checked = true;
                if (datesModeText) datesModeText.textContent = 'Standard dates';
                
                // Set standard dates for selected quarter/year
                setStandardDates(currentYear, currentQuarter);
                
                // Show modal
                addPeriodModal.show();
            });
        });
        
        // Function to get standard quarter dates
        function getStandardQuarterDates(year, quarter) {
            let startDate, endDate;
            const intQuarter = parseInt(quarter);

            switch (intQuarter) {
                case 1:
                    startDate = `${year}-01-01`;
                    endDate = `${year}-03-31`;
                    break;
                case 2:
                    startDate = `${year}-04-01`;
                    endDate = `${year}-06-30`;
                    break;
                case 3:
                    startDate = `${year}-07-01`;
                    endDate = `${year}-09-30`;
                    break;
                case 4:
                    startDate = `${year}-10-01`;
                    endDate = `${year}-12-31`;
                    break;
                case 5: // Half Yearly 1 (Q1-Q2)
                    startDate = `${year}-01-01`;
                    endDate = `${year}-06-30`;
                    break;
                case 6: // Half Yearly 2 (Q3-Q4)
                    startDate = `${year}-07-01`;
                    endDate = `${year}-12-31`;
                    break;
                default:
                    // Default to Q4 if quarter is somehow invalid, or handle error
                    startDate = `${year}-10-01`;
                    endDate = `${year}-12-31`;
                    break;
            }
            
            return { startDate, endDate };
        }
        
        // Function to set standard dates
        function setStandardDates(year, quarter) {
            const { startDate, endDate } = getStandardQuarterDates(year, quarter);
            const startDateField = safeSelect('#start_date');
            const endDateField = safeSelect('#end_date');
            const nonStandardStartIndicator = safeSelect('#nonStandardStartIndicator');
            const nonStandardEndIndicator = safeSelect('#nonStandardEndIndicator');
            
            if (startDateField) startDateField.value = startDate;
            if (endDateField) endDateField.value = endDate;
            
            // Hide non-standard indicators
            if (nonStandardStartIndicator) nonStandardStartIndicator.classList.add('d-none');
            if (nonStandardEndIndicator) nonStandardEndIndicator.classList.add('d-none');
        }
        
        // Year and quarter change handlers - auto-update dates when in standard mode
        const yearField = safeSelect('#year');
        if (yearField) {
            safeAttachEvent(yearField, 'change', function() {
                const useStandardDates = safeSelect('#useStandardDates');
                
                if (useStandardDates && useStandardDates.checked) {
                    const quarterField = safeSelect('#quarter');
                    if (quarterField) {
                        setStandardDates(this.value, quarterField.value);
                    }
                } else {
                    checkNonStandardDates();
                }
            });
        }
        
        const quarterField = safeSelect('#quarter');
        if (quarterField) {
            safeAttachEvent(quarterField, 'change', function() {
                const useStandardDates = safeSelect('#useStandardDates');
                
                if (useStandardDates && useStandardDates.checked) {
                    const yearField = safeSelect('#year');
                    if (yearField) {
                        setStandardDates(yearField.value, this.value);
                    }
                } else {
                    checkNonStandardDates();
                }
            });
        }
        
        // Toggle between standard and custom dates
        const useStandardDates = safeSelect('#useStandardDates');
        if (useStandardDates) {
            safeAttachEvent(useStandardDates, 'change', function() {
                const yearField = safeSelect('#year');
                const quarterField = safeSelect('#quarter');
                const datesModeText = safeSelect('#datesModeText');
                const startDateField = safeSelect('#start_date');
                const endDateField = safeSelect('#end_date');
                
                // Update text based on state
                if (datesModeText) {
                    datesModeText.textContent = this.checked ? 'Standard dates' : 'Custom dates';
                }
                
                if (this.checked && yearField && quarterField) {
                    // Switch to standard dates when toggled on
                    setStandardDates(yearField.value, quarterField.value);
                } else {
                    // When switching to custom mode, no automatic changes but check for indicators
                    checkNonStandardDates();
                }
                
                // Make date inputs readonly or editable based on toggle
                if (startDateField) startDateField.readOnly = this.checked;
                if (endDateField) endDateField.readOnly = this.checked;
            });
        }
        
        // Initialize edit period buttons
        document.querySelectorAll('.edit-period').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr'); // Get the parent table row
                if (!row) return;

                const id = row.getAttribute('data-period-id');
                const year = row.querySelector('td:nth-child(2) small').textContent.match(/\((\d{4})\)/)[1];
                const quarter = row.querySelector('td:nth-child(2) strong').textContent;
                let quarterValue;
                if (quarter.startsWith('Q')) {
                    quarterValue = quarter.replace('Q', '');
                } else if (quarter === 'Half Yearly 1') {
                    quarterValue = '5';
                } else if (quarter === 'Half Yearly 2') {
                    quarterValue = '6';
                }

                const dates = row.querySelector('td:nth-child(3)').textContent.split(' - ');
                // Convert date from 'M j, Y' to 'Y-m-d'
                const startDate = new Date(dates[0]).toISOString().split('T')[0];
                const endDate = new Date(dates[1]).toISOString().split('T')[0];
                const status = row.querySelector('td:nth-child(4) .badge').textContent.trim().toLowerCase();
                
                const periodModal = document.getElementById('periodModal');
                const modalTitle = document.getElementById('periodModalLabel');
                const periodForm = document.getElementById('periodForm');
                const periodIdField = document.getElementById('period_id');
                const yearField = document.getElementById('year');
                const quarterField = document.getElementById('quarter');
                const startDateField = document.getElementById('start_date');
                const endDateField = document.getElementById('end_date');
                const statusField = document.getElementById('status');
                const useStandardDatesField = document.getElementById('useStandardDates');
                
                if (periodModal && periodForm && periodIdField && yearField && quarterField && 
                    startDateField && endDateField && statusField && useStandardDatesField) {
                    
                    // Set form values
                    periodIdField.value = id;
                    yearField.value = year;
                    quarterField.value = quarterValue;
                    startDateField.value = startDate;
                    endDateField.value = endDate;
                    statusField.value = status;
                    
                    // Check if using standard dates
                    const { startDate: standardStart, endDate: standardEnd } = getStandardQuarterDates(year, quarterValue);
                    const isStandard = (startDate === standardStart && endDate === standardEnd);
                    useStandardDatesField.checked = isStandard;
                    
                    // Update readonly status
                    startDateField.readOnly = isStandard;
                    endDateField.readOnly = isStandard;
                    
                    // Update text
                    if (modalTitle) modalTitle.textContent = 'Edit Period';
                    const datesModeText = document.getElementById('datesModeText');
                    if (datesModeText) {
                        datesModeText.textContent = isStandard ? 'Standard dates' : 'Custom dates';
                    }
                    
                    // Show modal
                    const bsModal = new bootstrap.Modal(periodModal);
                    bsModal.show();
                }
            });
        });
        
        // Initialize delete period buttons
        document.querySelectorAll('.delete-period').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr'); // Get the parent table row
                if (!row) return;

                const id = row.getAttribute('data-period-id');
                const year = row.querySelector('td:nth-child(2) small').textContent.match(/\((\d{4})\)/)[1];
                const quarter = row.querySelector('td:nth-child(2) strong').textContent.replace('Q', '');
                
                const deleteModal = document.getElementById('deleteModal');
                const periodDisplay = document.getElementById('period-display');
                const periodIdField = document.getElementById('delete-period-id');
                
                if (deleteModal && periodDisplay && periodIdField) {
                    periodDisplay.textContent = `${quarter}-${year}`;
                    periodIdField.value = id;
                    
                    const bsModal = new bootstrap.Modal(deleteModal);
                    bsModal.show();
                }
            });
        });
        
        // Initialize toggle period status buttons
        document.querySelectorAll('.toggle-period-status').forEach(button => {
            button.addEventListener('click', function() {
                const periodId = this.getAttribute('data-period-id');
                const currentStatus = this.getAttribute('data-current-status');
                const newStatus = currentStatus === 'open' ? 'closed' : 'open';
                
                // Disable button while processing
                this.disabled = true;
                const originalButtonContent = this.innerHTML; // Store original HTML content
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; // Update with spinner
                
                // Create form data
                const formData = new FormData();
                formData.append('period_id', periodId);
                formData.append('status', newStatus);
                  // Get the correct path to the AJAX handler using APP_URL from global config
                // Use absolute path instead of relative path to avoid 404 errors
                const ajaxPath = `${APP_URL}/app/ajax/toggle_period_status.php`;
                
                // Send AJAX request
                fetch(ajaxPath, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if the response is ok before trying to parse JSON
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        // Create and show an error message on the page
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                        errorAlert.innerHTML = `
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div>${data.error}</div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        
                        // Insert the error message at the top of the periods card
                        const cardBody = document.querySelector('.card.shadow-sm .card-body');
                        if (cardBody) {
                            cardBody.insertBefore(errorAlert, cardBody.firstChild);
                        }
                        
                        // Restore button
                        this.disabled = false;
                        this.innerHTML = originalButtonContent; // Restore original HTML
                        
                        // Auto dismiss after 5 seconds
                        setTimeout(() => {
                            errorAlert.classList.remove('show');
                            setTimeout(() => errorAlert.remove(), 150);
                        }, 5000);
                    } else {
                        // Refresh the page to show updated status
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Create and show an error message on the page
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                    errorAlert.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div>An error occurred while updating the period status. Please try again.</div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Insert the error message at the top of the periods card
                    const cardBody = document.querySelector('.card.shadow-sm .card-body');
                    if (cardBody) {
                        cardBody.insertBefore(errorAlert, cardBody.firstChild);
                    }
                    
                    // Restore button
                    this.disabled = false;
                    this.innerHTML = originalButtonContent; // Restore original HTML
                    
                    // Auto dismiss after 5 seconds
                    setTimeout(() => {
                        errorAlert.classList.remove('show');
                        setTimeout(() => errorAlert.remove(), 150);
                    }, 5000);
                });
            });
        });
        
        // Function to check if dates are non-standard
        function checkNonStandardDates() {
            const yearField = safeSelect('#year');
            const quarterField = safeSelect('#quarter');
            const startDateField = safeSelect('#start_date');
            const endDateField = safeSelect('#end_date');
            const nonStandardStartIndicator = safeSelect('#nonStandardStartIndicator');
            const nonStandardEndIndicator = safeSelect('#nonStandardEndIndicator');
            
            if (!yearField || !quarterField || !startDateField || !endDateField) {
                console.error('Missing required form fields for date validation');
                return;
            }
            
            const year = yearField.value;
            const quarter = quarterField.value;
            const startDate = startDateField.value;
            const endDate = endDateField.value;
            
            if (year && quarter && startDate && endDate) {
                const { startDate: standardStart, endDate: standardEnd } = getStandardQuarterDates(year, quarter);
                
                // Check if start date is non-standard
                if (nonStandardStartIndicator) {
                    if (startDate !== standardStart) {
                        nonStandardStartIndicator.classList.remove('d-none');
                    } else {
                        nonStandardStartIndicator.classList.add('d-none');
                    }
                }
                
                // Check if end date is non-standard
                if (nonStandardEndIndicator) {
                    if (endDate !== standardEnd) {
                        nonStandardEndIndicator.classList.remove('d-none');
                    } else {
                        nonStandardEndIndicator.classList.add('d-none');
                    }
                }
            }
        }

        // Refresh page button handler
        const refreshPageBtn = safeSelect('#refreshPage');
        if (refreshPageBtn) {
            safeAttachEvent(refreshPageBtn, 'click', function() {
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
                
                // Use regular navigation to reload the page
                window.location.href = 'reporting_periods.php';
            });
        }

        // Function to reattach all event handlers
        function reattachEventHandlers() {
            // Reattach toggle button handlers
            document.querySelectorAll('.toggle-period-status').forEach(button => {
                if (!button.hasAttribute('data-initialized')) {
                    button.setAttribute('data-initialized', 'true');
                    button.addEventListener('click', handleToggleClick);
                }
            });
            
            // Re-attach edit button handlers
            document.querySelectorAll('.edit-period').forEach(button => {
                if (!button.hasAttribute('data-initialized')) {
                    button.setAttribute('data-initialized', 'true');
                    button.addEventListener('click', handleEditClick);
                }
            });
            
            // Re-attach delete button handlers
            document.querySelectorAll('.delete-period').forEach(button => {
                if (!button.hasAttribute('data-initialized')) {
                    button.setAttribute('data-initialized', 'true');
                    button.addEventListener('click', handleDeleteClick);
                }
            });
        }
        
        // Initialize accordion and search functionality
        initializeYearToggle();
        initializeSearchFunctionality();
        
        // Handle missing elements gracefully
        window.addEventListener('error', function(e) {
            if (e.message && (
                e.message.includes('Cannot read properties') ||
                e.message.includes('is null') ||
                e.message.includes('is not defined') ||
                e.message.includes('reporting errors')
            )) {
                console.warn('Caught potential DOM error:', e.message);
                
                // Prevent the error from showing to the user
                e.preventDefault();
                
                // Try to recover by reinitializing components
                setTimeout(initializeYearToggle, 100);
                setTimeout(initializeSearchFunctionality, 100);
            }
            
            return false;  // Don't prevent other error handlers
        }, true);
    });
}
