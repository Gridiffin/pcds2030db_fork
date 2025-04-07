/**
 * Manage Programs Functionality
 * Handles filtering and interactions on the admin programs list page
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Manage Programs JS loaded'); // Debugging
    
    // Initialize filtering
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('programTypeFilter');
    const resetFiltersBtn = document.getElementById('resetFilters');
    
    if (searchInput) {
        console.log('Search input found'); // Debugging
        // Use input event with debounce for smoother filtering
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterPrograms, 300); // Debounce by 300ms
        });
        
        // Clear button functionality
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'btn btn-sm btn-outline-secondary position-absolute end-0 me-2';
        clearButton.innerHTML = '<i class="fas fa-times"></i>';
        clearButton.style.top = '50%';
        clearButton.style.transform = 'translateY(-50%)';
        clearButton.style.display = 'none';
        clearButton.style.zIndex = '10';
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            this.style.display = 'none';
            filterPrograms();
            searchInput.focus();
        });
        
        // Add the clear button to search input's parent
        const inputGroup = searchInput.closest('.input-group');
        if (inputGroup) {
            inputGroup.style.position = 'relative';
            inputGroup.appendChild(clearButton);
            
            // Show/hide clear button based on input content
            searchInput.addEventListener('input', function() {
                clearButton.style.display = this.value ? 'block' : 'none';
            });
        }
    }
    
    if (statusFilter) {
        console.log('Status filter found'); // Debugging
        statusFilter.addEventListener('change', filterPrograms);
    }
    
    if (typeFilter) {
        console.log('Type filter found'); // Debugging
        typeFilter.addEventListener('change', filterPrograms);
    }
    
    // Add reset filters functionality
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', resetAllFilters);
    }
    
    // Initialize delete functionality
    initDeleteButtons();
    
    // Initial filtering to ensure it's working from the start
    filterPrograms();
    
    // Add filter reset button in the same row as the other filter elements
    const filterCard = document.querySelector('.card:has(#programSearch)');
    if (filterCard && !document.getElementById('resetFilters')) {
        // Find the row containing the filters
        const filterRow = filterCard.querySelector('.row');
        if (filterRow) {
            // Create a new column for the reset button that aligns with the inputs
            const resetCol = document.createElement('div');
            resetCol.className = 'col-12 d-flex align-items-end justify-content-end mt-3 filter-reset-container';
            
            const resetBtn = document.createElement('button');
            resetBtn.id = 'resetFilters';
            resetBtn.className = 'btn btn-sm btn-outline-secondary filter-reset-btn';
            resetBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Reset Filters';
            resetBtn.addEventListener('click', resetAllFilters);
            
            resetCol.appendChild(resetBtn);
            filterRow.appendChild(resetCol);
            console.log('Reset filter button added in filter row'); // Debugging
        }
    }
});

/**
 * Reset all filters to default state
 */
function resetAllFilters() {
    console.log('Resetting all filters'); // Debugging
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('programTypeFilter');
    
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    if (typeFilter) typeFilter.value = '';
    
    // Hide clear button if it exists
    const clearButton = searchInput?.closest('.input-group')?.querySelector('button');
    if (clearButton) clearButton.style.display = 'none';
    
    // Apply filtering
    filterPrograms();
    
    // Show brief animation on the reset button
    const resetBtn = document.getElementById('resetFilters');
    if (resetBtn) {
        resetBtn.classList.add('btn-secondary');
        resetBtn.classList.remove('btn-outline-secondary');
        setTimeout(() => {
            resetBtn.classList.remove('btn-secondary');
            resetBtn.classList.add('btn-outline-secondary');
        }, 300);
    }
}

/**
 * Filter programs based on search input and filters
 */
function filterPrograms() {
    console.log('Filtering programs'); // Debugging
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('programTypeFilter');
    
    if (!searchInput && !statusFilter && !typeFilter) {
        console.log('No filter inputs found');
        return;
    }
    
    const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
    const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
    const typeValue = typeFilter ? typeFilter.value.toLowerCase() : '';
    
    console.log(`Filter values - Search: "${searchValue}", Status: "${statusValue}", Type: "${typeValue}"`); // Debugging
    
    // Get program table
    const table = document.getElementById('programsTable');
    if (!table) {
        console.error('Programs table not found!');
        return;
    }
    
    // Update filter indicator in the UI
    updateFilterIndicator(searchValue, statusValue, typeValue);
    
    const rows = table.querySelectorAll('tbody tr');
    console.log(`Found ${rows.length} rows to filter`); // Debugging
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        // Get cell values for filtering
        const nameCell = row.cells[0];
        const typeCell = row.cells[1];
        const statusCell = row.cells[2];
        
        if (!nameCell || !typeCell || !statusCell) {
            console.warn('Missing required cells in row');
            return;
        }
        
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
                const badgeElements = [];
                const badges = nameElement.querySelectorAll('.badge');
                badges.forEach(badge => {
                    badgeElements.push(badge.outerHTML);
                });
                
                nameElement.innerHTML = name + (badgeElements.length > 0 ? ' ' + badgeElements.join('') : '');
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    console.log(`Visible rows after filtering: ${visibleCount}`); // Debugging
    
    // Show/hide "no results" message
    const tableContainer = table.closest('.table-responsive');
    let noResultsMsg = tableContainer.querySelector('.no-results-message');
    
    if (visibleCount === 0) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results-message text-center py-5 w-100';
            noResultsMsg.innerHTML = `
                <div class="mb-3">
                    <i class="fas fa-search fa-3x text-muted"></i>
                </div>
                <h5>No matching programs found</h5>
                <p class="text-muted">Try adjusting your search criteria</p>
                <button id="clearFiltersBtn" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="fas fa-times me-1"></i>Clear Filters
                </button>
            `;
            tableContainer.appendChild(noResultsMsg);
            
            // Add event listener to clear filters button
            const clearFiltersBtn = noResultsMsg.querySelector('#clearFiltersBtn');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', resetAllFilters);
            }
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
    
    // Update counters if needed
    const programCounter = document.querySelector('#allPrograms .badge');
    if (programCounter) {
        programCounter.textContent = `${visibleCount} Programs`;
    }
    
    // Update filter badge if it exists
    const filterBadge = document.querySelector('.filter-badge');
    if (filterBadge) {
        const hasFilters = searchValue || statusValue || typeValue;
        filterBadge.style.display = hasFilters ? 'inline-flex' : 'none';
    }
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
 * Update the filter indicator in the UI
 */
function updateFilterIndicator(searchValue, statusValue, typeValue) {
    // Find or create the filter indicator
    let filterIndicator = document.querySelector('.filter-indicator');
    
    if (!filterIndicator) {
        filterIndicator = document.createElement('div');
        filterIndicator.className = 'filter-indicator mt-2 mb-3';
        
        // Insert after the filter card
        const filterCard = document.querySelector('.card:has(#programSearch)');
        if (filterCard) {
            filterCard.parentNode.insertBefore(filterIndicator, filterCard.nextSibling);
        }
    }
    
    const hasFilters = searchValue || statusValue || typeValue;
    
    if (!hasFilters) {
        filterIndicator.innerHTML = '';
        return;
    }
    
    let filterText = '<div class="d-flex align-items-center flex-wrap gap-2">';
    filterText += '<span class="me-2"><i class="fas fa-filter text-primary"></i> Active filters:</span>';
    
    if (searchValue) {
        filterText += `<span class="badge bg-light text-dark me-1">Search: "${searchValue}"</span>`;
    }
    
    if (statusValue) {
        filterText += `<span class="badge bg-light text-dark me-1">Status: ${statusValue}</span>`;
    }
    
    if (typeValue) {
        const typeName = typeValue === 'assigned' ? 'Assigned Programs' : 'Agency-Created Programs';
        filterText += `<span class="badge bg-light text-dark me-1">Type: ${typeName}</span>`;
    }
    
    filterText += '</div>';
    filterIndicator.innerHTML = filterText;
}

/**
 * Highlight text in an element based on search term
 */
function highlightText(element, searchTerm) {
    // Skip elements with no firstChild (no text content)
    if (!element.firstChild) return;
    
    // Store original content and create a temporary container
    const originalContent = element.innerHTML;
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = originalContent;
    
    // Function to process text nodes
    function processNode(node) {
        if (node.nodeType === 3) { // Text node
            const text = node.nodeValue;
            const lowerText = text.toLowerCase();
            let index = lowerText.indexOf(searchTerm);
            
            if (index >= 0) {
                const span = document.createElement('span');
                span.innerHTML = '';
                
                let lastIndex = 0;
                while (index >= 0) {
                    // Add text before the match
                    span.innerHTML += text.substring(lastIndex, index);
                    
                    // Add highlighted match
                    span.innerHTML += `<mark>${text.substr(index, searchTerm.length)}</mark>`;
                    
                    // Move to after this match
                    lastIndex = index + searchTerm.length;
                    index = lowerText.indexOf(searchTerm, lastIndex);
                }
                
                // Add any remaining text
                span.innerHTML += text.substring(lastIndex);
                node.parentNode.replaceChild(span, node);
                return span;
            }
        } else if (node.nodeType === 1) { // Element node
            // Skip if it's already a mark element
            if (node.nodeName !== 'MARK') {
                // Process child nodes
                const childNodes = Array.from(node.childNodes);
                childNodes.forEach(processNode);
            }
        }
        return node;
    }
    
    // Process all nodes in the element
    Array.from(tempDiv.childNodes).forEach(processNode);
    
    // Replace original content only if changes were made
    if (tempDiv.innerHTML !== originalContent) {
        element.innerHTML = tempDiv.innerHTML;
    }
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
    
    console.log(`Found ${deleteButtons.length} delete buttons`); // Debugging
    
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
