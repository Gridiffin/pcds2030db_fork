/**
 * Agency Initiatives Module Entry Point
 * Main JavaScript file for the initiatives module
 */

// Import CSS for Vite bundling
import '../../css/agency/initiatives.css';

// Import initiative modules
import { initializeRatingChart } from './initiatives/view.js';
import { initializeListingPage, initializeResponsiveTable } from './initiatives/listing.js';

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Agency Initiatives module loading...');
    
    // Determine which page we're on based on DOM elements
    const isViewPage = document.getElementById('initiativeRatingChart') !== null;
    const isListingPage = document.querySelector('.initiatives-table, .table') !== null;
    
    if (isViewPage) {
        console.log('Initializing initiative view page...');
        initializeRatingChart();
    }
    
    if (isListingPage) {
        console.log('Initializing initiative listing page...');
        initializeListingPage();
        initializeResponsiveTable();
    }
    
    console.log('Agency Initiatives module loaded successfully');
});

// Export functions for manual initialization if needed
export {
    initializeRatingChart,
    initializeListingPage,
    initializeResponsiveTable
};
