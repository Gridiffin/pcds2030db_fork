/**
 * Admin Periods Management JavaScript
 * Handles AJAX operations for managing reporting periods
 */

$(document).ready(function() {
    // Only load periods if we're on the right page
    if (document.getElementById('periodsTable')) {
        loadPeriods();
    }
    
    // Handle add period form submission
    $('#savePeriod').on('click', function() {
        savePeriod();
    });

    // Handle click on the "Add Period" button that might be in the dashboard header
    // This ID comes from the $actions array in reporting_periods.php
    $(document).on('click', '#addPeriodBtn', function(e) {
        e.preventDefault(); // Prevent default action if it's an anchor tag
        
        // Reset the form for a new period (not edit)
        resetPeriodForm();
        
        // Show the modal
        $('#addPeriodModal').modal('show');
    });
    
    // Handle form reset when modal is hidden
    $('#addPeriodModal').on('hidden.bs.modal', function() {
        resetPeriodForm();
    });
      // Handle period type changes to update period number options
    $('#periodType').on('change', function() {
        updatePeriodNumberOptions();
        // Mark that period type changed, which may affect standard dates
        $('#period-dates-changed').val('true');
        updateDateFields();
    });
    
    // Handle period number and year changes to auto-calculate dates
    $('#periodNumber, #year').on('change', function() {
        // Mark that period number/year changed, which may affect standard dates
        $('#period-dates-changed').val('true');
        updateDateFields();
    });
    
    // Track when date fields are manually edited
    $('#startDate, #endDate').on('change', function() {
        const periodType = $('#periodType').val();
        const periodNumber = $('#periodNumber').val();
        const year = $('#year').val();
        
        if (!periodType || !periodNumber || !year) return;
        
        // Check if dates match standard dates for this period type/number/year
        const standardDates = calculatePeriodDates(periodType, parseInt(periodNumber), parseInt(year));
        if (!standardDates) return;
        
        const currentStartDate = $('#startDate').val();
        const currentEndDate = $('#endDate').val();
        
        // If either date doesn't match standard, mark as using custom dates
        if (currentStartDate !== standardDates.startDate || currentEndDate !== standardDates.endDate) {
            $('#useCustomDates').prop('checked', true);
        } else {
            $('#useCustomDates').prop('checked', false);
        }
    });
});

/**
 * Load periods data from server
 */
function loadPeriods() {
    const tableContainer = document.getElementById('periodsTable');
    
    // Check if element exists
    if (!tableContainer) {
        console.error('Element with ID "periodsTable" not found');
        return;
    }
    
    // Show loading indicator
    tableContainer.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="mt-2 text-muted">Loading periods...</p>
        </div>
    `;
              fetch(`${window.APP_URL}/app/ajax/periods_data.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response. Please check if you are logged in as an admin.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.data.length === 0) {
                    tableContainer.innerHTML = `<div class="alert alert-info">No reporting periods found. Add a new period to get started.</div>`;
                    return;
                }
                
                // Group periods by year
                const periodsByYear = groupPeriodsByYear(data.data);
                
                // Generate accordion HTML
                const accordionHtml = generateYearAccordion(periodsByYear);
                tableContainer.innerHTML = accordionHtml;
                
                // Add event listeners for the accordion toggles
                setupAccordionListeners();
                
                // Add event listeners for the edit, delete, and toggle status buttons
                setupActionButtons();
            } else {
                // Handle authentication and other errors
                let errorMessage = data.message || 'Failed to load periods';
                if (errorMessage.includes('Access denied') || errorMessage.includes('denied')) {
                    errorMessage = 'Access denied. Please make sure you are logged in as an administrator and refresh the page.';
                }
                tableContainer.innerHTML = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}</div>`;
            }
        })
        .catch(error => {
            console.error('Error loading periods:', error);
            let errorMessage = 'Error loading periods: ' + error.message;
            
            // Provide more helpful error messages
            if (error.message.includes('JSON')) {
                errorMessage = 'Failed to load periods data. This may be due to a session timeout or permission issue. Please try logging out and back in as an administrator.';
            } else if (error.message.includes('non-JSON')) {
                errorMessage = error.message; // Use the custom message from above
            }
            
            tableContainer.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>${errorMessage}</div>`;
        });
}

/**
 * Group periods by year
 */
function groupPeriodsByYear(periods) {
    const yearGroups = {};
    
    periods.forEach(period => {
        const year = period.year;
        if (!yearGroups[year]) {
            yearGroups[year] = [];
        }
        yearGroups[year].push(period);
    });
    
    // Sort years in descending order (newest first)
    const sortedYears = Object.keys(yearGroups).sort((a, b) => b - a);
    
    const result = {};
    sortedYears.forEach(year => {
        result[year] = yearGroups[year].sort((a, b) => {
            // Sort by period number within each year
            return a.period_number - b.period_number;
        });
    });
    
    return result;
}

/**
 * Generate accordion HTML for the periods grouped by year
 */
function generateYearAccordion(periodsByYear) {
    const years = Object.keys(periodsByYear);
    if (years.length === 0) return '<div class="alert alert-info">No reporting periods found.</div>';
    
    let html = '<div class="accordion" id="periodsAccordion">';
    
    years.forEach((year, index) => {
        const isFirstYear = index === 0;
        const periods = periodsByYear[year];
        
        html += `
            <div class="accordion-item year-group">
                <h2 class="accordion-header">
                    <button class="accordion-button year-toggle ${isFirstYear ? '' : 'collapsed'}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#year${year}" 
                            aria-expanded="${isFirstYear ? 'true' : 'false'}" 
                            aria-controls="year${year}">
                        <span class="fw-bold">${year}</span>
                        <span class="ms-2 badge rounded-pill bg-secondary">${periods.length} periods</span>
                    </button>
                </h2>
                <div id="year${year}" class="accordion-collapse collapse ${isFirstYear ? 'show' : ''}" 
                     data-bs-parent="#periodsAccordion">
                    <div class="accordion-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Date Range</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${generatePeriodRows(periods)}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    return html;
}

/**
 * Generate table rows for periods
 */
function generatePeriodRows(periods) {
    let rowsHtml = '';
    
    periods.forEach(period => {
        // Determine status class
        const statusClass = period.status === 'open' ? 'success' : 'danger';
        
        // Format dates
        const startDate = new Date(period.start_date).toLocaleDateString();
        const endDate = new Date(period.end_date).toLocaleDateString();
        
        // Generate period name
        let periodName = '';
        if (period.period_type === 'quarter') {
            periodName = `Q${period.period_number}`;
        } else if (period.period_type === 'half') {
            periodName = `Half Yearly ${period.period_number}`;
        } else if (period.period_type === 'yearly') {
            periodName = `Yearly ${period.period_number}`;
        }
        
        rowsHtml += `
            <tr data-period-id="${period.period_id}">
                <td>${periodName}</td>
                <td>${startDate} - ${endDate}</td>
                <td>
                    <span class="badge bg-${statusClass}">
                        ${period.status.charAt(0).toUpperCase() + period.status.slice(1)}
                    </span>
                </td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Period actions">
                        <button class="btn btn-outline-primary edit-period-btn" data-period-id="${period.period_id}" title="Edit Period">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger delete-period-btn" data-period-id="${period.period_id}" title="Delete Period">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="btn btn-outline-${period.status === 'open' ? 'warning' : 'success'} toggle-period-status" 
                                data-period-id="${period.period_id}" 
                                data-current-status="${period.status}"
                                title="${period.status === 'open' ? 'Close Period' : 'Open Period'}">
                            ${period.status === 'open' ? '<i class="fas fa-lock"></i> Close' : '<i class="fas fa-lock-open"></i> Open'}
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    return rowsHtml;
}

/**
 * Setup event listeners for accordion toggles
 */
function setupAccordionListeners() {
    const yearToggles = document.querySelectorAll('.year-toggle');
    yearToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Toggle is handled by Bootstrap's built-in data-bs attributes
        });
    });
}

/**
 * Setup event listeners for action buttons (edit, delete, toggle status)
 */
function setupActionButtons() {
    // Edit period buttons
    document.querySelectorAll('.edit-period-btn').forEach(button => {
        button.addEventListener('click', function() {
            const periodId = this.getAttribute('data-period-id');
            editPeriod(periodId);
        });
    });
    
    // Delete period buttons
    document.querySelectorAll('.delete-period-btn').forEach(button => {
        button.addEventListener('click', function() {
            const periodId = this.getAttribute('data-period-id');
            deletePeriod(periodId);
        });
    });
    
    // Toggle period status buttons
    document.querySelectorAll('.toggle-period-status').forEach(button => {
        button.addEventListener('click', function() {
            const periodId = this.getAttribute('data-period-id');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = currentStatus === 'open' ? 'closed' : 'open';
            togglePeriodStatus(periodId, newStatus);
        });
    });
}

/**
 * Save new period or update existing one
 */
function savePeriod() {
    const formData = {
        period_id: $('#periodId').val(), // Will be empty for new periods
        period_type: $('#periodType').val(),
        period_number: $('#periodNumber').val(),
        year: $('#year').val(),
        start_date: $('#startDate').val(),
        end_date: $('#endDate').val(),
        status: $('#status').val()
    };
    
    // Determine if this is an update or a new period
    const isUpdate = formData.period_id ? true : false;
    
    // Reset validation state
    $('#addPeriodForm .is-invalid').removeClass('is-invalid');
    $('#addPeriodForm .invalid-feedback').remove();
    
    // Comprehensive validation
    let isValid = true;
    
    // Validate period type
    if (!formData.period_type) {
        $('#periodType').addClass('is-invalid');
        $('<div class="invalid-feedback">Please select a period type.</div>').insertAfter('#periodType');
        isValid = false;
    } else if (!['quarter', 'half', 'yearly'].includes(formData.period_type)) {
        $('#periodType').addClass('is-invalid');
        $('<div class="invalid-feedback">Invalid period type selected.</div>').insertAfter('#periodType');
        isValid = false;
    }
    
    // Validate period number
    if (!formData.period_number) {
        $('#periodNumber').addClass('is-invalid');
        $('<div class="invalid-feedback">Please select a period number.</div>').insertAfter('#periodNumber');
        isValid = false;
    } else {
        const periodNum = parseInt(formData.period_number);
        if (isNaN(periodNum) || periodNum < 1) {
            $('#periodNumber').addClass('is-invalid');
            $('<div class="invalid-feedback">Period number must be a positive number.</div>').insertAfter('#periodNumber');
            isValid = false;
        } else if (formData.period_type === 'quarter' && (periodNum < 1 || periodNum > 4)) {
            $('#periodNumber').addClass('is-invalid');
            $('<div class="invalid-feedback">Quarter period number must be between 1 and 4.</div>').insertAfter('#periodNumber');
            isValid = false;
        } else if (formData.period_type === 'half' && (periodNum < 1 || periodNum > 2)) {
            $('#periodNumber').addClass('is-invalid');
            $('<div class="invalid-feedback">Half yearly period number must be between 1 and 2.</div>').insertAfter('#periodNumber');
            isValid = false;
        }
    }
    
    // Validate year
    if (!formData.year) {
        $('#year').addClass('is-invalid');
        $('<div class="invalid-feedback">Please enter a year.</div>').insertAfter('#year');
        isValid = false;
    } else {
        const yearNum = parseInt(formData.year);
        if (isNaN(yearNum) || yearNum < 2000 || yearNum > 2099) {
            $('#year').addClass('is-invalid');
            $('<div class="invalid-feedback">Year must be between 2000 and 2099.</div>').insertAfter('#year');
            isValid = false;
        }
    }
    
    // Validate dates
    if (!formData.start_date) {
        $('#startDate').addClass('is-invalid');
        $('<div class="invalid-feedback">Start date is required.</div>').insertAfter('#startDate');
        isValid = false;
    }
    
    if (!formData.end_date) {
        $('#endDate').addClass('is-invalid');
        $('<div class="invalid-feedback">End date is required.</div>').insertAfter('#endDate');
        isValid = false;
    }
    
    // Check if end date is after start date
    if (formData.start_date && formData.end_date) {
        const startDate = new Date(formData.start_date);
        const endDate = new Date(formData.end_date);
        
        if (endDate <= startDate) {
            $('#endDate').addClass('is-invalid');
            $('<div class="invalid-feedback">End date must be after start date.</div>').insertAfter('#endDate');
            isValid = false;
        }
    }
    
    // Validate status
    if (!formData.status || !['open', 'closed'].includes(formData.status)) {
        $('#status').addClass('is-invalid');
        $('<div class="invalid-feedback">Please select a valid status.</div>').insertAfter('#status');
        isValid = false;
    }
      // Exit if validation failed
    if (!isValid) {
        showError('Please correct the errors in the form.');
        return;
    }
    
    // Show loading state
    $('#savePeriod').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> ' + (isUpdate ? 'Updating...' : 'Saving...'));
    
    // Check for date overlaps
    const excludePeriodId = isUpdate ? formData.period_id : null;
    
    checkDateOverlap(formData.start_date, formData.end_date, excludePeriodId)
        .then(result => {
            if (result.overlaps) {
                // Format overlapping periods for display
                const periodList = result.periods.map(p => {
                    const startDate = new Date(p.start_date).toLocaleDateString();
                    const endDate = new Date(p.end_date).toLocaleDateString();
                    let periodName = '';
                    if (p.period_type === 'quarter') {
                        periodName = `Q${p.period_number}`;
                    } else if (p.period_type === 'half') {
                        periodName = `Half Yearly ${p.period_number}`;
                    } else if (p.period_type === 'yearly') {
                        periodName = `Yearly ${p.period_number}`;
                    }
                    return `- ${periodName} ${p.year} (${startDate} - ${endDate})`;
                }).join('\n');
                
                if (!confirm(`Warning: This period overlaps with the following existing periods:\n\n${periodList}\n\nDo you want to continue anyway?`)) {
                    $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> ' + (isUpdate ? 'Update Period' : 'Save Period'));
                    return;
                }
            }
            
            // Add custom dates flag
            formData.use_custom_dates = $('#useCustomDates').prop('checked') ? 1 : 0;
            
            // For updates, we can skip the duplicate check and proceed directly
            if (isUpdate) {
                submitPeriodData(formData, isUpdate);
                return;
            }
              // For new periods, check for duplicates
            $.ajax({
                url: window.APP_URL + '/app/ajax/check_period_exists.php',
                type: 'POST',
                data: {
                    period_type: formData.period_type,
                    period_number: formData.period_number,
                    year: formData.year
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.exists) {
                            // Period already exists
                            showError(`A period for ${formData.period_type} ${formData.period_number} ${formData.year} already exists.`);
                            $('#periodType, #periodNumber, #year').addClass('is-invalid');
                            $('<div class="invalid-feedback">This period already exists.</div>').insertAfter('#year');
                            $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Period');
                        } else {
                            // Proceed with saving the period
                            submitPeriodData(formData);
                        }
                    } else {
                        // Error checking for duplicate
                        showError('Error checking for duplicate periods: ' + (response.message || 'Unknown error'));
                        $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Period');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking for duplicate period:', error);
                    showError('Error checking for duplicate periods. Please try again.');
                    $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Period');
                }
            });
        }); // Closes the arrow function body for .then(result => { ... })
} // Closing brace for the savePeriod function

/**
 * Submit period data to the server after validation
 */
function submitPeriodData(formData, isUpdate = false) {
    // Determine which endpoint to use
    const endpoint = isUpdate ? 'update_period.php' : 'save_period.php';
    
    $.ajax({
                    url: window.APP_URL + '/app/ajax/' + endpoint,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addPeriodModal').modal('hide');
                showSuccess(isUpdate ? 'Period updated successfully!' : 'Period created successfully!');
                loadPeriods(); // Reload the periods table
                
                // Reset the form after successful submission
                resetPeriodForm();
            } else {
                showError(response.message || 'An error occurred while saving the period.');
                $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> ' + (isUpdate ? 'Update Period' : 'Save Period'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving period:', error);
            showError('An error occurred while saving the period. Please try again.');
            $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> ' + (isUpdate ? 'Update Period' : 'Save Period'));
        }
    });
}

/**
 * Reset the period form to its initial state
 */
function resetPeriodForm() {
    $('#addPeriodForm')[0].reset();
    $('#periodId').val(''); // Clear the hidden period ID field
    $('#period-dates-changed').val('false');
    $('#useCustomDates').prop('checked', false);
    $('#addPeriodForm .is-invalid').removeClass('is-invalid');
    $('#addPeriodForm .invalid-feedback').remove();
    $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Period');
    $('#addPeriodModalLabel').text('Add Reporting Period'); // Reset modal title
}

/**
 * Get human-readable period name from quarter value
 */
function getPeriodName(quarter) {
    quarter = parseInt(quarter);
    switch(quarter) {
        case 1: return 'Q1';
        case 2: return 'Q2';
        case 3: return 'Q3';
        case 4: return 'Q4';
        case 5: return 'Half Yearly 1';
        case 6: return 'Half Yearly 2';
        default: return 'Unknown Period';
    }
}

/**
 * Update period number options based on selected period type
 */
function updatePeriodNumberOptions() {
    const periodType = $('#periodType').val();
    const periodNumberSelect = $('#periodNumber');
    
    // Clear existing options
    periodNumberSelect.empty();
    periodNumberSelect.append('<option value="" disabled selected>Select Number</option>');
    
    if (periodType === 'quarter') {
        // Quarters: 1-4
        for (let i = 1; i <= 4; i++) {
            periodNumberSelect.append(`<option value="${i}">${i}</option>`);
        }
    } else if (periodType === 'half') {
        // Half yearly: 1-2
        for (let i = 1; i <= 2; i++) {
            periodNumberSelect.append(`<option value="${i}">${i}</option>`);
        }
    } else if (periodType === 'yearly') {
        // Yearly: 1
        periodNumberSelect.append('<option value="1">1</option>');
    }
}

/**
 * Calculate and update date fields based on selected period type, number and year
 * If dates have been manually edited, ask for confirmation before overwriting
 */
function updateDateFields() {
    const periodType = $('#periodType').val();
    const periodNumber = $('#periodNumber').val();
    const year = $('#year').val();
    
    if (!periodType || !periodNumber || !year) {
        $('#startDate').val('');
        $('#endDate').val('');
        return;
    }
    
    // Check if dates are already set (might be custom)
    const currentStartDate = $('#startDate').val();
    const currentEndDate = $('#endDate').val();
    const datesAlreadySet = currentStartDate && currentEndDate;
    
    // Get the standard dates for this period type/number/year
    const standardDates = calculatePeriodDates(periodType, parseInt(periodNumber), parseInt(year));
    if (!standardDates) return;
    
    // If dates are already set and different from standard dates, they might be custom
    if (datesAlreadySet && 
        (currentStartDate !== standardDates.startDate || currentEndDate !== standardDates.endDate)) {
        
        // Store the current custom flag state
        const wasUsingCustomDates = $('#useCustomDates').prop('checked') || false;
        
        // Mark as using custom dates (this will be reset if the user chooses to use standard dates)
        $('#useCustomDates').prop('checked', true);
        
        // Only ask for confirmation if this is a change from standard to custom or if quarter/year changed
        // but custom dates were already in use
        if (!wasUsingCustomDates || $('#period-dates-changed').val() === 'true') {
            if (confirm('You have custom dates set. Do you want to reset to standard dates for this period?')) {
                // User wants standard dates
                $('#startDate').val(standardDates.startDate);
                $('#endDate').val(standardDates.endDate);
                $('#useCustomDates').prop('checked', false);
            }
        }
    } else {
        // Either no dates set yet, or they match standard - update without asking
        $('#startDate').val(standardDates.startDate);
        $('#endDate').val(standardDates.endDate);
        $('#useCustomDates').prop('checked', false);
    }
    
    // Reset the change tracker
    $('#period-dates-changed').val('false');
}

/**
 * Calculate start and end dates based on period type, number and year
 */
function calculatePeriodDates(periodType, periodNumber, year) {
    const dateRanges = {
        quarter: {
            1: { start: [0, 1], end: [2, 31] },     // Q1: Jan 1 - Mar 31
            2: { start: [3, 1], end: [5, 30] },     // Q2: Apr 1 - Jun 30
            3: { start: [6, 1], end: [8, 30] },     // Q3: Jul 1 - Sep 30
            4: { start: [9, 1], end: [11, 31] }     // Q4: Oct 1 - Dec 31
        },
        half: {
            1: { start: [0, 1], end: [5, 30] },     // Half Yearly 1: Jan 1 - Jun 30
            2: { start: [6, 1], end: [11, 31] }     // Half Yearly 2: Jul 1 - Dec 31
        },
        yearly: {
            1: { start: [0, 1], end: [11, 31] }     // Yearly: Jan 1 - Dec 31
        }
    };
    
    if (!dateRanges[periodType] || !dateRanges[periodType][periodNumber]) {
        return null;
    }
    
    const range = dateRanges[periodType][periodNumber];
    
    // Create start date
    const startDate = new Date(year, range.start[0], range.start[1]);
    
    // Create end date
    const endDate = new Date(year, range.end[0], range.end[1]);
    
    // Format dates as YYYY-MM-DD for input fields
    const formatDate = (date) => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };
    
    return {
        startDate: formatDate(startDate),
        endDate: formatDate(endDate)
    };
}

/**
 * Toggle period status
 */
function togglePeriodStatus(periodId, newStatus) {
    const confirmMessage = newStatus === 'open' 
        ? 'Are you sure you want to open this period? This will close all other open periods.'
        : 'Are you sure you want to close this period?';
        
    if (!confirm(confirmMessage)) {
        return;
    }
    
    $.ajax({
                    url: window.APP_URL + '/app/ajax/toggle_period_status.php',
        type: 'POST',
        data: {
            period_id: periodId,
            status: newStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showSuccess('Period status updated successfully!');
                loadPeriods(); // Reload the periods table
            } else {
                showError('Failed to update period status: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating period status:', error);
            showError('Failed to update period status. Please try again.');
        }
    });
}

/**
 * View period details (placeholder)
 */
function viewPeriodDetails(periodId) {
    // TODO: Implement period details view
    showInfo('Period details view not yet implemented.');
}

/**
 * Helper function to format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-MY', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Helper function to format datetime
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-MY', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Show success message
 */
function showSuccess(message) {
    // Use existing notification system or create toast
    if (typeof showNotification !== 'undefined') {
        showNotification(message, 'success');
    } else {
        alert('Success: ' + message);
    }
}

/**
 * Show error message
 */
function showError(message) {
    // Use existing notification system or create toast
    if (typeof showNotification !== 'undefined') {
        showNotification(message, 'error');
    } else {
        alert('Error: ' + message);
    }
}

/**
 * Show info message
 */
function showInfo(message) {
    // Use existing notification system or create toast
    if (typeof showNotification !== 'undefined') {
        showNotification(message, 'info');
    } else {
        alert('Info: ' + message);
    }
}

/**
 * Edit period - show modal with period data for editing
 */
function editPeriod(periodId) {
    // Show loading state
    const editButton = document.querySelector(`.edit-period-btn[data-period-id="${periodId}"]`);
    if (editButton) {
        editButton.disabled = true;
        editButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    // Fetch period details from server
            fetch(`${window.APP_URL}/app/ajax/periods_data.php?period_id=${periodId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success || !data.data) {
                throw new Error(data.message || 'Failed to load period details');
            }
            
            const period = data.data;
            
            // Update modal title to indicate editing
            $('#addPeriodModalLabel').text('Edit Reporting Period');
              // Populate form fields
            $('#periodId').val(period.period_id);
            $('#periodType').val(period.period_type);
            $('#periodNumber').val(period.period_number);
            $('#year').val(period.year);
            
            // Get just the date part without time
            const startDate = period.start_date.split(' ')[0];
            const endDate = period.end_date.split(' ')[0];
            
            $('#startDate').val(startDate);
            $('#endDate').val(endDate);
            $('#status').val(period.status);
            
            // Reset the period dates changed flag
            $('#period-dates-changed').val('false');
            
            // Change the save button text
            $('#savePeriod').text('Update Period');
            
            // Show the modal
            $('#addPeriodModal').modal('show');
        })
        .catch(error => {
            console.error('Error fetching period details:', error);
            alert(`Error: ${error.message}`);
        })
        .finally(() => {
            // Restore button state
            if (editButton) {
                editButton.disabled = false;
                editButton.innerHTML = '<i class="fas fa-edit"></i>';
            }
        });
}

/**
 * Delete period
 */
function deletePeriod(periodId) {
    if (!confirm('Are you sure you want to delete this period? This action cannot be undone.')) {
        return;
    }

    // Show loading state (optional, but good for UX)
    // You might need to adapt this to your specific UI for showing loading
    const deleteButton = document.querySelector(`.delete-period-btn[data-period-id="${periodId}"]`);
    if (deleteButton) {
        deleteButton.disabled = true;
        deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

            fetch(`${window.APP_URL}/app/ajax/delete_period.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded', // Standard for form data
            'X-Requested-With': 'XMLHttpRequest' // Often used to identify AJAX requests
        },
        body: `period_id=${periodId}`
    })
    .then(response => {
        const contentType = response.headers.get("content-type");
        if (response.ok && contentType && contentType.includes("application/json")) {
            return response.json();
        } else if (response.ok && (!contentType || !contentType.includes("application/json"))) {
            // It's a 2xx response, but not JSON. Read as text.
            return response.text().then(text => {
                // This might be an HTML error page or some other non-JSON string
                console.warn('Received non-JSON response:', text);
                throw new Error(`Server returned unexpected response (not JSON). Status: ${response.status}. Response: ${text.substring(0, 100)}...`);
            });
        } else {
            // Handle HTTP errors (4xx, 5xx)
            return response.text().then(text => {
                console.error(`HTTP error! Status: ${response.status}. Response: ${text}`);
                let errorMsg = `Failed to delete period. Status: ${response.status}.`;
                if (text) {
                    // Attempt to parse as JSON if it's an error, as the PHP might still send JSON errors
                    try {
                        const errorJson = JSON.parse(text);
                        if (errorJson && errorJson.message) {
                            errorMsg = errorJson.message;
                        } else {
                             errorMsg += ` Server response: ${text.substring(0, 200)}...`;
                        }
                    } catch (e) {
                        // If parsing fails, just use the raw text (or a snippet)
                        errorMsg += ` Server response: ${text.substring(0, 200)}...`;
                    }
                }
                throw new Error(errorMsg);
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Show success message (e.g., using a toast notification library)
            // For now, just log and reload
            console.log('Period deleted successfully');
            alert('Period deleted successfully!'); // Simple alert
            loadPeriods(); // Reload the periods list
        } else {
            // Show error message from server
            console.error('Error deleting period:', data.message);
            alert(`Error deleting period: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error in deletePeriod fetch:', error);
        alert(`An error occurred: ${error.message}`);
    })
    .finally(() => {
        // Restore button state
        if (deleteButton) {
            deleteButton.disabled = false;
            deleteButton.innerHTML = '<i class="fas fa-trash"></i>'; // Restore original icon
        }
    });
}

/**
 * Check if the current period dates overlap with another period
 * @param {string} startDate - Start date in YYYY-MM-DD format
 * @param {string} endDate - End date in YYYY-MM-DD format
 * @param {number|null} excludePeriodId - Period ID to exclude from the check (for updates)
 * @returns {Promise} Promise that resolves with an object indicating overlap status
 */
function checkDateOverlap(startDate, endDate, excludePeriodId = null) {
    return new Promise((resolve, reject) => {
        // Get current form values for period type information
        const periodType = $('#periodType').val();
        const periodNumber = $('#periodNumber').val();
        const year = $('#year').val();
        
        $.ajax({
            url: window.APP_URL + '/app/ajax/check_period_overlap.php',
            type: 'POST',
            data: {
                start_date: startDate,
                end_date: endDate,
                exclude_period_id: excludePeriodId,
                period_type: periodType,
                period_number: periodNumber,
                year: year
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    resolve({
                        overlaps: response.overlaps,
                        periods: response.periods || []
                    });
                } else {
                    reject(new Error(response.message || 'Error checking date overlap'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking date overlap:', error);
                reject(new Error('Failed to check for overlapping periods'));
            }
        });
    });
}
