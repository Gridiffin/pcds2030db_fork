/**
 * Simple User Management - Main Module
 * Initializes user management components and handles coordination
 * This file must be loaded AFTER the component files
 */
document.addEventListener('DOMContentLoaded', function() {
    // Make sure dependencies are loaded
    if (typeof UserFormManager !== 'function' || 
        typeof ToastManager !== 'function' || 
        typeof UserTableManager !== 'function') {
        console.error('Required components not loaded. Check script loading order.');
        return;
    }

    // Import and initialize modules
    const formManager = UserFormManager();
    const toastManager = ToastManager();
    const tableManager = UserTableManager(formManager, toastManager);
    
    // Setup initial event listeners
    document.getElementById('addUserBtn').addEventListener('click', e => {
        e.preventDefault();
        formManager.showAddUserForm();
    });
    
    // Initial attachment of table action event listeners
    tableManager.attachEventListeners();
    
    // Check for page messages
    if (window.pageMessages && window.pageMessages.message) {
        toastManager.show(
            window.pageMessages.type === 'success' ? 'Success' : 'Error', 
            window.pageMessages.message, 
            window.pageMessages.type
        );
    }
    
    // Close form on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            formManager.hideForm();
        }
    });
});
