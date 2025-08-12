/**
 * Form Utilities - DEPRECATED
 * 
 * This file contains deprecated wrapper functions for backward compatibility.
 * New code should import from assets/js/shared/formValidation.js instead.
 * 
 * @deprecated Use assets/js/shared/formValidation.js instead
 */

import {
    validateForm,
    showValidationError,
    clearValidationError,
    createFormAlert,
    validateDateRange,
    addCharacterCounter,
    setButtonLoading,
    initPasswordToggle
} from '../shared/formValidation.js';

// Deprecated wrapper functions for backward compatibility
// These will be removed after a deprecation period

/**
 * @deprecated Use validateForm from assets/js/shared/formValidation.js
 */
window.validateForm = validateForm;

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
 * @deprecated Use validateDateRange from assets/js/shared/formValidation.js
 */
window.validateDateRange = validateDateRange;

/**
 * @deprecated Use addCharacterCounter from assets/js/shared/formValidation.js
 */
window.addCharacterCounter = addCharacterCounter;

/**
 * @deprecated Use setButtonLoading from assets/js/shared/formValidation.js
 */
window.setButtonLoading = setButtonLoading;

/**
 * @deprecated Use initPasswordToggle from assets/js/shared/formValidation.js
 */
window.initPasswordToggle = initPasswordToggle;

// Log deprecation warning
console.warn('form_utils.js is deprecated. Please import from assets/js/shared/formValidation.js instead.');
