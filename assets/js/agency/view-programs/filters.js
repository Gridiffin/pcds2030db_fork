/**
 * View Programs - Filtering Functionality
 * Handles all filtering logic and UI interactions
 */

import { ViewProgramsLogic } from './logic.js';

export class ViewProgramsFilters {
    constructor() {
        this.logic = new ViewProgramsLogic();
        this.activeFilters = {};
        this.currentActiveTab = 'draft'; // Track which tab is currently active
    }
    
    init() {
        this.initGlobalFilters();
        this.initTabSwitching();
    }
    
    /**
     * Initialize global filters that work across all tabs
     */
    initGlobalFilters() {
        const searchInput = document.getElementById('globalProgramSearch');
        const statusFilter = document.getElementById('globalStatusFilter');
        const initiativeFilter = document.getElementById('globalInitiativeFilter');
        const resetBtn = document.getElementById('resetGlobalFilters');
        
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => {
                this.handleGlobalFilter();
            }, 300));
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                this.handleGlobalFilter();
            });
        }
        
        if (initiativeFilter) {
            initiativeFilter.addEventListener('change', () => {
                this.handleGlobalFilter();
            });
        }
        
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                this.resetGlobalFilters();
            });
        }
    }
    
    /**
     * Initialize tab switching to detect active tab
     */
    initTabSwitching() {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', (event) => {
                // Update current active tab
                const target = event.target.getAttribute('data-bs-target');
                if (target === '#draft-programs') {
                    this.currentActiveTab = 'draft';
                } else if (target === '#finalized-programs') {
                    this.currentActiveTab = 'finalized';
                } else if (target === '#template-programs') {
                    this.currentActiveTab = 'empty';
                }
                
                // Reapply filters to the newly active tab
                this.handleGlobalFilter();
            });
        });
    }
    
    /**
     * Handle global filtering across all tabs
     */
    handleGlobalFilter() {
        const search = document.getElementById('globalProgramSearch')?.value || '';
        const status = document.getElementById('globalStatusFilter')?.value || '';
        const initiative = document.getElementById('globalInitiativeFilter')?.value || '';
        
        this.activeFilters = { search, status, initiative };
        
        // Apply filters to all containers
        this.applyTableFilter('draftProgramsContainer', this.activeFilters);
        this.applyTableFilter('finalizedProgramsContainer', this.activeFilters);
        this.applyTableFilter('emptyProgramsContainer', this.activeFilters);
        
        this.updateFilterBadges('globalFilterBadges', this.activeFilters);
        this.updateCounters();
    }
    
    /**
     * Apply filters to a container with program boxes
     */
    applyTableFilter(containerId, filters) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const programBoxes = container.querySelectorAll('.program-box');
        
        programBoxes.forEach(programBox => {
            let visible = true;
            
            // Search filter
            if (filters.search && filters.search.trim() !== '') {
                const searchTerm = filters.search.toLowerCase().trim();
                const programName = programBox.querySelector('.program-name')?.textContent.toLowerCase() || '';
                const programNumber = programBox.querySelector('.program-number')?.textContent.toLowerCase() || '';
                const initiativeName = programBox.querySelector('.initiative-badge, .initiative-icon')?.textContent.toLowerCase() || '';
                
                visible = visible && (
                    programName.includes(searchTerm) ||
                    programNumber.includes(searchTerm) ||
                    initiativeName.includes(searchTerm)
                );
            }
            
            // Status filter
            if (filters.status && filters.status !== '') {
                const rating = programBox.getAttribute('data-status') || '';
                visible = visible && (rating === filters.status);
            }
            
            // Initiative filter
            if (filters.initiative && filters.initiative !== '') {
                const initiativeId = programBox.getAttribute('data-initiative-id') || '0';
                
                if (filters.initiative === 'no-initiative') {
                    visible = visible && (initiativeId === '0' || initiativeId === '');
                } else {
                    visible = visible && (initiativeId === filters.initiative);
                }
            }
            
            // Show/hide program box
            programBox.style.display = visible ? '' : 'none';
        });
    }
    
    /**
     * Update filter badges
     */
    updateFilterBadges(containerId, filters) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = '';
        
        Object.entries(filters).forEach(([key, value]) => {
            if (value && value.trim() !== '') {
                const badge = this.createFilterBadge(key, value);
                container.appendChild(badge);
            }
        });
    }
    
    /**
     * Create a filter badge element
     */
    createFilterBadge(type, value) {
        const badge = document.createElement('span');
        badge.className = 'filter-badge';
        
        let displayValue = value;
        
        // Convert filter values to display labels
        if (type === 'status') {
            const statusLabels = {
                'monthly_target_achieved': 'Monthly Target Achieved',
                'on_track_for_year': 'On Track for Year',
                'severe_delay': 'Severe Delays',
                'not_started': 'Not Started'
            };
            displayValue = statusLabels[value] || value;
        } else if (type === 'initiative') {
            if (value === 'no-initiative') {
                displayValue = 'Not Linked to Initiative';
            } else {
                // Get initiative name from select option
                const selectEl = document.querySelector(`select option[value="${value}"]`);
                displayValue = selectEl?.textContent || value;
            }
        }
        
        badge.innerHTML = `
            ${this.capitalizeFirst(type)}: ${displayValue}
            <button type="button" class="btn-close" aria-label="Remove filter"></button>
        `;
        
        // Add remove functionality
        badge.querySelector('.btn-close').addEventListener('click', () => {
            this.removeFilter(type, badge);
        });
        
        return badge;
    }
    
    /**
     * Remove a specific filter
     */
    removeFilter(type, badgeElement) {
        // Clear the global filter value
        const filterMappings = {
            'search': 'globalProgramSearch',
            'status': 'globalStatusFilter',
            'initiative': 'globalInitiativeFilter'
        };
        
        const elementId = filterMappings[type];
        if (elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.value = '';
            }
        }
        
        // Remove the badge
        badgeElement.remove();
        
        // Reapply filters
        this.handleGlobalFilter();
    }
    
    /**
     * Reset global filters
     */
    resetGlobalFilters() {
        document.getElementById('globalProgramSearch').value = '';
        document.getElementById('globalStatusFilter').value = '';
        document.getElementById('globalInitiativeFilter').value = '';
        
        this.handleGlobalFilter();
    }
    
    /**
     * Update counters after filtering
     */
    updateCounters() {
        setTimeout(() => {
            const draftBoxes = document.querySelectorAll('#draftProgramsContainer .program-box:not([style*="display: none"])');
            const finalizedBoxes = document.querySelectorAll('#finalizedProgramsContainer .program-box:not([style*="display: none"])');
            const emptyBoxes = document.querySelectorAll('#emptyProgramsContainer .program-box:not([style*="display: none"])');
            
            const draftCount = draftBoxes.length;
            const finalizedCount = finalizedBoxes.length;
            const emptyCount = emptyBoxes.length;
            
            const draftCountEl = document.getElementById('draft-count');
            const finalizedCountEl = document.getElementById('finalized-count');
            const emptyCountEl = document.getElementById('empty-count');
            
            if (draftCountEl) draftCountEl.textContent = draftCount;
            if (finalizedCountEl) finalizedCountEl.textContent = finalizedCount;
            if (emptyCountEl) emptyCountEl.textContent = emptyCount;
        }, 50);
    }
    
    /**
     * Capitalize first letter
     */
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

/**
 * Debounce utility function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
