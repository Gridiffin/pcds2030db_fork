/**
 * Admin Users JavaScript Entry Point
 * Imports modular CSS and initializes users functionality
 */

// Import users-specific CSS bundle
import '../../css/admin/users/users.css';

// Users functionality is handled by manage_users.js loaded separately

// Users initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Users loaded');
    
    // Initialize users-specific features
    initializeUsers();
});

function initializeUsers() {
    // Users-specific initialization code
    console.log('Users module initialized');
}