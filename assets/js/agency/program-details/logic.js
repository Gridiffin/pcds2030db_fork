/**
 * Program Details Logic Module
 * 
 * Contains pure business logic and API interactions for program details.
 * This module is testable and reusable.
 */

export class ProgramDetailsLogic {
    constructor(controller) {
        this.controller = controller;
        this.programId = controller.programId;
        this.APP_URL = controller.APP_URL;
        this.cache = new Map();
    }

    /**
     * Initialize logic module
     */
    init() {
        console.log('Initializing Program Details Logic...');
    }

    /**
     * Load program status information
     */
    async loadProgramStatus() {
        try {
            const data = await this.controller.apiCall(
                `/app/api/program_status.php?action=status&program_id=${this.programId}`
            );
            
            this.renderStatus(data);
            return data;
        } catch (error) {
            console.error('Error loading program status:', error);
            throw error;
        }
    }

    /**
     * Render program status badge and information
     */
    renderStatus(data) {
        const badge = document.getElementById('program-status-badge');
        const holdInfo = document.getElementById('hold-point-info');
        
        if (!badge) return;

        const status = data.status || 'active';
        const statusInfo = this.getStatusInfo(status);
        
        // Update badge
        badge.textContent = statusInfo.label;
        badge.className = `badge status-badge bg-${statusInfo.class} py-2 px-3`;
        
        // Update icon if available
        const icon = badge.querySelector('i');
        if (icon) {
            icon.className = `${statusInfo.icon} me-1`;
        }

        // Handle hold point information
        if (holdInfo && data.active_hold_points && data.active_hold_points.length > 0) {
            this.renderHoldPointInfo(holdInfo, data.active_hold_points);
        }
    }

    /**
     * Get status information for display
     */
    getStatusInfo(status) {
        const statusMap = {
            'active': {
                label: 'Active',
                class: 'success',
                icon: 'fas fa-check-circle'
            },
            'on_hold': {
                label: 'On Hold',
                class: 'warning',
                icon: 'fas fa-pause-circle'
            },
            'cancelled': {
                label: 'Cancelled',
                class: 'danger',
                icon: 'fas fa-times-circle'
            },
            'completed': {
                label: 'Completed',
                class: 'info',
                icon: 'fas fa-flag-checkered'
            },
            'not_started': {
                label: 'Not Started',
                class: 'secondary',
                icon: 'fas fa-clock'
            }
        };

        return statusMap[status] || statusMap['active'];
    }

    /**
     * Render hold point information
     */
    renderHoldPointInfo(container, holdPoints) {
        let html = '<div class="alert alert-warning mt-2">';
        html += '<i class="fas fa-exclamation-triangle me-2"></i>';
        html += '<strong>Program is currently on hold:</strong><br>';
        
        holdPoints.forEach(hp => {
            html += `<small>• ${hp.reason}`;
            if (hp.remarks) {
                html += ` (${hp.remarks})`;
            }
            html += '</small><br>';
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    /**
     * Load program statistics
     */
    async loadStatistics() {
        try {
            const cacheKey = `stats_${this.programId}`;
            
            // Check cache first
            if (this.cache.has(cacheKey)) {
                return this.cache.get(cacheKey);
            }

            const data = await this.controller.apiCall(
                `/app/ajax/get_program_stats.php?program_id=${this.programId}`
            );
            
            // Cache the result
            this.cache.set(cacheKey, data);
            
            this.updateStatistics(data);
            return data;
        } catch (error) {
            console.error('Error loading statistics:', error);
            // Don't throw for statistics as it's not critical
        }
    }

    /**
     * Update statistics display
     */
    updateStatistics(data) {
        if (!data) return;

        // Update last activity if element exists
        const lastActivityElement = document.getElementById('last-activity-value');
        if (lastActivityElement && data.last_activity) {
            lastActivityElement.textContent = this.formatDate(data.last_activity);
        }

        // Update submission count badge if exists
        const submissionCountElement = document.querySelector('.stat-item .badge.bg-primary');
        if (submissionCountElement && data.submission_count !== undefined) {
            submissionCountElement.textContent = data.submission_count;
        }

        // Update targets count badge if exists
        const targetsCountElement = document.querySelector('.stat-item .badge.bg-info');
        if (targetsCountElement && data.targets_count !== undefined) {
            targetsCountElement.textContent = data.targets_count;
        }

        // Update attachments count badge if exists
        const attachmentsCountElement = document.querySelector('.stat-item .badge.bg-secondary');
        if (attachmentsCountElement && data.attachments_count !== undefined) {
            attachmentsCountElement.textContent = data.attachments_count;
        }
    }

    /**
     * Delete a draft submission
     */
    async deleteDraftSubmission(submissionId) {
        try {
            const data = await this.controller.apiCall(
                '/app/ajax/delete_submission.php',
                {
                    method: 'POST',
                    body: JSON.stringify({
                        submission_id: submissionId,
                        program_id: this.programId
                    })
                }
            );

            if (data.success) {
                this.controller.showSuccess('Draft submission deleted successfully');
                // Refresh the page to update the display
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to delete submission');
            }

            return data;
        } catch (error) {
            console.error('Error deleting draft submission:', error);
            this.controller.showError('Failed to delete submission: ' + error.message);
            throw error;
        }
    }

    /**
     * Load submission history
     */
    async loadSubmissionHistory() {
        try {
            const cacheKey = `history_${this.programId}`;
            
            if (this.cache.has(cacheKey)) {
                return this.cache.get(cacheKey);
            }

            const data = await this.controller.apiCall(
                `/app/ajax/get_program_submission.php?program_id=${this.programId}&include_history=1`
            );
            
            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('Error loading submission history:', error);
            throw error;
        }
    }

    /**
     * Utility method to format dates
     */
    formatDate(dateString) {
        if (!dateString) return 'Never';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        } catch (error) {
            return dateString;
        }
    }

    /**
     * Utility method to format time ago
     */
    timeAgo(dateString) {
        if (!dateString) return 'Never';
        
        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
            if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
            
            return this.formatDate(dateString);
        } catch (error) {
            return dateString;
        }
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Validate program ID
     */
    isValidProgramId(programId) {
        return programId && !isNaN(programId) && parseInt(programId) > 0;
    }

    /**
     * Get file icon based on file type
     */
    getFileIcon(mimeType) {
        const iconMap = {
            'application/pdf': 'fa-file-pdf',
            'application/msword': 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fa-file-word',
            'application/vnd.ms-excel': 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'fa-file-excel',
            'application/vnd.ms-powerpoint': 'fa-file-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'fa-file-powerpoint',
            'text/plain': 'fa-file-alt',
            'text/csv': 'fa-file-csv',
            'image/jpeg': 'fa-file-image',
            'image/png': 'fa-file-image',
            'image/gif': 'fa-file-image',
            'application/zip': 'fa-file-archive',
            'application/x-rar-compressed': 'fa-file-archive'
        };

        return iconMap[mimeType] || 'fa-file';
    }
}
