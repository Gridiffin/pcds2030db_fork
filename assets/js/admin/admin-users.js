/**
 * Admin Users JavaScript Entry Point
 * Imports modular CSS and initializes users functionality
 */

// Import main.js for global utilities like showToast
import '../main.js';

// Import users-specific CSS bundle
import '../../css/admin/users/users.css';

// Import the actual users functionality
import './manage_users.js';
import './user_form_manager.js';
import './user_table_manager.js';

// Users initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Users bundle loaded with manage_users functionality');
});