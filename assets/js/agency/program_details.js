/**
 * Program Details Page JavaScript
 * 
 * Enhanced functionality for the program details page including:
 * - Real-time status updates
 * - Interactive elements
 * - Data visualization
 * - Export functionality
 * - Mobile responsiveness
 */

class ProgramDetailsManager {
    constructor() {
        this.programId = this.getProgramIdFromUrl();
        this.currentUser = this.getCurrentUser();
        this.isOwner = this.checkOwnership();
        this.init();
    }

    /**
     * Initialize the program details manager
     */
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupRealTimeUpdates();
        this.enhanceAccessibility();
    }

    /**
     * Get program ID from URL parameters
     */
    getProgramIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
    }

    /**
     * Get current user information
     */
    getCurrentUser() {
        // This would typically come from a global variable set by PHP
        return window.currentUser || {
            id: null,
            agency_id: null,
            role: null
        };
    }

    /**
     * Check if current user owns this program
     */
    checkOwnership() {
        // This would be set by PHP in the page
        return window.isOwner || false;
    }

    /**
     * Set up event listeners for interactive elements
     */
    setupEventListeners() {
        // Status badge interactions
        this.setupStatusBadgeInteractions();
        
        // Attachment interactions
        this.setupAttachmentInteractions();
        
        // Target card interactions
        this.setupTargetCardInteractions();
        
        // Export functionality
        this.setupExportFunctionality();
        
        // Mobile responsiveness
        this.setupMobileResponsiveness();
    }

    /**
     * Set up status badge interactions
     */
    setupStatusBadgeInteractions() {
        const statusBadges = document.querySelectorAll('.status-badge, .badge');
        
        statusBadges.forEach(badge => {
            badge.addEventListener('click', (e) => {
                if (this.isOwner) {
                    this.showStatusDetails(e.target);
                }
            });
            
            // Add hover effects
            badge.addEventListener('mouseenter', (e) => {
                e.target.style.transform = 'scale(1.05)';
                e.target.style.transition = 'transform 0.2s ease';
            });
            
            badge.addEventListener('mouseleave', (e) => {
                e.target.style.transform = 'scale(1)';
            });
        });
    }

    /**
     * Set up attachment interactions
     */
    setupAttachmentInteractions() {
        const attachmentItems = document.querySelectorAll('.attachment-item');
        
        attachmentItems.forEach(item => {
            const downloadBtn = item.querySelector('.btn-outline-primary');
            const fileName = item.querySelector('h6').textContent;
            
            if (downloadBtn) {
                downloadBtn.addEventListener('click', (e) => {
                    this.trackDownload(fileName);
                });
            }
            
            // Add hover effects
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f8f9fa';
                item.style.transition = 'background-color 0.2s ease';
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = '';
            });
        });
    }

    /**
     * Set up target card interactions
     */
    setupTargetCardInteractions() {
        const targetCards = document.querySelectorAll('.card.border');
        
        targetCards.forEach(card => {
            // Add click to expand functionality
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.btn')) {
                    this.toggleTargetCard(card);
                }
            });
            
            // Add hover effects
            card.addEventListener('mouseenter', () => {
                card.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                card.style.transform = 'translateY(-2px)';
                card.style.transition = 'all 0.2s ease';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.boxShadow = '';
                card.style.transform = '';
            });
        });
    }

    /**
     * Set up export functionality
     */
    setupExportFunctionality() {
        const exportBtn = document.querySelector('.btn-export');
        if (exportBtn) {
            exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportProgramData();
            });
        }
    }

    /**
     * Set up mobile responsiveness
     */
    setupMobileResponsiveness() {
        // Handle mobile menu toggle
        const mobileMenuToggle = document.querySelector('.navbar-toggler');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', () => {
                this.handleMobileMenuToggle();
            });
        }
        
        // Handle responsive table
        this.setupResponsiveTable();
    }

    /**
     * Initialize UI components
     */
    initializeComponents() {
        this.initializeTooltips();
        this.initializePopovers();
        this.initializeProgressBars();
        this.initializeCharts();
    }

    /**
     * Initialize Bootstrap tooltips
     */
    initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Initialize Bootstrap popovers
     */
    initializePopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    /**
     * Initialize progress bars
     */
    initializeProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const target = bar.getAttribute('aria-valuenow');
            if (target) {
                this.animateProgressBar(bar, parseInt(target));
            }
        });
    }

    /**
     * Initialize charts if Chart.js is available
     */
    initializeCharts() {
        if (typeof Chart !== 'undefined') {
            this.createProgressChart();
            this.createTimelineChart();
        }
    }

    /**
     * Set up real-time updates
     */
    setupRealTimeUpdates() {
        // Check for updates every 30 seconds
        setInterval(() => {
            this.checkForUpdates();
        }, 30000);
        
        // Listen for WebSocket updates if available
        this.setupWebSocketUpdates();
    }

    /**
     * Check for program updates
     */
    async checkForUpdates() {
        try {
            const response = await fetch(`${APP_URL}/app/ajax/get_program_updates.php?program_id=${this.programId}`);
            const data = await response.json();
            
            if (data.hasUpdates) {
                this.showUpdateNotification(data);
            }
        } catch (error) {
            console.error('Error checking for updates:', error);
        }
    }

    /**
     * Set up WebSocket updates
     */
    setupWebSocketUpdates() {
        // This would be implemented if WebSocket support is added
        // For now, we'll use polling
    }

    /**
     * Enhance accessibility features
     */
    enhanceAccessibility() {
        // Add ARIA labels
        this.addAriaLabels();
        
        // Add keyboard navigation
        this.addKeyboardNavigation();
        
        // Add focus management
        this.addFocusManagement();
        
        // Add screen reader support
        this.addScreenReaderSupport();
    }

    /**
     * Add ARIA labels to interactive elements
     */
    addAriaLabels() {
        const interactiveElements = document.querySelectorAll('button, a, input, select, textarea');
        
        interactiveElements.forEach(element => {
            if (!element.getAttribute('aria-label') && !element.getAttribute('aria-labelledby')) {
                const text = element.textContent || element.value || element.placeholder;
                if (text) {
                    element.setAttribute('aria-label', text.trim());
                }
            }
        });
    }

    /**
     * Add keyboard navigation support
     */
    addKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Escape key to close modals
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
            
            // Ctrl+S to save (if editing)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                this.saveChanges();
            }
        });
    }

    /**
     * Add focus management
     */
    addFocusManagement() {
        // Trap focus in modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            this.trapFocus(modal);
        });
    }

    /**
     * Add screen reader support
     */
    addScreenReaderSupport() {
        // Add live regions for dynamic content
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        document.body.appendChild(liveRegion);
        
        this.liveRegion = liveRegion;
    }

    /**
     * Show status details in a modal
     */
    showStatusDetails(badge) {
        const status = badge.textContent.trim();
        const modal = this.createStatusModal(status);
        document.body.appendChild(modal);
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    /**
     * Create status details modal
     */
    createStatusModal(status) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Status Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Current status: <strong>${status}</strong></p>
                        <p>This status indicates the current progress of the program.</p>
                    </div>
                </div>
            </div>
        `;
        return modal;
    }

    /**
     * Toggle target card expansion
     */
    toggleTargetCard(card) {
        const content = card.querySelector('.card-text');
        const isExpanded = card.classList.contains('expanded');
        
        if (isExpanded) {
            card.classList.remove('expanded');
            content.style.maxHeight = '3em';
            content.style.overflow = 'hidden';
        } else {
            card.classList.add('expanded');
            content.style.maxHeight = 'none';
            content.style.overflow = 'visible';
        }
    }

    /**
     * Track file download
     */
    trackDownload(fileName) {
        // Send analytics event
        if (typeof gtag !== 'undefined') {
            gtag('event', 'download', {
                'event_category': 'program_attachment',
                'event_label': fileName
            });
        }
        
        // Show download notification
        this.showToast('Download Started', `Downloading ${fileName}...`, 'info');
    }

    /**
     * Export program data
     */
    async exportProgramData() {
        try {
            this.showToast('Export Started', 'Preparing export...', 'info');
            
            const response = await fetch(`${APP_URL}/app/ajax/export_program_data.php?program_id=${this.programId}`);
            const blob = await response.blob();
            
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `program_${this.programId}_export.pdf`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            this.showToast('Export Complete', 'Program data exported successfully', 'success');
        } catch (error) {
            console.error('Export error:', error);
            this.showToast('Export Failed', 'Failed to export program data', 'error');
        }
    }

    /**
     * Animate progress bar
     */
    animateProgressBar(bar, target) {
        let current = 0;
        const increment = target / 50;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            bar.style.width = current + '%';
            bar.setAttribute('aria-valuenow', current);
        }, 20);
    }

    /**
     * Create progress chart
     */
    createProgressChart() {
        const ctx = document.getElementById('progressChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Not Started'],
                datasets: [{
                    data: [30, 50, 20],
                    backgroundColor: ['#28a745', '#ffc107', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    /**
     * Create timeline chart
     */
    createTimelineChart() {
        const ctx = document.getElementById('timelineChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Progress',
                    data: [10, 25, 40, 60, 75, 85],
                    borderColor: '#007bff',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    /**
     * Show update notification
     */
    showUpdateNotification(data) {
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show';
        notification.innerHTML = `
            <i class="fas fa-sync-alt me-2"></i>
            Program has been updated. 
            <a href="#" onclick="location.reload()">Refresh page</a> to see changes.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-fluid');
        container.insertBefore(notification, container.firstChild);
    }

    /**
     * Show toast notification
     */
    showToast(title, message, type = 'info') {
        if (typeof showToast === 'function') {
            showToast(title, message, type);
        } else {
            // Fallback toast implementation
            this.createToast(title, message, type);
        }
    }

    /**
     * Create fallback toast
     */
    createToast(title, message, type) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <strong>${title}</strong><br>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }

    /**
     * Handle mobile menu toggle
     */
    handleMobileMenuToggle() {
        const navbar = document.querySelector('.navbar-collapse');
        if (navbar) {
            navbar.classList.toggle('show');
        }
    }

    /**
     * Set up responsive table
     */
    setupResponsiveTable() {
        const tables = document.querySelectorAll('.table-responsive table');
        tables.forEach(table => {
            this.makeTableResponsive(table);
        });
    }

    /**
     * Make table responsive
     */
    makeTableResponsive(table) {
        const headers = table.querySelectorAll('th');
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index].textContent);
                }
            });
        });
    }

    /**
     * Close all modals
     */
    closeAllModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }

    /**
     * Save changes (placeholder for editing functionality)
     */
    saveChanges() {
        this.showToast('Save', 'Save functionality not implemented yet', 'info');
    }

    /**
     * Trap focus in modal
     */
    trapFocus(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        modal.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the program details page
    if (window.location.pathname.includes('program_details.php')) {
        new ProgramDetailsManager();
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProgramDetailsManager;
}
