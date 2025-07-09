/**
 * Create Program Page JavaScript
 * Handles program number validation and initiative selection logic
 */

document.addEventListener('DOMContentLoaded', function() {
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

    // Handle initiative selection for program numbering
    initiativeSelect.addEventListener('change', function() {
        const selectedInitiative = this.value;
        const helpText = document.getElementById('number-help-text');
        const finalNumberDisplay = document.getElementById('final-number-display');
        const finalNumberPreview = document.getElementById('final-number-preview');
        const validationDiv = document.getElementById('number-validation');
        
        if (selectedInitiative) {
            const initiativeNumber = initiativeMap[selectedInitiative];
            programNumberInput.disabled = false;
            programNumberInput.placeholder = `e.g., ${initiativeNumber}.1 or ${initiativeNumber}.A`;
            helpText.textContent = `Program number must start with "${initiativeNumber}." (e.g., ${initiativeNumber}.1, ${initiativeNumber}.A)`;
            
            // Show final number preview
            finalNumberDisplay.style.display = 'block';
            finalNumberPreview.textContent = 'Will be generated automatically';
            
            // Clear any existing validation
            validationDiv.style.display = 'none';
            programNumberInput.classList.remove('is-valid', 'is-invalid');
        } else {
            programNumberInput.disabled = true;
            programNumberInput.placeholder = 'Select initiative first';
            helpText.textContent = 'Select an initiative to enable program numbering';
            finalNumberDisplay.style.display = 'none';
            validationDiv.style.display = 'none';
            programNumberInput.classList.remove('is-valid', 'is-invalid');
        }
    });

    // Handle program number validation with real-time feedback
    programNumberInput.addEventListener('input', function() {
        const number = this.value.trim();
        const validationDiv = document.getElementById('number-validation');
        const validationMessage = document.getElementById('validation-message');
        const finalNumberPreview = document.getElementById('final-number-preview');
        const selectedInitiative = initiativeSelect.value;
        
        if (!selectedInitiative) {
            validationDiv.style.display = 'none';
            return;
        }
        
        if (number) {
            const initiativeNumber = initiativeMap[selectedInitiative];
            let isValid = true;
            let message = '';
            
            // Check basic format
            if (!/^[a-zA-Z0-9.]+$/.test(number)) {
                isValid = false;
                message = 'Invalid format. Use only letters, numbers, and dots.';
            }
            // Check if it starts with initiative number
            else if (!number.startsWith(initiativeNumber + '.')) {
                isValid = false;
                message = `Program number must start with "${initiativeNumber}."`;
            }
            // Check if it has content after the initiative number
            else if (number === initiativeNumber + '.') {
                isValid = false;
                message = 'Please add a suffix after the initiative number (e.g., 1, A, 2B)';
            }
            // Check if it's too long
            else if (number.length > 20) {
                isValid = false;
                message = 'Program number is too long (max 20 characters)';
            }
            else {
                isValid = true;
                message = `Valid program number format (${initiativeNumber}.suffix)`;
            }
            
            // Update UI
            validationDiv.style.display = 'block';
            validationMessage.className = isValid ? 'text-success' : 'text-danger';
            validationMessage.textContent = message;
            programNumberInput.classList.remove('is-valid', 'is-invalid');
            programNumberInput.classList.add(isValid ? 'is-valid' : 'is-invalid');
            finalNumberPreview.textContent = number;
        } else {
            validationDiv.style.display = 'none';
            programNumberInput.classList.remove('is-valid', 'is-invalid');
            finalNumberPreview.textContent = 'Will be generated automatically';
        }
    });

    // Date validation for program timeline
    const form = document.getElementById('createProgramForm');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    if (form && startDateInput && endDateInput) {
        form.addEventListener('submit', function(e) {
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            let valid = true;
            let errorMsg = '';
            if (startDateInput.value && !dateRegex.test(startDateInput.value)) {
                valid = false;
                errorMsg += 'Start Date must be in YYYY-MM-DD format. ';
            }
            if (endDateInput.value && !dateRegex.test(endDateInput.value)) {
                valid = false;
                errorMsg += 'End Date must be in YYYY-MM-DD format. ';
            }
            if (!valid) {
                e.preventDefault();
                alert(errorMsg);
            }
        });
    }
});
