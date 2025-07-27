/**
 * Admin Dashboard JavaScript Entry Point
 * Imports modular CSS and initializes dashboard functionality
 */

// Import dashboard-specific CSS bundle
import '../../css/admin/dashboard/dashboard.css';

// Import dashboard functionality
import './dashboard.js';

// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Dashboard loaded');
    
    // Initialize dashboard-specific features
    initializeDashboard();
});

function initializeDashboard() {
    // Dashboard-specific initialization code
    // Core functionality is now handled by dashboard.js
    console.log('Dashboard module initialized');
}