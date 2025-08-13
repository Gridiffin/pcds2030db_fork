/**
 * Admin Periods JavaScript Entry Point
 * Imports modular CSS and initializes periods functionality
 */

// Import periods-specific CSS bundle
import '../../css/admin/periods/periods.css';

// Import the actual periods functionality
import './periods-management.js';

// Periods initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Periods bundle loaded with periods-management functionality');
});