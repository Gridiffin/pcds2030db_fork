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

    // KPI Selector Logic
    const kpiSelector = document.getElementById('kpiSelector');
    if (kpiSelector) {
        const kpiCheckboxes = kpiSelector.querySelectorAll('input[type="checkbox"][name="selected_kpi_ids[]"]');
        const maxKpis = 3;

        kpiCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedKpis = kpiSelector.querySelectorAll('input[type="checkbox"][name="selected_kpi_ids[]"]:checked');
                if (checkedKpis.length > maxKpis) {
                    this.checked = false; // Prevent checking more than maxKpis
                    // Optionally, show a message to the user
                    alert(`You can select a maximum of ${maxKpis} KPIs.`);
                }
            });
        });
    }

    // Initialize the UI
    if (typeof ReportUI !== 'undefined') {
        ReportUI.initUI();
    } else {
        console.error('ReportUI module not found. Make sure report-ui.js is loaded before report-generator.js');
    }
});