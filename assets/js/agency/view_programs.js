/**
 * View Programs Functionality
 * Handles filtering and interactions on the programs list page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete functionality
    initDeleteButtons();
    
    // Initialize filters
    const searchInput = document.getElementById('programSearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('programTypeFilter');
    const resetButton = document.getElementById('resetFilters');
    
    if (searchInput) searchInput.addEventListener('keyup', filterPrograms);
    if (statusFilter) statusFilter.addEventListener('change', filterPrograms);
    if (typeFilter) typeFilter.addEventListener('change', filterPrograms);
    
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (typeFilter) typeFilter.value = '';
            filterPrograms();
        });
    }
    
    // Initialize pagination if we have program data
    if (typeof allPrograms !== 'undefined' && allPrograms.length > 0) {
        initializePagination();
    }

    // Initialize table sorting
    initProgramTableSorting();
});

/**
 * Filter programs based on search input, status, and type
 */
function filterPrograms() {
    const searchValue = document.getElementById('programSearch')?.value.toLowerCase() || '';
    const statusValue = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const typeValue = document.getElementById('programTypeFilter')?.value.toLowerCase() || '';
    
    const table = document.getElementById('programsTable');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        // Get data to filter on
        const programNameCell = row.querySelector('td:nth-child(1)');
        const statusCell = row.querySelector('td:nth-child(2)');
        
        if (!programNameCell || !statusCell) return;
        
        // Get program name text, excluding any badges
        let programName = '';
        const nameElement = programNameCell.querySelector('.fw-medium');
        if (nameElement) {
            programName = nameElement.textContent.toLowerCase();
        } else {
            programName = programNameCell.textContent.toLowerCase();
        }
        
        // Get program status
        const statusText = statusCell.textContent.toLowerCase();
        
        // Get program type from data attribute or program type indicator
        let programType = '';
        const typeIndicator = programNameCell.querySelector('.program-type-indicator');
        if (typeIndicator) {
            // Check if text contains "Assigned" or "Custom"
            const typeText = typeIndicator.textContent.toLowerCase();
            programType = typeText.includes('assigned') ? 'assigned' : 'created';
        } else {
            // Fallback to data attribute
            programType = row.getAttribute('data-program-type') || '';
        }
        
        // Check if row matches all filter criteria
        const matchesSearch = searchValue === '' || programName.includes(searchValue);
        const matchesStatus = statusValue === '' || statusText.includes(statusValue);
        const matchesType = typeValue === '' || programType === typeValue;
        
        // Show or hide row based on filter matches
        if (matchesSearch && matchesStatus && matchesType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update filter indicator if it exists
    updateFilterIndicator(visibleCount, searchValue, statusValue, typeValue);
    
    // After filtering, update pagination
    if (typeof allPrograms !== 'undefined') {
        // Calculate filtered programs
        const filteredPrograms = allPrograms.filter(program => {
            const matchesSearch = searchValue === '' || 
                program.program_name.toLowerCase().includes(searchValue);
            
            // Handle status matching with proper conversion
            let matchesStatus = false;
            if (statusValue === '') {
                matchesStatus = true;
            } else if (program.status) {
                // Map filter values to database values for comparison
                const statusFilterMap = {
                    'target-achieved': ['target-achieved', 'completed'], // Add 'completed' to target-achieved
                    'on-track-yearly': ['on-track-yearly', 'on-track'], 
                    'severe-delay': ['severe-delay', 'delayed'],
                    'not-started': ['not-started']
                };
                
                // Check if the program's status is in the array of statuses for the filter
                const validStatusesForFilter = statusFilterMap[statusValue] || [];
                matchesStatus = validStatusesForFilter.includes(program.status);
            }
            
            const programType = program.is_assigned ? 'assigned' : 'created';
            const matchesType = typeValue === '' || programType === typeValue;
            
            return matchesSearch && matchesStatus && matchesType;
        });
        
        // Update pagination with filtered results
        const totalPages = Math.ceil(filteredPrograms.length / paginationOptions.itemsPerPage);
        paginationOptions.currentPage = 1; // Reset to first page on filter
        renderPagination(totalPages || 1, 1);
        
        // Update the showing entries text
        const showingEntries = document.getElementById('showing-entries');
        if (showingEntries) {
            const start = filteredPrograms.length > 0 ? 1 : 0;
            const end = Math.min(paginationOptions.itemsPerPage, filteredPrograms.length);
            showingEntries.textContent = `Showing ${start}-${end} of ${filteredPrograms.length} entries`;
        }
        
        // Render first page of filtered results
        renderProgramPage(1, filteredPrograms);
    }
}

/**
 * Update the filter indicator showing active filters
 */
function updateFilterIndicator(count, search, status, type) {
    const container = document.querySelector('.filter-indicator-container');
    if (!container) return;
    
    let html = '';
    const hasFilters = search || status || type;
    
    if (hasFilters) {
        html = `<div class="alert alert-info filter-indicator">
            <div class="d-flex align-items-center">
                <div>
                    <i class="fas fa-filter me-2"></i>
                    <strong>Filtered Results:</strong> Showing ${count} program(s)
                </div>
                <div class="ms-auto">
                    <button id="clearFilters" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </button>
                </div>
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2">
                ${search ? `<span class="badge bg-primary">Search: ${search}</span>` : ''}
                ${status ? `<span class="badge bg-primary">Status: ${status}</span>` : ''}
                ${type ? `<span class="badge bg-primary">Type: ${type === 'assigned' ? 'Assigned' : 'Custom'}</span>` : ''}
            </div>
        </div>`;
    }
    
    container.innerHTML = html;
    
    // Add event listener to clear filters button
    const clearButton = document.getElementById('clearFilters');
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            document.getElementById('resetFilters').click();
        });
    }
}

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

/**
 * Initialize pagination for the programs table
 */
function initializePagination() {
    const pagination = document.getElementById('programPagination');
    if (!pagination) return;
    
    const totalPages = Math.ceil(allPrograms.length / paginationOptions.itemsPerPage);
    
    // Render initial pagination
    renderPagination(totalPages, paginationOptions.currentPage);
    
    // Render initial page
    renderProgramPage(paginationOptions.currentPage);
}

/**
 * Render pagination controls
 */
function renderPagination(totalPages, currentPage) {
    const pagination = document.getElementById('programPagination');
    if (!pagination) return;
    
    let html = '';
    
    // Previous button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
        </a>
    </li>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>`;
    }
    
    // Next button
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
        </a>
    </li>`;
    
    pagination.innerHTML = html;
    
    // Add event listeners to pagination links
    const links = pagination.querySelectorAll('.page-link');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            if (page < 1 || page > totalPages || page === currentPage) return;
            
            paginationOptions.currentPage = page;
            renderPagination(totalPages, page);
            renderProgramPage(page);
        });
    });
}

/**
 * Render a specific page of programs
 * @param {number} page - The page number to render
 * @param {Array} filteredPrograms - Optional array of filtered programs
 */
function renderProgramPage(page, filteredPrograms = null) {
    const table = document.getElementById('programsTable');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    // Use provided filtered programs or all programs
    const programsToRender = filteredPrograms || allPrograms;
    
    // Calculate start and end indices
    const startIndex = (page - 1) * paginationOptions.itemsPerPage;
    const endIndex = Math.min(startIndex + paginationOptions.itemsPerPage, programsToRender.length);
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    // If no programs to display
    if (programsToRender.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = '<td colspan="4" class="text-center py-4">No programs found.</td>';
        tbody.appendChild(emptyRow);
        return;
    }
    
    // Render programs for current page
    for (let i = startIndex; i < endIndex; i++) {
        const program = programsToRender[i];
        
        // Create row
        const row = document.createElement('tr');
        row.className = (program.is_draft ? 'draft-program' : '');
        row.setAttribute('data-program-type', program.is_assigned ? 'assigned' : 'created');
        
        // Format status for display
        let statusClass = 'secondary';
        let statusText = 'Not Started';
        
        if (program.status) {
            // Map database status values to display labels and classes
            const statusMap = {
                'on-track': { label: 'On Track', class: 'warning' },
                'on-track-yearly': { label: 'On Track for Year', class: 'warning' },
                'target-achieved': { label: 'Monthly Target Achieved', class: 'success' },
                'delayed': { label: 'Delayed', class: 'danger' },
                'severe-delay': { label: 'Severe Delays', class: 'danger' },
                'completed': { label: 'Monthly Target Achieved', class: 'success' },
                'not-started': { label: 'Not Started', class: 'secondary' }
            };
            
            // Use the status mapping if available, otherwise default
            if (statusMap[program.status]) {
                statusClass = statusMap[program.status].class;
                statusText = statusMap[program.status].label;
            }
        }
        
        // Format date
        const dateFormatted = program.updated_at 
            ? new Date(program.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
            : (program.created_at 
                ? new Date(program.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : 'Not set');
        
        // Set row HTML
        row.innerHTML = `
            <td>
                <div class="fw-medium">
                    ${program.program_name}
                    ${program.is_draft ? '<span class="draft-indicator" title="Draft"></span>' : ''}
                </div>
                <div class="small text-muted program-type-indicator">
                    <i class="fas fa-${program.is_assigned ? 'tasks' : 'folder-plus'} me-1"></i>
                    ${program.is_assigned ? 'Assigned' : 'Agency-Created'}
                </div>
            </td>
            <td>
                <span class="badge bg-${statusClass}">${statusText}</span>
            </td>
            <td>${dateFormatted}</td>
            <td>
                <div class="btn-group btn-group-sm float-end">
                    <a href="program_details.php?id=${program.program_id}" class="btn btn-outline-primary" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    
                    <!-- Show edit button for all programs that:
                         1. Are drafts, OR
                         2. Don't have a finalized submission for current period -->
                    <a href="update_program.php?id=${program.program_id}" class="btn btn-outline-secondary" title="Update Program">
                        <i class="fas fa-edit"></i>
                    </a>
                    
                    ${!program.is_assigned ? 
                        `<button type="button" class="btn btn-outline-danger delete-program-btn" 
                            data-id="${program.program_id}"
                            data-name="${program.program_name}"
                            title="Delete Program">
                            <i class="fas fa-trash"></i>
                        </button>` : ''
                    }
                </div>
            </td>
        `;
        
        // Add row to table
        tbody.appendChild(row);
    }
    
    // Update the showing entries text
    const showingEntries = document.getElementById('showing-entries');
    if (showingEntries) {
        const start = programsToRender.length > 0 ? startIndex + 1 : 0;
        showingEntries.textContent = `Showing ${start}-${endIndex} of ${programsToRender.length} entries`;
    }
    
    // Reinitialize delete buttons for the new rows
    initDeleteButtons();
}

/**
 * Initialize the program table sorting functionality
 */
function initProgramTableSorting() {
    const programTable = document.getElementById('programsTable').querySelector('tbody');
    const sortableHeaders = document.querySelectorAll('th.sortable');
    
    if (!programTable || !sortableHeaders.length) return;
    
    // Current sort state
    let currentSort = {
        column: null,
        direction: 'asc'
    };
    
    // Add click handlers to sortable headers
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.getAttribute('data-sort');
            
            // Update sort direction
            if (currentSort.column === sortBy) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = sortBy;
                currentSort.direction = 'asc';
            }
            
            // Update header icons
            sortableHeaders.forEach(h => {
                const icon = h.querySelector('i');
                if (h === this) {
                    icon.className = currentSort.direction === 'asc' 
                        ? 'fas fa-sort-up ms-1' 
                        : 'fas fa-sort-down ms-1';
                } else {
                    icon.className = 'fas fa-sort ms-1';
                }
            });
            
            // Sort the table
            sortProgramTable(programTable, sortBy, currentSort.direction);
        });
    });
}

/**
 * Sort the program table rows based on column and direction
 */
function sortProgramTable(table, column, direction) {
    const rows = Array.from(table.querySelectorAll('tr'));
    if (!rows.length) return;
    
    // Sort the rows
    const sortedRows = rows.sort((a, b) => {
        let aValue, bValue;
        
        switch(column) {
            case 'name':
                // Get program name text
                aValue = a.querySelector('td:first-child .fw-medium').textContent.trim().toLowerCase();
                bValue = b.querySelector('td:first-child .fw-medium').textContent.trim().toLowerCase();
                break;
                
            case 'status':
                // Get status text
                aValue = a.querySelector('td:nth-child(2) .badge').textContent.trim().toLowerCase();
                bValue = b.querySelector('td:nth-child(2) .badge').textContent.trim().toLowerCase();
                break;
                
            case 'date':
                // Get date as timestamp for comparison
                const aDate = a.querySelector('td:nth-child(3)').textContent.trim();
                const bDate = b.querySelector('td:nth-child(3)').textContent.trim();
                
                if (aDate === 'Not set' && bDate === 'Not set') return 0;
                if (aDate === 'Not set') return 1;
                if (bDate === 'Not set') return -1;
                
                aValue = new Date(aDate).getTime();
                bValue = new Date(bDate).getTime();
                break;
                
            default:
                return 0;
        }
        
        // Compare values
        if (aValue < bValue) return direction === 'asc' ? -1 : 1;
        if (aValue > bValue) return direction === 'asc' ? 1 : -1;
        return 0;
    });
    
    // Re-append rows in sorted order
    sortedRows.forEach(row => table.appendChild(row));
}
