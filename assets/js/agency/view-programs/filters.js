/**
 * View Programs - Filtering Functionality
 * Handles all filtering logic and UI interactions
 */

import { ViewProgramsLogic } from './logic.js';

export class ViewProgramsFilters {
    constructor() {
        this.logic = new ViewProgramsLogic();
        this.activeFilters = {
            draft: {},
            finalized: {},
            empty: {}
        };
    }
    
    init() {
        
        
        this.initDraftFilters();
        this.initFinalizedFilters();
        this.initEmptyFilters();
        
        
    }
    
    /**
     * Initialize draft programs filters
     */
    initDraftFilters() {
        const searchInput = document.getElementById('draftProgramSearch');
        const ratingFilter = document.getElementById('draftRatingFilter');
        const typeFilter = document.getElementById('draftTypeFilter');
        const initiativeFilter = document.getElementById('draftInitiativeFilter');
        const resetBtn = document.getElementById('resetDraftFilters');
        
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => {
                this.handleDraftFilter();
            }, 300));
        }
        
        if (ratingFilter) {
            ratingFilter.addEventListener('change', () => {
                this.handleDraftFilter();
            });
        }
        
        if (typeFilter) {
            typeFilter.addEventListener('change', () => {
                this.handleDraftFilter();
            });
        }
        
        if (initiativeFilter) {
            initiativeFilter.addEventListener('change', () => {
                this.handleDraftFilter();
            });
        }
        
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                this.resetDraftFilters();
            });
        }
    }
    
    /**
     * Initialize finalized programs filters
     */
    initFinalizedFilters() {
        const searchInput = document.getElementById('finalizedProgramSearch');
        const ratingFilter = document.getElementById('finalizedRatingFilter');
        const typeFilter = document.getElementById('finalizedTypeFilter');
        const initiativeFilter = document.getElementById('finalizedInitiativeFilter');
        const resetBtn = document.getElementById('resetFinalizedFilters');
        
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => {
                this.handleFinalizedFilter();
            }, 300));
        }
        
        if (ratingFilter) {
            ratingFilter.addEventListener('change', () => {
                this.handleFinalizedFilter();
            });
        }
        
        if (typeFilter) {
            typeFilter.addEventListener('change', () => {
                this.handleFinalizedFilter();
            });
        }
        
        if (initiativeFilter) {
            initiativeFilter.addEventListener('change', () => {
                this.handleFinalizedFilter();
            });
        }
        
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                this.resetFinalizedFilters();
            });
        }
    }
    
    /**
     * Initialize empty programs filters
     */
    initEmptyFilters() {
        const searchInput = document.getElementById('emptyProgramSearch');
        const typeFilter = document.getElementById('emptyTypeFilter');
        const initiativeFilter = document.getElementById('emptyInitiativeFilter');
        const resetBtn = document.getElementById('resetEmptyFilters');
        
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => {
                this.handleEmptyFilter();
            }, 300));
        }
        
        if (typeFilter) {
            typeFilter.addEventListener('change', () => {
                this.handleEmptyFilter();
            });
        }
        
        if (initiativeFilter) {
            initiativeFilter.addEventListener('change', () => {
                this.handleEmptyFilter();
            });
        }
        
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                this.resetEmptyFilters();
            });
        }
    }
    
    /**
     * Handle draft programs filtering
     */
    handleDraftFilter() {
        const search = document.getElementById('draftProgramSearch')?.value || '';
        const rating = document.getElementById('draftRatingFilter')?.value || '';
        const type = document.getElementById('draftTypeFilter')?.value || '';
        const initiative = document.getElementById('draftInitiativeFilter')?.value || '';
        
        this.activeFilters.draft = { search, rating, type, initiative };
        
        this.applyTableFilter('draftProgramsTable', this.activeFilters.draft);
        this.updateFilterBadges('draftFilterBadges', this.activeFilters.draft);
        this.updateCounters();
    }
    
    /**
     * Handle finalized programs filtering
     */
    handleFinalizedFilter() {
        const search = document.getElementById('finalizedProgramSearch')?.value || '';
        const rating = document.getElementById('finalizedRatingFilter')?.value || '';
        const type = document.getElementById('finalizedTypeFilter')?.value || '';
        const initiative = document.getElementById('finalizedInitiativeFilter')?.value || '';
        
        this.activeFilters.finalized = { search, rating, type, initiative };
        
        this.applyTableFilter('finalizedProgramsTable', this.activeFilters.finalized);
        this.updateFilterBadges('finalizedFilterBadges', this.activeFilters.finalized);
        this.updateCounters();
    }
    
    /**
     * Handle empty programs filtering
     */
    handleEmptyFilter() {
        const search = document.getElementById('emptyProgramSearch')?.value || '';
        const type = document.getElementById('emptyTypeFilter')?.value || '';
        const initiative = document.getElementById('emptyInitiativeFilter')?.value || '';
        
        this.activeFilters.empty = { search, type, initiative };
        
        this.applyTableFilter('emptyProgramsTable', this.activeFilters.empty);
        this.updateFilterBadges('emptyFilterBadges', this.activeFilters.empty);
        this.updateCounters();
    }
    
    /**
     * Apply filters to a table
     */
    applyTableFilter(tableId, filters) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            // Skip "no data" rows
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            let visible = true;
            
            // Search filter
            if (filters.search && filters.search.trim() !== '') {
                const searchTerm = filters.search.toLowerCase().trim();
                const programName = row.querySelector('.program-name')?.textContent.toLowerCase() || '';
                const programNumber = row.querySelector('.badge.bg-info')?.textContent.toLowerCase() || '';
                const initiativeName = row.querySelector('.initiative-badge-card')?.textContent.toLowerCase() || '';
                
                visible = visible && (
                    programName.includes(searchTerm) ||
                    programNumber.includes(searchTerm) ||
                    initiativeName.includes(searchTerm)
                );
            }
            
            // Rating filter
            if (filters.rating && filters.rating !== '') {
                const rating = row.querySelector('[data-rating]')?.getAttribute('data-rating') || '';
                const filterMapping = {
                    'target-achieved': 'monthly_target_achieved',
                    'on-track-yearly': 'on_track_for_year',
                    'severe-delay': 'severe_delay',
                    'not-started': 'not_started'
                };
                
                const expectedRating = filterMapping[filters.rating] || filters.rating;
                visible = visible && (rating === expectedRating);
            }
            
            // Type filter
            if (filters.type && filters.type !== '') {
                const programType = row.getAttribute('data-program-type') || '';
                visible = visible && (programType === filters.type);
            }
            
            // Initiative filter
            if (filters.initiative && filters.initiative !== '') {
                const initiativeId = row.querySelector('[data-initiative-id]')?.getAttribute('data-initiative-id') || '0';
                
                if (filters.initiative === 'no-initiative') {
                    visible = visible && (initiativeId === '0' || initiativeId === '');
                } else {
                    visible = visible && (initiativeId === filters.initiative);
                }
            }
            
            // Show/hide row
            row.style.display = visible ? '' : 'none';
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
        if (type === 'rating') {
            const ratingLabels = {
                'target-achieved': 'Monthly Target Achieved',
                'on-track-yearly': 'On Track for Year',
                'severe-delay': 'Severe Delays',
                'not-started': 'Not Started'
            };
            displayValue = ratingLabels[value] || value;
        } else if (type === 'type') {
            const typeLabels = {
                'assigned': 'Assigned',
                'created': 'Agency-Created'
            };
            displayValue = typeLabels[value] || value;
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
        // Clear the filter value
        const filterMappings = {
            'search': ['draftProgramSearch', 'finalizedProgramSearch', 'emptyProgramSearch'],
            'rating': ['draftRatingFilter', 'finalizedRatingFilter'],
            'type': ['draftTypeFilter', 'finalizedTypeFilter', 'emptyTypeFilter'],
            'initiative': ['draftInitiativeFilter', 'finalizedInitiativeFilter', 'emptyInitiativeFilter']
        };
        
        const elementIds = filterMappings[type] || [];
        elementIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = '';
            }
        });
        
        // Remove the badge
        badgeElement.remove();
        
        // Reapply filters
        this.handleDraftFilter();
        this.handleFinalizedFilter();
        this.handleEmptyFilter();
    }
    
    /**
     * Reset draft filters
     */
    resetDraftFilters() {
        document.getElementById('draftProgramSearch').value = '';
        document.getElementById('draftRatingFilter').value = '';
        document.getElementById('draftTypeFilter').value = '';
        document.getElementById('draftInitiativeFilter').value = '';
        
        this.handleDraftFilter();
    }
    
    /**
     * Reset finalized filters
     */
    resetFinalizedFilters() {
        document.getElementById('finalizedProgramSearch').value = '';
        document.getElementById('finalizedRatingFilter').value = '';
        document.getElementById('finalizedTypeFilter').value = '';
        document.getElementById('finalizedInitiativeFilter').value = '';
        
        this.handleFinalizedFilter();
    }
    
    /**
     * Reset empty filters
     */
    resetEmptyFilters() {
        document.getElementById('emptyProgramSearch').value = '';
        document.getElementById('emptyTypeFilter').value = '';
        document.getElementById('emptyInitiativeFilter').value = '';
        
        this.handleEmptyFilter();
    }
    
    /**
     * Update counters after filtering
     */
    updateCounters() {
        setTimeout(() => {
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
