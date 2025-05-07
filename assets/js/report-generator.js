/**
 * Report Generator
 * 
 * Main controller for the PPTX report generation functionality.
 * This file coordinates the modules and initializes the report generator.
 */

// Global initialization flag to prevent duplicate initialization
if (typeof reportGeneratorInitialized === 'undefined') {
    var reportGeneratorInitialized = false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations
    if (reportGeneratorInitialized) {
        console.log('Report generator already initialized, skipping duplicate initialization.');
        return;
    }
    reportGeneratorInitialized = true;
    console.log('Initializing report generator...');
    
    // Initialize the UI
    if (typeof ReportUI !== 'undefined') {
        ReportUI.initUI();
    } else {
        console.error('ReportUI module not found. Make sure report-ui.js is loaded before report-generator.js');
    }
});