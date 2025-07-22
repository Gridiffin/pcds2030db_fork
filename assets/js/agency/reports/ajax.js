/**
 * Reports AJAX Module
 * Handles all AJAX operations for reports
 */

export class ReportsAjax {
    
    constructor() {
        this.baseUrl = this.getBaseUrl();
    }
    
    /**
     * Get base URL for API calls
     * @returns {string}
     */
    getBaseUrl() {
        const path = window.location.pathname;
        const segments = path.split('/');
        const baseIndex = segments.findIndex(seg => seg === 'app');
        
        if (baseIndex >= 0) {
            return segments.slice(0, baseIndex + 1).join('/');
        }
        
        return '/app';
    }
    
    /**
     * Load reports for specific period
     * @param {number} periodId 
     * @returns {Promise}
     */
    async loadReportsForPeriod(periodId) {
        try {
            const response = await fetch(`${this.baseUrl}/ajax/get_reports.php?period_id=${periodId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            return data;
            
        } catch (error) {
            console.error('Error loading reports:', error);
            throw error;
        }
    }
    
    /**
     * Load public reports
     * @returns {Promise}
     */
    async loadPublicReports() {
        try {
            const response = await fetch(`${this.baseUrl}/ajax/get_public_reports.php`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            return data;
            
        } catch (error) {
            console.error('Error loading public reports:', error);
            throw error;
        }
    }
    
    /**
     * Download report
     * @param {number} reportId 
     * @param {string} fileType 
     * @returns {Promise}
     */
    async downloadReport(reportId, fileType = 'pdf') {
        try {
            const response = await fetch(`${this.baseUrl}/ajax/download_report.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    report_id: reportId,
                    file_type: fileType
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Handle file download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `report_${reportId}.${fileType}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            return true;
            
        } catch (error) {
            console.error('Error downloading report:', error);
            throw error;
        }
    }
    
    /**
     * Get report statistics
     * @returns {Promise}
     */
    async getReportStatistics() {
        try {
            const response = await fetch(`${this.baseUrl}/ajax/get_report_stats.php`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            return data;
            
        } catch (error) {
            console.error('Error loading report statistics:', error);
            throw error;
        }
    }
    
    /**
     * Request report generation
     * @param {Object} params 
     * @returns {Promise}
     */
    async requestReportGeneration(params) {
        try {
            const response = await fetch(`${this.baseUrl}/ajax/generate_report.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(params)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            return data;
            
        } catch (error) {
            console.error('Error requesting report generation:', error);
            throw error;
        }
    }
    
    /**
     * Check report generation status
     * @param {string} jobId 
     * @returns {Promise}
     */
    async checkReportStatus(jobId) {
        try {
            const response = await fetch(`${this.baseUrl}/ajax/check_report_status.php?job_id=${jobId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            return data;
            
        } catch (error) {
            console.error('Error checking report status:', error);
            throw error;
        }
    }
    
    /**
     * Generic error handler for AJAX responses
     * @param {Error} error 
     * @param {string} operation 
     */
    handleError(error, operation = 'operation') {
        console.error(`Error during ${operation}:`, error);
        
        // Show user-friendly error message
        if (typeof window.showToast === 'function') {
            window.showToast('Error', `Failed to ${operation}. Please try again.`, 'danger');
        }
    }
    
    /**
     * Show loading state
     * @param {HTMLElement} element 
     */
    showLoading(element) {
        if (element) {
            element.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        }
    }
    
    /**
     * Hide loading state
     * @param {HTMLElement} element 
     */
    hideLoading(element) {
        if (element) {
            const loader = element.querySelector('.fa-spinner');
            if (loader) {
                loader.closest('.text-center').remove();
            }
        }
    }
}
