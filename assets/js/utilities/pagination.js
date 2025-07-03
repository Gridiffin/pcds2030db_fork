/**
 * Table Pagination Utility
 * Provides pagination functionality for tables
 */

class TablePagination {
    constructor(tableId, options = {}) {
        this.tableId = tableId;
        this.table = document.getElementById(tableId);
        this.tbody = this.table.querySelector('tbody');
        this.itemsPerPage = options.itemsPerPage || 5;
        this.currentPage = 1;
        this.totalPages = 1;
        this.filteredRows = [];
        this.allRows = [];
        
        // Pagination container
        this.paginationContainerId = options.paginationContainerId || `${tableId}Pagination`;
        this.counterElementId = options.counterElementId || `${tableId}Counter`;
        
        this.init();
    }
    
    init() {
        this.getAllRows();
        this.createPaginationControls();
        this.updatePagination();
    }
    
    getAllRows() {
        // Get all rows except empty state rows
        this.allRows = Array.from(this.tbody.querySelectorAll('tr:not(.no-results-row):not(.no-filter-results)'));
        this.filteredRows = [...this.allRows];
    }
    
    createPaginationControls() {
        // Create pagination container if it doesn't exist
        let paginationContainer = document.getElementById(this.paginationContainerId);
        if (!paginationContainer) {
            paginationContainer = document.createElement('div');
            paginationContainer.id = this.paginationContainerId;
            paginationContainer.className = 'pagination-container mt-3 d-flex justify-content-between align-items-center';
            
            // Create counter element
            const counterDiv = document.createElement('div');
            counterDiv.innerHTML = `<span id="${this.counterElementId}">Showing 0-0 of 0 entries</span>`;
            
            // Create pagination nav
            const navElement = document.createElement('nav');
            navElement.setAttribute('aria-label', `${this.tableId} pagination`);
            navElement.innerHTML = `
                <ul class="pagination pagination-sm" id="${this.tableId}PaginationNav">
                    <!-- Pagination buttons will be populated here -->
                </ul>
            `;
            
            paginationContainer.appendChild(counterDiv);
            paginationContainer.appendChild(navElement);
            
            // Insert after the table's parent card
            const tableCard = this.table.closest('.card');
            if (tableCard && tableCard.nextSibling) {
                tableCard.parentNode.insertBefore(paginationContainer, tableCard.nextSibling);
            } else if (tableCard) {
                tableCard.parentNode.appendChild(paginationContainer);
            }
        }
    }
    
    updatePagination() {
        this.getVisibleRows();
        this.calculatePages();
        this.showCurrentPage();
        this.updatePaginationNav();
        this.updateCounter();
    }
    
    getVisibleRows() {
        // Get rows that are not hidden by filters
        this.filteredRows = this.allRows.filter(row => !row.classList.contains('d-none'));
    }
    
    calculatePages() {
        this.totalPages = Math.ceil(this.filteredRows.length / this.itemsPerPage);
        if (this.currentPage > this.totalPages) {
            this.currentPage = Math.max(1, this.totalPages);
        }
    }
    
    showCurrentPage() {
        // Hide all rows first
        this.allRows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Show only rows for current page
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageRows = this.filteredRows.slice(startIndex, endIndex);
        
        pageRows.forEach(row => {
            row.style.display = '';
        });
        
        // Show empty state if no rows to display
        this.handleEmptyState();
    }
    
    handleEmptyState() {
        const emptyRow = this.tbody.querySelector('.no-results-row, .no-filter-results');
        if (this.filteredRows.length === 0 && !emptyRow) {
            const emptyRowHtml = `
                <tr class="no-results-row">
                    <td colspan="100%" class="text-center py-4">No items found.</td>
                </tr>
            `;
            this.tbody.insertAdjacentHTML('beforeend', emptyRowHtml);
        } else if (this.filteredRows.length > 0 && emptyRow) {
            emptyRow.remove();
        }
    }
    
    updatePaginationNav() {
        const paginationNav = document.getElementById(`${this.tableId}PaginationNav`);
        if (!paginationNav) return;
        
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <button class="page-link" onclick="tablePaginations['${this.tableId}'].goToPage(${this.currentPage - 1})" 
                        ${this.currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
            </li>
        `;
        
        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);
        
        // Adjust start page if we're near the end
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        // First page
        if (startPage > 1) {
            paginationHtml += `
                <li class="page-item">
                    <button class="page-link" onclick="tablePaginations['${this.tableId}'].goToPage(1)">1</button>
                </li>
            `;
            if (startPage > 2) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <button class="page-link" onclick="tablePaginations['${this.tableId}'].goToPage(${i})">${i}</button>
                </li>
            `;
        }
        
        // Last page
        if (endPage < this.totalPages) {
            if (endPage < this.totalPages - 1) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHtml += `
                <li class="page-item">
                    <button class="page-link" onclick="tablePaginations['${this.tableId}'].goToPage(${this.totalPages})">${this.totalPages}</button>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${this.currentPage === this.totalPages ? 'disabled' : ''}">
                <button class="page-link" onclick="tablePaginations['${this.tableId}'].goToPage(${this.currentPage + 1})" 
                        ${this.currentPage === this.totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </li>
        `;
        
        paginationNav.innerHTML = paginationHtml;
    }
    
    updateCounter() {
        const counterElement = document.getElementById(this.counterElementId);
        if (!counterElement) return;
        
        const total = this.filteredRows.length;
        if (total === 0) {
            counterElement.textContent = 'Showing 0-0 of 0 entries';
            return;
        }
        
        const startIndex = (this.currentPage - 1) * this.itemsPerPage + 1;
        const endIndex = Math.min(this.currentPage * this.itemsPerPage, total);
        
        counterElement.textContent = `Showing ${startIndex}-${endIndex} of ${total} entries`;
    }
    
    goToPage(page) {
        if (page < 1 || page > this.totalPages) return;
        this.currentPage = page;
        this.updatePagination();
    }
    
    refresh() {
        this.getAllRows();
        this.updatePagination();
    }
    
    // Call this when filters change
    onFilterChange() {
        this.currentPage = 1; // Reset to first page
        this.updatePagination();
    }
}

// Global object to store pagination instances
window.tablePaginations = window.tablePaginations || {};
