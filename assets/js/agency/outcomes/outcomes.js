/**
 * Outcomes Module - Main JavaScript Entry Point
 * ES6 module that imports CSS and exports modular components
 */

// Import main CSS bundle
import '../../../css/agency/outcomes/outcomes.css';

// Import utility modules  
import { initializeTooltips, showAlert, formatDate } from '../../shared/utils.js';
import { BaseModule } from '../../shared/base-module.js';

// Import outcomes-specific modules
import { ViewOutcome } from './view.js';
import { EditOutcome } from './edit.js';
import { SubmitOutcomes } from './submit.js';
import { ChartManager } from './chart-manager.js';

/**
 * Main Outcomes Module Class
 */
class OutcomesModule extends BaseModule {
    constructor() {
        super('outcomes');
        this.viewModule = null;
        this.editModule = null;
        this.submitModule = null;
        this.chartManager = null;
    }

    /**
     * Initialize the module based on page context
     */
    init() {
        super.init();
        
        // Initialize common functionality
        this.initCommon();
        
        // Initialize page-specific functionality
        const pageType = this.getPageType();
        
        switch (pageType) {
            case 'view':
                this.initViewPage();
                break;
            case 'edit':
                this.initEditPage();
                break;
            case 'submit':
                this.initSubmitPage();
                break;
            default:
                console.warn('Unknown outcomes page type:', pageType);
        }
    }

    /**
     * Initialize common functionality across all outcomes pages
     */
    initCommon() {
        // Initialize tooltips
        initializeTooltips();
        
        // Initialize chart manager
        this.chartManager = new ChartManager();
        
        // Set up global error handling
        this.setupErrorHandling();
        
        // Add navbar body class for proper spacing
        document.body.classList.add('outcomes-page');
    }

    /**
     * Initialize view outcome page
     */
    initViewPage() {
        this.viewModule = new ViewOutcome(this.chartManager);
        this.viewModule.init();
    }

    /**
     * Initialize edit outcome page
     */
    initEditPage() {
        this.editModule = new EditOutcome(this.chartManager);
        this.editModule.init();
    }

    /**
     * Initialize submit outcomes page
     */
    initSubmitPage() {
        this.submitModule = new SubmitOutcomes();
        this.submitModule.init();
    }

    /**
     * Determine page type based on current URL or page indicators
     */
    getPageType() {
        const path = window.location.pathname;
        const filename = path.split('/').pop();
        
        if (filename.includes('view_outcome') || document.querySelector('.outcome-view-container')) {
            return 'view';
        } else if (filename.includes('edit_outcome') || document.querySelector('.outcome-edit-form')) {
            return 'edit';
        } else if (filename.includes('submit_outcomes') || document.querySelector('.outcomes-submit-container')) {
            return 'submit';
        }
        
        return 'unknown';
    }

    /**
     * Set up global error handling for outcomes module
     */
    setupErrorHandling() {
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Outcomes module unhandled promise rejection:', event.reason);
            showAlert('An unexpected error occurred. Please refresh the page and try again.', 'error');
        });
    }

    /**
     * Clean up resources
     */
    destroy() {
        super.destroy();
        
        if (this.viewModule) {
            this.viewModule.destroy();
        }
        
        if (this.editModule) {
            this.editModule.destroy();
        }
        
        if (this.submitModule) {
            this.submitModule.destroy();
        }
        
        if (this.chartManager) {
            this.chartManager.destroy();
        }
        
        document.body.classList.remove('outcomes-page');
    }
}

// Initialize module when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.outcomesModule = new OutcomesModule();
    window.outcomesModule.init();
});

// Export for potential external use
export { OutcomesModule, ViewOutcome, EditOutcome, SubmitOutcomes, ChartManager };
