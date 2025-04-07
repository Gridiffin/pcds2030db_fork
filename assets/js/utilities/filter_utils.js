/**
 * Filter Utilities
 * 
 * Shared functions for filtering tables and lists
 */

/**
 * Initialize filter inputs with common functionality
 * @param {Object} options - Configuration options
 * @param {string} options.tableId - ID of the table to filter
 * @param {string} options.searchInputId - ID of the search input
 * @param {string} options.statusFilterId - ID of the status filter select
 * @param {string} options.typeFilterId - ID of the type filter select
 * @param {string} options.resetButtonId - ID of the reset button
 * @param {Function} options.customFilter - Custom filter function (optional)
 */
function initializeFilters(options) {
    const {
        tableId,
        searchInputId,
        statusFilterId,
        typeFilterId,
        resetButtonId,
        customFilter
    } = options;
    
    const table = document.getElementById(tableId);
    const searchInput = document.getElementById(searchInputId);
    const statusFilter = document.getElementById(statusFilterId);
    const typeFilter = document.getElementById(typeFilterId);
    const resetButton = document.getElementById(resetButtonId);
    
    if (!table) {
        console.error(`Table with ID ${tableId} not found`);
        return;
    }
    
    // Configure search input
    if (searchInput) {
        // Debounce search for better performance
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterTable();
            }, 300);
        });
        
        // Add clear button
        addClearButton(searchInput);
    }
    
    // Configure filters
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterTable);
    }
    
    // Configure reset button
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            resetFilters();
        });
    }
    
    // Filter function
    function filterTable() {
        const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
        const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
        const typeValue = typeFilter ? typeFilter.value.toLowerCase() : '';
        
        // If custom filter function provided, use it
        if (typeof customFilter === 'function') {
            customFilter(searchValue, statusValue, typeValue);
            return;
        }
        
        // Default filtering logic
        const rows = table.querySelectorAll('tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            // Skip header rows
            if (row.querySelector('th')) return;
            
            // Get name cell (first cell)
            const nameCell = row.cells[0];
            const statusCell = row.cells.length > 1 ? row.cells[1] : null;
            
            // Get program type from data attribute
            const programType = row.getAttribute('data-program-type');
            
            // Get values for filtering
            const name = nameCell.textContent.toLowerCase();
            const status = statusCell ? statusCell.textContent.toLowerCase() : '';
            
            // Apply filters
            const matchesSearch = searchValue === '' || name.includes(searchValue);
            const matchesStatus = statusValue === '' || status.includes(statusValue);
            const matchesType = typeValue === '' || !programType || programType.includes(typeValue);
            
            if (matchesSearch && matchesStatus && matchesType) {
                row.style.display = '';
                visibleCount++;
                
                // Highlight search term if needed
                if (searchValue && searchValue.length > 1) {
                    highlightText(nameCell, searchValue);
                } else {
                    // Remove highlights if search is cleared
                    removeHighlights(nameCell);
                }
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update filter indicator
        updateFilterIndicator(searchValue, statusValue, typeValue);
        
        // Handle no results
        handleNoResults(visibleCount);
    }
    
    // Reset all filters
    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = '';
        if (typeFilter) typeFilter.value = '';
        
        // Hide clear button if exists
        const clearButton = searchInput?.parentNode.querySelector('.clear-button');
        if (clearButton) clearButton.style.display = 'none';
        
        // Apply filtering to reset the view
        filterTable();
    }
    
    // Add clear button to search input
    function addClearButton(input) {
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'btn btn-sm btn-outline-secondary position-absolute end-0 me-2 clear-button';
        clearButton.innerHTML = '<i class="fas fa-times"></i>';
        clearButton.style.top = '50%';
        clearButton.style.transform = 'translateY(-50%)';
        clearButton.style.display = 'none';
        clearButton.style.zIndex = '10';
        
        clearButton.addEventListener('click', function() {
            input.value = '';
            this.style.display = 'none';
            filterTable();
            input.focus();
        });
        
        // Add to parent
        const parent = input.parentNode;
        parent.style.position = 'relative';
        parent.appendChild(clearButton);
        
        // Show/hide based on input content
        input.addEventListener('input', function() {
            clearButton.style.display = this.value ? 'block' : 'none';
        });
    }
    
    // Update the filter indicator
    function updateFilterIndicator(searchValue, statusValue, typeValue) {
        // Find or create filter indicator
        let filterIndicator = document.querySelector('.filter-indicator');
        
        if (!filterIndicator) {
            filterIndicator = document.createElement('div');
            filterIndicator.className = 'filter-indicator mt-2 mb-3';
            
            // Insert after the filter card
            const filterCard = table.closest('.card').previousElementSibling;
            if (filterCard && filterCard.classList.contains('card')) {
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
    
    // Handle no results message
    function handleNoResults(visibleCount) {
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
                    <h5>No matching results found</h5>
                    <p class="text-muted">Try adjusting your search criteria</p>
                    <button id="clearFiltersBtn" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </button>
                `;
                tableContainer.appendChild(noResultsMsg);
                
                // Add event listener to clear filters button
                const clearFiltersBtn = noResultsMsg.querySelector('#clearFiltersBtn');
                if (clearFiltersBtn) {
                    clearFiltersBtn.addEventListener('click', resetFilters);
                }
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
    
    // Initial filtering on load
    setTimeout(filterTable, 0);
}

/**
 * Highlight search terms in text content
 * @param {HTMLElement} element - Element containing text to highlight
 * @param {string} searchTerm - Term to highlight
 */
function highlightText(element, searchTerm) {
    if (!element || !searchTerm) return;
    
    // Store original content
    const originalContent = element.innerHTML;
    
    // Skip if already highlighted
    if (originalContent.includes('<mark>')) return;
    
    // Create text node with highlights
    const lowerContent = element.textContent.toLowerCase();
    const termToFind = searchTerm.toLowerCase();
    
    let result = '';
    let lastIndex = 0;
    let index = lowerContent.indexOf(termToFind);
    
    // If found, highlight each occurrence
    if (index >= 0) {
        while (index >= 0) {
            // Add content before match
            result += originalContent.substring(lastIndex, index);
            
            // Add highlighted match
            result += `<mark>${originalContent.substr(index, searchTerm.length)}</mark>`;
            
            // Move past this match
            lastIndex = index + searchTerm.length;
            index = lowerContent.indexOf(termToFind, lastIndex);
        }
        
        // Add remaining content
        result += originalContent.substring(lastIndex);
        
        // Update the element
        element.innerHTML = result;
    }
}

/**
 * Remove all highlights from an element
 * @param {HTMLElement} element - Element to remove highlights from
 */
function removeHighlights(element) {
    if (!element) return;
    
    const marks = element.querySelectorAll('mark');
    
    if (marks.length > 0) {
        // Replace each <mark> with its text content
        marks.forEach(mark => {
            const textNode = document.createTextNode(mark.textContent);
            mark.parentNode.replaceChild(textNode, mark);
        });
    }
}
