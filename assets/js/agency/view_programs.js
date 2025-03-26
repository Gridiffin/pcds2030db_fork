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
        typeFilter.addEventListener('change', filterProgramsByType);
    }
    
    // Initial filtering
    filterPrograms();
});

/**
 * Filter programs based on search text and status
 */
function filterPrograms() {
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    
    if (!searchInput && !statusFilter) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
    const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
    
    // Get all program tables
    const tables = document.querySelectorAll('.table-custom');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const nameCell = row.cells[0];
            const statusCell = row.cells[3];
            
            if (!nameCell || !statusCell) return;
            
            const name = nameCell.textContent.toLowerCase();
            const status = statusCell.textContent.toLowerCase();
            
            const nameMatch = name.includes(searchValue);
            const statusMatch = statusValue === '' || status.includes(statusValue);
            
            row.style.display = nameMatch && statusMatch ? '' : 'none';
        });
        
        // Check if any rows are visible, if not, show a message
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        const programSection = table.closest('.program-section');
        
        if (programSection) {
            const noResultsMsg = programSection.querySelector('.no-results-message');
            
            if (visibleRows.length === 0) {
                if (!noResultsMsg) {
                    const noResultsDiv = document.createElement('div');
                    noResultsDiv.className = 'no-results-message text-center py-3';
                    noResultsDiv.innerHTML = '<i class="fas fa-search me-2"></i> No matching programs found';
                    table.parentNode.appendChild(noResultsDiv);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    });
}

/**
 * Filter programs by type (assigned/created)
 */
function filterProgramsByType() {
    const typeFilter = document.getElementById('programTypeFilter');
    if (!typeFilter) return;
    
    const typeValue = typeFilter.value;
    
    // Get all program sections
    const assignedSection = document.getElementById('assignedPrograms');
    const createdSection = document.getElementById('createdPrograms');
    
    if (assignedSection && createdSection) {
        switch (typeValue) {
            case 'assigned':
                assignedSection.style.display = 'block';
                createdSection.style.display = 'none';
                break;
            case 'created':
                assignedSection.style.display = 'none';
                createdSection.style.display = 'block';
                break;
            default:
                assignedSection.style.display = 'block';
                createdSection.style.display = 'block';
        }
    }
    
    // Also apply the other filters
    filterPrograms();
}
