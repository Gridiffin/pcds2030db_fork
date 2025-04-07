/**
 * View Programs Functionality
 * Handles filtering and interactions on the programs list page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filtering
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('programTypeFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterPrograms);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterPrograms);
    }
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterPrograms);
    }
    
    // Initial filtering
    filterPrograms();
});

/**
 * Filter programs based on search text, status and type
 */
function filterPrograms() {
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('programTypeFilter');
    
    if (!searchInput && !statusFilter && !typeFilter) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
    const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
    const typeValue = typeFilter ? typeFilter.value.toLowerCase() : '';
    
    // Get program table
    const table = document.getElementById('programsTable');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        // Get cell values for filtering
        const nameCell = row.cells[0];
        const typeCell = row.cells[1];
        const statusCell = row.cells[4];
        
        if (!nameCell || !typeCell || !statusCell) return;
        
        const name = nameCell.textContent.toLowerCase();
        const status = statusCell.textContent.toLowerCase();
        const programType = row.getAttribute('data-program-type') || '';
        
        // Check if program matches all filter criteria
        const matchesSearch = name.includes(searchValue);
        const matchesStatus = statusValue === '' || status.includes(statusValue);
        const matchesType = typeValue === '' || programType === typeValue;
        
        // Show/hide row based on filter results
        if (matchesSearch && matchesStatus && matchesType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide "no results" message
    const noResultsMsg = table.parentNode.querySelector('.no-results-message');
    
    if (visibleCount === 0) {
        if (!noResultsMsg) {
            const noResultsDiv = document.createElement('div');
            noResultsDiv.className = 'no-results-message text-center py-3';
            noResultsDiv.innerHTML = '<i class="fas fa-search me-2"></i> No matching programs found';
            table.parentNode.appendChild(noResultsDiv);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
    
    // Update counters if needed
    const programCounter = document.querySelector('#allPrograms .badge');
    if (programCounter) {
        programCounter.textContent = `${visibleCount} Programs`;
    }
}
