/**
 * Submit Outcomes Module
 * Handles the outcomes submission and listing page functionality
 */

export class SubmitOutcomes {
    constructor() {
        this.currentPeriod = null;
        this.outcomes = [];
        this.filteredOutcomes = [];
        this.loading = false;
    }

    /**
     * Initialize submit outcomes functionality
     */
    init() {
        console.log('SubmitOutcomes: Initializing submit outcomes module');
        
        // Load initial data
        this.loadCurrentPeriod();
        this.loadOutcomes();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Initialize filters
        this.initializeFilters();
    }

    /**
     * Load current period information
     */
    loadCurrentPeriod() {
        const periodSelect = document.getElementById('period-select');
        if (periodSelect) {
            this.currentPeriod = periodSelect.value;
        }
    }

    /**
     * Load outcomes data from server or page
     */
    loadOutcomes() {
        // Check if outcomes data is provided by PHP
        if (typeof window.outcomesData !== 'undefined') {
            this.outcomes = window.outcomesData;
            this.filteredOutcomes = [...this.outcomes];
            this.renderOutcomes();
        } else {
            // Load via AJAX if needed
            this.fetchOutcomes();
        }
    }

    /**
     * Fetch outcomes from server via AJAX
     */
    async fetchOutcomes() {
        this.setLoading(true);
        
        try {
            const response = await fetch(`ajax/get_outcomes.php?period=${this.currentPeriod}`);
            if (response.ok) {
                const data = await response.json();
                this.outcomes = data.outcomes || [];
                this.filteredOutcomes = [...this.outcomes];
                this.renderOutcomes();
            } else {
                throw new Error('Failed to fetch outcomes');
            }
        } catch (error) {
            console.error('SubmitOutcomes: Error fetching outcomes:', error);
            this.showError('Failed to load outcomes. Please refresh the page.');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Period selector change
        const periodSelect = document.getElementById('period-select');
        if (periodSelect) {
            periodSelect.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.loadOutcomes();
            });
        }

        // Search functionality
        const searchInput = document.getElementById('outcomes-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterOutcomes(e.target.value);
            });
        }

        // Type filter
        const typeFilter = document.getElementById('type-filter');
        if (typeFilter) {
            typeFilter.addEventListener('change', (e) => {
                this.filterByType(e.target.value);
            });
        }

        // Quick action buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quick-action-btn')) {
                e.preventDefault();
                this.handleQuickAction(e.target.dataset.action);
            }
        });

        // Outcome card actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.outcome-action-btn')) {
                this.handleOutcomeAction(e);
            }
        });
    }

    /**
     * Initialize filter controls
     */
    initializeFilters() {
        // Set up type filter options
        const typeFilter = document.getElementById('type-filter');
        if (typeFilter && this.outcomes.length > 0) {
            const types = [...new Set(this.outcomes.map(outcome => outcome.type))];
            types.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = this.formatType(type);
                typeFilter.appendChild(option);
            });
        }
    }

    /**
     * Filter outcomes by search term
     */
    filterOutcomes(searchTerm) {
        const term = searchTerm.toLowerCase();
        this.filteredOutcomes = this.outcomes.filter(outcome => 
            outcome.title.toLowerCase().includes(term) ||
            outcome.code.toLowerCase().includes(term) ||
            outcome.description.toLowerCase().includes(term)
        );
        this.renderOutcomes();
    }

    /**
     * Filter outcomes by type
     */
    filterByType(type) {
        if (type === 'all' || !type) {
            this.filteredOutcomes = [...this.outcomes];
        } else {
            this.filteredOutcomes = this.outcomes.filter(outcome => outcome.type === type);
        }
        this.renderOutcomes();
    }

    /**
     * Render outcomes grid
     */
    renderOutcomes() {
        const container = document.getElementById('outcomes-grid');
        if (!container) {
            console.warn('SubmitOutcomes: Outcomes grid container not found');
            return;
        }

        if (this.filteredOutcomes.length === 0) {
            this.renderEmptyState(container);
            return;
        }

        let html = '';
        this.filteredOutcomes.forEach(outcome => {
            html += this.renderOutcomeCard(outcome);
        });

        container.innerHTML = html;
        console.log('SubmitOutcomes: Rendered', this.filteredOutcomes.length, 'outcomes');
    }

    /**
     * Render individual outcome card
     */
    renderOutcomeCard(outcome) {
        const updatedDate = new Date(outcome.updated_at);
        const formattedDate = updatedDate.toLocaleDateString();
        
        return `
            <div class=\"outcome-card\" data-outcome-id=\"${outcome.id}\">
                <div class=\"outcome-card-header\">
                    <div class=\"outcome-code\">${this.escapeHtml(outcome.code)}</div>
                    <h3 class=\"outcome-title\">${this.escapeHtml(outcome.title)}</h3>
                </div>
                <div class=\"outcome-card-body\">
                    <p class=\"outcome-description\">${this.escapeHtml(outcome.description || 'No description available.')}</p>
                    <div class=\"outcome-meta\">
                        <span class=\"outcome-type type-${outcome.type}\">${this.formatType(outcome.type)}</span>
                        <span class=\"outcome-updated\">Updated ${formattedDate}</span>
                    </div>
                    <div class=\"outcome-actions\">
                        <a href=\"view_outcome.php?id=${outcome.id}\" 
                           class=\"outcome-action-btn btn-primary\">
                            <i class=\"fas fa-eye\"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render empty state
     */
    renderEmptyState(container) {
        container.innerHTML = `
            <div class=\"outcomes-empty\">
                <i class=\"fas fa-chart-bar\"></i>
                <h3>No Outcomes Available</h3>
                <p>No outcomes found for the selected period. Outcomes are created by administrators and assigned to reporting periods.</p>
                <div class=\"outcomes-empty-actions\">
                    <button type=\"button\" class=\"btn btn-primary\" onclick=\"location.reload()\">
                        <i class=\"fas fa-refresh\"></i> Refresh
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Handle quick action buttons
     */
    handleQuickAction(action) {
        switch (action) {
            case 'refresh':
                this.loadOutcomes();
                break;
            case 'export':
                this.exportOutcomes();
                break;
            default:
                console.warn('SubmitOutcomes: Unknown quick action:', action);
        }
    }

    /**
     * Handle outcome card action clicks
     */
    handleOutcomeAction(event) {
        // Let the default navigation happen for view links
        // Could add tracking or confirmation here if needed
        const action = event.target.textContent.trim().toLowerCase();
        const outcomeId = event.target.closest('.outcome-card').dataset.outcomeId;
        
        console.log(`SubmitOutcomes: ${action} action for outcome ${outcomeId}`);
    }

    /**
     * Export outcomes data
     */
    exportOutcomes() {
        try {
            let csvContent = 'Code,Title,Type,Description,Last Updated\\n';
            
            this.filteredOutcomes.forEach(outcome => {
                const row = [
                    outcome.code,
                    outcome.title,
                    outcome.type,
                    outcome.description || '',
                    outcome.updated_at
                ].map(field => `\"${(field || '').toString().replace(/\"/g, '\"\"')}\"`);
                
                csvContent += row.join(',') + '\\n';
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `outcomes-${this.currentPeriod}-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            console.log('SubmitOutcomes: Outcomes exported successfully');
        } catch (error) {
            console.error('SubmitOutcomes: Error exporting outcomes:', error);
        }
    }

    /**
     * Set loading state
     */
    setLoading(loading) {
        this.loading = loading;
        
        const container = document.getElementById('outcomes-grid');
        if (!container) return;
        
        if (loading) {
            container.innerHTML = `
                <div class=\"outcomes-loading\">
                    <i class=\"fas fa-spinner fa-spin\"></i>
                    <span class=\"outcomes-loading-text\">Loading outcomes...</span>
                </div>
            `;
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const container = document.getElementById('outcomes-grid');
        if (container) {
            container.innerHTML = `
                <div class=\"alert alert-danger\">
                    <i class=\"fas fa-exclamation-triangle\"></i>
                    ${message}
                </div>
            `;
        }
    }

    /**
     * Format outcome type for display
     */
    formatType(type) {
        return type.split('_').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    /**
     * Escape HTML for safe display
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    /**
     * Clean up resources
     */
    destroy() {
        // Clean up any intervals or timeouts if needed
        console.log('SubmitOutcomes: Module destroyed');
    }
}
