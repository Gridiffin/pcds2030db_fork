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
                    const searchTerm = this.value.toLowerCase().trim();
                    const yearGroups = document.querySelectorAll('.year-group');
                    let hasResults = false;

                    yearGroups.forEach(yearGroup => {
                        const periodRows = yearGroup.querySelectorAll('tr.period-row'); // Use the added class
                        let yearHasMatches = false;

                        periodRows.forEach(row => {
                            const yearText = row.getAttribute('data-year').toLowerCase();
                            const quarterValue = row.getAttribute('data-quarter');
                            let quarterText = '';
                            if (quarterValue >= 1 && quarterValue <= 4) {
                                quarterText = `q${quarterValue}`;
                            } else if (quarterValue == 5) {
                                quarterText = 'half yearly 1';
                            } else if (quarterValue == 6) {
                                quarterText = 'half yearly 2';
                            }

                            const statusText = row.querySelector('td:nth-child(4) .badge').textContent.trim().toLowerCase();
                            const fullPeriodText = `${quarterText} ${yearText}`;

                            const matches = yearText.includes(searchTerm) ||
                                          quarterText.includes(searchTerm) ||
                                          statusText.includes(searchTerm) ||
                                          fullPeriodText.includes(searchTerm);

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
                    const noResultsMessage = document.getElementById('noPeriodsFound'); // Ensure this element exists or is created
                    const periodsAccordion = document.getElementById('periodsAccordion');

                    if (noResultsMessage) {
                        noResultsMessage.classList.toggle('d-none', hasResults);
                        noResultsMessage.textContent = hasResults ? '' : 'No periods found matching your search.';
                    }

                    // Hide or show the entire accordion based on search results
                    if (periodsAccordion) {
                        periodsAccordion.style.display = hasResults || !searchTerm ? '' : 'none';
                        // If no results and search term exists, show a message if noResultsMessage is not available
                        if (!hasResults && searchTerm && !noResultsMessage) {
                            let existingMsg = document.getElementById('dynamicNoResultsMsg');
                            if (!existingMsg) {
                                existingMsg = document.createElement('div');
                                existingMsg.id = 'dynamicNoResultsMsg';
                                existingMsg.className = 'alert alert-info text-center py-3';
                                periodsAccordion.parentNode.insertBefore(existingMsg, periodsAccordion.nextSibling);
                            }
                            existingMsg.textContent = 'No periods found matching your search.';
                            existingMsg.style.display = '';
                        } else if (document.getElementById('dynamicNoResultsMsg')){
                            document.getElementById('dynamicNoResultsMsg').style.display = 'none';
                        }
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
                case 5: // Half Yearly 1 (Jan-Jun)
                    startDate = `${year}-01-01`;
                    endDate = `${year}-06-30`;
                    break;
                case 6: // Half Yearly 2 (Jul-Dec)
                    startDate = `${year}-07-01`;
                    endDate = `${year}-12-31`;
                    break;
                default:
                    // Default to Q1 if quarter is somehow invalid, or handle error
                    console.warn(`Invalid quarter/period type: ${quarter}. Defaulting dates.`);
                    startDate = `${year}-01-01`;
                    endDate = `${year}-03-31`;
                    break;
            }
            
            return { startDate, endDate };
        }
        
        // Function to set standard dates
        function setStandardDates(year, quarter) {
            const { startDate, endDate } = getStandardQuarterDates(year, quarter);
            const startDateField = safeSelect('#startDate'); // Changed from #start_date
            const endDateField = safeSelect('#endDate');   // Changed from #end_date
            const nonStandardStartIndicator = safeSelect('#nonStandardStartIndicator');
            const nonStandardEndIndicator = safeSelect('#nonStandardEndIndicator');
            
            if (startDateField) startDateField.value = startDate;
            if (endDateField) endDateField.value = endDate;
            
            // Hide non-standard indicators
            if (nonStandardStartIndicator) nonStandardStartIndicator.classList.add('d-none');
            if (nonStandardEndIndicator) nonStandardEndIndicator.classList.add('d-none');
        }
        
        // Year and quarter change handlers - auto-update dates
        const yearFieldModal = safeSelect('#addPeriodForm #year'); // Scoped to the modal form
        if (yearFieldModal) {
            safeAttachEvent(yearFieldModal, 'change', function() {
                const quarterFieldModal = safeSelect('#addPeriodForm #quarter'); // Scoped to the modal form
                if (quarterFieldModal && quarterFieldModal.value && this.value) {
                    setStandardDates(this.value, quarterFieldModal.value);
                }
            });
            safeAttachEvent(yearFieldModal, 'input', function() { // Also trigger on input for better UX
                const quarterFieldModal = safeSelect('#addPeriodForm #quarter');
                if (quarterFieldModal && quarterFieldModal.value && this.value) {
                    setStandardDates(this.value, quarterFieldModal.value);
                }
            });
        }
        
        const quarterFieldModal = safeSelect('#addPeriodForm #quarter'); // Scoped to the modal form
        if (quarterFieldModal) {
            safeAttachEvent(quarterFieldModal, 'change', function() {
                const yearFieldModal = safeSelect('#addPeriodForm #year'); // Scoped to the modal form
                if (yearFieldModal && yearFieldModal.value && this.value) {
                    setStandardDates(yearFieldModal.value, this.value);
                }
            });
        }

        // Override or adjust existing Add Period button handler if it was for a different modal structure
        // The existing code has a modal with id '#periodModal'. The new one is '#addPeriodModal'
        // We need to ensure the correct modal and form elements are targetted.
        // The new modal form ID is 'addPeriodForm'
        
        const addPeriodModalInstance = safeSelect('#addPeriodModal');
        const bootstrapAddPeriodModal = addPeriodModalInstance ? new bootstrap.Modal(addPeriodModalInstance) : null;

        document.querySelectorAll('#addPeriodBtn, button[data-bs-target="#addPeriodModal"]').forEach(button => {
            safeAttachEvent(button, 'click', function() {
                const periodForm = safeSelect('#addPeriodForm');
                const periodModalLabel = safeSelect('#addPeriodModalLabel');
                
                if (!periodForm || !periodModalLabel || !bootstrapAddPeriodModal) {
                    console.error('Missing required elements for Add Period modal (new structure)');
                    return;
                }
                
                periodForm.reset();
                periodModalLabel.textContent = 'Add New Reporting Period';
                
                // Set current year as default in the new year field
                const currentYear = new Date().getFullYear();
                const yearFieldNew = safeSelect('#addPeriodForm #year');
                if (yearFieldNew) yearFieldNew.value = currentYear;
                
                // Set default quarter based on current date in the new quarter field
                const currentMonth = new Date().getMonth() + 1; // 1-12
                let defaultPeriodType = Math.ceil(currentMonth / 3); // Default to current quarter (1-4)
                // Potentially adjust defaultPeriodType if you want to default to Half Yearly based on current date
                // For example, if in June (month 6), default to Half Yearly 1 (value 5)
                // if (currentMonth >= 1 && currentMonth <= 6) defaultPeriodType = 5; 
                // else defaultPeriodType = 6;
                // For now, it defaults to the current quarter.

                const quarterFieldNew = safeSelect('#addPeriodForm #quarter');
                if (quarterFieldNew) quarterFieldNew.value = defaultPeriodType.toString();
                
                // Auto-populate dates based on default year and quarter
                if (yearFieldNew && quarterFieldNew && yearFieldNew.value && quarterFieldNew.value) {
                    setStandardDates(yearFieldNew.value, quarterFieldNew.value);
                }
                
                // Set default status (optional, if not handled by HTML)
                const statusField = safeSelect('#addPeriodForm #status');
                if (statusField) statusField.value = 'inactive'; // Default to inactive

                bootstrapAddPeriodModal.show();
            });
        });

        // Save Period Handler (for the new modal)
        const savePeriodButton = safeSelect('#addPeriodModal #savePeriod');
        if (savePeriodButton) {
            safeAttachEvent(savePeriodButton, 'click', function() {
                const form = safeSelect('#addPeriodForm');
                if (!form) {
                    console.error("Add period form not found");
                    return;
                }

                // Basic client-side validation
                const quarter = form.quarter.value;
                const year = form.year.value;
                const startDate = form.start_date.value;
                const endDate = form.end_date.value;

                if (!quarter) {
                    alert("Please select a period type.");
                    form.quarter.focus();
                    return;
                }
                if (!year || year.length !== 4 || isNaN(parseInt(year))) {
                    alert("Please enter a valid year (YYYY).");
                    form.year.focus();
                    return;
                }
                if (!startDate || !endDate) {
                    alert("Start date and end date are required and should be auto-populated.");
                    return;
                }
                
                const formData = new FormData(form);
                // Construct period_name for backend compatibility if still needed, or send quarter/year directly
                // The backend will be updated to use quarter and year directly.
                // formData.append('period_name', `Q${quarter} ${year}`); // No longer needed if backend is updated

                // Add loading indicator to button
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

                fetch(`${APP_URL}/app/ajax/save_period.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // alert('Period saved successfully!'); // Replace with a toast notification
                        if (typeof window.showToast === 'function') {
                            window.showToast('Success', data.message || 'Period saved successfully.', 'success');
                        } else {
                            alert(data.message || 'Period saved successfully.');
                        }
                        bootstrapAddPeriodModal.hide();
                        // Optionally, refresh the periods table or redirect
                        if (typeof loadPeriodsTable === 'function') {
                            loadPeriodsTable(); // If such a function exists to refresh the table
                        } else {
                            window.location.reload(); // Fallback to reload
                        }
                    } else {
                        // alert(`Error: ${data.message}`); // Replace with a toast notification
                         if (typeof window.showToast === 'function') {
                            window.showToast('Error', data.message || 'Failed to save period.', 'danger');
                        } else {
                            alert(`Error: ${data.message || 'Failed to save period.'}`);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving period:', error);
                    // alert('An unexpected error occurred. Please try again.');
                    if (typeof window.showToast === 'function') {
                        window.showToast('Error', 'An unexpected error occurred. Please try again.', 'danger');
                    } else {
                        alert('An unexpected error occurred. Please try again.');
                    }
                })
                .finally(() => {
                    // Re-enable button and restore text
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-save me-1"></i> Save Period';
                });
            });
        }
        
        // Remove or comment out the old modal's specific handlers if they conflict
        // For example, the old '#addPeriodBtn' and '#addPeriodBtnAlt' handlers might need adjustment
        // if they were targeting a modal with ID 'periodModal' and form 'periodForm'
        // The new modal is 'addPeriodModal' and form 'addPeriodForm'.
        // The code above already re-assigns handlers for buttons opening the new modal.

        // The existing `initializeEditPeriodButtons` and `initializeDeletePeriodButtons`
        // might need to be reviewed if their modals are also being refactored, but the prompt
        // only specified refactoring the "Add New Reporting Period" modal.

        // The `useStandardDates` toggle logic might not be relevant for the new "Add Period" modal
        // as dates are now always standard and read-only. It might still be used for an "Edit Period" modal.
        // For now, we assume it's for a different modal or an edit context.
        // If `#useStandardDates` was part of the old add modal, its related listeners might need removal or adjustment.
        // Based on the HTML provided for `reporting_periods.php`, the modal ID is `addPeriodModal`.
        // The JS has handlers for `periodModal` (which seems to be an edit modal) and `addPeriodModal`.
        // The new logic correctly targets `addPeriodModal` and `addPeriodForm`.

        // Ensure `getStandardQuarterDates` is available or defined if not already.
        // It is defined in the provided `reporting_periods.js` content.

        // Ensure `APP_URL` is defined. It is defined in `reporting_periods.php` script tag.
        
    }); // End DOMContentLoaded
} // End window.reportingPeriodsInitialized
