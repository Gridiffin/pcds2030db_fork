/**
 * Initiatives Listing DOM Module
 * Handles DOM manipulation for initiatives listing page
 */

import {
    initializeSearch,
    initializeTooltips,
    addLoadingStates,
    enhanceTableInteractions
} from './view.js';

/**
 * Initialize initiatives listing page functionality
 */
export function initializeListingPage() {
    console.log('Initializing initiatives listing page...');
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Add loading states to buttons
    addLoadingStates();
    
    // Enhance table interactions
    enhanceTableInteractions();
    
    // Initialize filter reset functionality
    initializeFilterReset();
    
    console.log('Initiatives listing page initialized successfully');
}

/**
 * Initialize filter reset functionality
 */
function initializeFilterReset() {
    const resetLink = document.querySelector('a[href*="initiatives.php"]:not([href*="view_initiative"])');
    if (!resetLink) return;
    
    resetLink.addEventListener('click', function(e) {
        // Add loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Resetting...';
        
        // Navigate after short delay for UX
        setTimeout(() => {
            window.location.href = this.href;
        }, 300);
    });
}

/**
 * Initialize responsive table features
 */
export function initializeResponsiveTable() {
    const table = document.querySelector('.table-responsive table');
    if (!table) return;
    
    // Add horizontal scroll indicator on mobile
    const tableContainer = table.parentElement;
    
    function checkScrollable() {
        const isScrollable = tableContainer.scrollWidth > tableContainer.clientWidth;
        
        if (isScrollable && window.innerWidth <= 768) {
            if (!tableContainer.querySelector('.scroll-indicator')) {
                const indicator = document.createElement('div');
                indicator.className = 'scroll-indicator alert alert-info alert-sm';
                indicator.innerHTML = '<i class="fas fa-arrows-alt-h me-1"></i>Scroll horizontally to see more columns';
                tableContainer.parentElement.insertBefore(indicator, tableContainer);
            }
        } else {
            const indicator = tableContainer.parentElement.querySelector('.scroll-indicator');
            if (indicator) {
                indicator.remove();
            }
        }
    }
    
    // Check on load and resize
    checkScrollable();
    window.addEventListener('resize', checkScrollable);
}
