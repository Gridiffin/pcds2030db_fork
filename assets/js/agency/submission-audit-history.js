/**
 * Submission Audit History Manager
 * Handles loading and displaying audit history for submissions
 */
class SubmissionAuditHistory {
    constructor() {
        this.modal = null;
        this.currentSubmissionId = null;
        this.isLoading = false;
    }

    /**
     * Initialize the audit history functionality
     */
    init() {
        this.createModal();
        this.bindEvents();
    }

    /**
     * Create the audit history modal
     */
    createModal() {
        const modalHTML = `
            <div class="audit-history-modal" id="auditHistoryModal" style="display: none;">
                <div class="audit-history-content">
                    <div class="audit-history-header">
                        <div>
                            <h3 class="audit-history-title">Submission History</h3>
                            <p class="audit-history-subtitle" id="auditHistorySubtitle"></p>
                        </div>
                        <button class="audit-history-close" id="auditHistoryClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="audit-history-body" id="auditHistoryBody">
                        <div class="audit-history-loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading audit history...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to body if it doesn't exist
        if (!document.getElementById('auditHistoryModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        this.modal = document.getElementById('auditHistoryModal');
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Close modal events
        document.getElementById('auditHistoryClose').addEventListener('click', () => {
            this.hideModal();
        });

        // Close on backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.hideModal();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.style.display !== 'none') {
                this.hideModal();
            }
        });
    }

    /**
     * Show audit history for a submission
     */
    async showAuditHistory(submissionId, submissionInfo = {}) {
        if (this.isLoading) return;

        this.currentSubmissionId = submissionId;
        this.showModal();
        this.showLoading();

        try {
            const data = await this.loadAuditHistory(submissionId);
            this.displayAuditHistory(data, submissionInfo);
        } catch (error) {
            this.showError(error.message);
        }
    }

    /**
     * Load audit history from server
     */
    async loadAuditHistory(submissionId) {
        const response = await fetch(`${window.APP_URL}/app/ajax/get_submission_audit_history.php?submission_id=${submissionId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to load audit history');
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Failed to load audit history');
        }

        return data.data;
    }

    /**
     * Display audit history in the modal
     */
    displayAuditHistory(data, submissionInfo) {
        const { submission_info, audit_history, total_changes } = data;
        
        // Update subtitle
        const subtitle = document.getElementById('auditHistorySubtitle');
        subtitle.textContent = `${submission_info.program_name} - ${submission_info.period_name} ${submission_info.year}`;

        // Generate timeline HTML
        const timelineHTML = this.generateTimelineHTML(audit_history, total_changes);
        
        // Update modal body
        const body = document.getElementById('auditHistoryBody');
        body.innerHTML = timelineHTML;

        // Bind timeline events
        this.bindTimelineEvents();
    }

    /**
     * Generate timeline HTML from audit history data
     */
    generateTimelineHTML(auditHistory, totalChanges) {
        if (!auditHistory || auditHistory.length === 0) {
            return `
                <div class="audit-history-empty">
                    <i class="fas fa-history"></i>
                    <h4>No History Available</h4>
                    <p>No changes have been recorded for this submission yet.</p>
                </div>
            `;
        }

        const timelineItems = auditHistory.map((entry, index) => {
            return this.generateTimelineItemHTML(entry, index);
        }).join('');

        return `
            <div class="audit-timeline">
                <div class="audit-timeline-summary" style="margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 6px;">
                    <strong>Total Changes:</strong> ${totalChanges} | 
                    <strong>Last Modified:</strong> ${this.formatDate(auditHistory[0].timestamp)}
                </div>
                ${timelineItems}
            </div>
        `;
    }

    /**
     * Generate HTML for a single timeline item
     */
    generateTimelineItemHTML(entry, index) {
        const actionClass = entry.action === 'create_submission' ? 'create' : 'update';
        const actionLabel = entry.action === 'create_submission' ? 'Created' : 'Updated';
        const actionColor = entry.action === 'create_submission' ? '#28a745' : '#ffc107';

        const fieldChangesHTML = this.generateFieldChangesHTML(entry.field_changes);
        const userInitials = this.getUserInitials(entry.user_name);

        return `
            <div class="audit-timeline-item ${actionClass}" data-audit-id="${entry.audit_id}">
                <div class="audit-timeline-content">
                    <div class="audit-timeline-header">
                        <div class="audit-timeline-user">
                            <div class="audit-user-avatar" style="background: ${actionColor};">
                                ${userInitials}
                            </div>
                            <div class="audit-user-info">
                                <h4>${entry.user_name}</h4>
                                <p>${entry.user_agency || 'Unknown Agency'}</p>
                            </div>
                        </div>
                        <div class="audit-timeline-meta">
                            <div class="audit-timestamp">${this.formatDate(entry.timestamp)}</div>
                            <span class="audit-action-badge ${actionClass}">${actionLabel}</span>
                        </div>
                    </div>
                    
                    <div class="audit-timeline-summary">
                        ${entry.summary}
                    </div>
                    
                    ${entry.field_changes.length > 0 ? `
                        <div class="audit-field-changes">
                            <button class="audit-field-changes-toggle" onclick="submissionAuditHistory.toggleFieldChanges(${entry.audit_id})">
                                <i class="fas fa-chevron-down"></i> Show ${entry.field_changes.length} field change(s)
                            </button>
                            <div class="audit-field-changes-content" id="fieldChanges_${entry.audit_id}">
                                ${fieldChangesHTML}
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    /**
     * Generate HTML for field changes
     */
    generateFieldChangesHTML(fieldChanges) {
        return fieldChanges.map(change => {
            const changeTypeClass = change.change_type;
            const changeTypeLabel = change.change_type.charAt(0).toUpperCase() + change.change_type.slice(1);

            if (change.change_type === 'added') {
                return `
                    <div class="audit-field-change">
                        <div class="audit-field-label">${change.field_label}</div>
                        <div class="audit-field-values">
                            <div class="audit-field-new">${this.formatValue(change.new_value)}</div>
                        </div>
                        <span class="audit-change-type ${changeTypeClass}">${changeTypeLabel}</span>
                    </div>
                `;
            } else if (change.change_type === 'removed') {
                return `
                    <div class="audit-field-change">
                        <div class="audit-field-label">${change.field_label}</div>
                        <div class="audit-field-values">
                            <div class="audit-field-old">${this.formatValue(change.old_value)}</div>
                        </div>
                        <span class="audit-change-type ${changeTypeClass}">${changeTypeLabel}</span>
                    </div>
                `;
            } else {
                return `
                    <div class="audit-field-change">
                        <div class="audit-field-label">${change.field_label}</div>
                        <div class="audit-field-values">
                            <div class="audit-field-old">${this.formatValue(change.old_value)}</div>
                            <div class="audit-field-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="audit-field-new">${this.formatValue(change.new_value)}</div>
                        </div>
                        <span class="audit-change-type ${changeTypeClass}">${changeTypeLabel}</span>
                    </div>
                `;
            }
        }).join('');
    }

    /**
     * Toggle field changes visibility
     */
    toggleFieldChanges(auditId) {
        const content = document.getElementById(`fieldChanges_${auditId}`);
        const toggle = content.previousElementSibling;
        const icon = toggle.querySelector('i');

        if (content.classList.contains('show')) {
            content.classList.remove('show');
            icon.className = 'fas fa-chevron-down';
            toggle.innerHTML = `<i class="fas fa-chevron-down"></i> Show field changes`;
        } else {
            content.classList.add('show');
            icon.className = 'fas fa-chevron-up';
            toggle.innerHTML = `<i class="fas fa-chevron-up"></i> Hide field changes`;
        }
    }

    /**
     * Bind timeline-specific events
     */
    bindTimelineEvents() {
        // Any additional timeline events can be added here
    }

    /**
     * Show loading state
     */
    showLoading() {
        const body = document.getElementById('auditHistoryBody');
        body.innerHTML = `
            <div class="audit-history-loading">
                <i class="fas fa-spinner"></i>
                <p>Loading audit history...</p>
            </div>
        `;
    }

    /**
     * Show error state
     */
    showError(message) {
        const body = document.getElementById('auditHistoryBody');
        body.innerHTML = `
            <div class="audit-history-empty">
                <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                <h4>Error Loading History</h4>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="submissionAuditHistory.showAuditHistory(${this.currentSubmissionId})">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            </div>
        `;
    }

    /**
     * Show the modal
     */
    showModal() {
        this.modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    /**
     * Hide the modal
     */
    hideModal() {
        this.modal.style.display = 'none';
        document.body.style.overflow = '';
        this.currentSubmissionId = null;
    }

    /**
     * Format date for display
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Format field values for display
     */
    formatValue(value) {
        if (value === null || value === undefined || value === '') {
            return '<em>Empty</em>';
        }
        
        // Handle date formatting
        if (this.isDate(value)) {
            return new Date(value).toLocaleDateString();
        }
        
        // Truncate long text
        if (typeof value === 'string' && value.length > 100) {
            return value.substring(0, 100) + '...';
        }
        
        return value;
    }

    /**
     * Check if value is a date
     */
    isDate(value) {
        if (typeof value !== 'string') return false;
        const date = new Date(value);
        return date instanceof Date && !isNaN(date);
    }

    /**
     * Get user initials from name
     */
    getUserInitials(name) {
        if (!name) return '?';
        return name.split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }
}

// Initialize the audit history manager
const submissionAuditHistory = new SubmissionAuditHistory();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    submissionAuditHistory.init();
});

// Export for use in other scripts
window.submissionAuditHistory = submissionAuditHistory; 