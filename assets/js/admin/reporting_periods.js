// Make sure this script only runs once
if (!window.reportingPeriodsInitialized) {
    window.reportingPeriodsInitialized = true;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handler for both Add Period buttons
        const addPeriodModal = new bootstrap.Modal(document.getElementById('periodModal'));
        
        // Handle both buttons with different IDs
        document.querySelectorAll('#addPeriodBtn, #addPeriodBtnAlt').forEach(button => {
            button.addEventListener('click', function() {
                // Reset form for new period
                document.getElementById('periodForm').reset();
                document.getElementById('period_id').value = '';
                document.getElementById('periodModalLabel').textContent = 'Add Period';
                
                // Set current year as default
                const currentYear = new Date().getFullYear();
                document.getElementById('year').value = currentYear;
                
                // Set default quarter based on current date
                const currentMonth = new Date().getMonth() + 1;
                const currentQuarter = Math.ceil(currentMonth / 3);
                document.getElementById('quarter').value = currentQuarter;
                
                // Standard dates mode is checked by default
                document.getElementById('useStandardDates').checked = true;
                document.getElementById('datesModeText').textContent = 'Standard dates';
                
                // Set standard dates for selected quarter/year
                setStandardDates(currentYear, currentQuarter);
                
                // Show modal
                addPeriodModal.show();
            });
        });
        
        // Function to get standard quarter dates
        function getStandardQuarterDates(year, quarter) {
            let startDate, endDate;
            
            switch (parseInt(quarter)) {
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
            }
            
            return { startDate, endDate };
        }
        
        // Function to set standard dates
        function setStandardDates(year, quarter) {
            const { startDate, endDate } = getStandardQuarterDates(year, quarter);
            document.getElementById('start_date').value = startDate;
            document.getElementById('end_date').value = endDate;
            
            // Hide non-standard indicators
            document.getElementById('nonStandardStartIndicator').classList.add('d-none');
            document.getElementById('nonStandardEndIndicator').classList.add('d-none');
        }
        
        // Year and quarter change handlers - auto-update dates when in standard mode
        document.getElementById('year').addEventListener('change', function() {
            if (document.getElementById('useStandardDates').checked) {
                const quarter = document.getElementById('quarter').value;
                setStandardDates(this.value, quarter);
            } else {
                checkNonStandardDates();
            }
        });
        
        document.getElementById('quarter').addEventListener('change', function() {
            if (document.getElementById('useStandardDates').checked) {
                const year = document.getElementById('year').value;
                setStandardDates(year, this.value);
            } else {
                checkNonStandardDates();
            }
        });
        
        // Toggle between standard and custom dates
        document.getElementById('useStandardDates').addEventListener('change', function() {
            const year = document.getElementById('year').value;
            const quarter = document.getElementById('quarter').value;
            
            // Update text based on state
            document.getElementById('datesModeText').textContent = this.checked ? 'Standard dates' : 'Custom dates';
            
            if (this.checked) {
                // Switch to standard dates when toggled on
                setStandardDates(year, quarter);
            } else {
                // When switching to custom mode, no automatic changes but check for indicators
                checkNonStandardDates();
            }
            
            // Make date inputs readonly or editable based on toggle
            document.getElementById('start_date').readOnly = this.checked;
            document.getElementById('end_date').readOnly = this.checked;
        });
        
        // Check for non-standard dates when dates are changed directly
        document.getElementById('start_date').addEventListener('change', function() {
            checkNonStandardDates();
            // If date is non-standard, switch toggle to custom
            const year = document.getElementById('year').value;
            const quarter = document.getElementById('quarter').value;
            const { startDate: standardStart } = getStandardQuarterDates(year, quarter);
            if (this.value !== standardStart) {
                document.getElementById('useStandardDates').checked = false;
                document.getElementById('datesModeText').textContent = 'Custom dates';
                document.getElementById('start_date').readOnly = false;
                document.getElementById('end_date').readOnly = false;
            }
        });
        
        document.getElementById('end_date').addEventListener('change', function() {
            checkNonStandardDates();
            // If date is non-standard, switch toggle to custom
            const year = document.getElementById('year').value;
            const quarter = document.getElementById('quarter').value;
            const { endDate: standardEnd } = getStandardQuarterDates(year, quarter);
            if (this.value !== standardEnd) {
                document.getElementById('useStandardDates').checked = false;
                document.getElementById('datesModeText').textContent = 'Custom dates';
                document.getElementById('start_date').readOnly = false;
                document.getElementById('end_date').readOnly = false;
            }
        });
        
        // Function to check if dates are non-standard
        function checkNonStandardDates() {
            const year = document.getElementById('year').value;
            const quarter = document.getElementById('quarter').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (year && quarter && startDate && endDate) {
                const { startDate: standardStart, endDate: standardEnd } = getStandardQuarterDates(year, quarter);
                
                // Check if start date is non-standard
                if (startDate !== standardStart) {
                    document.getElementById('nonStandardStartIndicator').classList.remove('d-none');
                } else {
                    document.getElementById('nonStandardStartIndicator').classList.add('d-none');
                }
                
                // Check if end date is non-standard
                if (endDate !== standardEnd) {
                    document.getElementById('nonStandardEndIndicator').classList.remove('d-none');
                } else {
                    document.getElementById('nonStandardEndIndicator').classList.add('d-none');
                }
            }
        }
        
        // Fix accordion behavior to properly handle both expanding and collapsing
        const accordionButtons = document.querySelectorAll('.accordion-header button');
        accordionButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Get target collapse element
                const targetId = this.getAttribute('data-bs-target');
                const targetCollapse = document.querySelector(targetId);
                
                // Check if it's already expanded
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                // Toggle the collapse state
                if (isExpanded) {
                    // Currently expanded, so collapse it
                    this.setAttribute('aria-expanded', 'false');
                    this.classList.add('collapsed');
                    targetCollapse.classList.remove('show');
                    
                    // Update the indicator icon
                    const indicator = this.querySelector('.collapse-indicator i');
                    indicator.style.transform = 'rotate(0deg)';
                } else {
                    // Currently collapsed, so expand it
                    this.setAttribute('aria-expanded', 'true');
                    this.classList.remove('collapsed');
                    targetCollapse.classList.add('show');
                    
                    // Update the indicator icon
                    const indicator = this.querySelector('.collapse-indicator i');
                    indicator.style.transform = 'rotate(180deg)';
                }
            });
        });
        
        // Search functionality
        const searchInput = document.getElementById('periodSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const yearGroups = document.querySelectorAll('.year-group');
                let foundAnyMatch = false;
                
                yearGroups.forEach(yearGroup => {
                    const rows = yearGroup.querySelectorAll('.period-row');
                    let foundInGroup = false;
                    
                    rows.forEach(row => {
                        const year = row.getAttribute('data-year');
                        const quarter = row.getAttribute('data-quarter');
                        const searchableText = `${year} Q${quarter}`.toLowerCase();
                        const match = searchableText.includes(searchTerm);
                        
                        if (match) {
                            row.classList.remove('d-none');
                            foundInGroup = true;
                            foundAnyMatch = true;
                        } else {
                            row.classList.add('d-none');
                        }
                    });
                    
                    // If no matches in this year group, hide it
                    if (!foundInGroup) {
                        yearGroup.classList.add('d-none');
                    } else {
                        yearGroup.classList.remove('d-none');
                        // Expand the year group if it contains matches
                        const collapseElement = yearGroup.querySelector('.accordion-collapse');
                        const bsCollapse = new bootstrap.Collapse(collapseElement, {toggle: false});
                        bsCollapse.show();
                    }
                });
                
                // Show or hide the "no results" message
                const noResultsElement = document.getElementById('noPeriodsFound');
                if (!foundAnyMatch && searchTerm.length > 0) {
                    noResultsElement.classList.remove('d-none');
                } else {
                    noResultsElement.classList.add('d-none');
                }
            });
        }
    
        // Find all toggle buttons and attach click handlers, using a data attribute to prevent duplicates
        document.querySelectorAll('.toggle-period-status').forEach(button => {
            // Mark this button as initialized to prevent duplicate handlers
            if (!button.hasAttribute('data-initialized')) {
                button.setAttribute('data-initialized', 'true');
                button.addEventListener('click', handleToggleClick);
            }
        });
        
        // Handler function for the toggle buttons
        function handleToggleClick() {
            const periodId = this.getAttribute('data-period-id');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = currentStatus === 'open' ? 'closed' : 'open';
            
            // Ask for confirmation
            if (!confirm(`Are you sure you want to ${newStatus === 'open' ? 'open' : 'close'} this reporting period?${newStatus === 'open' ? '\n\nThis will close any other currently open periods.' : ''}`)) {
                return;
            }
            
            // Disable button to prevent multiple clicks
            this.disabled = true;
            
            // Create form data for the request
            const formData = new FormData();
            formData.append('period_id', periodId);
            formData.append('status', newStatus);
            
            // Send AJAX request
            fetch('/pcds2030_dashboard/ajax/toggle_period_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable the button
                this.disabled = false;
                
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                // Remove any existing success messages first
                document.querySelectorAll('.alert-success').forEach(alert => {
                    alert.remove();
                });
                
                // Show success message
                const alertElement = document.createElement('div');
                alertElement.className = 'alert alert-success alert-dismissible fade show';
                alertElement.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>${data.message}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                // Insert alert before the table
                const tableContainer = document.querySelector('.table-responsive').parentNode;
                tableContainer.insertBefore(alertElement, document.querySelector('.table-responsive'));
                
                // Auto dismiss after 3 seconds
                setTimeout(() => {
                    alertElement.classList.remove('show');
                    setTimeout(() => alertElement.remove(), 300);
                }, 3000);
                
                // Reload the page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(error => {
                this.disabled = false;
                alert('Error: ' + error.message);
            });
        }
        
        // Edit period button handlers
        document.querySelectorAll('.edit-period-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const year = this.getAttribute('data-year');
                const quarter = this.getAttribute('data-quarter');
                const startDate = this.getAttribute('data-start-date');
                const endDate = this.getAttribute('data-end-date');
                const status = this.getAttribute('data-status');
                
                document.getElementById('periodModalLabel').textContent = `Edit Period Q${quarter}-${year}`;
                document.getElementById('period_id').value = id;
                document.getElementById('year').value = year;
                document.getElementById('quarter').value = quarter;
                document.getElementById('start_date').value = startDate;
                document.getElementById('end_date').value = endDate;
                document.getElementById('status').value = status;
                
                // Check if dates are standard
                checkNonStandardDates();
                
                addPeriodModal.show();
            });
        });
        
        // Delete period button handlers
        document.querySelectorAll('.delete-period-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const year = this.getAttribute('data-year');
                const quarter = this.getAttribute('data-quarter');
                
                document.getElementById('period-display').textContent = `Q${quarter}-${year}`;
                document.getElementById('delete-period-id').value = id;
                
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
    });
}