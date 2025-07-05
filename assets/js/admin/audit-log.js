/**
 * Audit Log Management
 * 
 * Handles loading, filtering, pagination, and export of audit logs.
 */

document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let totalPages = 1;
    let isLoading = false;
    
    // Initialize the audit log interface
    initAuditLog();
    
    function initAuditLog() {
        // Load initial audit logs
        loadAuditLogs();
        
    // Set up event listeners
    setupEventListeners();
    
    // Set default date range (last 30 days)
    setDefaultDateRange();
    
    // Add refresh button
    addRefreshButton();
    }
    
    function setupEventListeners() {
        // Filter form submission
        const filterForm = document.getElementById('auditFilter');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                currentPage = 1;
                loadAuditLogs();
            });
        }
        
        // Clear filters button
        const clearFiltersBtn = document.getElementById('clearFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                clearFilters();
                currentPage = 1;
                loadAuditLogs();
            });
        }
        
        // Export logs button
        const exportBtn = document.getElementById('exportLogs');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                exportAuditLogs();
            });
        }
        
        // Export button in header actions
        const exportLogBtn = document.getElementById('exportLogBtn');
        if (exportLogBtn) {
            exportLogBtn.addEventListener('click', function() {
                exportAuditLogs();
            });
        }
        
        // Clear filters button in header actions
        const clearFiltersHeaderBtn = document.getElementById('clearFiltersBtn');
        if (clearFiltersHeaderBtn) {
            clearFiltersHeaderBtn.addEventListener('click', function() {
                clearFilters();
                currentPage = 1;
                loadAuditLogs();
            });
        }

        // Set up pagination click event delegation
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('page-link')) {
                e.preventDefault();
                const page = parseInt(e.target.getAttribute('data-page') || '1', 10);
                if (!isNaN(page) && page > 0) {
                    currentPage = page;
                    loadAuditLogs();
                }
            }
        });
    }
    
    function setDefaultDateRange() {
        const dateFrom = document.getElementById('filterDate');
        const dateTo = document.getElementById('filterDateTo');
        
        if (dateFrom && dateTo) {
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            
            dateFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
        }
    }
    
    function clearFilters() {
        const filterForm = document.getElementById('auditFilter');
        if (filterForm) {
            filterForm.reset();
            setDefaultDateRange();
        }
    }
      function loadAuditLogs() {
        if (isLoading) return;
        
        isLoading = true;
        showLoadingState();
        
        // Clear any existing errors
        const alertContainer = document.getElementById('auditLogAlertContainer');
        if (alertContainer) {
            alertContainer.innerHTML = '';
        }
        
        const formData = new FormData();
        
        // Get filter values
        const filterForm = document.getElementById('auditFilter');
        if (filterForm) {
            const formDataObj = new FormData(filterForm);
            for (let [key, value] of formDataObj.entries()) {
                if (value.trim() !== '') {
                    formData.append(key, value);
                }
            }
        }
        
        // Add pagination
        formData.append('page', currentPage);
        formData.append('limit', 25);
        
        fetch(APP_URL + '/app/ajax/load_audit_logs.php', {
            method: 'POST',
            body: formData,
            // Add proper headers to ensure consistent content type expectations
            headers: {
                'Accept': 'application/json'
            }
        })        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response error: ${response.status} ${response.statusText}`);
            }
            return response.text(); // Get the raw text first to handle potential invalid JSON
        })
        .then(text => {
            try {
                // Try to parse the JSON response
                // If there's any prefix character (like 'i'), try to remove it
                let cleanedText = text;
                if (text && typeof text === 'string' && !text.startsWith('{') && !text.startsWith('[')) {
                    // Remove any non-JSON prefix characters
                    cleanedText = text.substring(text.indexOf('{'));
                    console.warn('Found and removed non-JSON prefix in server response');
                }
                return JSON.parse(cleanedText);
            } catch (e) {
                // If JSON parsing fails, show the error and response for debugging
                console.error('JSON Parse Error:', e);
                console.error('Response Text:', text);
                throw new Error('Invalid JSON response from server. See console for details.');
            }
        })
        .then(data => {
            if (data.success) {
                displayAuditLogs(data.logs);
                displayPagination(data.pagination);
                // Update current page and total pages
                currentPage = data.pagination.current_page;
                totalPages = data.pagination.total_pages;
            } else {
                showError('Failed to load audit logs: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError(`Failed to load audit logs: ${error.message}`);
        })
        .finally(() => {
            isLoading = false;
        });
    }
    
    function showLoadingState() {
        const tableContainer = document.getElementById('auditLogTable');
        if (tableContainer) {
            tableContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Loading audit logs...</p>
                </div>
            `;
        }
    }
    
    function displayAuditLogs(logs) {
        const tableContainer = document.getElementById('auditLogTable');
        if (!tableContainer) return;
        
        if (!logs || logs.length === 0) {
            tableContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-search fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">No audit logs found matching your criteria.</p>
                </div>
            `;
            return;
        }
        
        let tableHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Field Changes</th>
                            <th>IP Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        logs.forEach(log => {
            // Ensure values exist to prevent undefined errors
            const userName = log.user_name || 'System';
            const action = log.action || 'Unknown';
            const details = log.details || 'No details available';
            const ipAddress = log.ip_address || 'Unknown';
            const status = log.status || 'Unknown';
            const formattedDate = log.formatted_date || 'Unknown Date';
            const actionBadge = log.action_badge || 'secondary';
            const statusBadge = log.status_badge || 'secondary';
            const fieldChangesCount = log.field_changes_count || 0;
            const fieldChangesSummary = log.field_changes_summary || '';
            
            tableHTML += `
                <tr>
                    <td>
                        <small class="text-muted">${formattedDate}</small>
                    </td>
                    <td>
                        <span class="fw-medium">${escapeHtml(userName)}</span>
                    </td>
                    <td>
                        <span class="badge bg-${actionBadge}">${escapeHtml(action)}</span>
                    </td>
                    <td>
                        <small class="text-muted" title="${escapeHtml(details)}">
                            ${truncateText(escapeHtml(details), 100)}
                        </small>
                        ${details.length > 100 ? `<a href="#" class="view-details-link" data-details="${escapeHtml(details)}">View all</a>` : ''}
                    </td>
                    <td>
                        ${fieldChangesCount > 0 ? 
                            `<button class="btn btn-sm btn-outline-info view-field-changes" data-audit-id="${log.id}" data-changes-count="${fieldChangesCount}">
                                <i class="fas fa-list-ul me-1"></i>${fieldChangesCount} changes
                            </button>` : 
                            '<span class="text-muted">No changes</span>'
                        }
                    </td>
                    <td>
                        <small class="text-muted">${escapeHtml(ipAddress)}</small>
                    </td>
                    <td>
                        <span class="badge bg-${statusBadge}">
                            ${status === 'success' ? 'Success' : 'Failed'}
                        </span>
                    </td>
                </tr>
            `;
        });
        
        tableHTML += `
                    </tbody>
                </table>
            </div>
        `;
        
        tableContainer.innerHTML = tableHTML;
        
        // Add event listeners to view-details links
        const detailLinks = tableContainer.querySelectorAll('.view-details-link');
        detailLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const details = this.getAttribute('data-details');
                showDetailsModal(details);
            });
        });
        
        // Add event listeners to view-field-changes buttons
        const fieldChangeButtons = tableContainer.querySelectorAll('.view-field-changes');
        fieldChangeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const auditId = this.getAttribute('data-audit-id');
                const changesCount = this.getAttribute('data-changes-count');
                showFieldChangesModal(auditId, changesCount);
            });
        });
    }
    
    function displayPagination(pagination) {
        const paginationContainer = document.getElementById('paginationContainer');
        if (!paginationContainer) return;
        
        let paginationHTML = '';
        
        if (pagination.total_pages > 1) {
            paginationHTML = `
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing ${pagination.showing_from} to ${pagination.showing_to} of ${pagination.total_records} entries
                    </div>
                    <nav aria-label="Audit log pagination">
                        <ul class="pagination pagination-sm mb-0">
            `;
            
            // Previous button
            const prevDisabled = pagination.current_page <= 1 ? 'disabled' : '';
            paginationHTML += `
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                </li>
            `;
            
            // Page numbers
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
            
            if (startPage > 1) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>
                `;
                if (startPage > 2) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const active = i === pagination.current_page ? 'active' : '';
                paginationHTML += `
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }
            
            if (endPage < pagination.total_pages) {
                if (endPage < pagination.total_pages - 1) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${pagination.total_pages}">${pagination.total_pages}</a>
                    </li>
                `;
            }
            
            // Next button
            const nextDisabled = pagination.current_page >= pagination.total_pages ? 'disabled' : '';
            paginationHTML += `
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                </li>
            `;
            
            paginationHTML += `
                        </ul>
                    </nav>
                </div>
            `;
        } else if (pagination.total_records > 0) {
            paginationHTML = `
                <div class="text-muted mt-3">
                    Showing ${pagination.showing_from} to ${pagination.showing_to} of ${pagination.total_records} entries
                </div>
            `;
        }
        
        paginationContainer.innerHTML = paginationHTML;
    }
    
    function exportAuditLogs() {
        // Get current filters
        const formData = new FormData();
        const filterForm = document.getElementById('auditFilter');
        
        if (filterForm) {
            const formDataObj = new FormData(filterForm);
            for (let [key, value] of formDataObj.entries()) {
                if (value.trim() !== '') {
                    formData.append(key, value);
                }
            }
        }
          // Show export in progress messages
        // Find both export buttons and update their state
        const exportButtons = [
            document.getElementById('exportLogs'),
            document.getElementById('exportLogBtn')
        ];
        
        const originalBtnTexts = [];
        exportButtons.forEach((btn, index) => {
            if (btn) {
                originalBtnTexts[index] = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Exporting...`;
            }
        });
        
        // Show export message
        showExportMessage(true, 'Preparing export...');
        
        // Make the request to export logs
        fetch(APP_URL + '/app/ajax/export_audit_logs.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Export failed');
            }
            return response.blob();
        })
        .then(blob => {
            // Create a download link and click it
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            
            // Get current date for filename
            const date = new Date();
            const dateStr = date.toISOString().split('T')[0];
            
            a.href = url;
            a.download = `audit_logs_export_${dateStr}.csv`;
            document.body.appendChild(a);
            a.click();            window.URL.revokeObjectURL(url);
            
            // Show success message
            showSuccess('Audit logs exported successfully');
            showExportMessage(false);
        })
        .catch(error => {
            console.error('Export error:', error);
            showError('Failed to export audit logs: ' + error.message);
            showExportMessage(false, 'Export failed. Please try again.');
        })
        .finally(() => {
            // Restore all button states
            exportButtons.forEach((btn, index) => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnTexts[index];
                }
            });
        });
    }
    
    function addRefreshButton() {
        const cardHeader = document.querySelector('.card-header');
        if (cardHeader) {
            const refreshButton = document.createElement('button');
            refreshButton.className = 'btn btn-sm btn-outline-primary float-end';
            refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            refreshButton.id = 'refreshLogs';
            refreshButton.addEventListener('click', function(e) {
                e.preventDefault();
                loadAuditLogs();
            });
            cardHeader.appendChild(refreshButton);
        }
    }
    
    function showError(message) {
        // Create or update error alert
        let alertContainer = document.getElementById('auditLogAlertContainer');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'auditLogAlertContainer';
            alertContainer.className = 'mb-3';
            
            const tableContainer = document.getElementById('auditLogTable');
            if (tableContainer) {
                tableContainer.parentNode.insertBefore(alertContainer, tableContainer);
            }
        }
        
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
    
    function showSuccess(message) {
        // Create a floating alert that auto-dismisses
        const alertId = 'successAlert' + Date.now();
        
        const alertDiv = document.createElement('div');
        alertDiv.id = alertId;
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(document.getElementById(alertId));
            if (alert) {
                alert.close();
            }
            
            // Remove from DOM after animation
            setTimeout(() => {
                if (document.getElementById(alertId)) {
                    document.getElementById(alertId).remove();
                }
            }, 500);
        }, 3000);
    }
    
    function showExportMessage(isShowing, message = 'Preparing audit log export...') {
        let messageContainer = document.getElementById('exportMessageContainer');
        
        if (isShowing) {
            if (!messageContainer) {
                messageContainer = document.createElement('div');
                messageContainer.id = 'exportMessageContainer';
                messageContainer.className = 'alert alert-info alert-dismissible fade show';
                messageContainer.innerHTML = `
                    <i class="fas fa-file-download me-2"></i>
                    <span id="exportMessageText">${message}</span>
                `;
                
                const alertContainer = document.getElementById('auditLogAlertContainer');
                if (alertContainer) {
                    alertContainer.appendChild(messageContainer);
                }
            } else {
                document.getElementById('exportMessageText').textContent = message;
            }
        } else if (messageContainer) {
            // Use setTimeout to let users see the success message briefly
            setTimeout(() => {
                messageContainer.remove();
            }, 1500);
        }
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    function truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength) + '...';
    }
    
    function showDetailsModal(details) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('detailsModal');
        if (!modal) {
            const modalHTML = `
                <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Log Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <pre id="detailsContent" class="border p-3 bg-light" style="white-space: pre-wrap;"></pre>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            modal = document.getElementById('detailsModal');
        }
        
        // Set the details content
        const detailsContent = document.getElementById('detailsContent');
        if (detailsContent) {
            detailsContent.textContent = details;
        }
        
        // Show the modal
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    }
    
    function showFieldChangesModal(auditId, changesCount) {
        // Show loading state
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Field Changes (${changesCount} changes)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="mt-2 text-muted">Loading field changes...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Fetch field changes
        fetch(`${APP_URL}/app/ajax/get_audit_field_changes.php?audit_log_id=${auditId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = generateFieldChangesHTML(data.field_changes);
                } else {
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Failed to load field changes: ${data.error || 'Unknown error'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading field changes:', error);
                const modalBody = modal.querySelector('.modal-body');
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load field changes: ${error.message}
                    </div>
                `;
            });
        
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(modal);
        });
    }
    
    function generateFieldChangesHTML(fieldChanges) {
        if (!fieldChanges || fieldChanges.length === 0) {
            return `
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">No field changes found for this audit log entry.</p>
                </div>
            `;
        }
        
        let html = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Field</th>
                            <th>Change Type</th>
                            <th>Old Value</th>
                            <th>New Value</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        fieldChanges.forEach(change => {
            const changeTypeBadge = getChangeTypeBadge(change.change_type);
            const oldValue = change.old_value || '<em class="text-muted">null</em>';
            const newValue = change.new_value || '<em class="text-muted">null</em>';
            
            html += `
                <tr>
                    <td><strong>${escapeHtml(change.field_name)}</strong></td>
                    <td>${changeTypeBadge}</td>
                    <td><code>${oldValue}</code></td>
                    <td><code>${newValue}</code></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        return html;
    }
    
    function getChangeTypeBadge(changeType) {
        const badges = {
            'added': '<span class="badge bg-success">Added</span>',
            'modified': '<span class="badge bg-warning">Modified</span>',
            'removed': '<span class="badge bg-danger">Removed</span>'
        };
        
        return badges[changeType] || `<span class="badge bg-secondary">${changeType}</span>`;
    }
});