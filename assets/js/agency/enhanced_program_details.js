/**
 * Enhanced Program Details JavaScript
 * Provides interactive functionality for the enhanced program details page
 */

class EnhancedProgramDetails {
    constructor() {
        this.programId = window.programId;
        this.isOwner = window.isOwner;
        this.currentUser = window.currentUser;
        this.APP_URL = window.APP_URL;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeComponents();
        this.loadAdditionalData();
        this.loadProgramStatus();
        // Only enable editing logic if edit-status-btn exists (edit page)
        if (document.getElementById('edit-status-btn')) {
            this.enableStatusEditing = true;
        } else {
            this.enableStatusEditing = false;
        }
    }

    loadProgramStatus() {
        const self = this;
        fetch(`${this.APP_URL}/app/api/program_status.php?action=status&program_id=${this.programId}`)
            .then(res => res.json())
            .then(data => {
                self.renderStatus(data);
            });
    }

    renderStatus(data) {
        const badge = document.getElementById('program-status-badge');
        const holdInfo = document.getElementById('hold-point-info');
        if (!badge) return;
        // Status badge
        let status = data.status || 'active';
        let statusLabel = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        badge.textContent = statusLabel;
        badge.className = 'status-badge status-' + status;
        // Hold point info
        if (status === 'on_hold' && data.hold_point) {
            holdInfo.innerHTML = `<i class='fas fa-pause-circle text-warning'></i> On Hold: <b>${data.hold_point.reason || ''}</b> <span class='text-muted'>(${this.formatDate(data.hold_point.created_at)})</span> <span>${data.hold_point.remarks ? ' - ' + data.hold_point.remarks : ''}</span>`;
        } else {
            holdInfo.innerHTML = '';
        }
    }

    bindEvents() {
        // Target progress bars animation
        this.animateProgressBars();
        
        // Timeline item interactions
        this.bindTimelineEvents();
        
        // Attachment interactions
        this.bindAttachmentEvents();
        
        // Quick action buttons
        this.bindQuickActionEvents();
        
        // Responsive behavior
        this.bindResponsiveEvents();
        // Status/History buttons
        const statusBtn = document.getElementById('edit-status-btn');
        const historyBtn = document.getElementById('view-status-history-btn');
        if (statusBtn && this.enableStatusEditing) {
            statusBtn.addEventListener('click', () => this.openEditStatusModal());
        }
        if (historyBtn) {
            historyBtn.addEventListener('click', () => this.openStatusHistoryModal());
        }
    }

    initializeComponents() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize animations
        this.initAnimations();
        
        // Initialize charts if needed
        this.initCharts();
    }

    loadAdditionalData() {
        // Load additional program statistics
        this.loadProgramStats();
        
        // Load target progress data
        this.loadTargetProgress();
    }

    animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBar = entry.target;
                    const width = progressBar.style.width;
                    
                    // Reset width to 0 for animation
                    progressBar.style.width = '0%';
                    
                    // Animate to target width
                    setTimeout(() => {
                        progressBar.style.transition = 'width 1s ease-in-out';
                        progressBar.style.width = width;
                    }, 100);
                    
                    observer.unobserve(progressBar);
                }
            });
        });

        progressBars.forEach(bar => observer.observe(bar));
    }

    bindTimelineEvents() {
        const timelineItems = document.querySelectorAll('.timeline-item');
        
        timelineItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Don't trigger if clicking on a link
                if (e.target.tagName === 'A' || e.target.closest('a')) {
                    return;
                }
                
                // Toggle timeline item details
                this.toggleTimelineDetails(item);
            });
            
            // Add hover effects
            item.addEventListener('mouseenter', () => {
                item.classList.add('timeline-item-hover');
            });
            
            item.addEventListener('mouseleave', () => {
                item.classList.remove('timeline-item-hover');
            });
        });
    }

    toggleTimelineDetails(timelineItem) {
        const content = timelineItem.querySelector('.timeline-content');
        const isExpanded = timelineItem.classList.contains('expanded');
        
        if (isExpanded) {
            timelineItem.classList.remove('expanded');
            content.style.maxHeight = '60px';
        } else {
            timelineItem.classList.add('expanded');
            content.style.maxHeight = content.scrollHeight + 'px';
        }
    }

    bindAttachmentEvents() {
        const attachmentItems = document.querySelectorAll('.attachment-item');
        
        attachmentItems.forEach(item => {
            const downloadBtn = item.querySelector('.attachment-actions .btn');
            
            if (downloadBtn) {
                downloadBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleAttachmentDownload(downloadBtn.href, item);
                });
            }
        });
    }

    handleAttachmentDownload(url, item) {
        // Show loading state
        const btn = item.querySelector('.attachment-actions .btn');
        const originalContent = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        // Simulate download (in real implementation, this would be an actual download)
        setTimeout(() => {
            // Open download in new window
            window.open(url, '_blank');
            
            // Reset button
            btn.innerHTML = originalContent;
            btn.disabled = false;
            
            // Show success message
            this.showToast('Download Started', 'File download has been initiated.', 'success');
        }, 500);
    }

    bindQuickActionEvents() {
        const quickActionBtns = document.querySelectorAll('.card-body .btn');
        
        quickActionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Add click animation
                btn.classList.add('btn-clicked');
                setTimeout(() => {
                    btn.classList.remove('btn-clicked');
                }, 200);
            });
        });
    }

    bindResponsiveEvents() {
        // Handle responsive behavior
        const handleResize = () => {
            const isMobile = window.innerWidth < 768;
            
            if (isMobile) {
                this.enableMobileView();
            } else {
                this.enableDesktopView();
            }
        };
        
        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call
    }

    enableMobileView() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.classList.add('mobile-optimized');
        });
    }

    enableDesktopView() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.classList.remove('mobile-optimized');
        });
    }

    initTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    initAnimations() {
        // Animate cards on scroll
        const cards = document.querySelectorAll('.card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        cards.forEach(card => observer.observe(card));
    }

    initCharts() {
        // Initialize any charts if needed
        // This could include progress charts, timeline charts, etc.
    }

    loadProgramStats() {
        // Load additional program statistics via AJAX
        if (!this.programId) return;
        
        fetch(`${this.APP_URL}/app/ajax/get_program_stats.php?program_id=${this.programId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateProgramStats(data.stats);
                }
            })
            .catch(error => {
                console.error('Error loading program stats:', error);
            });
    }

    updateProgramStats(stats) {
        // Update statistics display
        const statElements = document.querySelectorAll('.stat-item .badge');
        
        if (stats.total_submissions !== undefined) {
            const submissionsBadge = document.querySelector('.stat-item:first-child .badge');
            if (submissionsBadge) {
                submissionsBadge.textContent = stats.total_submissions;
            }
        }
        
        if (stats.completion_rate !== undefined) {
            const progressBars = document.querySelectorAll('.target-progress .progress-bar');
            progressBars.forEach(bar => {
                bar.style.width = `${stats.completion_rate}%`;
            });
        }

        // Update Last Activity
        const lastActivityElem = document.getElementById('last-activity-value');
        if (lastActivityElem) {
            if (stats.last_activity_date) {
                const date = new Date(stats.last_activity_date);
                lastActivityElem.textContent = isNaN(date.getTime()) ? 'Never' : date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } else {
                lastActivityElem.textContent = 'Never';
            }
        }
    }

    loadTargetProgress() {
        // Load target progress data
        if (!this.programId) return;
        
        fetch(`${this.APP_URL}/app/ajax/get_target_progress.php?program_id=${this.programId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateTargetProgress(data.progress);
                }
            })
            .catch(error => {
                console.error('Error loading target progress:', error);
            });
    }

    updateTargetProgress(progress) {
        // Update target progress indicators
        progress.forEach(targetProgress => {
            const targetItem = document.querySelector(`[data-target-id="${targetProgress.target_id}"]`);
            if (targetItem) {
                const progressBar = targetItem.querySelector('.progress-bar');
                const progressText = targetItem.querySelector('.text-muted');
                
                if (progressBar) {
                    progressBar.style.width = `${targetProgress.percentage}%`;
                }
                
                if (progressText) {
                    progressText.textContent = `${targetProgress.percentage}% Complete`;
                }
            }
        });
    }

    showToast(title, message, type = 'info', duration = 5000) {
        // Show toast notification
        if (typeof showToast === 'function') {
            showToast(title, message, type, duration);
        } else {
            // Fallback to alert
            alert(`${title}: ${message}`);
        }
    }

    // Utility methods
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    openEditStatusModal() {
        if (!this.enableStatusEditing) return;
        // Fetch current status and hold point for form
        fetch(`${this.APP_URL}/app/api/program_status.php?action=status&program_id=${this.programId}`)
            .then(res => res.json())
            .then(data => {
                this.renderEditStatusForm(data);
                const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
                modal.show();
            });
    }

    renderEditStatusForm(data) {
        if (!this.enableStatusEditing) return;
        const modalBody = document.getElementById('edit-status-modal-body');
        if (!modalBody) return;
        let status = data.status || 'active';
        let hold = data.hold_point || {};
        // Status options
        const statusOptions = [
            { value: 'active', label: 'Active' },
            { value: 'on_hold', label: 'On Hold' },
            { value: 'completed', label: 'Completed' },
            { value: 'delayed', label: 'Delayed' },
            { value: 'cancelled', label: 'Cancelled' }
        ];
        let html = `<form id='edit-status-form'>
            <div class='mb-3'>
                <label for='status-select' class='form-label'>Status</label>
                <select class='form-select' id='status-select' name='status'>
                    ${statusOptions.map(opt => `<option value='${opt.value}' ${opt.value === status ? 'selected' : ''}>${opt.label}</option>`).join('')}
                </select>
            </div>
            <div class='mb-3'>
                <label for='status-remarks' class='form-label'>Remarks (optional)</label>
                <textarea class='form-control' id='status-remarks' name='remarks' rows='2'></textarea>
            </div>`;
        if (status === 'on_hold' || hold) {
            html += `<div id='hold-point-fields'>
                <div class='mb-3'>
                    <label for='hold-reason' class='form-label'>Hold Reason</label>
                    <input type='text' class='form-control' id='hold-reason' name='reason' value='${hold.reason || ''}' required />
                </div>
                <div class='mb-3'>
                    <label for='hold-remarks' class='form-label'>Hold Remarks (optional)</label>
                    <textarea class='form-control' id='hold-remarks' name='hold_remarks' rows='2'>${hold.remarks || ''}</textarea>
                </div>
            </div>`;
        }
        html += `<button type='submit' class='btn btn-primary'>Save</button>
        </form>`;
        modalBody.innerHTML = html;
        // Show/hide hold fields on status change
        const statusSelect = document.getElementById('status-select');
        statusSelect.addEventListener('change', (e) => {
            const holdFields = document.getElementById('hold-point-fields');
            if (e.target.value === 'on_hold') {
                if (!holdFields) {
                    // Add hold fields
                    const div = document.createElement('div');
                    div.id = 'hold-point-fields';
                    div.innerHTML = `<div class='mb-3'><label for='hold-reason' class='form-label'>Hold Reason</label><input type='text' class='form-control' id='hold-reason' name='reason' required /></div><div class='mb-3'><label for='hold-remarks' class='form-label'>Hold Remarks (optional)</label><textarea class='form-control' id='hold-remarks' name='hold_remarks' rows='2'></textarea></div>`;
                    statusSelect.parentNode.parentNode.appendChild(div);
                } else {
                    holdFields.style.display = '';
                }
            } else if (holdFields) {
                holdFields.style.display = 'none';
            }
        });
        // Submit handler
        document.getElementById('edit-status-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitStatusForm();
        });
    }

    submitStatusForm() {
        if (!this.enableStatusEditing) return;
        const form = document.getElementById('edit-status-form');
        const formData = new FormData(form);
        formData.append('action', 'set_status');
        formData.append('program_id', this.programId);
        fetch(`${this.APP_URL}/app/api/program_status.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.loadProgramStatus();
                bootstrap.Modal.getInstance(document.getElementById('editStatusModal')).hide();
                this.showToast('Status Updated', 'Program status updated successfully.', 'success');
            } else {
                this.showToast('Error', data.error || 'Failed to update status.', 'danger');
            }
        });
    }

    openStatusHistoryModal() {
        fetch(`${this.APP_URL}/app/api/program_status.php?action=status_history&program_id=${this.programId}`)
            .then(res => res.json())
            .then(data => {
                this.renderStatusHistory(data);
                const modal = new bootstrap.Modal(document.getElementById('statusHistoryModal'));
                modal.show();
            });
    }

    renderStatusHistory(data) {
        const modalBody = document.getElementById('status-history-modal-body');
        if (!modalBody) return;
        let html = '<h6>Status Changes</h6><ul class="list-group mb-3">';
        (data.status_history || []).forEach(item => {
            html += `<li class="list-group-item"><b>${item.status}</b> by User #${item.changed_by} <span class="text-muted">(${this.formatDate(item.changed_at)})</span> ${item.remarks ? ' - ' + item.remarks : ''}</li>`;
        });
        html += '</ul><h6>Hold Points</h6><ul class="list-group">';
        (data.hold_points || []).forEach(item => {
            html += `<li class="list-group-item"><i class='fas fa-pause-circle text-warning'></i> <b>${item.reason}</b> (${this.formatDate(item.created_at)})${item.ended_at ? ' - Ended: ' + this.formatDate(item.ended_at) : ''} ${item.remarks ? ' - ' + item.remarks : ''}</li>`;
        });
        html += '</ul>';
        modalBody.innerHTML = html;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new EnhancedProgramDetails();
});

// Export for potential use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedProgramDetails;
} 