/**
 * Admin Periods JavaScript Entry Point
 * Imports modular CSS and initializes periods functionality
 */

// Import periods-specific CSS bundle
import '../../css/admin/periods/periods.css';

// Periods functionality is handled by reporting_periods.js loaded separately

// Periods initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Periods loaded');
    
    // Initialize periods-specific features
    initializePeriods();
});

function initializePeriods() {
    // Periods-specific initialization code
    console.log('Periods module initialized');
}