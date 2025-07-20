/**
 * Reports Logic Module
 * Pure functions for reports functionality
 */

export class ReportsLogic {
    
    /**
     * Format date for display
     * @param {string} dateString 
     * @returns {string}
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (error) {
            console.warn('Invalid date string:', dateString);
            return 'Invalid Date';
        }
    }
    
    /**
     * Format time for display
     * @param {string} dateString 
     * @returns {string}
     */
    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.warn('Invalid date string:', dateString);
            return 'Invalid Date';
        }
    }
    
    /**
     * Get report type badge class
     * @param {string} reportType 
     * @returns {string}
     */
    getReportTypeBadgeClass(reportType) {
        const badgeMap = {
            'program': 'bg-primary',
            'sector': 'bg-info',
            'public': 'bg-secondary',
            'agency': 'bg-success'
        };
        
        return badgeMap[reportType] || 'bg-secondary';
    }
    
    /**
     * Get file type icon
     * @param {string} fileType 
     * @returns {string}
     */
    getFileTypeIcon(fileType) {
        const iconMap = {
            'pdf': 'fas fa-file-pdf',
            'pptx': 'fas fa-file-powerpoint',
            'xlsx': 'fas fa-file-excel',
            'docx': 'fas fa-file-word'
        };
        
        return iconMap[fileType] || 'fas fa-file';
    }
    
    /**
     * Validate period selection
     * @param {string|number} periodId 
     * @returns {boolean}
     */
    validatePeriodSelection(periodId) {
        return periodId && !isNaN(parseInt(periodId)) && parseInt(periodId) > 0;
    }
    
    /**
     * Generate download URL
     * @param {string} filePath 
     * @returns {string}
     */
    generateDownloadUrl(filePath) {
        if (!filePath) return '#';
        
        // Get base URL from global config or construct it
        const baseUrl = window.APP_URL || '';
        return `${baseUrl}/reports/${filePath}`;
    }
    
    /**
     * Check if report is recent (within last 7 days)
     * @param {string} dateString 
     * @returns {boolean}
     */
    isRecentReport(dateString) {
        if (!dateString) return false;
        
        try {
            const reportDate = new Date(dateString);
            const weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            
            return reportDate >= weekAgo;
        } catch (error) {
            return false;
        }
    }
    
    /**
     * Filter reports by type
     * @param {Array} reports 
     * @param {string} reportType 
     * @returns {Array}
     */
    filterReportsByType(reports, reportType) {
        if (!reportType || reportType === 'all') {
            return reports;
        }
        
        return reports.filter(report => report.report_type === reportType);
    }
    
    /**
     * Sort reports by date
     * @param {Array} reports 
     * @param {string} order - 'asc' or 'desc'
     * @returns {Array}
     */
    sortReportsByDate(reports, order = 'desc') {
        return [...reports].sort((a, b) => {
            const dateA = new Date(a.generated_at);
            const dateB = new Date(b.generated_at);
            
            return order === 'desc' ? dateB - dateA : dateA - dateB;
        });
    }
    
    /**
     * Get report statistics
     * @param {Array} reports 
     * @returns {Object}
     */
    getReportStatistics(reports) {
        const stats = {
            total: reports.length,
            byType: {},
            recent: 0,
            thisMonth: 0
        };
        
        const now = new Date();
        const thisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        const weekAgo = new Date();
        weekAgo.setDate(weekAgo.getDate() - 7);
        
        reports.forEach(report => {
            // Count by type
            const type = report.report_type || 'unknown';
            stats.byType[type] = (stats.byType[type] || 0) + 1;
            
            // Count recent and this month
            const reportDate = new Date(report.generated_at);
            if (reportDate >= weekAgo) {
                stats.recent++;
            }
            if (reportDate >= thisMonth) {
                stats.thisMonth++;
            }
        });
        
        return stats;
    }
    
    /**
     * Truncate text with ellipsis
     * @param {string} text 
     * @param {number} maxLength 
     * @returns {string}
     */
    truncateText(text, maxLength = 50) {
        if (!text || text.length <= maxLength) {
            return text || '';
        }
        
        return text.substring(0, maxLength - 3) + '...';
    }
}
