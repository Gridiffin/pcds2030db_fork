/**
 * Program Details Main JavaScript - ES6 Module Entry Point
 * 
 * Main entry point for program details page functionality.
 * Imports and initializes modular components.
 */

// Import CSS
import '../../../css/agency/program-details/program-details.css';

// Import modular JavaScript components
import { ProgramDetailsLogic } from './logic.js';
import { ProgramDetailsModals } from './modals.js';
import { ProgramDetailsToast } from './toast.js';

/**
 * Main Program Details Controller
 */
class ProgramDetailsController {
    constructor() {
        // Ensure window variables are available
        this.programId = window.programId;
        this.isOwner = window.isOwner || false;
        this.canEdit = window.canEdit || false;
        this.currentUser = window.currentUser || {};
        this.APP_URL = window.APP_URL || '';
        
        // Initialize components
        this.logic = new ProgramDetailsLogic(this);
        this.modals = new ProgramDetailsModals(this);
        this.toast = new ProgramDetailsToast(this);
        
        this.init();
    }

    /**
     * Initialize the program details page
     */
    init() {
        console.log('Initializing Program Details page...');
        
        try {
            // Initialize components
            this.logic.init();
            this.modals.init();
            this.toast.init();
            
            // Bind global events
            this.bindEvents();
            
            // Load initial data
            this.loadInitialData();
            
            console.log('Program Details page initialized successfully');
        } catch (error) {
            console.error('Error initializing Program Details page:', error);
            this.showError('Failed to initialize page. Please refresh and try again.');
        }
    }

    /**
     * Bind global event listeners
     */
    bindEvents() {
        // Global error handler for fetch requests
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            this.showError('An unexpected error occurred. Please try again.');
        });

        // Handle browser back/forward navigation
        window.addEventListener('popstate', (event) => {
            // Refresh data if needed when navigating back to page
            this.refreshData();
        });

        // Handle visibility change (when user returns to tab)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Refresh data when user returns to tab
                this.refreshData();
            }
        });
    }

    /**
     * Load initial data for the page
     */
    async loadInitialData() {
        try {
            // Load program status
            await this.logic.loadProgramStatus();
            
            // Load any additional data as needed
            await this.logic.loadStatistics();
            
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showError('Failed to load some data. Please refresh the page.');
        }
    }

    /**
     * Refresh page data
     */
    async refreshData() {
        try {
            console.log('Refreshing program details data...');
            await this.loadInitialData();
        } catch (error) {
            console.error('Error refreshing data:', error);
        }
    }

    /**
     * Show error message to user
     */
    showError(message) {
        if (this.toast && typeof this.toast.showError === 'function') {
            this.toast.showError(message);
        } else {
            // Fallback to alert if toast not available
            alert(message);
        }
    }

    /**
     * Show success message to user
     */
    showSuccess(message) {
        if (this.toast && typeof this.toast.showSuccess === 'function') {
            this.toast.showSuccess(message);
        } else {
            console.log('Success:', message);
        }
    }

    /**
     * Show info message to user
     */
    showInfo(message) {
        if (this.toast && typeof this.toast.showInfo === 'function') {
            this.toast.showInfo(message);
        } else {
            console.log('Info:', message);
        }
    }

    /**
     * Utility method to make API calls
     */
    async apiCall(endpoint, options = {}) {
        const url = `${this.APP_URL}${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };

        const finalOptions = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, finalOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if we're on the program details page
    if (typeof window.programId !== 'undefined' && window.programId) {
        window.programDetailsController = new ProgramDetailsController();
    }
});

// Export for potential external use
export { ProgramDetailsController };
