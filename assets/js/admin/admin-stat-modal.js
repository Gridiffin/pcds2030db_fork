/**
 * Admin Stat Modal System
 * Handles stat card clicks and modal display with detailed program information
 */

class AdminStatModal {
    constructor() {
        this.modal = null;
        this.currentStatType = null;
        this.currentPeriodId = null;
        this.init();
    }

    init() {
        this.createModal();
        this.setupEventListeners();
    }

    /**
     * Create the modal HTML structure
     */
    createModal() {
        const modalHTML = `
            <div class="admin-stat-modal-overlay" id="adminStatModal">
                <div class="admin-stat-modal">
                    <div class="admin-stat-modal-header">
                        <h2 class="admin-stat-modal-title">
                            <div class="admin-stat-modal-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <span id="adminStatModalTitle">Stat Details</span>
                        </h2>
                        <button type="button" class="admin-stat-modal-close" aria-label="Close modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="admin-stat-modal-body">
                        <div id="adminStatModalContent">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                    <div class="admin-stat-modal-footer">
                        <div class="admin-stat-modal-count">
                            <span id="adminStatModalCount">Loading...</span>
                        </div>
                        <div class="admin-stat-modal-actions">
                            <a href="#" class="admin-stat-modal-action secondary" id="adminStatModalViewAll">
                                <i class="fas fa-list"></i>
                                View All Programs
                            </a>
                            <button type="button" class="admin-stat-modal-action primary" id="adminStatModalClose">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('adminStatModal');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Stat card click handlers
        document.addEventListener('click', (e) => {
            const statCard = e.target.closest('.admin-stat-clickable');
            if (statCard) {
                this.handleStatCardClick(statCard);
            }
        });

        // Keyboard support for stat cards
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const statCard = e.target.closest('.admin-stat-clickable');
                if (statCard) {
                    e.preventDefault();
                    this.handleStatCardClick(statCard);
                }
            }
        });

        // Modal close handlers
        if (this.modal) {
            // Close button
            const closeBtn = this.modal.querySelector('.admin-stat-modal-close');
            const footerCloseBtn = this.modal.querySelector('#adminStatModalClose');
            
            if (closeBtn) closeBtn.addEventListener('click', () => this.closeModal());
            if (footerCloseBtn) footerCloseBtn.addEventListener('click', () => this.closeModal());

            // Overlay click
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });

            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                    this.closeModal();
                }
            });
        }
    }

    /**
     * Handle stat card click
     */
    handleStatCardClick(statCard) {
        const statType = statCard.dataset.statType;
        const statValue = statCard.dataset.statValue;
        const periodId = statCard.dataset.periodId;

        if (!statType) return;

        this.currentStatType = statType;
        this.currentPeriodId = periodId;

        // Update modal title and icon
        this.updateModalHeader(statType, statValue);
        
        // Show modal
        this.showModal();
        
        // Load content
        this.loadStatDetails(statType, periodId);
    }

    /**
     * Update modal header based on stat type
     */
    updateModalHeader(statType, statValue) {
        const titleElement = this.modal.querySelector('#adminStatModalTitle');
        const iconElement = this.modal.querySelector('.admin-stat-modal-icon i');

        const statConfig = {
            'delayed_programs': {
                title: `Programs Delayed (${statValue})`,
                icon: 'fas fa-exclamation-triangle'
            },
            'on_track_programs': {
                title: `Programs On Track (${statValue})`,
                icon: 'fas fa-calendar-check'
            },
            'agencies_reported': {
                title: `Users Reporting (${statValue})`,
                icon: 'fas fa-users'
            },
            'completion_percentage': {
                title: `Overall Completion (${statValue}%)`,
                icon: 'fas fa-clipboard-list'
            }
        };

        const config = statConfig[statType] || {
            title: `Stat Details (${statValue})`,
            icon: 'fas fa-chart-bar'
        };

        if (titleElement) titleElement.textContent = config.title;
        if (iconElement) iconElement.className = config.icon;
    }

    /**
     * Show modal
     */
    showModal() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Focus management
            const firstFocusable = this.modal.querySelector('.admin-stat-modal-close');
            if (firstFocusable) {
                setTimeout(() => firstFocusable.focus(), 100);
            }
        }
    }

    /**
     * Close modal
     */
    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
            
            // Return focus to the stat card that was clicked
            const statCard = document.querySelector(`[data-stat-type="${this.currentStatType}"]`);
            if (statCard) {
                statCard.focus();
            }
        }
    }

    /**
     * Load stat details via AJAX
     */
    async loadStatDetails(statType, periodId) {
        const contentElement = this.modal.querySelector('#adminStatModalContent');
        const countElement = this.modal.querySelector('#adminStatModalCount');
        const viewAllBtn = this.modal.querySelector('#adminStatModalViewAll');

        // Show loading state
        contentElement.innerHTML = `
            <div class="admin-stat-modal-loading">
                <div class="admin-stat-modal-spinner"></div>
                Loading program details...
            </div>
        `;

        if (countElement) countElement.textContent = 'Loading...';

        try {
            const response = await fetch(`${APP_URL}/app/ajax/get_stat_details.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    stat_type: statType,
                    period_id: periodId
                })
            });

            if (!response.ok) {
                throw new Error('Failed to fetch data');
            }

            const data = await response.json();

            if (data.success) {
                this.renderStatDetails(data.programs, statType);
                
                if (countElement) {
                    if (statType === 'agencies_reported') {
                        // For agencies, count them directly
                        countElement.textContent = `${data.programs.length} ${data.programs.length === 1 ? 'agency' : 'agencies'} reported`;
                    } else {
                        countElement.textContent = `${data.programs.length} ${data.programs.length === 1 ? 'program' : 'programs'} found`;
                    }
                }

                // Update "View All" button
                if (viewAllBtn) {
                    if (statType === 'agencies_reported') {
                        viewAllBtn.textContent = 'View All Users';
                        viewAllBtn.innerHTML = '<i class="fas fa-users"></i> View All Users';
                        viewAllBtn.href = `${APP_URL}/app/views/admin/users/manage_users.php`;
                    } else {
                        const filterParam = this.getFilterParam(statType);
                        viewAllBtn.href = `${APP_URL}/app/views/admin/programs/programs.php${filterParam ? `?${filterParam}` : ''}`;
                    }
                }
            } else {
                throw new Error(data.message || 'Failed to load data');
            }
        } catch (error) {
            console.error('Error loading stat details:', error);
            this.renderError(error.message);
        }
    }

    /**
     * Get filter parameter for "View All" link
     */
    getFilterParam(statType) {
        const filterMap = {
            'delayed_programs': 'rating=delayed',
            'on_track_programs': 'rating=on_track',
            'agencies_reported': 'status=reported'
        };
        
        return filterMap[statType] || '';
    }

    /**
     * Render stat details
     */
    renderStatDetails(data, statType) {
        const contentElement = this.modal.querySelector('#adminStatModalContent');

        if (!data || data.length === 0) {
            const emptyType = statType === 'agencies_reported' ? 'Agencies' : 'Programs';
            contentElement.innerHTML = `
                <div class="admin-stat-modal-empty">
                    <div class="admin-stat-modal-empty-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>No ${emptyType} Found</h3>
                    <p>No ${emptyType.toLowerCase()} match the selected criteria for the current period.</p>
                </div>
            `;
            return;
        }

        if (statType === 'agencies_reported') {
            // Render agencies that have reported
            const agenciesList = data.map(agency => `
                <li class="admin-stat-program-item" onclick="window.location.href='${APP_URL}/app/views/admin/users/manage_users.php?agency_id=${agency.agency_id}'">
                    <div class="admin-stat-program-header">
                        <div>
                            <h4 class="admin-stat-program-name">
                                ${this.escapeHtml(agency.agency_name)}
                            </h4>
                            <div class="admin-stat-program-id">Agency ID: ${agency.agency_id}</div>
                        </div>
                    </div>
                    <div class="admin-stat-program-meta">
                        <div class="admin-stat-program-agency">
                            <strong>Programs:</strong> ${agency.total_programs}
                        </div>
                        <div class="admin-stat-program-status">
                            <strong>Submitted:</strong> ${agency.submitted_programs}
                        </div>
                    </div>
                </li>
            `).join('');

            contentElement.innerHTML = `
                <div class="admin-stat-modal-content">
                    <ul class="admin-stat-program-list">
                        ${agenciesList}
                    </ul>
                </div>
            `;
        } else {
            // Render programs for other stat types
            const programsList = data.map(program => `
                <li class="admin-stat-program-item" onclick="window.location.href='${APP_URL}/app/views/admin/programs/program_details.php?id=${program.program_id}'">
                    <div class="admin-stat-program-header">
                        <div>
                            <h4 class="admin-stat-program-name">
                                ${this.escapeHtml(program.program_name)}
                            </h4>
                            <div class="admin-stat-program-id">ID: ${program.program_id}</div>
                        </div>
                    </div>
                    <div class="admin-stat-program-meta">
                        <div class="admin-stat-program-agency">
                            <strong>Agency:</strong> ${this.escapeHtml(program.agency_name || 'Unknown')}
                        </div>
                        <div class="admin-stat-program-status">
                            <div class="admin-stat-status-indicator ${this.getStatusClass(program.rating || statType)}"></div>
                            <span>${this.getStatusText(program.rating || statType)}</span>
                        </div>
                    </div>
                </li>
            `).join('');

            contentElement.innerHTML = `
                <div class="admin-stat-modal-content">
                    <ul class="admin-stat-program-list">
                        ${programsList}
                    </ul>
                </div>
            `;
        }
    }

    /**
     * Render error state
     */
    renderError(message) {
        const contentElement = this.modal.querySelector('#adminStatModalContent');
        contentElement.innerHTML = `
            <div class="admin-stat-modal-empty">
                <div class="admin-stat-modal-empty-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Error Loading Data</h3>
                <p>${this.escapeHtml(message)}</p>
                <button onclick="this.closest('.admin-stat-modal-overlay').querySelector('.admin-stat-modal-close').click()" 
                        class="btn btn-outline-primary">Close</button>
            </div>
        `;
    }

    /**
     * Get status CSS class
     */
    getStatusClass(status) {
        const statusMap = {
            'delayed': 'delayed',
            'delayed_programs': 'delayed',
            'on_track': 'on-track',
            'on_track_programs': 'on-track',
            'completed': 'completed'
        };
        
        return statusMap[status] || 'on-track';
    }

    /**
     * Get status display text
     */
    getStatusText(status) {
        const statusMap = {
            'delayed': 'Delayed',
            'delayed_programs': 'Delayed',
            'on_track': 'On Track',
            'on_track_programs': 'On Track',
            'completed': 'Completed'
        };
        
        return statusMap[status] || 'On Track';
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new AdminStatModal();
});

// Also initialize if script loads after DOM
if (document.readyState !== 'loading') {
    new AdminStatModal();
}