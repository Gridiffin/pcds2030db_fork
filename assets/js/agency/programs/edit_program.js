/**
 * Edit Program - Main JS
 *
 * Initializes the edit program page, imports necessary styles,
 * and wires up the logic for form handling and interactions.
 */

// Import styles
import '../../../css/agency/programs/edit_program_entry.css';
import '../../../css/agency/programs/edit_program.css';

// Import main utilities including showToast
import '../../main.js';

// Import logic
import { initEditProgram } from './editProgramLogic.js';

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    // Check if we are on the correct page
    if (document.getElementById('editProgramForm')) {
        initEditProgram();
    }
}); 