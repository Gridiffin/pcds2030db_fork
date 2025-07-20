/**
 * Base Module Class
 * Provides common functionality for all page modules
 */

export class BaseModule {
    constructor(moduleName) {
        this.moduleName = moduleName;
        this.initialized = false;
        this.eventListeners = [];
        this.intervals = [];
        this.timeouts = [];
    }

    /**
     * Initialize the module
     */
    init() {
        if (this.initialized) {
            console.warn(`${this.moduleName}: Module already initialized`);
            return;
        }

        console.log(`${this.moduleName}: Initializing module`);
        this.initialized = true;
        
        // Common initialization
        this.setupErrorHandling();
    }

    /**
     * Set up global error handling for the module
     */
    setupErrorHandling() {
        // Store original console.error to prevent infinite loops
        const originalError = console.error;
        
        // Add module context to errors
        window.addEventListener('error', (event) => {
            originalError(`${this.moduleName}: JavaScript error:`, event.error);
        });
    }

    /**
     * Add event listener and track it for cleanup
     */
    addEventListener(element, event, handler, options) {
        if (!element) {
            console.warn(`${this.moduleName}: Cannot add event listener - element is null`);
            return;
        }

        element.addEventListener(event, handler, options);
        
        // Store for cleanup
        this.eventListeners.push({
            element,
            event,
            handler,
            options
        });
    }

    /**
     * Remove tracked event listener
     */
    removeEventListener(element, event, handler) {
        if (!element) return;

        element.removeEventListener(event, handler);
        
        // Remove from tracking
        this.eventListeners = this.eventListeners.filter(listener => 
            !(listener.element === element && 
              listener.event === event && 
              listener.handler === handler)
        );
    }

    /**
     * Set interval and track it for cleanup
     */
    setInterval(callback, delay) {
        const intervalId = setInterval(callback, delay);
        this.intervals.push(intervalId);
        return intervalId;
    }

    /**
     * Clear tracked interval
     */
    clearInterval(intervalId) {
        clearInterval(intervalId);
        this.intervals = this.intervals.filter(id => id !== intervalId);
    }

    /**
     * Set timeout and track it for cleanup
     */
    setTimeout(callback, delay) {
        const timeoutId = setTimeout(callback, delay);
        this.timeouts.push(timeoutId);
        return timeoutId;
    }

    /**
     * Clear tracked timeout
     */
    clearTimeout(timeoutId) {
        clearTimeout(timeoutId);
        this.timeouts = this.timeouts.filter(id => id !== timeoutId);
    }

    /**
     * Show loading state for an element
     */
    showLoading(element, message = 'Loading...') {
        if (!element) return;

        element.innerHTML = `
            <div class="d-flex justify-content-center align-items-center p-3">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>${message}</span>
            </div>
        `;
    }

    /**
     * Show error state for an element
     */
    showError(element, message = 'An error occurred') {
        if (!element) return;

        element.innerHTML = `
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span>${message}</span>
            </div>
        `;
    }

    /**
     * Show empty state for an element
     */
    showEmpty(element, message = 'No data available', icon = 'fas fa-inbox') {
        if (!element) return;

        element.innerHTML = `
            <div class="text-center text-muted p-4">
                <i class="${icon} fa-2x mb-3 d-block"></i>
                <p class="mb-0">${message}</p>
            </div>
        `;
    }

    /**
     * Get element by ID with module context
     */
    getElementById(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn(`${this.moduleName}: Element with ID '${id}' not found`);
        }
        return element;
    }

    /**
     * Query selector with module context
     */
    querySelector(selector) {
        const element = document.querySelector(selector);
        if (!element) {
            console.warn(`${this.moduleName}: Element with selector '${selector}' not found`);
        }
        return element;
    }

    /**
     * Query selector all with module context
     */
    querySelectorAll(selector) {
        const elements = document.querySelectorAll(selector);
        if (elements.length === 0) {
            console.warn(`${this.moduleName}: No elements found with selector '${selector}'`);
        }
        return elements;
    }

    /**
     * Log module message
     */
    log(message, ...args) {
        console.log(`${this.moduleName}:`, message, ...args);
    }

    /**
     * Log module warning
     */
    warn(message, ...args) {
        console.warn(`${this.moduleName}:`, message, ...args);
    }

    /**
     * Log module error
     */
    error(message, ...args) {
        console.error(`${this.moduleName}:`, message, ...args);
    }

    /**
     * Check if module is initialized
     */
    isInitialized() {
        return this.initialized;
    }

    /**
     * Get module name
     */
    getModuleName() {
        return this.moduleName;
    }

    /**
     * Clean up module resources
     */
    destroy() {
        if (!this.initialized) {
            this.warn('Attempting to destroy uninitialized module');
            return;
        }

        this.log('Destroying module');

        // Clear all tracked event listeners
        this.eventListeners.forEach(({ element, event, handler }) => {
            if (element && element.removeEventListener) {
                element.removeEventListener(event, handler);
            }
        });
        this.eventListeners = [];

        // Clear all tracked intervals
        this.intervals.forEach(intervalId => {
            clearInterval(intervalId);
        });
        this.intervals = [];

        // Clear all tracked timeouts
        this.timeouts.forEach(timeoutId => {
            clearTimeout(timeoutId);
        });
        this.timeouts = [];

        this.initialized = false;
        this.log('Module destroyed');
    }
}
