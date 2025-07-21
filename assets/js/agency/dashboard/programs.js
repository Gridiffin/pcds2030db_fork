/**
 * Agency Dashboard - Programs Table Component
 * 
 * Handles the programs table sorting and interactions
 */

export class ProgramsTable {
    constructor() {
        this.sortColumn = null;
        this.sortDirection = 'asc';
        
        this.init();
    }
    
    init() {
        this.setupTableSorting();
        this.setupTableInteractions();
    }
    
    setupTableSorting() {
        const sortableHeaders = document.querySelectorAll('.table th.sortable');
        
        sortableHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const sortBy = header.getAttribute('data-sort');
                this.sortTable(sortBy);
            });
        });
        
        if (sortableHeaders.length > 0) {
            console.log('📊 Table sorting initialized');
        }
    }
    
    sortTable(column) {
        const table = document.getElementById('dashboardProgramsTable');
        if (!table) return;
        
        const rows = Array.from(table.querySelectorAll('tr'));
        
        // Determine sort direction
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortDirection = 'asc';
            this.sortColumn = column;
        }
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = this.getCellValue(a, column);
            const bValue = this.getCellValue(b, column);
            
            let comparison = 0;
            
            if (column === 'date') {
                // Handle date sorting
                const aDate = new Date(aValue);
                const bDate = new Date(bValue);
                comparison = aDate - bDate;
            } else if (column === 'rating') {
                // Handle rating sorting (by priority)
                const ratingOrder = {
                    'delayed': 1,
                    'severe-delay': 2,
                    'not-started': 3,
                    'on-track-yearly': 4,
                    'on-track': 5,
                    'target-achieved': 6,
                    'completed': 7
                };
                const aOrder = ratingOrder[aValue.toLowerCase()] || 999;
                const bOrder = ratingOrder[bValue.toLowerCase()] || 999;
                comparison = aOrder - bOrder;
            } else {
                // Handle text sorting
                comparison = aValue.localeCompare(bValue);
            }
            
            return this.sortDirection === 'asc' ? comparison : -comparison;
        });
        
        // Update table
        rows.forEach(row => table.appendChild(row));
        
        // Update sort indicators
        this.updateSortIndicators(column);
        
        console.log(`📊 Table sorted by ${column} (${this.sortDirection})`);
    }
    
    getCellValue(row, column) {
        switch (column) {
            case 'name':
                const nameCell = row.querySelector('td:first-child .fw-medium');
                return nameCell ? nameCell.textContent.trim() : '';
            
            case 'rating':
                const ratingCell = row.querySelector('td:nth-child(2) .badge');
                return ratingCell ? ratingCell.textContent.trim() : '';
            
            case 'date':
                const dateCell = row.querySelector('td:nth-child(3)');
                return dateCell ? dateCell.textContent.trim() : '';
            
            default:
                return '';
        }
    }
    
    updateSortIndicators(activeColumn) {
        const headers = document.querySelectorAll('.table th.sortable');
        
        headers.forEach(header => {
            const icon = header.querySelector('i');
            const column = header.getAttribute('data-sort');
            
            if (column === activeColumn) {
                // Update active header
                icon.className = this.sortDirection === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1';
                header.classList.add('sorted');
            } else {
                // Reset other headers
                icon.className = 'fas fa-sort ms-1';
                header.classList.remove('sorted');
            }
        });
    }
    
    setupTableInteractions() {
        // Add hover effects to table rows
        const table = document.getElementById('dashboardProgramsTable');
        if (!table) return;
        
        // Add click handlers for program rows (if needed)
        table.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            if (row && row.dataset.programId) {
                // Could add program details popup or navigation here
                console.log('Program row clicked:', row.dataset.programId);
            }
        });
        
        // Add keyboard navigation for table
        table.setAttribute('tabindex', '0');
        table.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const activeRow = table.querySelector('tr:focus');
                if (activeRow) {
                    activeRow.click();
                }
            }
        });
    }
    
    updateTable(newData) {
        const table = document.getElementById('dashboardProgramsTable');
        const programCount = document.getElementById('programCount');
        
        if (!table || !newData) return;
        
        // Update program count badge
        if (programCount) {
            programCount.textContent = newData.length;
        }
        
        // Clear existing rows
        table.innerHTML = '';
        
        // Add new rows
        newData.forEach(program => {
            const row = this.createProgramRow(program);
            table.appendChild(row);
        });
        
        console.log('📊 Programs table updated with new data');
    }
    
    createProgramRow(program) {
        const row = document.createElement('tr');
        const programType = (program.is_assigned && program.is_assigned) ? 'assigned' : 'created';
        const programTypeLabel = programType === 'assigned' ? 'Assigned' : 'Agency-Created';
        const isDraft = program.is_draft && program.is_draft == 1;
        const isNewAssigned = programType === 'assigned' && !program.rating;
        
        row.setAttribute('data-program-type', programType);
        if (isDraft || isNewAssigned) {
            row.classList.add('draft-program');
        }
        
        // Rating badge styling
        const rating = program.rating || 'not-started';
        let ratingClass = 'secondary';
        
        switch(rating) {
            case 'on-track':
            case 'on-track-yearly':
                ratingClass = 'warning';
                break;
            case 'delayed':
            case 'severe-delay':
                ratingClass = 'danger';
                break;
            case 'completed':
            case 'target-achieved':
                ratingClass = 'success';
                break;
        }
        
        const formattedDate = program.updated_at ? 
            new Date(program.updated_at).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            }) : 'N/A';
        
        row.innerHTML = `
            <td>
                <div class="fw-medium">
                    ${this.escapeHtml(program.program_name)}
                    ${(isDraft || isNewAssigned) ? '<span class="badge bg-secondary ms-1">Draft</span>' : ''}
                </div>
                <div class="small text-muted program-type-indicator">
                    <i class="fas fa-${programType === 'assigned' ? 'tasks' : 'folder-plus'} me-1"></i>
                    ${programTypeLabel}
                </div>
            </td>
            <td>
                <span class="badge bg-${ratingClass}">
                    ${this.formatRating(rating)}
                </span>
            </td>
            <td>${formattedDate}</td>
        `;
        
        return row;
    }
    
    formatRating(rating) {
        return rating.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    refresh() {
        // Refresh table by reapplying current sort
        if (this.sortColumn) {
            this.sortTable(this.sortColumn);
        }
    }
}
