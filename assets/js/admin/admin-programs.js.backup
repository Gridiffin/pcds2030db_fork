/**
 * Admin Programs Bundle - Complete CSS and JS Entry Point
 * Fixed to ensure all styles are included properly
 */

// CRITICAL: Import main.css first to get all foundation styles
import '../../css/main.css';

// Import Bootstrap CSS explicitly if needed
// (main.css should include everything, but let's be explicit)

// Import admin-specific styles
import '../../css/admin/programs.css';
import '../../css/custom/admin.css';

// Import essential utilities that agencies use
import '../utilities/initialization.js';
import '../utilities/dropdown_init.js';

// Import shared admin functionality
import './admin-common.js';

// Import programs-specific functionality  
import './programs/programs.js';

console.log('Admin Programs bundle loaded successfully');

// Define triggerDeleteFromModal function directly in global scope
window.triggerDeleteFromModal = function(programId, programName) {
    console.log('triggerDeleteFromModal called with:', { programId, programName });
    
    const deleteModal = document.getElementById('deleteModal');
    if (!deleteModal) {
        console.error('Delete modal not found');
        return;
    }

    const programNameDisplay = deleteModal.querySelector('#program-name-display');
    const programIdInput = deleteModal.querySelector('#program-id-input');

    if (programNameDisplay) {
        programNameDisplay.textContent = programName;
        console.log('Set program name display to:', programName);
    } else {
        console.error('Program name display element not found');
    }
    
    if (programIdInput) {
        programIdInput.value = programId;
        console.log('Set program ID input to:', programId);
    } else {
        console.error('Program ID input element not found');
    }

    const modal = new bootstrap.Modal(deleteModal);
    modal.show();
};

// Check if triggerDeleteFromModal is available after import
setTimeout(() => {
    console.log('Checking if triggerDeleteFromModal is available:', typeof window.triggerDeleteFromModal);
    if (typeof window.triggerDeleteFromModal === 'function') {
        console.log('✅ triggerDeleteFromModal function is ready');
    } else {
        console.error('❌ triggerDeleteFromModal function is NOT available');
    }
}, 100);
