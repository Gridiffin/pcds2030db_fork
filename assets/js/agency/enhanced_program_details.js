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
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new EnhancedProgramDetails();
});

// Export for potential use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedProgramDetails;
} 