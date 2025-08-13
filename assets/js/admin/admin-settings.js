/**
 * Admin Settings JavaScript Entry Point
 * Imports modular CSS and initializes settings functionality
 */

// Import settings-specific CSS bundle
import '../../css/admin/settings/settings.css';

// Settings functionality is handled by system_settings.js loaded separately

// Settings initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Settings loaded');
    
    // Initialize settings-specific features
    initializeSettings();
});

function initializeSettings() {
    // Settings-specific initialization code
    console.log('Settings module initialized');
}