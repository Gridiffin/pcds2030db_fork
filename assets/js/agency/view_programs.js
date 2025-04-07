/**
 * View Programs Functionality
 * Handles filtering and interactions on the programs list page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete functionality
    initDeleteButtons();
    
    // Initialize filters using the shared utility
    initializeFilters({
        tableId: 'programsTable',
        searchInputId: 'programSearch',
        statusFilterId: 'statusFilter',
        typeFilterId: 'programTypeFilter',
        resetButtonId: 'resetFilters'
    });
    
    // Update status filter dropdown with new values
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        // Clear existing options
        statusFilter.innerHTML = '';
        
        // Add new options with updated status values
        statusFilter.innerHTML = `
            <option value="">All Statuses</option>
            <option value="target-achieved">Monthly Target Achieved</option>
            <option value="on-track-yearly">On Track for Year</option>
            <option value="severe-delay">Severe Delays</option>
            <option value="not-started">Not Started</option>
        `;
    }
    
    // Add event listener specifically for the reset button
    const resetButton = document.getElementById('resetFilters');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            // Reset all filter inputs
            document.getElementById('programSearch').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('programTypeFilter').value = '';
            
            // Trigger filtering to update the view
            const event = new Event('change');
            document.getElementById('statusFilter').dispatchEvent(event);
        });
    }
});

/**
 * Initialize delete buttons functionality
 */
function initDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-program-btn');
    const modal = document.getElementById('deleteModal');
    
    if (!modal || !deleteButtons.length) return;
    
    const programNameDisplay = document.getElementById('program-name-display');
    const programIdInput = document.getElementById('program-id-input');
    
    if (!programNameDisplay || !programIdInput) return;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const programId = this.getAttribute('data-id');
            const programName = this.getAttribute('data-name');
            
            programNameDisplay.textContent = programName;
            programIdInput.value = programId;
            
            // Show the modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        });
    });
}
