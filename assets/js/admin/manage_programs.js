/**
 * Manage Programs Functionality
 * Handles filtering and interactions on the admin programs list page
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Manage Programs JS loaded');
    
    // Initialize delete buttons functionality
    initDeleteButtons();
    
    // Initialize filters using the shared filter utility
    initializeFilters({
        tableId: 'programsTable',
        searchInputId: 'programSearch',
        statusFilterId: 'statusFilter',
        typeFilterId: 'programTypeFilter',
        resetButtonId: 'resetFilters',
        customFilter: customFilterPrograms
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
});

/**
 * Custom filter function for programs table
 */
function customFilterPrograms(searchValue, statusValue, typeValue) {
    const table = document.getElementById('programsTable');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        // Get cell values for filtering
        const nameCell = row.cells[0];
        const statusCell = row.cells[1];
        
        if (!nameCell || !statusCell) return;
        
        // Get the program name text, excluding badges
        let name = '';
        const nameElement = nameCell.querySelector('.fw-medium');
        if (nameElement) {
            // Clone the element to avoid modifying the original
            const clone = nameElement.cloneNode(true);
            // Remove any badges from the clone
            const badges = clone.querySelectorAll('.badge');
            badges.forEach(badge => badge.remove());
            // Get the text content of the clone (should now exclude badges)
            name = clone.textContent.toLowerCase().trim();
        } else {
            name = nameCell.textContent.toLowerCase();
        }
        
        const status = statusCell.textContent.toLowerCase();
        const programType = row.getAttribute('data-program-type') || '';
        
        // Check if program matches all filter criteria
        const matchesSearch = searchValue === '' || name.includes(searchValue);
        const matchesStatus = statusValue === '' || status.includes(statusValue);
        const matchesType = typeValue === '' || programType === typeValue;
        
        // Show/hide row based on filter results
        if (matchesSearch && matchesStatus && matchesType) {
            row.style.display = '';
            visibleCount++;
            
            // Highlight search terms if there's a search value
            if (searchValue && nameElement) {
                highlightProgramName(nameElement, searchValue);
            } else if (!searchValue && nameElement) {
                // Remove any existing highlights but preserve badges
                removeHighlightsPreserveBadges(nameElement);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update filter indicator is handled by the shared utility
    
    // Update counters if needed
    const programCounter = document.querySelector('#allPrograms .badge');
    if (programCounter) {
        programCounter.textContent = `${visibleCount} Programs`;
    }
}

/**
 * Remove highlights but preserve badges
 */
function removeHighlightsPreserveBadges(element) {
    // Save badges
    const badgeElements = [];
    const badges = element.querySelectorAll('.badge');
    badges.forEach(badge => {
        badgeElements.push(badge.outerHTML);
    });
    
    // Get original text without marks
    const clone = element.cloneNode(true);
    const marks = clone.querySelectorAll('mark');
    marks.forEach(mark => {
        const text = document.createTextNode(mark.textContent);
        mark.parentNode.replaceChild(text, mark);
    });
    
    // Get text without badges
    const badgesToRemove = clone.querySelectorAll('.badge');
    badgesToRemove.forEach(badge => badge.remove());
    const text = clone.textContent.trim();
    
    // Reconstruct content
    element.innerHTML = text + (badgeElements.length > 0 ? ' ' + badgeElements.join('') : '');
}

/**
 * Highlight program name text only (not including badges)
 */
function highlightProgramName(element, searchTerm) {
    // Save any badges
    const badgeElements = [];
    const badges = element.querySelectorAll('.badge');
    badges.forEach(badge => {
        badgeElements.push(badge.outerHTML);
    });
    
    // Get the program name text (exclude badges)
    const clone = element.cloneNode(true);
    const badgesToRemove = clone.querySelectorAll('.badge');
    badgesToRemove.forEach(badge => badge.remove());
    const nameText = clone.textContent.trim();
    
    // Create highlighted HTML
    const lowerText = nameText.toLowerCase();
    const searchText = searchTerm.toLowerCase();
    let highlightedHTML = '';
    let lastIndex = 0;
    let index = lowerText.indexOf(searchText);
    
    // Build the highlighted text
    while (index >= 0) {
        highlightedHTML += nameText.substring(lastIndex, index);
        highlightedHTML += `<mark>${nameText.substr(index, searchTerm.length)}</mark>`;
        lastIndex = index + searchTerm.length;
        index = lowerText.indexOf(searchText, lastIndex);
    }
    
    // Add any remaining text
    highlightedHTML += nameText.substring(lastIndex);
    
    // Add back the badges
    if (badgeElements.length > 0) {
        highlightedHTML += ' ' + badgeElements.join('');
    }
    
    // Update the element with highlighted text
    element.innerHTML = highlightedHTML;
}

/**
 * Initialize delete buttons functionality
 */
function initDeleteButtons() {
    const deleteButtons = document.querySelectorAll('.delete-program-btn');
    const modal = document.getElementById('deleteModal');
    
    if (!modal) {
        console.warn('Delete modal not found');
        return;
    }
    
    const programNameDisplay = document.getElementById('program-name-display');
    const programIdInput = document.getElementById('program-id-input');
    
    if (!programNameDisplay || !programIdInput) {
        console.warn('Delete modal elements not found');
        return;
    }
    
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
