/**
 * Program Details Modals Module
 * 
 * Handles all modal interactions for the program details page.
 */

export class ProgramDetailsModals {
    constructor(controller) {
        this.controller = controller;
        this.programId = controller.programId;
        this.isOwner = controller.isOwner;
        this.canEdit = controller.canEdit;
        this.APP_URL = controller.APP_URL;
    }

    /**
     * Initialize modal functionality
     */
    init() {
        console.log('Initializing Program Details Modals...');
        this.bindModalEvents();
        this.initializeBootstrapModals();
    }

    /**
     * Bind modal-related event listeners
     */
    bindModalEvents() {
        // Status history modal
        this.bindStatusHistoryModal();
        
        // Edit status modal
        this.bindEditStatusModal();
        
        // View submissions modal
        this.bindViewSubmissionsModal();
        
        // Delete submission functionality
        this.bindDeleteSubmissionButtons();
        
        // Modal cleanup on hide
        this.bindModalCleanup();
    }

    /**
     * Initialize Bootstrap modals
     */
    initializeBootstrapModals() {
        // Initialize all modals
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(modalElement => {
            if (window.bootstrap && window.bootstrap.Modal) {
                new window.bootstrap.Modal(modalElement);
            }
        });
    }

    /**
     * Bind status history modal events
     */
    bindStatusHistoryModal() {
        const statusHistoryModal = document.getElementById('statusHistoryModal');
        if (!statusHistoryModal) return;

        statusHistoryModal.addEventListener('show.bs.modal', async (event) => {
            const modalBody = document.getElementById('status-history-modal-body');
            if (!modalBody) return;

            // Show loading state
            modalBody.innerHTML = this.getLoadingHTML();

            try {
                const data = await this.controller.apiCall(
                    `/app/api/program_status.php?action=history&program_id=${this.programId}`
                );

                this.renderStatusHistory(modalBody, data);
            } catch (error) {
                console.error('Error loading status history:', error);
                modalBody.innerHTML = this.getErrorHTML('Failed to load status history.');
            }
        });
    }

    /**
     * Bind edit status modal events
     */
    bindEditStatusModal() {
        const editStatusModal = document.getElementById('editStatusModal');
        if (!editStatusModal || !this.canEdit) return;

        editStatusModal.addEventListener('show.bs.modal', async (event) => {
            const modalBody = document.getElementById('edit-status-modal-body');
            if (!modalBody) return;

            // Show loading state
            modalBody.innerHTML = this.getLoadingHTML();

            try {
                const data = await this.controller.apiCall(
                    `/app/api/program_status.php?action=edit_form&program_id=${this.programId}`
                );

                this.renderEditStatusForm(modalBody, data);
            } catch (error) {
                console.error('Error loading edit status form:', error);
                modalBody.innerHTML = this.getErrorHTML('Failed to load edit form.');
            }
        });
    }

    /**
     * Bind view submissions modal events
     */
    bindViewSubmissionsModal() {
        // Handle modal triggers for viewing submissions
        const viewSubmissionButtons = document.querySelectorAll('[data-bs-target="#viewSubmissionModal"]');
        viewSubmissionButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                // Modal content is already populated by PHP, so no additional loading needed
                console.log('Opening view submission modal');
            });
        });

        // Handle submission links within modals
        const modal = document.getElementById('viewSubmissionModal');
        if (modal) {
            modal.addEventListener('click', (event) => {
                const submissionLink = event.target.closest('a[href*="view_submissions.php"]');
                if (submissionLink) {
                    // Let the default navigation happen
                    console.log('Navigating to submission view');
                }
            });
        }
    }

    /**
     * Bind delete submission button events
     */
    bindDeleteSubmissionButtons() {
        document.addEventListener('click', async (event) => {
            const deleteBtn = event.target.closest('.delete-submission-btn');
            if (!deleteBtn) return;

            event.preventDefault();

            const submissionId = deleteBtn.dataset.submissionId;
            if (!submissionId) {
                this.controller.showError('Invalid submission ID');
                return;
            }

            // Confirm deletion
            if (!confirm('Are you sure you want to delete this draft submission? This action cannot be undone.')) {
                return;
            }

            // Disable button and show loading
            deleteBtn.disabled = true;
            const originalText = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

            try {
                await this.controller.logic.deleteDraftSubmission(submissionId);
            } catch (error) {
                // Error is already handled in logic module
                console.error('Delete submission failed:', error);
            } finally {
                // Restore button state
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            }
        });
    }

    /**
     * Bind modal cleanup events
     */
    bindModalCleanup() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('hidden.bs.modal', (event) => {
                // Clear any loading states or dynamic content
                const modalBody = modal.querySelector('.modal-body');
                if (modalBody && modalBody.querySelector('.loading-overlay')) {
                    // Remove loading overlays when modal is closed
                    modalBody.querySelectorAll('.loading-overlay').forEach(overlay => {
                        overlay.remove();
                    });
                }
            });
        });
    }

    /**
     * Render status history in modal
     */
    renderStatusHistory(container, data) {
        if (!data || !data.history || data.history.length === 0) {
            container.innerHTML = this.getEmptyStateHTML('No status history available');
            return;
        }

        let html = '<div class="timeline-container">';
        
        data.history.forEach((entry, index) => {
            const statusInfo = this.controller.logic.getStatusInfo(entry.status);
            
            html += `
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <i class="${statusInfo.icon}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h6 class="timeline-title">${statusInfo.label}</h6>
                            <span class="badge bg-${statusInfo.class}">${statusInfo.label}</span>
                        </div>
                        <div class="timeline-meta">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                ${this.controller.logic.formatDate(entry.created_at)}
                                ${entry.created_by ? `<i class="fas fa-user ms-2 me-1"></i>${entry.created_by}` : ''}
                            </small>
                        </div>
                        ${entry.remarks ? `<p class="mt-2 mb-0">${entry.remarks}</p>` : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    /**
     * Render edit status form in modal
     */
    renderEditStatusForm(container, data) {
        const currentStatus = data.current_status || 'active';
        const availableStatuses = data.available_statuses || [];

        let html = `
            <form id="edit-status-form">
                <div class="mb-3">
                    <label for="new-status" class="form-label">Program Status</label>
                    <select class="form-select" id="new-status" name="status" required>
        `;

        availableStatuses.forEach(status => {
            const statusInfo = this.controller.logic.getStatusInfo(status.value);
            const selected = status.value === currentStatus ? 'selected' : '';
            html += `<option value="${status.value}" ${selected}>${statusInfo.label}</option>`;
        });

        html += `
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status-remarks" class="form-label">Remarks (optional)</label>
                    <textarea class="form-control" id="status-remarks" name="remarks" rows="3" 
                              placeholder="Add any notes about this status change..."></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        `;

        container.innerHTML = html;

        // Bind form submission
        const form = document.getElementById('edit-status-form');
        if (form) {
            form.addEventListener('submit', (event) => this.handleStatusFormSubmit(event));
        }
    }

    /**
     * Handle status form submission
     */
    async handleStatusFormSubmit(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');

        // Disable form and show loading
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        try {
            const data = await this.controller.apiCall(
                '/app/api/program_status.php',
                {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'update',
                        program_id: this.programId,
                        status: formData.get('status'),
                        remarks: formData.get('remarks')
                    })
                }
            );

            if (data.success) {
                this.controller.showSuccess('Program status updated successfully');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editStatusModal'));
                if (modal) modal.hide();
                
                // Refresh status display
                await this.controller.logic.loadProgramStatus();
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            this.controller.showError('Failed to update status: ' + error.message);
        } finally {
            // Restore button
            submitButton.disabled = false;
            submitButton.innerHTML = 'Update Status';
        }
    }

    /**
     * Get loading HTML template
     */
    getLoadingHTML() {
        return `
            <div class="loading-overlay">
                <div class="loading-spinner"></div>
            </div>
        `;
    }

    /**
     * Get error HTML template
     */
    getErrorHTML(message) {
        return `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }

    /**
     * Get empty state HTML template
     */
    getEmptyStateHTML(message) {
        return `
            <div class="text-center text-muted py-4">
                <i class="fas fa-folder-open fa-2x mb-2"></i>
                <div>${message}</div>
            </div>
        `;
    }

    /**
     * Show modal programmatically
     */
    showModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement && window.bootstrap) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }

    /**
     * Hide modal programmatically
     */
    hideModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement && window.bootstrap) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        }
    }
}
