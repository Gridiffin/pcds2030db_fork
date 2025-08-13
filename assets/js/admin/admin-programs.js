/**
 * Admin Programs JavaScript Entry Point
 * Imports modular CSS and initializes programs functionality
 */

// Import programs-specific CSS bundle
import '../../css/admin/programs/programs.css';

// Programs functionality is handled by programs_admin.js loaded separately

// Programs initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Programs loaded');
    
    // Initialize programs-specific features
    initializePrograms();
});

function initializePrograms() {
    // Programs-specific initialization code
    console.log('Programs module initialized');
}