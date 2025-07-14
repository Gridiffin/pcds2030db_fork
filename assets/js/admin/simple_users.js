/**
 * Simple User Management - Main Module
 * Initializes user management components and handles coordination
 * This file must be loaded AFTER the component files
 */
document.addEventListener('DOMContentLoaded', function() {
    // Make sure dependencies are loaded
    if (typeof UserFormManager !== 'function' || 
        typeof UserTableManager !== 'function') {
        console.error('Required components not loaded. Check script loading order.');
        return;
    }

    // Import and initialize modules
    const formManager = UserFormManager();
    const tableManager = UserTableManager(formManager);
    
    // Note: Add User functionality is handled by the page header link
    // No need to set up event listeners for addUserBtn as it doesn't exist on this page
    
    // Initial attachment of table action event listeners
    tableManager.attachEventListeners();
    
    // Check for page messages
    if (window.pageMessages && window.pageMessages.message) {
        if (typeof window.showToast === 'function') {
            window.showToast(
                window.pageMessages.type === 'success' ? 'Success' : 'Error', 
                window.pageMessages.message, 
                window.pageMessages.type
            );
        } else {
            alert(`${window.pageMessages.type === 'success' ? 'Success' : 'Error'}: ${window.pageMessages.message}`);
        }
    }
    
    // Close form on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            formManager.hideForm();
        }
    });
});
