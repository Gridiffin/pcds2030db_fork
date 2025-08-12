/**
 * Form Validation Utilities - DEPRECATED
 * 
 * This file contains deprecated wrapper functions for backward compatibility.
 * New code should import from assets/js/shared/formValidation.js instead.
 * 
 * @deprecated Use assets/js/shared/formValidation.js instead
 */

import {
    validateDateRange,
    showValidationError,
    clearValidationError,
    createFormAlert,
    initCharacterCounter
} from '../shared/formValidation.js';

// Deprecated wrapper functions for backward compatibility
// These will be removed after a deprecation period

/**
 * @deprecated Use validateDateRange from assets/js/shared/formValidation.js
 */
window.validateDateRange = validateDateRange;

/**
 * @deprecated Use showValidationError from assets/js/shared/formValidation.js
 */
window.showValidationError = showValidationError;

/**
 * @deprecated Use clearValidationError from assets/js/shared/formValidation.js
 */
window.clearValidationError = clearValidationError;

/**
 * @deprecated Use createFormAlert from assets/js/shared/formValidation.js
 */
window.createFormAlert = createFormAlert;

/**
 * @deprecated Use initCharacterCounter from assets/js/shared/formValidation.js
 */
window.initCharacterCounter = initCharacterCounter;

// Log deprecation warning
console.warn('form_validation.js is deprecated. Please import from assets/js/shared/formValidation.js instead.');
