/**
 * Public Reports Page Logic
 * Handles functionality specific to the public reports page
 */

export class PublicReports {
    
    constructor(logic, ajax) {
        this.logic = logic;
        this.ajax = ajax;
        this.reports = [];
        this.filteredReports = [];
    }
    
    /**
     * Initialize the public reports page
     */
    init() {
        this.attachEventListeners();
        this.loadPublicReports();
        
        
    }
    
    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Download buttons
        this.attachDownloadListeners();
        
        // View report buttons
        this.attachViewListeners();
        
        // Search functionality
        this.attachSearchListeners();
        
        // Filter functionality
        this.attachFilterListeners();
        
        // Refresh button
        const refreshBtn = document.querySelector('.refresh-reports-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshReports();
            });
        }
    }
    
    /**
     * Load public reports
     */
    async loadPublicReports() {
        try {
            const reportsContainer = document.querySelector('#public-reports-container');
            if (reportsContainer) {
                this.ajax.showLoading(reportsContainer);
            }
            
            const data = await this.ajax.loadPublicReports();
            this.reports = data.reports || [];
            this.filteredReports = [...this.reports];
            
            this.renderReportsList();
            this.updateReportsCount();
            
        } catch (error) {
            this.ajax.handleError(error, 'load public reports');
            this.showError('Failed to load public reports. Please try again.');
        }
    }
    
    /**
     * Refresh reports
     */
    async refreshReports() {
        const refreshBtn = document.querySelector('.refresh-reports-btn');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            const originalHtml = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            
            try {
                await this.loadPublicReports();
                
                if (typeof window.showToast === 'function') {
                    window.showToast('Success', 'Reports refreshed successfully', 'success');
                }
            } catch (error) {
                // Error already handled in loadPublicReports
            } finally {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = originalHtml;
            }
        } else {
            await this.loadPublicReports();
        }
    }
    
    /**
     * Render reports list
     */
    renderReportsList() {
        const container = document.querySelector('#public-reports-container');
        if (!container) return;
        
        if (this.filteredReports.length === 0) {
            this.showEmptyState();
            return;
        }
        
        const reportsHtml = this.filteredReports.map(report => this.renderReportCard(report)).join('');
        
        container.innerHTML = `
            <div class="row">
                ${reportsHtml}
            </div>
        `;
        
        // Re-attach event listeners for new elements
        this.attachDownloadListeners();
        this.attachViewListeners();
    }
    
    /**
     * Render individual report card
     */
    renderReportCard(report) {
        const downloadUrl = this.logic.generateDownloadUrl(report.file_path);
        const formattedDate = this.logic.formatDate(report.generated_at);
        const fileTypeIcon = this.logic.getFileTypeIcon(report.file_type || 'pdf');
        const isRecent = this.logic.isRecentReport(report.generated_at);
        
        return `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 report-card ${isRecent ? 'recent-report' : ''}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <i class="${fileTypeIcon} fa-2x text-primary"></i>
                        ${isRecent ? '<span class="badge bg-warning text-dark">New</span>' : ''}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${report.report_name || 'Untitled Report'}</h6>
                        <p class="card-text flex-grow-1">${this.logic.truncateText(report.description || 'No description available', 100)}</p>
                        <div class="mt-auto">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-calendar-alt"></i> ${formattedDate}
                            </small>
                            <div class="d-flex gap-2">
                                <a href="${downloadUrl}" class="btn btn-outline-primary btn-sm view-report-btn flex-grow-1" 
                                   target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <button class="btn btn-success btn-sm download-report-btn" 
                                        data-report-id="${report.report_id}" 
                                        data-file-type="${report.file_type || 'pdf'}"
                                        title="Download Report">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Show empty state when no reports found
     */
    showEmptyState() {
        const container = document.querySelector('#public-reports-container');
        if (container) {
            const isFiltered = this.filteredReports.length !== this.reports.length;
            const message = isFiltered 
                ? 'No reports match your current filters.'
                : 'No public reports are currently available for download.';
            
            container.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5 reports-empty-state">
                        <i class="fas fa-file-alt"></i>
                        <p>${message}</p>
                        ${isFiltered ? '<button class="btn btn-outline-primary clear-filters-btn">Clear Filters</button>' : ''}
                    </div>
                </div>
            `;
            
            // Attach clear filters listener
            const clearFiltersBtn = container.querySelector('.clear-filters-btn');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', () => {
                    this.clearFilters();
                });
            }
        }
    }
    
    /**
     * Update reports count display
     */
    updateReportsCount() {
        const countElement = document.querySelector('.reports-count');
        if (countElement) {
            const showing = this.filteredReports.length;
            const total = this.reports.length;
            
            if (showing === total) {
                countElement.textContent = `${total} Reports`;
            } else {
                countElement.textContent = `Showing ${showing} of ${total} Reports`;
            }
        }
    }
    
    /**
     * Attach download event listeners
     */
    attachDownloadListeners() {
        document.querySelectorAll('.download-report-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const reportId = btn.dataset.reportId;
                const fileType = btn.dataset.fileType || 'pdf';
                
                try {
                    btn.disabled = true;
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    await this.ajax.downloadReport(reportId, fileType);
                    
                    if (typeof window.showToast === 'function') {
                        window.showToast('Success', 'Report downloaded successfully', 'success');
                    }
                    
                    // Track download analytics
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'download', {
                            'event_category': 'Public Reports',
                            'event_label': `Report ${reportId}`
                        });
                    }
                    
                } catch (error) {
                    this.ajax.handleError(error, 'download report');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-download"></i>';
                }
            });
        });
    }
    
    /**
     * Attach view event listeners
     */
    attachViewListeners() {
        document.querySelectorAll('.view-report-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Analytics tracking (if available)
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'view_report', {
                        'report_type': 'public_report'
                    });
                }
            });
        });
    }
    
    /**
     * Attach search event listeners
     */
    attachSearchListeners() {
        const searchInput = document.querySelector('#reports-search');
        if (searchInput) {
            let searchTimeout;
            
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.filterReports();
                }, 300);
            });
        }
    }
    
    /**
     * Attach filter event listeners
     */
    attachFilterListeners() {
        const filterSelect = document.querySelector('#report-type-filter');
        if (filterSelect) {
            filterSelect.addEventListener('change', () => {
                this.filterReports();
            });
        }
    }
    
    /**
     * Filter reports based on search and filters
     */
    filterReports() {
        const searchInput = document.querySelector('#reports-search');
        const filterSelect = document.querySelector('#report-type-filter');
        
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedType = filterSelect ? filterSelect.value : 'all';
        
        this.filteredReports = this.reports.filter(report => {
            // Text search
            const matchesSearch = !searchTerm || 
                (report.report_name && report.report_name.toLowerCase().includes(searchTerm)) ||
                (report.description && report.description.toLowerCase().includes(searchTerm));
            
            // Type filter
            const matchesType = selectedType === 'all' || report.report_type === selectedType;
            
            return matchesSearch && matchesType;
        });
        
        this.renderReportsList();
        this.updateReportsCount();
    }
    
    /**
     * Clear all filters
     */
    clearFilters() {
        const searchInput = document.querySelector('#reports-search');
        const filterSelect = document.querySelector('#report-type-filter');
        
        if (searchInput) searchInput.value = '';
        if (filterSelect) filterSelect.value = 'all';
        
        this.filteredReports = [...this.reports];
        this.renderReportsList();
        this.updateReportsCount();
    }
    
    /**
     * Show error message
     */
    showError(message) {
        if (typeof window.showToast === 'function') {
            window.showToast('Error', message, 'danger');
        } else {
            // No toast function available, could add fallback error display here
        }
    }
}
