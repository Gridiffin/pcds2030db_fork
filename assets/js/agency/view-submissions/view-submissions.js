/**
 * View Submissions - Main JavaScript Entry Point
 * ES6 module that imports CSS and initializes all components
 */

// Import CSS (Vite will bundle this)
import '../../../css/agency/view-submissions/view-submissions.css';

// Import JavaScript modules
import { initializeSubmissionActions } from './actions.js';
import { initializeTargetInteractions } from './targets.js';
import { initializeAttachmentHandlers } from './attachments.js';
import { validateSubmissionData } from './logic.js';

/**
 * Initialize view submissions page
 */
function initializeViewSubmissions() {
    console.log('Initializing view submissions page...');
    
    // Initialize all components
    initializeSubmissionActions();
    initializeTargetInteractions();
    initializeAttachmentHandlers();
    
    // Validate submission data on load
    if (window.submissionId) {
        validateSubmissionData(window.submissionId);
    }
    
    console.log('View submissions page initialized successfully');
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeViewSubmissions);
} else {
    initializeViewSubmissions();
}

// Export for external use if needed
export { initializeViewSubmissions };
