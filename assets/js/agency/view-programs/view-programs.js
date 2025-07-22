/**
 * View Programs - Main JavaScript Entry Point
 * Imports CSS and initializes all functionality
 */

// Import CSS
import '../../../css/agency/view-programs.css';

// Import JavaScript modules
import { ViewProgramsLogic } from './logic.js';
import { ViewProgramsDOM } from './dom.js';
import { ViewProgramsFilters } from './filters.js';

class ViewPrograms {
    constructor() {
        this.logic = new ViewProgramsLogic();
        this.dom = new ViewProgramsDOM();
        this.filters = new ViewProgramsFilters();
        
        this.init();
    }
    
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            
            
            // Initialize all components
            this.dom.init();
            this.filters.init();
            
            // Setup global event listeners
            this.setupGlobalEvents();
            
            
        });
    }
    
    setupGlobalEvents() {
        // Global error handling
        window.addEventListener('error', (event) => {
            console.error('💥 View Programs error:', event.error);
        });
        
        // Setup resize handler for responsive behavior
        window.addEventListener('resize', () => {
            this.dom.handleResize();
        });
    }
}

// Initialize the module
new ViewPrograms();

// Export for potential external use
export default ViewPrograms;
