/**
 * Create Program Page - Main Entry Point
 * Handles program creation form functionality
 */

// Import CSS
import '../../../css/agency/programs/create_program.css';

// Import modules
import { initProgramNumberValidation } from './createLogic.js';
import { initFormValidation } from './formValidation.js';
import { initUserPermissions } from './userPermissions.js';

// Initialize all modules when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize program number validation
    initProgramNumberValidation();

    // Initialize form validation
    initFormValidation();

    // Initialize user permissions
    initUserPermissions();
}); 