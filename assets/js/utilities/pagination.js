/**
 * Table Pagination Utility
 * Provides pagination functionality for tables
 */

class TablePagination {
    constructor(tableId, options = {}) {
        this.tableId = tableId;
        this.container = document.getElementById(tableId);
        this.itemsPerPage = options.itemsPerPage || 5;
        this.currentPage = 1;
        this.totalPages = 1;
        this.filteredItems = [];
        this.allItems = [];
        this.isTable = false;
        this.itemSelector = options.itemSelector || 'tr';
        this.enableSmoothTransitions = options.enableSmoothTransitions !== false; // Default to true
        
        // Pagination container
        this.paginationContainerId = options.paginationContainerId || `${tableId}Pagination`;
        this.counterElementId = options.counterElementId || `${tableId}Counter`;
        
        // Detect if this is a table or a container with program boxes
        this.table = this.container.querySelector('table');
        this.tbody = this.table ? this.table.querySelector('tbody') : null;
        this.isTable = !!this.tbody;
        
        this.init();
    }
    
    init() {
        console.log(`🔧 [DEBUG] TablePagination.init() for ${this.tableId}`);
        this.getAllItems();
        console.log(`🔧 [DEBUG] Found ${this.allItems.length} items in ${this.tableId}`);
        this.createPaginationControls();
        this.createLoadingOverlay();
        this.updatePagination();
        console.log(`🔧 [DEBUG] TablePagination initialization complete for ${this.tableId}`);
    }
    
    getAllItems() {
        if (this.isTable) {
            // Get all rows except empty state rows for tables
            this.allItems = Array.from(this.tbody.querySelectorAll('tr:not(.no-results-row):not(.no-filter-results)'));
        } else {
            // Get all items using the provided selector for containers
            this.allItems = Array.from(this.container.querySelectorAll(this.itemSelector));
        }
        this.filteredItems = [...this.allItems];
    }
    
    createLoadingOverlay() {
        if (!this.enableSmoothTransitions) return;
        
        // Create loading overlay for smooth transitions
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'pagination-loading-overlay';
        loadingOverlay.id = `${this.tableId}LoadingOverlay`;
        loadingOverlay.style.display = 'none';
        loadingOverlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading...</div>
                <div class="loading-progress">
                    <div class="progress-bar" id="${this.tableId}ProgressBar"></div>
                </div>
            </div>
        `;
        
        // Make container relative for overlay positioning
        this.container.style.position = 'relative';
        this.container.style.minHeight = '200px';
        this.container.appendChild(loadingOverlay);
        
        // Add CSS styles if not already added
        this.addSmoothTransitionStyles();
    }
    
    addSmoothTransitionStyles() {
        const styleId = 'pagination-smooth-styles';
        if (document.getElementById(styleId)) return;
        
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .pagination-loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(2px);
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
            }
            
            .loading-content {
                text-align: center;
                padding: 2rem;
            }
            
            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 3px solid #f3f3f3;
                border-top: 3px solid #28a745;
                border-radius: 50%;
                animation: paginate-spin 1s linear infinite;
                margin: 0 auto 1rem;
            }
            
            @keyframes paginate-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .loading-text {
                color: #6c757d;
                font-size: 14px;
                margin-bottom: 1rem;
            }
            
            .loading-progress {
                width: 200px;
                height: 4px;
                background: #e9ecef;
                border-radius: 2px;
                overflow: hidden;
                margin: 0 auto;
            }
            
            .progress-bar {
                height: 100%;
                background: linear-gradient(90deg, #28a745, #20c997);
                border-radius: 2px;
                width: 0%;
                transition: width 0.3s ease;
            }
            
            .program-box, tr {
                transition: opacity 0.3s ease, transform 0.2s ease;
            }
            
            .fade-out {
                opacity: 0 !important;
                transform: translateY(-10px) !important;
            }
            
            .fade-in {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    createPaginationControls() {
        console.log(`🔧 [DEBUG] createPaginationControls() for ${this.tableId}`);
        
        // Check if pagination container already exists (for pre-existing containers)
        let paginationContainer = document.getElementById(this.paginationContainerId);
        let navElement = document.getElementById(`${this.tableId}PaginationNav`);
        
        console.log(`🔧 [DEBUG] Pagination container '${this.paginationContainerId}' exists: ${!!paginationContainer}`);
        console.log(`🔧 [DEBUG] Nav element '${this.tableId}PaginationNav' exists: ${!!navElement}`);
        
        if (!paginationContainer) {
            console.log(`🔧 [DEBUG] Creating new pagination container for ${this.tableId}`);
            // Create new pagination container for tables
            paginationContainer = document.createElement('div');
            paginationContainer.id = this.paginationContainerId;
            paginationContainer.className = 'pagination-container mt-3 d-flex justify-content-between align-items-center';
            
            // Create counter element
            const counterDiv = document.createElement('div');
            counterDiv.innerHTML = `<span id="${this.counterElementId}">Showing 0-0 of 0 entries</span>`;
            
            // Create pagination nav
            navElement = document.createElement('nav');
            navElement.setAttribute('aria-label', `${this.tableId} pagination`);
            navElement.innerHTML = `
                <ul class="pagination pagination-sm" id="${this.tableId}PaginationNav">
                    <!-- Pagination buttons will be populated here -->
                </ul>
            `;
            
            paginationContainer.appendChild(counterDiv);
            paginationContainer.appendChild(navElement);
            
            // Insert after the table's parent card
            const tableCard = this.table ? this.table.closest('.card') : null;
            if (tableCard && tableCard.nextSibling) {
                tableCard.parentNode.insertBefore(paginationContainer, tableCard.nextSibling);
            } else if (tableCard) {
                tableCard.parentNode.appendChild(paginationContainer);
            } else {
                console.log(`🔧 [DEBUG] No table card found, appending to body`);
                document.body.appendChild(paginationContainer);
            }
        } else {
            console.log(`🔧 [DEBUG] Using existing pagination container for ${this.tableId}`);
            // For existing containers (like agency programs), just add the nav if it doesn't exist
            if (!navElement) {
                console.log(`🔧 [DEBUG] Adding nav element to existing container`);
                navElement = document.createElement('nav');
                navElement.setAttribute('aria-label', `${this.tableId} pagination`);
                navElement.innerHTML = `
                    <ul class="pagination pagination-sm" id="${this.tableId}PaginationNav">
                        <!-- Pagination buttons will be populated here -->
                    </ul>
                `;
                paginationContainer.appendChild(navElement);
            }
        }
        
        console.log(`🔧 [DEBUG] Pagination controls setup complete for ${this.tableId}`);
    }
    
    updatePagination(showLoading = false) {
        if (this.enableSmoothTransitions && showLoading) {
            this.showLoadingState();
            setTimeout(() => {
                this.performPaginationUpdate(true); // Pass flag to indicate this is from navigation
                this.hideLoadingState();
            }, 400);
        } else {
            this.performPaginationUpdate(false);
        }
    }
    
    performPaginationUpdate(isFromNavigation = false) {
        // If this is from navigation, preserve the current filteredItems count
        // to prevent currentPage from being reset due to temporary hiding of items
        if (!isFromNavigation) {
            this.getVisibleItems();
        }
        this.calculatePages();
        this.showCurrentPage();
        this.updatePaginationNav();
        this.updateCounter();
    }
    
    getVisibleItems() {
        // Get items that are not hidden by filters
        const previousCount = this.filteredItems.length;
        const previousCurrentPage = this.currentPage;
        
        this.filteredItems = this.allItems.filter(item => 
            item.style.display !== 'none' && !item.classList.contains('d-none')
        );
        
        console.log(`🔧 [DEBUG] getVisibleItems() for ${this.tableId}: ${previousCount} -> ${this.filteredItems.length}`);
        console.log(`🔧 [DEBUG] getVisibleItems() currentPage before filter: ${previousCurrentPage}, after filter: ${this.currentPage}`);
    }
    
    showLoadingState() {
        const loadingOverlay = document.getElementById(`${this.tableId}LoadingOverlay`);
        const progressBar = document.getElementById(`${this.tableId}ProgressBar`);
        
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
            
            // Animate progress bar
            if (progressBar) {
                progressBar.style.width = '0%';
                setTimeout(() => progressBar.style.width = '30%', 50);
                setTimeout(() => progressBar.style.width = '60%', 150);
                setTimeout(() => progressBar.style.width = '90%', 250);
                setTimeout(() => progressBar.style.width = '100%', 350);
            }
        }
    }
    
    hideLoadingState() {
        const loadingOverlay = document.getElementById(`${this.tableId}LoadingOverlay`);
        const progressBar = document.getElementById(`${this.tableId}ProgressBar`);
        
        if (loadingOverlay) {
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
                if (progressBar) {
                    progressBar.style.width = '0%';
                }
            }, 100);
        }
    }
    
    calculatePages() {
        const previousTotalPages = this.totalPages;
        const previousCurrentPage = this.currentPage;
        
        this.totalPages = Math.ceil(this.filteredItems.length / this.itemsPerPage);
        
        console.log(`🔧 [DEBUG] calculatePages() for ${this.tableId}: filteredItems=${this.filteredItems.length}, itemsPerPage=${this.itemsPerPage}, totalPages=${this.totalPages}`);
        console.log(`🔧 [DEBUG] calculatePages() before check: currentPage=${this.currentPage}, totalPages=${this.totalPages}`);
        
        if (this.currentPage > this.totalPages && this.totalPages > 0) {
            console.log(`🔧 [DEBUG] Current page ${this.currentPage} > totalPages ${this.totalPages}, resetting to ${Math.max(1, this.totalPages)}`);
            this.currentPage = Math.max(1, this.totalPages);
        }
        
        console.log(`🔧 [DEBUG] calculatePages() result: currentPage=${this.currentPage}, totalPages=${this.totalPages}`);
    }
    
    showCurrentPage() {
        if (this.enableSmoothTransitions) {
            this.showCurrentPageWithAnimation();
        } else {
            this.showCurrentPageInstant();
        }
    }
    
    showCurrentPageInstant() {
        // Hide all items first
        this.allItems.forEach(item => {
            item.style.display = 'none';
        });
        
        // Show only items for current page
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageItems = this.filteredItems.slice(startIndex, endIndex);
        
        pageItems.forEach(item => {
            item.style.display = '';
        });
        
        // Show empty state if no items to display
        this.handleEmptyState();
    }
    
    showCurrentPageWithAnimation() {
        console.log(`🔧 [DEBUG] showCurrentPageWithAnimation() for ${this.tableId}, page ${this.currentPage}`);
        
        // Add fade-out effect to current visible items
        const visibleItems = this.allItems.filter(item => 
            item.style.display !== 'none' && !item.classList.contains('d-none')
        );
        
        console.log(`🔧 [DEBUG] Found ${visibleItems.length} visible items to fade out`);
        
        visibleItems.forEach(item => {
            item.classList.add('fade-out');
        });
        
        // After fade-out, update the display
        setTimeout(() => {
            // Hide all items first
            this.allItems.forEach(item => {
                item.style.display = 'none';
                item.classList.remove('fade-out', 'fade-in');
            });
            
            // Show items for current page with fade-in effect
            const startIndex = (this.currentPage - 1) * this.itemsPerPage;
            const endIndex = startIndex + this.itemsPerPage;
            const pageItems = this.filteredItems.slice(startIndex, endIndex);
            
            console.log(`🔧 [DEBUG] Showing items ${startIndex}-${endIndex-1} (${pageItems.length} items)`);
            
            pageItems.forEach((item, index) => {
                item.style.display = '';
                // Stagger the fade-in animation slightly
                setTimeout(() => {
                    item.classList.add('fade-in');
                }, index * 50);
            });
            
            // Show empty state if no items to display
            this.handleEmptyState();
        }, 150); // Wait for fade-out to complete
    }
    
    handleEmptyState() {
        if (this.isTable) {
            const emptyRow = this.tbody.querySelector('.no-results-row, .no-filter-results');
            if (this.filteredItems.length === 0 && !emptyRow) {
                const emptyRowHtml = `
                    <tr class="no-results-row">
                        <td colspan="100%" class="text-center py-4">No items found.</td>
                    </tr>
                `;
                this.tbody.insertAdjacentHTML('beforeend', emptyRowHtml);
            } else if (this.filteredItems.length > 0 && emptyRow) {
                emptyRow.remove();
            }
        } else {
            // Handle empty state for program containers
            const emptyState = this.container.querySelector('.no-results-message, .no-filter-results');
            if (this.filteredItems.length === 0 && !emptyState) {
                const emptyStateHtml = `
                    <div class="no-results-message programs-empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="empty-title">No items found</div>
                        <div class="empty-description">Try adjusting your search or filter criteria.</div>
                    </div>
                `;
                this.container.insertAdjacentHTML('beforeend', emptyStateHtml);
            } else if (this.filteredItems.length > 0 && emptyState) {
                emptyState.remove();
            }
        }
    }
    
    updatePaginationNav() {
        console.log(`🔧 [DEBUG] updatePaginationNav() for ${this.tableId}, totalPages: ${this.totalPages}`);
        const paginationNav = document.getElementById(`${this.tableId}PaginationNav`);
        if (!paginationNav) {
            console.log(`❌ [DEBUG] Pagination nav element not found: ${this.tableId}PaginationNav`);
            return;
        }
        
        // Hide pagination if only one page or no items
        if (this.totalPages <= 1) {
            console.log(`🔧 [DEBUG] Hiding pagination - only ${this.totalPages} page(s)`);
            const container = document.getElementById(this.paginationContainerId);
            if (container) {
                container.style.display = 'none';
            }
            return;
        } else {
            console.log(`🔧 [DEBUG] Showing pagination - ${this.totalPages} pages`);
            const container = document.getElementById(this.paginationContainerId);
            if (container) {
                container.style.display = '';
            }
        }
        
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <button class="page-link" data-page="${this.currentPage - 1}" data-table="${this.tableId}" 
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
                    <button class="page-link" data-page="1" data-table="${this.tableId}">1</button>
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
                    <button class="page-link" data-page="${i}" data-table="${this.tableId}">${i}</button>
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
                    <button class="page-link" data-page="${this.totalPages}" data-table="${this.tableId}">${this.totalPages}</button>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${this.currentPage === this.totalPages ? 'disabled' : ''}">
                <button class="page-link" data-page="${this.currentPage + 1}" data-table="${this.tableId}" 
                        ${this.currentPage === this.totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </li>
        `;
        
        paginationNav.innerHTML = paginationHtml;
        
        // Add event delegation for pagination buttons
        this.setupPaginationEventListeners(paginationNav);
    }
    
    updateCounter() {
        const counterElement = document.getElementById(this.counterElementId);
        if (!counterElement) return;
        
        const total = this.filteredItems.length;
        if (total === 0) {
            counterElement.textContent = 'Showing 0-0 of 0 entries';
            return;
        }
        
        const startIndex = (this.currentPage - 1) * this.itemsPerPage + 1;
        const endIndex = Math.min(this.currentPage * this.itemsPerPage, total);
        
        counterElement.textContent = `Showing ${startIndex}-${endIndex} of ${total} entries`;
    }
    
    goToPage(page) {
        console.log(`🔧 [DEBUG] goToPage(${page}) called for ${this.tableId}, totalPages: ${this.totalPages}`);
        
        if (page < 1 || page > this.totalPages) {
            console.log(`🔧 [DEBUG] Page ${page} is out of range (1-${this.totalPages})`);
            return;
        }
        
        console.log(`🔧 [DEBUG] Setting currentPage from ${this.currentPage} to ${page}`);
        this.currentPage = page;
        this.updatePagination(true); // Show loading for navigation
    }
    
    refresh() {
        this.getAllItems();
        this.updatePagination();
    }
    
    // Call this when filters change
    onFilterChange() {
        this.currentPage = 1; // Reset to first page
        this.updatePagination();
    }
    
    // Setup event listeners for pagination buttons
    setupPaginationEventListeners(paginationNav) {
        // Remove any existing listeners
        paginationNav.removeEventListener('click', this.paginationClickHandler);
        
        // Create bound handler
        this.paginationClickHandler = (e) => {
            if (e.target.matches('button.page-link[data-page]') && !e.target.disabled) {
                e.preventDefault();
                const page = parseInt(e.target.getAttribute('data-page'));
                const tableId = e.target.getAttribute('data-table');
                
                console.log(`🔧 [DEBUG] Pagination click: page ${page}, table ${tableId}`);
                
                if (tableId === this.tableId && page > 0 && page <= this.totalPages) {
                    this.goToPage(page);
                }
            }
        };
        
        paginationNav.addEventListener('click', this.paginationClickHandler);
    }
}

// Make TablePagination class globally available
window.TablePagination = TablePagination;

// Global object to store pagination instances
window.tablePaginations = window.tablePaginations || {};
