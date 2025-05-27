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
    
    // Handle form reset when modal is hidden
    $('#addPeriodModal').on('hidden.bs.modal', function() {
        $('#addPeriodForm')[0].reset();
        $('#addPeriodForm .is-invalid').removeClass('is-invalid');
        $('#addPeriodForm .invalid-feedback').remove();
        // Clear the date fields
        $('#startDate').val('');
        $('#endDate').val('');
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
    $.ajax({
        url: APP_URL + '/app/ajax/periods_data.php',
        type: 'GET',
        dataType: 'text', // Changed from 'json' to 'text' for better error debugging
        success: function(data) {
            console.log('Raw response:', data); // Debug logging
            
            try {
                const response = JSON.parse(data);
                if (response.success) {
                    displayPeriods(response.data);
                } else {
                    showError('Failed to load periods: ' + (response.message || 'Unknown error'));
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response data:', data);
                showError('Invalid response from server. Check console for details.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
            });
            showError('Failed to load periods: ' + error + ' (Status: ' + xhr.status + ')');
        }
    });
}

/**
 * Display periods in the table
 */
function displayPeriods(periods) {
    let html = '';
    
    if (periods.length === 0) {
        html = `
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Reporting Periods Found</h5>
                <p class="text-muted">Click "Add New Period" to create your first reporting period.</p>
            </div>
        `;
    } else {
        html = `
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Period</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
          periods.forEach(function(period) {
            const statusClass = period.status === 'open' ? 'success' : 'secondary';
            const statusIcon = period.status === 'open' ? 'unlock' : 'lock';
            const toggleText = period.status === 'open' ? 'Close' : 'Open';
            const toggleClass = period.status === 'open' ? 'warning' : 'success';
            
            // Get proper display name for period
            let periodDisplay;
            if (period.quarter >= 1 && period.quarter <= 4) {
                periodDisplay = `Q${period.quarter} ${period.year}`;
            } else if (period.quarter == 5) {
                periodDisplay = `Half Yearly 1 ${period.year}`;
            } else if (period.quarter == 6) {
                periodDisplay = `Half Yearly 2 ${period.year}`;
            } else {
                periodDisplay = `Period ${period.quarter} ${period.year}`;
            }
            
            html += `
                <tr>
                    <td>
                        <strong>${periodDisplay}</strong>
                    </td>
                    <td>${formatDate(period.start_date)}</td>
                    <td>${formatDate(period.end_date)}</td>
                    <td>
                        <span class="badge bg-${statusClass}">
                            <i class="fas fa-${statusIcon} me-1"></i>
                            ${period.status.charAt(0).toUpperCase() + period.status.slice(1)}
                        </span>
                    </td>
                    <td>${formatDateTime(period.created_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-${toggleClass}" 
                                onclick="togglePeriodStatus(${period.period_id}, '${period.status === 'open' ? 'closed' : 'open'}')">
                            <i class="fas fa-${period.status === 'open' ? 'lock' : 'unlock'} me-1"></i>
                            ${toggleText}
                        </button>
                        <button class="btn btn-sm btn-outline-info ms-1" 
                                onclick="viewPeriodDetails(${period.period_id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
    }
    
    $('#periodsTable').html(html);
}

/**
 * Save new period
 */
function savePeriod() {
    const formData = {
        quarter: $('#quarter').val(),
        year: $('#year').val(),
        start_date: $('#startDate').val(),
        end_date: $('#endDate').val(),
        status: $('#status').val()
    };
    
    // Basic validation
    if (!formData.quarter || !formData.year || !formData.start_date || !formData.end_date) {
        showError('Please fill in all required fields.');
        return;
    }
    
    // Check if end date is after start date
    if (new Date(formData.end_date) <= new Date(formData.start_date)) {
        showError('End date must be after start date.');
        return;
    }
    
    $('#savePeriod').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
    
    $.ajax({
        url: APP_URL + '/app/ajax/save_period.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addPeriodModal').modal('hide');
                showSuccess('Period created successfully!');
                loadPeriods(); // Reload the periods table
            } else {
                showError('Failed to create period: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving period:', error);
            showError('Failed to save period. Please try again.');
        },
        complete: function() {
            $('#savePeriod').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Period');
        }
    });
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
