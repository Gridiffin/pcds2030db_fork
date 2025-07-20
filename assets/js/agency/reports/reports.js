/**
 * Agency Reports JavaScript
 * Main entry point for reports functionality
 */

// Import CSS
import '../../../css/agency/reports/reports.css';

// Import JavaScript modules
import { ReportsLogic } from './logic.js';
import { ReportsAjax } from './ajax.js';
import { ViewReports } from './view_reports.js';
import { PublicReports } from './public_reports.js';

// Initialize based on current page
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    
    // Initialize common reports logic
    const logic = new ReportsLogic();
    const ajax = new ReportsAjax();
    
    // Page-specific initialization
    switch(currentPage) {
        case 'view_reports.php':
            const viewReports = new ViewReports(logic, ajax);
            viewReports.init();
            break;
            
        case 'public_reports.php':
            const publicReports = new PublicReports(logic, ajax);
            publicReports.init();
            break;
    }
});

// Export for global access if needed
window.ReportsModule = {
    ReportsLogic,
    ReportsAjax,
    ViewReports,
    PublicReports
};
