/**
 * Create Program - Business Logic
 * Handles program number validation and initiative selection logic
 */

// Constants
const PROGRAM_NUMBER_REGEX = /^[a-zA-Z0-9.]+$/;
const MAX_PROGRAM_NUMBER_LENGTH = 20;

/**
 * Validates a program number format
 * @param {string} number - The program number to validate
 * @param {string} initiativeNumber - The parent initiative number
 * @returns {Object} Validation result with isValid and message
 */
export function validateProgramNumber(number, initiativeNumber) {
    if (!number) {
        return { isValid: false, message: 'Program number is required' };
    }

    if (!PROGRAM_NUMBER_REGEX.test(number)) {
        return { isValid: false, message: 'Invalid format. Use only letters, numbers, and dots.' };
    }

    if (!number.startsWith(initiativeNumber + '.')) {
        return { isValid: false, message: `Program number must start with "${initiativeNumber}."` };
    }

    const validPattern = new RegExp('^' + initiativeNumber.replace(/[.*+?^${}()|[\\]\\]/g, '\\$&') + '\\.[a-zA-Z0-9]+$');
    if (!validPattern.test(number)) {
        return { isValid: false, message: 'Please add a suffix after the initiative number (e.g., 1, A, 2B)' };
    }

    if (number.length > MAX_PROGRAM_NUMBER_LENGTH) {
        return { isValid: false, message: 'Program number is too long (max 20 characters)' };
    }

    return { isValid: true, message: `Valid program number format (${initiativeNumber}.suffix)` };
}

/**
 * Checks if a program number is already in use
 * @param {string} number - The program number to check
 * @param {string} initiativeId - The selected initiative ID
 * @returns {Promise<boolean>} True if the number exists, false otherwise
 */
export async function checkProgramNumberExists(number, initiativeId) {
    try {
        const response = await fetch(`${window.APP_URL}/app/ajax/agency/check_program_number.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                initiative_id: initiativeId,
                program_number: number
            })
        });

        const data = await response.json();
        return data.exists;
    } catch (error) {
        console.error('Error checking program number:', error);
        throw new Error('Failed to check program number');
    }
}

/**
 * Updates the program number input state based on initiative selection
 * @param {HTMLSelectElement} initiativeSelect - The initiative select element
 * @param {HTMLInputElement} programNumberInput - The program number input element
 * @param {Object} initiativeMap - Map of initiative IDs to their numbers
 */
export function updateProgramNumberState(initiativeSelect, programNumberInput, initiativeMap) {
    const selectedInitiative = initiativeSelect.value;
    const helpText = document.getElementById('number-help-text');
    const finalNumberDisplay = document.getElementById('final-number-display');
    const finalNumberPreview = document.getElementById('final-number-preview');
    const validationDiv = document.getElementById('number-validation');

    if (selectedInitiative) {
        const initiativeNumber = initiativeMap[selectedInitiative];
        programNumberInput.disabled = false;
        programNumberInput.placeholder = `e.g., ${initiativeNumber}.1 or ${initiativeNumber}.A`;
        helpText.textContent = `Program number must start with "${initiativeNumber}." (e.g., ${initiativeNumber}.1, ${initiativeNumber}.A)`;
        finalNumberDisplay.style.display = 'block';
        finalNumberPreview.textContent = 'Will be generated automatically';
    } else {
        programNumberInput.disabled = true;
        programNumberInput.placeholder = 'Select initiative first';
        helpText.textContent = 'Select an initiative to enable program numbering';
        finalNumberDisplay.style.display = 'none';
    }

    // Clear validation state
    validationDiv.style.display = 'none';
    programNumberInput.classList.remove('is-valid', 'is-invalid');
}

/**
 * Initializes program number validation functionality
 */
export function initProgramNumberValidation() {
    const initiativeSelect = document.getElementById('initiative_id');
    const programNumberInput = document.getElementById('program_number');
    
    if (!initiativeSelect || !programNumberInput) {
        console.warn('Create program elements not found');
        return;
    }
    
    // Create initiative data map for validation
    const initiatives = window.initiativeData || [];
    const initiativeMap = {};
    initiatives.forEach(initiative => {
        initiativeMap[initiative.initiative_id] = initiative.initiative_number;
    });

    // Handle initiative selection
    initiativeSelect.addEventListener('change', () => {
        updateProgramNumberState(initiativeSelect, programNumberInput, initiativeMap);
    });

    // Handle program number validation
    programNumberInput.addEventListener('input', async () => {
        const number = programNumberInput.value.trim();
        const validationDiv = document.getElementById('number-validation');
        const validationMessage = document.getElementById('validation-message');
        const finalNumberPreview = document.getElementById('final-number-preview');
        const selectedInitiative = initiativeSelect.value;
        
        if (!selectedInitiative || !number) {
            validationDiv.style.display = 'none';
            programNumberInput.classList.remove('is-valid', 'is-invalid');
            finalNumberPreview.textContent = 'Will be generated automatically';
            return;
        }

        const initiativeNumber = initiativeMap[selectedInitiative];
        const validation = validateProgramNumber(number, initiativeNumber);

        if (validation.isValid) {
            try {
                const exists = await checkProgramNumberExists(number, selectedInitiative);
                if (exists) {
                    validation.isValid = false;
                    validation.message = 'Duplicate program number. This number is already in use for the selected initiative.';
                }
            } catch (error) {
                validation.isValid = false;
                validation.message = 'Error checking program number. Please try again.';
            }
        }

        // Update UI
        validationDiv.style.display = 'block';
        validationMessage.className = validation.isValid ? 'text-success' : 'text-danger';
        validationMessage.textContent = validation.message;
        programNumberInput.classList.remove('is-valid', 'is-invalid');
        programNumberInput.classList.add(validation.isValid ? 'is-valid' : 'is-invalid');
        finalNumberPreview.textContent = number || 'Will be generated automatically';
    });
} 