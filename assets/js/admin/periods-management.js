/**
 * Admin Periods Management JavaScript
 * Handles AJAX operations for managing reporting periods
 */

$(document).ready(function() {
    // Load periods on page load
    loadPeriods();
    
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
    
    // Handle quarter and year changes to auto-calculate dates
    $('#quarter, #year').on('change', function() {
        updateDateFields();
    });
});

/**
 * Load periods data from server
 */
function loadPeriods() {
    const tableContainer = document.getElementById('periodsTable');
    
    // Show loading indicator
    tableContainer.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="mt-2 text-muted">Loading periods...</p>
        </div>
    `;
    
    fetch(`${APP_URL}/app/ajax/periods_data.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
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
                tableContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load periods'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error loading periods:', error);
            tableContainer.innerHTML = `<div class="alert alert-danger">Error loading periods: ${error.message}</div>`;
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
            // Sort by quarter within each year
            return a.quarter - b.quarter;
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
        if (period.quarter >= 1 && period.quarter <= 4) {
            periodName = `Q${period.quarter}`;
        } else if (period.quarter === 5) {
            periodName = 'Half Yearly 1';
        } else if (period.quarter === 6) {
            periodName = 'Half Yearly 2';
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
        quarter: $('#quarter').val(),
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
    
    // Validate quarter
    if (!formData.quarter) {
        $('#quarter').addClass('is-invalid');
        $('<div class="invalid-feedback">Please select a period type.</div>').insertAfter('#quarter');
        isValid = false;
    } else if (![1, 2, 3, 4, 5, 6, '1', '2', '3', '4', '5', '6'].includes(formData.quarter)) {
        $('#quarter').addClass('is-invalid');
        $('<div class="invalid-feedback">Invalid period type selected.</div>').insertAfter('#quarter');
        isValid = false;
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
    
    // For updates, we can skip the duplicate check and proceed directly
    if (isUpdate) {
        submitPeriodData(formData, isUpdate);
        return;
    }
    
    // For new periods, check for duplicates
    $.ajax({
        url: APP_URL + '/app/ajax/check_period_exists.php',
        type: 'POST',
        data: {
            quarter: formData.quarter,
            year: formData.year
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.exists) {
                    // Period already exists
                    showError(`A period for ${getPeriodName(formData.quarter)} ${formData.year} already exists.`);
                    $('#quarter, #year').addClass('is-invalid');
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
}

/**
 * Submit period data to the server after validation
 */
function submitPeriodData(formData, isUpdate = false) {
    // Determine which endpoint to use
    const endpoint = isUpdate ? 'update_period.php' : 'save_period.php';
    
    $.ajax({
        url: APP_URL + '/app/ajax/' + endpoint,
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
 * Calculate and update date fields based on selected quarter and year
 */
function updateDateFields() {
    const quarter = $('#quarter').val();
    const year = $('#year').val();
    
    if (!quarter || !year) {
        $('#startDate').val('');
        $('#endDate').val('');
        return;
    }
    
    const dates = calculatePeriodDates(parseInt(quarter), parseInt(year));
    if (dates) {
        $('#startDate').val(dates.startDate);
        $('#endDate').val(dates.endDate);
    }
}

/**
 * Calculate start and end dates based on quarter/period type and year
 */
function calculatePeriodDates(quarter, year) {
    const dateRanges = {
        1: { start: [0, 1], end: [2, 31] },     // Q1: Jan 1 - Mar 31
        2: { start: [3, 1], end: [5, 30] },     // Q2: Apr 1 - Jun 30
        3: { start: [6, 1], end: [8, 30] },     // Q3: Jul 1 - Sep 30
        4: { start: [9, 1], end: [11, 31] },    // Q4: Oct 1 - Dec 31
        5: { start: [0, 1], end: [5, 30] },     // Half Yearly 1: Jan 1 - Jun 30
        6: { start: [6, 1], end: [11, 31] }     // Half Yearly 2: Jul 1 - Dec 31
    };
    
    if (!dateRanges[quarter]) {
        return null;
    }
    
    const range = dateRanges[quarter];
    
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
        url: APP_URL + '/app/ajax/toggle_period_status.php',
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
    fetch(`${APP_URL}/app/ajax/periods_data.php?period_id=${periodId}`)
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
            $('#quarter').val(period.quarter);
            $('#year').val(period.year);
            $('#startDate').val(period.start_date.split(' ')[0]); // Just get the date part
            $('#endDate').val(period.end_date.split(' ')[0]); // Just get the date part
            $('#status').val(period.status);
            
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

    fetch(`${APP_URL}/app/ajax/delete_period.php`, {
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
 * Edit period (placeholder - implement as needed)
 */
