/**
 * Target Number Utility Functions
 * 
 * Provides robust functions for parsing and manipulating target numbers
 * in the format: {programNumber}.{counter}
 */

/**
 * Extract the counter from a target number
 * @param {string} targetNumber - The full target number (e.g., "31.A.1", "31.2B.5")
 * @returns {string} The counter part (e.g., "1", "5")
 */
function extractCounterFromTargetNumber(targetNumber) {
    if (!targetNumber || typeof targetNumber !== 'string') {
        return '';
    }
    
    // Remove any leading/trailing whitespace
    const trimmed = targetNumber.trim();
    if (!trimmed) return '';
    
    // Extract the last part after the last dot (most robust approach)
    const match = trimmed.match(/\.([^.]+)$/);
    return match ? match[1] : '';
}

/**
 * Extract the program number prefix from a target number
 * @param {string} targetNumber - The full target number (e.g., "31.A.1", "31.2B.5")
 * @returns {string} The program number part (e.g., "31.A", "31.2B")
 */
function extractProgramNumberFromTargetNumber(targetNumber) {
    if (!targetNumber || typeof targetNumber !== 'string') {
        return '';
    }
    
    // Remove any leading/trailing whitespace
    const trimmed = targetNumber.trim();
    if (!trimmed) return '';
    
    // Get everything before the last dot
    const lastDotIndex = trimmed.lastIndexOf('.');
    return lastDotIndex !== -1 ? trimmed.substring(0, lastDotIndex) : trimmed;
}

/**
 * Construct a full target number from program number and counter
 * @param {string} programNumber - The program number (e.g., "31.A", "31.2B")
 * @param {string} counter - The counter (e.g., "1", "5")
 * @returns {string} The full target number (e.g., "31.A.1", "31.2B.5")
 */
function constructTargetNumber(programNumber, counter) {
    if (!programNumber || !counter) {
        return '';
    }
    
    return `${programNumber.trim()}.${counter.trim()}`;
}

/**
 * Validate if a target number follows the correct format
 * @param {string} targetNumber - The target number to validate
 * @returns {boolean} True if valid, false otherwise
 */
function isValidTargetNumberFormat(targetNumber) {
    if (!targetNumber || typeof targetNumber !== 'string') {
        return false;
    }
    
    // Pattern: programNumber.counter where counter is alphanumeric
    const pattern = /^[^.]+\.\w+$/;
    return pattern.test(targetNumber.trim());
}

/**
 * Get all counters from an array of target numbers
 * @param {Array} targetNumbers - Array of target number strings
 * @returns {Array} Array of counter strings
 */
function extractCountersFromTargetNumbers(targetNumbers) {
    if (!Array.isArray(targetNumbers)) {
        return [];
    }
    
    return targetNumbers
        .map(targetNumber => extractCounterFromTargetNumber(targetNumber))
        .filter(counter => counter !== '');
}

// Export functions for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        extractCounterFromTargetNumber,
        extractProgramNumberFromTargetNumber,
        constructTargetNumber,
        isValidTargetNumberFormat,
        extractCountersFromTargetNumbers
    };
} 