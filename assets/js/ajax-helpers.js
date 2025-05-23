/**
 * AJAX Helper Functions
 * 
 * Provides helper functions for AJAX operations across the application
 */

/**
 * Generate the correct AJAX URL for a given endpoint
 * 
 * @param {string} endpoint - The endpoint filename (e.g., 'get_data.php')
 * @returns {string} - The complete URL to the AJAX endpoint
 */
function getAjaxUrl(endpoint) {
    // Get base URL from a meta tag (should be set in header)
    const baseUrlMeta = document.querySelector('meta[name="base-url"]');
    const baseUrl = baseUrlMeta ? baseUrlMeta.getAttribute('content') : '';
    
    // If we have a base URL, use it
    if (baseUrl) {
        return `${baseUrl}/app/ajax/${endpoint}`;
    }
    
    // Fallback to relative path - assumes we're in a views subdirectory
    return `../ajax/${endpoint}`;
}

/**
 * Make an AJAX request using fetch with standard error handling
 * 
 * @param {string} endpoint - The endpoint filename
 * @param {Object} options - Fetch options (method, headers, body, etc)
 * @returns {Promise} - Promise that resolves to the JSON response
 */
async function ajaxRequest(endpoint, options = {}) {
    const url = getAjaxUrl(endpoint);
    
    try {
        const response = await fetch(url, {
            // Default options
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('AJAX request failed:', error);
        throw error;
    }
}
