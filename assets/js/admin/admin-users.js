/**
 * Admin Users JavaScript Entry Point
 * Imports modular CSS and initializes users functionality
 */

// Import users-specific CSS bundle
import '../../css/admin/users/users.css';

// Import the actual users functionality
import './manage_users.js';

// Users initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Users bundle loaded with manage_users functionality');
});