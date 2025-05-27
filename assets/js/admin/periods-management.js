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
            
            html += `
                <tr>
                    <td>
                        <strong>Q${period.quarter} ${period.year}</strong>
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
        period_name: $('#periodName').val(),
        start_date: $('#startDate').val(),
        end_date: $('#endDate').val(),
        status: $('#status').val()
    };
    
    // Basic validation
    if (!formData.period_name || !formData.start_date || !formData.end_date) {
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
