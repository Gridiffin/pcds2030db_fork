/**
 * Command Palette for Agency Dashboard
 * 
 * Transforms the navbar search into a powerful command palette
 * allowing users to quickly access common actions and navigate.
 */

class CommandPalette {
    constructor() {
        this.commands = [];
        this.isVisible = false;
        this.selectedIndex = -1;
        this.filteredCommands = [];
        
        this.init();
    }

    init() {
        this.setupCommands();
        this.filteredCommands = [...this.commands]; // Initialize with all commands
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
    }

    setupCommands() {
        // Define all available commands with categories
        this.commands = [
            // Program Management
            {
                id: 'create-program',
                title: 'Create a new program',
                description: 'Start a new program for your agency',
                category: 'Programs',
                icon: 'fas fa-plus-circle',
                action: () => this.navigate('/app/views/agency/programs/create_program.php'),
                keywords: ['create', 'new', 'program', 'add', 'start', 'make']
            },
            {
                id: 'view-programs',
                title: 'View my programs',
                description: 'See all your agency programs',
                category: 'Programs',
                icon: 'fas fa-project-diagram',
                action: () => this.navigate('/app/views/agency/programs/view_programs.php'),
                keywords: ['view', 'programs', 'my', 'list', 'see', 'show']
            },
            {
                id: 'edit-program',
                title: 'Edit a program',
                description: 'Select and update program information',
                category: 'Programs',
                icon: 'fas fa-edit',
                action: () => this.showProgramSelector('edit'),
                keywords: ['edit', 'update', 'modify', 'program', 'change']
            },
            {
                id: 'view-submissions',
                title: 'Check my submissions',
                description: 'Select and review specific submissions',
                category: 'Programs',
                icon: 'fas fa-list',
                action: () => this.showSubmissionSelector(),
                keywords: ['view', 'submissions', 'check', 'program', 'review', 'see']
            },

            {
                id: 'view-outcomes',
                title: 'Check my outcomes',
                description: 'Review outcome reports and results',
                category: 'Outcomes',
                icon: 'fas fa-chart-bar',
                action: () => this.navigate('/app/views/agency/outcomes/view_outcome.php'),
                keywords: ['view', 'outcomes', 'review', 'reports', 'metrics', 'check', 'see']
            },

            // Navigation
            {
                id: 'dashboard',
                title: 'Go to dashboard',
                description: 'Return to the main dashboard page',
                category: 'Navigation',
                icon: 'fas fa-tachometer-alt',
                action: () => this.navigate('/app/views/agency/dashboard/dashboard.php'),
                keywords: ['dashboard', 'home', 'main', 'overview', 'go']
            },
            {
                id: 'initiatives',
                title: 'Browse initiatives',
                description: 'View available initiatives to join',
                category: 'Navigation',
                icon: 'fas fa-lightbulb',
                action: () => this.navigate('/app/views/agency/initiatives/initiatives.php'),
                keywords: ['initiatives', 'browse', 'view', 'available', 'explore']
            },
            {
                id: 'public-reports',
                title: 'Download public reports',
                description: 'Access and download public reports',
                category: 'Reports',
                icon: 'fas fa-file-download',
                action: () => this.navigate('/app/views/agency/reports/public_reports.php'),
                keywords: ['public', 'reports', 'download', 'export', 'get']
            },
            {
                id: 'notifications',
                title: 'Check my notifications',
                description: 'View all your notifications and updates',
                category: 'Account',
                icon: 'fas fa-bell',
                action: () => this.navigate('/app/views/agency/users/all_notifications.php'),
                keywords: ['notifications', 'alerts', 'messages', 'updates', 'check']
            },


            // Additional common actions users might search for
            {
                id: 'help',
                title: 'Get help',
                description: 'Find help and support resources',
                category: 'Support',
                icon: 'fas fa-question-circle',
                action: () => this.showHelp(),
                keywords: ['help', 'support', 'assistance', 'guide', 'how to']
            },
            {
                id: 'settings',
                title: 'Account settings',
                description: 'Manage your account preferences',
                category: 'Account',
                icon: 'fas fa-cog',
                action: () => this.showSettings(),
                keywords: ['settings', 'preferences', 'account', 'profile', 'configure']
            }
        ];
    }

    setupEventListeners() {
        const searchInput = document.querySelector('.navbar-search-input');
        if (!searchInput) return;

        // Override existing search input behavior
        const existingEventListeners = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(existingEventListeners, searchInput);
        
        // Add new event listeners
        existingEventListeners.addEventListener('input', (e) => this.handleSearch(e));
        existingEventListeners.addEventListener('focus', () => this.show());
        existingEventListeners.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar-search-modern') && !e.target.closest('.command-palette')) {
                this.hide();
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K to open command palette
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.toggle();
            }
            
            // Escape to close
            if (e.key === 'Escape' && this.isVisible) {
                this.hide();
            }
        });
    }

    handleSearch(e) {
        const query = e.target.value.trim().toLowerCase();
        
        if (query === '') {
            this.filteredCommands = [...this.commands];
        } else {
            this.filteredCommands = this.commands.filter(command => {
                const searchText = [
                    command.title,
                    command.description,
                    command.category,
                    ...command.keywords
                ].join(' ').toLowerCase();
                
                return this.fuzzyMatch(searchText, query);
            });
        }
        
        this.selectedIndex = -1;
        this.render();
        this.show();
    }

    fuzzyMatch(text, query) {
        // Simple fuzzy matching algorithm
        const chars = query.split('');
        let index = 0;
        
        for (let char of chars) {
            index = text.indexOf(char, index);
            if (index === -1) return false;
            index++;
        }
        
        return true;
    }

    handleKeydown(e) {
        if (!this.isVisible) return;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                const maxIndex = document.querySelectorAll('.command-item').length - 1;
                this.selectedIndex = Math.min(this.selectedIndex + 1, maxIndex);
                this.updateSelection();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection();
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0) {
                    const selectedItem = document.querySelectorAll('.command-item')[this.selectedIndex];
                    if (selectedItem) {
                        const commandId = selectedItem.dataset.commandId;
                        const command = this.commands.find(cmd => cmd.id === commandId);
                        if (command) {
                            this.executeCommand(command);
                        }
                    }
                }
                break;
                
            case 'Escape':
                this.hide();
                break;
        }
    }

    updateSelection() {
        const items = document.querySelectorAll('.command-item');
        items.forEach((item, index) => {
            // Use the DOM index for selection highlighting
            item.classList.toggle('selected', index === this.selectedIndex);
        });
        
        // Scroll selected item into view
        if (this.selectedIndex >= 0 && this.selectedIndex < items.length) {
            const selectedItem = items[this.selectedIndex];
            if (selectedItem) {
                selectedItem.scrollIntoView({ block: 'nearest' });
            }
        }
    }

    executeCommand(command) {
        this.hide();
        this.addToHistory(command);
        command.action();
    }

    addToHistory(command) {
        // Store recent commands in localStorage
        let history = JSON.parse(localStorage.getItem('commandHistory') || '[]');
        history = history.filter(cmd => cmd.id !== command.id);
        history.unshift({ id: command.id, timestamp: Date.now() });
        history = history.slice(0, 10); // Keep only 10 recent commands
        localStorage.setItem('commandHistory', JSON.stringify(history));
    }

    show() {
        if (this.isVisible) return;
        
        this.isVisible = true;
        this.createPalette();
        this.render();
        
        // Show the palette
        const palette = document.querySelector('.command-palette');
        if (palette) {
            palette.classList.add('show');
        }
        
        // Focus search input
        const searchInput = document.querySelector('.navbar-search-input');
        if (searchInput) {
            searchInput.focus();
        }
    }

    hide() {
        this.isVisible = false;
        const palette = document.querySelector('.command-palette');
        if (palette) {
            palette.remove();
        }
        
        // Clear search input
        const searchInput = document.querySelector('.navbar-search-input');
        if (searchInput) {
            searchInput.value = '';
        }
        
        this.selectedIndex = -1;
        this.filteredCommands = [...this.commands];
    }

    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }

    createPalette() {
        // Remove existing palette
        const existingPalette = document.querySelector('.command-palette');
        if (existingPalette) {
            existingPalette.remove();
        }
        
        // Create palette container
        const palette = document.createElement('div');
        palette.className = 'command-palette';
        palette.innerHTML = `
            <div class="command-palette-content">
                <div class="command-palette-header">
                    <div class="command-palette-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </div>
                    <div class="command-palette-shortcut">
                        <kbd>Ctrl</kbd> + <kbd>K</kbd>
                    </div>
                </div>
                <div class="command-palette-results"></div>
                <div class="command-palette-footer">
                    <div class="command-palette-hints">
                        <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
                        <span><kbd>Enter</kbd> Select</span>
                        <span><kbd>Esc</kbd> Close</span>
                    </div>
                </div>
            </div>
        `;
        
        // Position palette below search input
        const searchContainer = document.querySelector('.navbar-search-modern');
        if (searchContainer) {
            searchContainer.appendChild(palette);
        } else {
            document.body.appendChild(palette);
        }
    }

    render() {
        const resultsContainer = document.querySelector('.command-palette-results');
        if (!resultsContainer) return;
        
        if (this.filteredCommands.length === 0) {
            resultsContainer.innerHTML = `
                <div class="command-empty">
                    <i class="fas fa-search"></i>
                    <div>No actions found</div>
                    <small>Try searching for "create", "view", or "check"</small>
                </div>
            `;
            return;
        }
        
        // Group commands by category
        const groupedCommands = this.groupByCategory(this.filteredCommands);
        
        let html = '';
        let domIndex = 0; // Track the DOM index across all groups
        
        Object.entries(groupedCommands).forEach(([category, commands]) => {
            html += `<div class="command-group">
                <div class="command-group-title">${category}</div>
            `;
            
            commands.forEach((command) => {
                html += `
                    <div class="command-item ${domIndex === this.selectedIndex ? 'selected' : ''}" 
                         data-command-id="${command.id}"
                         data-dom-index="${domIndex}">
                        <div class="command-icon">
                            <i class="${command.icon}"></i>
                        </div>
                        <div class="command-content">
                            <div class="command-title">${command.title}</div>
                            <div class="command-description">${command.description}</div>
                        </div>
                    </div>
                `;
                domIndex++; // Increment for each command item
            });
            
            html += '</div>';
        });
        
        resultsContainer.innerHTML = html;
        
        // Add click handlers with proper indexing
        const commandItems = document.querySelectorAll('.command-item');
        commandItems.forEach((item, domIndex) => {
            item.addEventListener('click', () => {
                const commandId = item.dataset.commandId;
                const command = this.commands.find(cmd => cmd.id === commandId);
                if (command) {
                    this.executeCommand(command);
                }
            });
            
            item.addEventListener('mouseenter', () => {
                // Set selectedIndex to the DOM index, which matches the rendered order
                this.selectedIndex = domIndex;
                this.updateSelection();
            });
            
            item.addEventListener('mouseleave', () => {
                // Optional: clear selection when mouse leaves
                // this.selectedIndex = -1;
                // this.updateSelection();
            });
        });
    }

    groupByCategory(commands) {
        return commands.reduce((groups, command) => {
            const category = command.category;
            if (!groups[category]) {
                groups[category] = [];
            }
            groups[category].push(command);
            return groups;
        }, {});
    }

    // Action methods
    navigate(url) {
        // Use the same pattern as the rest of the application: APP_URL + path
        // The APP_URL is dynamically detected and works for both local and live environments
        
        const pathSegments = window.location.pathname.split('/');
        
        // Find the base application path (should include pcds2030_dashboard_fork or similar)
        let appUrl = window.location.origin;
        
        // Check if we're in a subfolder (like pcds2030_dashboard_fork)
        if (pathSegments.includes('pcds2030_dashboard_fork')) {
            appUrl += '/pcds2030_dashboard_fork';
        } else if (pathSegments.includes('pcds2030_dashboard')) {
            appUrl += '/pcds2030_dashboard';
        }
        
        // Navigate using the same pattern as other PHP files: APP_URL + /app/views/...
        const fullUrl = appUrl + url;
        window.location.href = fullUrl;
    }

    getApiUrl(path) {
        // Helper method to generate API URLs using the same pattern as navigate()
        const pathSegments = window.location.pathname.split('/');
        
        // Find the base application path
        let appUrl = window.location.origin;
        
        // Check if we're in a subfolder
        if (pathSegments.includes('pcds2030_dashboard_fork')) {
            appUrl += '/pcds2030_dashboard_fork';
        } else if (pathSegments.includes('pcds2030_dashboard')) {
            appUrl += '/pcds2030_dashboard';
        }
        
        return appUrl + path;
    }

    refreshDashboard() {
        const refreshButton = document.getElementById('refreshDashboard');
        if (refreshButton) {
            refreshButton.click();
        } else {
            window.location.reload();
        }
    }

    async showProgramSelector(action) {
        try {
            const response = await fetch(this.getApiUrl('/app/api/command_palette_data.php?type=programs'));
            const data = await response.json();
            
            if (data.success && data.data) {
                this.showSelectionModal(
                    action === 'edit' ? 'Select Program to Edit' : 'Select Program',
                    data.data,
                    (selectedItem) => {
                        if (action === 'edit' && selectedItem && selectedItem.id > 0) {
                            // Navigate to edit page with the selected program ID
                            this.navigate(`/app/views/agency/programs/edit_program.php?id=${selectedItem.id}`);
                        } else {
                            // Navigate to view programs page for other actions
                            this.navigate('/app/views/agency/programs/view_programs.php');
                        }
                    }
                );
            } else {
                // Fallback to sample data if API fails
                this.showSelectionModal(
                    action === 'edit' ? 'Select Program to Edit' : 'Select Program',
                    [{ id: 0, name: 'No programs found', status: 'Info' }],
                    () => {
                        this.navigate('/app/views/agency/programs/view_programs.php');
                    }
                );
            }
        } catch (error) {
            console.error('Error fetching programs:', error);
            // Fallback to sample data if fetch fails
            this.showSelectionModal(
                action === 'edit' ? 'Select Program to Edit' : 'Select Program',
                [{ id: 0, name: 'Error loading programs', status: 'Error' }],
                () => {
                    this.navigate('/app/views/agency/programs/view_programs.php');
                }
            );
        }
    }

    async showSubmissionSelector() {
        try {
            const response = await fetch(this.getApiUrl('/app/api/command_palette_data.php?type=submissions'));
            const data = await response.json();
            
            if (data.success && data.data) {
                this.showSelectionModal(
                    'Select Submission to Review',
                    data.data,
                    (selectedItem) => {
                        if (selectedItem && selectedItem.id > 0 && selectedItem.program_id && selectedItem.period_id) {
                            // Navigate to view submission page with the correct parameters
                            this.navigate(`/app/views/agency/programs/view_submissions.php?program_id=${selectedItem.program_id}&period_id=${selectedItem.period_id}`);
                        } else {
                            // Navigate to view submissions page for fallback
                            this.navigate('/app/views/agency/programs/view_submissions.php');
                        }
                    }
                );
            } else {
                // Fallback to empty state if API fails
                this.showSelectionModal(
                    'Select Submission to Review',
                    [{ id: 0, name: 'No submissions found', status: 'Info' }],
                    () => {
                        this.navigate('/app/views/agency/programs/view_submissions.php');
                    }
                );
            }
        } catch (error) {
            console.error('Error fetching submissions:', error);
            // Fallback to error state if fetch fails
            this.showSelectionModal(
                'Select Submission to Review',
                [{ id: 0, name: 'Error loading submissions', status: 'Error' }],
                () => {
                    this.navigate('/app/views/agency/programs/view_submissions.php');
                }
            );
        }
    }

    showSelectionModal(title, items, onSelect) {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'command-modal-overlay';
        
        // Create modal content
        const modal = document.createElement('div');
        modal.className = 'command-modal';
        modal.innerHTML = `
            <div class="command-modal-header">
                <h3>${title}</h3>
                <button class="command-modal-close" type="button">&times;</button>
            </div>
            <div class="command-modal-body">
                <div class="command-modal-list">
                    ${items.map(item => `
                        <div class="command-modal-item" data-id="${item.id}">
                            <div class="command-modal-item-content">
                                <div class="command-modal-item-name">${item.name}</div>
                                ${item.status ? `<div class="command-modal-item-status status-${item.status.toLowerCase().replace(' ', '-')}">${item.status}</div>` : ''}
                                ${item.period ? `<div class="command-modal-item-period">${item.period}</div>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Add event listeners
        const closeBtn = modal.querySelector('.command-modal-close');
        const modalItems = modal.querySelectorAll('.command-modal-item');
        
        const closeModal = () => {
            overlay.remove();
        };
        
        closeBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModal();
        });
        
        modalItems.forEach(item => {
            item.addEventListener('click', () => {
                const selectedId = parseInt(item.dataset.id);
                const selectedItem = items.find(i => i.id === selectedId);
                closeModal();
                onSelect(selectedItem);
            });
        });
        
        // Show modal with animation
        setTimeout(() => overlay.classList.add('show'), 10);
    }

    showHelp() {
        alert('Help and support resources will be available soon. For now, please contact your system administrator for assistance.');
    }

    showSettings() {
        alert('Account settings functionality will be available soon. Please contact your system administrator to update your preferences.');
    }
}

// Initialize command palette when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on agency pages
    if (document.querySelector('.navbar-search-modern')) {
        window.commandPalette = new CommandPalette();
    }
});