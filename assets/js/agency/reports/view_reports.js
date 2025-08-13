/**
 * View Reports Page Logic
 * Handles functionality specific to the view reports page
 */

// Import CSS for reports
import '../../../css/agency/reports/reports.css';
import '../../../css/agency/reports/partials/list.css';
import '../../../css/agency/reports/partials/info.css';
import '../../../css/agency/reports/partials/filter.css';

export class ViewReports {
    
    constructor(logic, ajax) {
        this.logic = logic;
        this.ajax = ajax;
        this.currentPeriod = null;
        this.reports = [];
    }
    
    /**
     * Initialize the view reports page
     */
    init() {
        this.attachEventListeners();
        this.initializeFilters();
        this.loadInitialData();
        
        
    }
    
    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Period filter form submission
        const filterForm = document.querySelector('#period-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handlePeriodFilter();
            });
        }
        
        // Period selection change
        const periodSelect = document.querySelector('#period_id');
        if (periodSelect) {
            periodSelect.addEventListener('change', () => {
                this.handlePeriodChange();
            });
        }
        
        // Clear filter button
        const clearBtn = document.querySelector('.clear-filter-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearFilters();
            });
        }
        
        // Download buttons
        this.attachDownloadListeners();
        
        // View report buttons
        this.attachViewListeners();
    }
    
    /**
     * Initialize filters from URL parameters
     */
    initializeFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        const periodId = urlParams.get('period_id');
        
        if (periodId) {
            const periodSelect = document.querySelector('#period_id');
            if (periodSelect) {
                periodSelect.value = periodId;
                this.currentPeriod = parseInt(periodId);
            }
        }
    }
    
    /**
     * Load initial data
     */
    async loadInitialData() {
        if (this.currentPeriod) {
            await this.loadReportsForPeriod(this.currentPeriod);
        }
    }
    
    /**
     * Handle period filter submission
     */
    async handlePeriodFilter() {
        const periodSelect = document.querySelector('#period_id');
        const periodId = periodSelect ? parseInt(periodSelect.value) : null;
        
        if (!this.logic.validatePeriodSelection(periodId)) {
            this.showError('Please select a valid reporting period.');
            return;
        }
        
        // Update URL without refresh
        const url = new URL(window.location);
        url.searchParams.set('period_id', periodId);
        window.history.pushState({}, '', url);
        
        this.currentPeriod = periodId;
        await this.loadReportsForPeriod(periodId);
    }
    
    /**
     * Handle period selection change
     */
    handlePeriodChange() {
        const periodSelect = document.querySelector('#period_id');
        const periodId = periodSelect ? parseInt(periodSelect.value) : null;
        
        // Enable/disable filter button based on selection
        const filterBtn = document.querySelector('.filter-btn');
        if (filterBtn) {
            filterBtn.disabled = !this.logic.validatePeriodSelection(periodId);
        }
    }
    
    /**
     * Clear all filters
     */
    clearFilters() {
        const periodSelect = document.querySelector('#period_id');
        if (periodSelect) {
            periodSelect.value = '';
        }
        
        // Clear URL parameters
        const url = new URL(window.location);
        url.searchParams.delete('period_id');
        window.history.pushState({}, '', url);
        
        this.currentPeriod = null;
        this.clearReportsList();
        this.showSelectPeriodMessage();
    }
    
    /**
     * Load reports for specific period
     */
    async loadReportsForPeriod(periodId) {
        try {
            const reportsContainer = document.querySelector('#reports-container');
            if (reportsContainer) {
                this.ajax.showLoading(reportsContainer);
            }
            
            const data = await this.ajax.loadReportsForPeriod(periodId);
            this.reports = data.reports || [];
            
            this.renderReportsList();
            this.updateReportsCount();
            
        } catch (error) {
            this.ajax.handleError(error, 'load reports');
            this.showError('Failed to load reports. Please try again.');
        }
    }
    
    /**
     * Render reports list
     */
    renderReportsList() {
        const container = document.querySelector('#reports-container');
        if (!container) return;
        
        if (this.reports.length === 0) {
            this.showEmptyState();
            return;
        }
        
        const reportsHtml = this.reports.map(report => this.renderReportRow(report)).join('');
        
        container.innerHTML = `
            <div class="card reports-list">
                <div class="card-header">
                    <h5 class="card-title">Available Reports</h5>
                    <span class="reports-count-badge">${this.reports.length} Reports</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table reports-table">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Generated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${reportsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        // Re-attach event listeners for new elements
        this.attachDownloadListeners();
        this.attachViewListeners();
    }
    
    /**
     * Render individual report row
     */
    renderReportRow(report) {
        const badgeClass = this.logic.getReportTypeBadgeClass(report.report_type);
        const downloadUrl = this.logic.generateDownloadUrl(report.file_path);
        const formattedDate = this.logic.formatDate(report.generated_at);
        const isRecent = this.logic.isRecentReport(report.generated_at);
        
        return `
            <tr ${isRecent ? 'class="table-warning"' : ''}>
                <td>
                    <div class="report-name">${report.report_name || 'Untitled Report'}</div>
                    ${isRecent ? '<small class="text-warning"><i class="fas fa-star"></i> New</small>' : ''}
                </td>
                <td>${this.logic.truncateText(report.description || 'No description available', 80)}</td>
                <td>
                    <span class="badge ${badgeClass} report-type-badge">
                        ${(report.report_type || 'general').charAt(0).toUpperCase() + (report.report_type || 'general').slice(1)}
                    </span>
                </td>
                <td>${formattedDate}</td>
                <td>
                    <div class="reports-actions">
                        <a href="${downloadUrl}" class="btn btn-outline-primary btn-sm view-report-btn" 
                           target="_blank" title="View Report">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-outline-success btn-sm download-report-btn" 
                                data-report-id="${report.report_id}" 
                                data-file-type="${report.file_type || 'pdf'}"
                                title="Download Report">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    
    /**
     * Show empty state when no reports found
     */
    showEmptyState() {
        const container = document.querySelector('#reports-container');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-info reports-alert">
                    <i class="fas fa-info-circle"></i>
                    No reports found for the selected reporting period.
                </div>
            `;
        }
    }
    
    /**
     * Show select period message
     */
    showSelectPeriodMessage() {
        const container = document.querySelector('#reports-container');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-info reports-alert">
                    <i class="fas fa-info-circle"></i>
                    Please select a reporting period to view available reports.
                </div>
            `;
        }
    }
    
    /**
     * Update reports count display
     */
    updateReportsCount() {
        const countElement = document.querySelector('.reports-count-badge');
        if (countElement) {
            countElement.textContent = `${this.reports.length} Reports`;
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
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    await this.ajax.downloadReport(reportId, fileType);
                    
                    if (typeof window.showToast === 'function') {
                        window.showToast('Success', 'Report downloaded successfully', 'success');
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
                        'report_type': 'agency_report'
                    });
                }
            });
        });
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
    
    /**
     * Clear reports list
     */
    clearReportsList() {
        const container = document.querySelector('#reports-container');
        if (container) {
            container.innerHTML = '';
        }
    }
}

// Initialize reports functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reports view if on reports page
    if (document.querySelector('#reports-container') || document.querySelector('.reports-page')) {
        // Import other report modules if needed
        import('./logic.js').then(({ ReportsLogic }) => {
            import('./ajax.js').then(({ ReportsAjax }) => {
                const ajax = new ReportsAjax();
                const logic = new ReportsLogic();
                const viewReports = new ViewReports(logic, ajax);
                viewReports.init();
            }).catch(err => console.log('Reports modules not found, using basic functionality'));
        }).catch(err => console.log('Reports modules not found, using basic functionality'));
    }
});
