/**
 * Programs Viewing
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality if it exists
    const searchInput = document.getElementById('programSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', filterPrograms);
    }
    
    // Initialize status filter if it exists
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterPrograms);
    }
});

/**
 * Filter programs based on search input and status filter
 */
function filterPrograms() {
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    
    if (!searchInput || !statusFilter) return;
    
    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const programsTable = document.querySelector('.table-custom');
    
    if (!programsTable) return;
    
    const rows = programsTable.querySelectorAll('tbody tr');
    
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
}
