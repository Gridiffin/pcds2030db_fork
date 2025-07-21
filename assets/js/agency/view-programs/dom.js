/**
 * View Programs - DOM Manipulation and Events
 * Handles all DOM interactions, event listeners, and UI updates
 */

import { ViewProgramsLogic } from './logic.js';

export class ViewProgramsDOM {
    constructor() {
        this.logic = new ViewProgramsLogic();
        this.currentSort = { column: null, direction: 'asc' };
    }
    
    init() {
        console.log('🎯 Initializing DOM handlers...');
        
        this.initDeleteModal();
        this.initSimpleMoreActions();
        this.initTooltips();
        this.initTableSorting();
        this.updateCounters();
        
        console.log('✅ DOM handlers initialized');
    }
    
    /**
     * Initialize simple more actions menu
     */
    initSimpleMoreActions() {
        // Add click outside listener to close menus
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.more-actions-btn')) {
                this.closeAllMenus();
            }
        });
        
        // Make toggleMoreActions available globally
        window.toggleMoreActions = (programId, programName, buttonElement) => {
            this.toggleMoreActions(programId, programName, buttonElement);
        };
    }
    
    toggleMoreActions(programId, programName, buttonElement) {
        // Close all other menus first
        this.closeAllMenus();
        
        // Create or get menu
        let menu = document.getElementById(`global-menu-${programId}`);
        if (!menu) {
            menu = this.createMenuOutsideTable(programId, programName);
        }
        
        // Position menu relative to button
        this.positionMenu(menu, buttonElement);
        
        // Show menu
        this.showMenu(menu);
    }
    
    createMenuOutsideTable(programId, programName) {
        const menu = document.createElement('div');
        menu.className = 'more-actions-menu';
        menu.id = `global-menu-${programId}`;
        menu.style.position = 'fixed';
        menu.style.display = 'none';
        menu.style.zIndex = '9999';
        
        menu.innerHTML = `
            <div class="menu-header">
                <strong>${programName}</strong>
            </div>
            <div class="menu-divider"></div>
            <a href="edit_program.php?id=${programId}" class="menu-item">
                <i class="fas fa-edit"></i> Edit Program Details
            </a>
            <a href="edit_submission.php?program_id=${programId}" class="menu-item">
                <i class="fas fa-file-edit"></i> Edit Latest Submission
            </a>
            <a href="add_submission.php?program_id=${programId}" class="menu-item">
                <i class="fas fa-plus"></i> Add New Submission
            </a>
            <div class="menu-divider"></div>
            <a href="program_details.php?id=${programId}" class="menu-item">
                <i class="fas fa-eye"></i> View Full Details
            </a>
        `;
        
        // Append to body (outside any container)
        document.body.appendChild(menu);
        
        return menu;
    }
    
    positionMenu(menu, buttonElement) {
        const buttonRect = buttonElement.getBoundingClientRect();
        const menuWidth = 220; // min-width from CSS
        const menuHeight = 200; // estimated height
        
        // Calculate position
        let left = buttonRect.right - menuWidth; // Align right edge with button
        let top = buttonRect.bottom + 5; // 5px below button
        
        // Adjust if menu would go off-screen
        if (left < 10) {
            left = buttonRect.left; // Align left edge with button instead
        }
        
        if (left + menuWidth > window.innerWidth - 10) {
            left = window.innerWidth - menuWidth - 10;
        }
        
        if (top + menuHeight > window.innerHeight - 10) {
            top = buttonRect.top - menuHeight - 5; // Show above button instead
        }
        
        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
    }
    
    showMenu(menu) {
        menu.style.display = 'block';
        menu.classList.add('show');
        menu.classList.remove('hide');
    }
    
    hideMenu(menu) {
        menu.classList.add('hide');
        menu.classList.remove('show');
        
        setTimeout(() => {
            menu.style.display = 'none';
            menu.classList.remove('hide');
        }, 200);
    }
    
    closeAllMenus() {
        document.querySelectorAll('.more-actions-menu[id^="global-menu-"]').forEach(menu => {
            if (menu.style.display !== 'none') {
                this.hideMenu(menu);
            }
        });
    }
    
    /**
     * Initialize delete confirmation modal
     */
    initDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (!modal) {
            console.warn('Delete modal not found');
            return;
        }
        
        // Attach event listeners to all delete trigger buttons
        document.querySelectorAll('.trigger-delete-modal').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const programId = btn.getAttribute('data-id');
                const programName = btn.getAttribute('data-name');
                
                this.setupDeleteModal(programId, programName);
            });
        });
        
        // Setup double confirmation logic
        const continueBtn = document.getElementById('delete-continue-btn');
        const confirmBtn = document.getElementById('delete-confirm-btn');
        
        if (continueBtn) {
            continueBtn.addEventListener('click', () => {
                this.showDeleteStep2();
            });
        }
        
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                this.submitDeleteForm();
            });
        }
    }
    
    setupDeleteModal(programId, programName) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        
        // Set values in modal
        const programIdInput = document.getElementById('program-id-input');
        const programNameDisplay = document.getElementById('program-name-display');
        
        if (programIdInput) programIdInput.value = programId;
        if (programNameDisplay) programNameDisplay.textContent = programName;
        
        // Reset to step 1
        this.showDeleteStep1();
        
        modal.show();
    }
    
    showDeleteStep1() {
        const step1 = document.getElementById('deleteStep1');
        const step2 = document.getElementById('deleteStep2');
        const continueBtn = document.getElementById('delete-continue-btn');
        const confirmBtn = document.getElementById('delete-confirm-btn');
        
        if (step1) step1.style.display = '';
        if (step2) step2.style.display = 'none';
        if (continueBtn) continueBtn.style.display = '';
        if (confirmBtn) confirmBtn.style.display = 'none';
    }
    
    showDeleteStep2() {
        const step1 = document.getElementById('deleteStep1');
        const step2 = document.getElementById('deleteStep2');
        const continueBtn = document.getElementById('delete-continue-btn');
        const confirmBtn = document.getElementById('delete-confirm-btn');
        
        if (step1) step1.style.display = 'none';
        if (step2) step2.style.display = '';
        if (continueBtn) continueBtn.style.display = 'none';
        if (confirmBtn) {
            confirmBtn.style.display = '';
            confirmBtn.focus();
        }
    }
    
    submitDeleteForm() {
        const form = document.getElementById('delete-program-form');
        if (form) {
            form.submit();
        }
    }
    
    /**
     * Initialize Bootstrap tooltips
     */
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    /**
     * Initialize table sorting
     */
    initTableSorting() {
        document.querySelectorAll('.table th.sortable').forEach(th => {
            th.addEventListener('click', () => {
                const sortColumn = th.getAttribute('data-sort');
                this.handleSort(sortColumn, th);
            });
        });
    }
    
    handleSort(column, headerElement) {
        // Determine sort direction
        let direction = 'asc';
        if (this.currentSort.column === column && this.currentSort.direction === 'asc') {
            direction = 'desc';
        }
        
        this.currentSort = { column, direction };
        
        // Update visual indicators
        this.updateSortIndicators(headerElement, direction);
        
        // Sort the table
        this.sortTable(column, direction, headerElement.closest('table'));
    }
    
    updateSortIndicators(activeHeader, direction) {
        // Reset all sort indicators
        document.querySelectorAll('.table th.sortable .fas').forEach(icon => {
            icon.className = 'fas fa-sort ms-1';
        });
        
        // Update active sort indicator
        const icon = activeHeader.querySelector('.fas');
        if (icon) {
            icon.className = direction === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1';
        }
    }
    
    sortTable(column, direction, table) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => {
            return !row.querySelector('td[colspan]'); // Exclude "no data" rows
        });
        
        rows.sort((a, b) => {
            let valueA, valueB;
            
            switch (column) {
                case 'name':
                    valueA = a.querySelector('.program-name')?.textContent.trim() || '';
                    valueB = b.querySelector('.program-name')?.textContent.trim() || '';
                    break;
                    
                case 'initiative':
                    valueA = a.querySelector('[data-initiative]')?.getAttribute('data-initiative') || 'zzz_no_initiative';
                    valueB = b.querySelector('[data-initiative]')?.getAttribute('data-initiative') || 'zzz_no_initiative';
                    break;
                    
                case 'rating':
                    valueA = parseInt(a.querySelector('[data-rating-order]')?.getAttribute('data-rating-order') || '999');
                    valueB = parseInt(b.querySelector('[data-rating-order]')?.getAttribute('data-rating-order') || '999');
                    break;
                    
                case 'date':
                    valueA = a.querySelector('[data-date]')?.getAttribute('data-date') || '1970-01-01';
                    valueB = b.querySelector('[data-date]')?.getAttribute('data-date') || '1970-01-01';
                    break;
                    
                default:
                    return 0;
            }
            
            if (column === 'rating') {
                // Numeric comparison for rating order
                return direction === 'asc' ? valueA - valueB : valueB - valueA;
            } else {
                // String comparison
                const comparison = valueA.localeCompare(valueB);
                return direction === 'asc' ? comparison : -comparison;
            }
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }
    
    /**
     * Update program counters
     */
    updateCounters() {
        const draftRows = document.querySelectorAll('#draftProgramsTable tbody tr:not([style*="display: none"])');
        const finalizedRows = document.querySelectorAll('#finalizedProgramsTable tbody tr:not([style*="display: none"])');
        const emptyRows = document.querySelectorAll('#emptyProgramsTable tbody tr:not([style*="display: none"])');
        
        // Filter out "no data" rows
        const draftCount = Array.from(draftRows).filter(row => !row.querySelector('td[colspan]')).length;
        const finalizedCount = Array.from(finalizedRows).filter(row => !row.querySelector('td[colspan]')).length;
        const emptyCount = Array.from(emptyRows).filter(row => !row.querySelector('td[colspan]')).length;
        
        const draftCountEl = document.getElementById('draft-count');
        const finalizedCountEl = document.getElementById('finalized-count');
        const emptyCountEl = document.getElementById('empty-count');
        
        if (draftCountEl) draftCountEl.textContent = draftCount;
        if (finalizedCountEl) finalizedCountEl.textContent = finalizedCount;
        if (emptyCountEl) emptyCountEl.textContent = emptyCount;
    }
    
    /**
     * Show toast notification
     */
    showToast(title, message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(title, message, type);
        } else {
            console.log(`${title}: ${message}`);
        }
    }
    
    /**
     * Handle responsive behavior
     */
    handleResize() {
        // Update responsive elements based on screen size
        const screenWidth = window.innerWidth;
        
        if (screenWidth < 768) {
            // Mobile adjustments
            document.querySelectorAll('.initiative-display').forEach(el => {
                el.style.display = 'none';
            });
        } else {
            // Desktop view
            document.querySelectorAll('.initiative-display').forEach(el => {
                el.style.display = '';
            });
        }
    }
    
    /**
     * Update table loading state
     */
    setTableLoading(tableSelector, isLoading) {
        const table = document.querySelector(tableSelector);
        if (!table) return;
        
        if (isLoading) {
            table.classList.add('table-loading');
        } else {
            table.classList.remove('table-loading');
        }
    }
}
