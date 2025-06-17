/**
 * Responsive Navbar Brand Text Handler
 * Dynamically adjusts navbar brand text based on screen size
 */

(function() {
    'use strict';

    // Breakpoints for different text versions
    const BREAKPOINTS = {
        ULTRA_SMALL: 380,
        SMALL: 576,
        MEDIUM: 768
    };

    // Get the navbar brand element
    let navbarBrand = null;
    let brandTextElement = null;

    // Text variations
    let fullText = '';
    let shortText = '';
    let ultraShortText = '';

    /**
     * Initialize the responsive navbar text handler
     */
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupResponsiveText);
        } else {
            setupResponsiveText();
        }
    }

    /**
     * Setup responsive text functionality
     */
    function setupResponsiveText() {
        navbarBrand = document.querySelector('.navbar-brand');
        if (!navbarBrand) return;

        brandTextElement = navbarBrand.querySelector('.brand-text');
        if (!brandTextElement) return;

        // Get text variations from data attributes
        fullText = navbarBrand.getAttribute('data-full-text') || brandTextElement.textContent;
        shortText = navbarBrand.getAttribute('data-short-text') || 'PCDS 2030 Dashboard';
        ultraShortText = navbarBrand.getAttribute('data-ultra-short-text') || 'PCDS Dashboard';

        // Set initial text based on current screen size
        updateBrandText();

        // Add resize listener
        window.addEventListener('resize', debounce(updateBrandText, 100));
    }

    /**
     * Update brand text based on current screen size
     */
    function updateBrandText() {
        if (!brandTextElement) return;

        const screenWidth = window.innerWidth;
        let newText = fullText;

        if (screenWidth <= BREAKPOINTS.ULTRA_SMALL) {
            newText = ultraShortText;
        } else if (screenWidth <= BREAKPOINTS.SMALL) {
            newText = shortText;
        } else if (screenWidth <= BREAKPOINTS.MEDIUM) {
            newText = shortText;
        }

        // Only update if text has changed
        if (brandTextElement.textContent !== newText) {
            brandTextElement.textContent = newText;
            
            // Update title attribute for accessibility
            navbarBrand.setAttribute('title', fullText);
        }
    }

    /**
     * Debounce function to limit resize event frequency
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initialize when script loads
    init();

    // Expose to global scope for debugging
    window.ResponsiveNavbarText = {
        updateBrandText,
        getCurrentText: () => brandTextElement ? brandTextElement.textContent : null,
        getFullText: () => fullText,
        getShortText: () => shortText,
        getUltraShortText: () => ultraShortText
    };
})();
