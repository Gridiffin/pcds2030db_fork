/**
 * Bootstrap Modal Fix
 * 
 * This script fixes issues with Bootstrap modals.
 * It ensures modals appear properly and can be interacted with.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Ensure proper z-index
    const fixZIndex = () => {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.zIndex = '1050';
            // Make sure inputs and buttons are interactive
            modal.querySelectorAll('input, select, textarea, button').forEach(el => {
                el.style.zIndex = '1051';
                el.style.position = 'relative';
            });
        });
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.style.zIndex = '1040';
        });
    };
    
    // Fix modals when they're shown
    document.addEventListener('shown.bs.modal', fixZIndex);
    
    // Initial z-index fix
    fixZIndex();
});
