/**
 * URL Helper Functions
 * 
 * JavaScript equivalent of the PHP URL helper functions
 * to ensure consistency between server and client-side URL generation.
 */

// Base URL constant (will be set from PHP in header.php)
window.APP_URL = window.APP_URL || "";

/**
 * Generate URL for view files
 * 
 * @param {string} view Type of view ('admin' or 'agency')
 * @param {string} file The file name to link to
 * @param {Object} params Query parameters to append (optional)
 * @return {string} The complete URL
 */
function viewUrl(view, file, params = {}) {
    let url = `${window.APP_URL}/app/views/${view}/${file}`;
    
    // Add query parameters if provided
    const queryString = new URLSearchParams(params).toString();
    if (queryString) {
        // Check if the file already has query parameters
        url += (file.includes('?') ? '&' : '?') + queryString;
    }
    
    return url;
}

/**
 * Generate URL for API endpoints
 * 
 * @param {string} endpoint The API endpoint file name
 * @param {Object} params Query parameters to append (optional)
 * @return {string} The complete URL
 */
function apiUrl(endpoint, params = {}) {
    let url = `${window.APP_URL}/app/api/${endpoint}`;
    
    // Add query parameters if provided
    const queryString = new URLSearchParams(params).toString();
    if (queryString) {
        url += '?' + queryString;
    }
    
    return url;
}

/**
 * Generate URL for AJAX handlers
 * 
 * @param {string} handler The AJAX handler file name
 * @param {Object} params Query parameters to append (optional)
 * @return {string} The complete URL
 */
function ajaxUrl(handler, params = {}) {
    let url = `${window.APP_URL}/app/ajax/${handler}`;
    
    // Add query parameters if provided
    const queryString = new URLSearchParams(params).toString();
    if (queryString) {
        url += '?' + queryString;
    }
    
    return url;
}

/**
 * Generate URL for assets (CSS, JS, images)
 * 
 * @param {string} type Asset type ('css', 'js', 'images', 'fonts')
 * @param {string} file The asset file name
 * @return {string} The complete URL
 */
function assetUrl(type, file) {
    return `${window.APP_URL}/assets/${type}/${file}`;
}
