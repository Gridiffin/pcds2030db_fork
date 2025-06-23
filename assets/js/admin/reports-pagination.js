/**
 * Reports Pagination System
 * 
 * Handles pagination, search, and state management for the admin reports section
 */

class ReportsPagination {
    constructor(containerSelector = '#recentReportsContainer') {
        this.container = document.querySelector(containerSelector);
        this.searchInput = document.querySelector('#reportSearch');
        this.clearSearchBtn = document.querySelector('#clearSearch');
        
        // Pagination state
        this.currentPage = 1;
        this.perPage = 10;
        this.currentSearch = '';
        this.loading = false;        // Configuration
        this.config = {
            apiUrl: this.getAppUrl() + '/app/views/admin/ajax/recent_reports_paginated.php',
            deleteApiUrl: this.getDeleteApiUrl(),
            debounceDelay: 300,
            loadingClass: 'loading-reports'
        };
          this.init();
    }
      getAppUrl() {
        // Try to get APP_URL from ReportGeneratorConfig first, then fallback to global
        if (typeof window.ReportGeneratorConfig !== 'undefined' && window.ReportGeneratorConfig.appUrl) {
            return window.ReportGeneratorConfig.appUrl;
        }
        if (typeof window.APP_URL !== 'undefined') {
            return window.APP_URL;
        }
        // Last resort - try to detect from current URL
        return window.location.origin + '/pcds2030_dashboard';
    }
    
    getDeleteApiUrl() {
        // Try to get delete API URL from ReportGeneratorConfig first
        if (typeof window.ReportGeneratorConfig !== 'undefined' && 
            window.ReportGeneratorConfig.apiEndpoints && 
            window.ReportGeneratorConfig.apiEndpoints.deleteReport) {
            return window.ReportGeneratorConfig.apiEndpoints.deleteReport;
        }
        // Fallback
        return this.getAppUrl() + '/app/api/delete_report.php';
    }
    
    init() {
        this.setupEventListeners();
        this.loadPage(1); // Load first page
    }
    
    setupEventListeners() {
        // Search functionality
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, this.config.debounceDelay);
            });
        }
        
        // Clear search button
        if (this.clearSearchBtn) {
            this.clearSearchBtn.addEventListener('click', () => {
                this.clearSearch();
            });
        }
        
        // Global clear search function for empty state
        window.clearSearchAndReload = () => {
            this.clearSearch();
        };
        
        // Listen for new report generation to refresh
        window.addEventListener('reportGenerated', () => {
            this.refresh();
        });
    }
    
    async loadPage(page, perPage = null, search = null) {
        if (this.loading) return;
        
        this.loading = true;
        this.showLoading();
        
        try {
            // Update state
            this.currentPage = page;
            if (perPage !== null) this.perPage = perPage;
            if (search !== null) this.currentSearch = search;
            
            // Build URL parameters
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: this.currentSearch,
                format: 'html'
            });
            
            // Make request
            const response = await fetch(`${this.config.apiUrl}?${params}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const html = await response.text();
            
            // Update container
            this.container.innerHTML = html;
            
            // Setup event listeners for new content
            this.setupPaginationListeners();
            this.setupDeleteButtons();
            this.updateSearchUI();
            
            // Update URL without reload (for bookmarking)
            this.updateURL();
            
        } catch (error) {
            console.error('Error loading reports page:', error);
            this.showError('Failed to load reports. Please try again.');
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }
    
    setupPaginationListeners() {
        // Page navigation buttons
        const pageButtons = this.container.querySelectorAll('.page-link[data-page]');
        pageButtons.forEach(button => {
            if (!button.disabled) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = parseInt(button.dataset.page);
                    if (page && page !== this.currentPage) {
                        this.loadPage(page);
                    }
                });
            }
        });
        
        // Page size selector
        const pageSizeSelect = this.container.querySelector('#pageSizeSelect');
        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', (e) => {
                const newPerPage = parseInt(e.target.value);
                this.loadPage(1, newPerPage); // Reset to page 1 when changing page size
            });
        }
        
        // Generate report toggle from empty state
        const generateToggleEmpty = this.container.querySelector('#generateReportToggleEmpty');
        if (generateToggleEmpty && typeof window.setupGenerateReportToggle === 'function') {
            generateToggleEmpty.addEventListener('click', () => {
                const mainToggle = document.querySelector('#generateReportToggle');
                if (mainToggle) {
                    mainToggle.click();
                }
            });
        }
    }
      setupDeleteButtons() {
        // Re-setup delete button event listeners for new content
        const deleteButtons = this.container.querySelectorAll('.delete-report-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const reportId = button.dataset.reportId;
                const reportName = button.dataset.reportName;
                
                console.log('Delete button clicked:', { reportId, reportName, button }); // Debug log
                console.log('All button datasets:', button.dataset); // Debug log
                
                if (!reportId || reportId === 'undefined') {
                    console.error('Invalid report ID detected:', reportId);
                    this.showMessage('Invalid report ID. Please refresh the page and try again.', 'error');
                    return;
                }
                
                // Update modal content
                const modal = document.querySelector('#deleteReportModal');
                if (modal) {
                    const nameElement = modal.querySelector('#reportNameToDelete');
                    const confirmBtn = modal.querySelector('#confirmDeleteBtn');
                    
                    if (nameElement) nameElement.textContent = reportName;
                    if (confirmBtn) {
                        // Remove existing listeners
                        const newConfirmBtn = confirmBtn.cloneNode(true);
                        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                        
                        // Add new listener
                        newConfirmBtn.addEventListener('click', () => {
                            this.handleDeleteReport(reportId);
                        });
                    }
                }
            });
        });
    }
      async handleDeleteReport(reportId) {
        console.log('handleDeleteReport called with reportId:', reportId); // Debug log
        
        if (!reportId || reportId === 'undefined' || reportId === '') {
            console.error('Invalid report ID provided to handleDeleteReport:', reportId);
            this.showMessage('Invalid report ID. Please refresh the page and try again.', 'error');
            return;
        }
        
        try {            const requestData = { report_id: parseInt(reportId) };
            console.log('Sending delete request with data:', requestData); // Debug log
            
            const response = await fetch(this.config.deleteApiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            console.log('Delete API response status:', response.status); // Debug log
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('Delete API response data:', result); // Debug log
            
            if (result.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.querySelector('#deleteReportModal'));
                if (modal) modal.hide();
                
                // Show success message
                this.showMessage('Report deleted successfully.', 'success');
                
                // Refresh current page
                this.refresh();
            } else {
                throw new Error(result.error || result.message || 'Failed to delete report');
            }
        } catch (error) {
            console.error('Error deleting report:', error);
            this.showMessage(`Failed to delete report: ${error.message}`, 'error');
        }
    }
    
    handleSearch(searchTerm) {
        const trimmed = searchTerm.trim();
        if (trimmed !== this.currentSearch) {
            this.loadPage(1, null, trimmed); // Reset to page 1 when searching
        }
    }
    
    clearSearch() {
        if (this.searchInput) {
            this.searchInput.value = '';
        }
        this.loadPage(1, null, ''); // Reset to page 1 with no search
    }
    
    updateSearchUI() {
        if (this.searchInput && this.clearSearchBtn) {
            const hasSearch = this.currentSearch.length > 0;
            this.clearSearchBtn.style.display = hasSearch ? 'block' : 'none';
            
            // Update search input if it doesn't match current state
            if (this.searchInput.value !== this.currentSearch) {
                this.searchInput.value = this.currentSearch;
            }
        }
    }
    
    updateURL() {
        // Update browser URL for bookmarking (without reload)
        const url = new URL(window.location);
        const params = url.searchParams;
        
        // Update or remove parameters
        if (this.currentPage > 1) {
            params.set('page', this.currentPage);
        } else {
            params.delete('page');
        }
        
        if (this.perPage !== 10) {
            params.set('per_page', this.perPage);
        } else {
            params.delete('per_page');
        }
        
        if (this.currentSearch) {
            params.set('search', this.currentSearch);
        } else {
            params.delete('search');
        }
        
        // Update URL without reload
        window.history.replaceState({}, '', url.toString());
    }
    
    showLoading() {
        if (this.container) {
            this.container.classList.add(this.config.loadingClass);
            
            // Add loading overlay if not exists
            if (!this.container.querySelector('.loading-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'loading-overlay';
                overlay.innerHTML = `
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading reports...</span>
                    </div>
                `;
                this.container.appendChild(overlay);
            }
        }
    }
    
    hideLoading() {
        if (this.container) {
            this.container.classList.remove(this.config.loadingClass);
            
            // Remove loading overlay
            const overlay = this.container.querySelector('.loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
    }
    
    showError(message) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        if (this.container) {
            this.container.innerHTML = errorHtml;
        }
    }
    
    showMessage(message, type = 'info') {
        // Create or update toast notification
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Find or create toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1055';
            document.body.appendChild(toastContainer);
        }
        
        // Add toast
        const toastElement = document.createElement('div');
        toastElement.innerHTML = toastHtml;
        const toast = toastElement.firstElementChild;
        toastContainer.appendChild(toast);
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
        bsToast.show();
        
        // Remove from DOM after hide
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    refresh() {
        // Refresh current page
        this.loadPage(this.currentPage, this.perPage, this.currentSearch);
    }
    
    // Static method to initialize from URL parameters
    static initFromURL() {
        const url = new URL(window.location);
        const params = url.searchParams;
        
        const pagination = new ReportsPagination();
        
        // Extract parameters from URL
        const page = parseInt(params.get('page')) || 1;
        const perPage = parseInt(params.get('per_page')) || 10;
        const search = params.get('search') || '';
        
        // Set search input if provided
        if (search && pagination.searchInput) {
            pagination.searchInput.value = search;
        }
        
        // Load the specified page
        pagination.loadPage(page, perPage, search);
        
        return pagination;
    }
}

// Initialize pagination when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the generate reports page
    if (document.querySelector('#recentReportsContainer')) {
        window.reportsPagination = ReportsPagination.initFromURL();
    }
});

// Export for external use
window.ReportsPagination = ReportsPagination;
