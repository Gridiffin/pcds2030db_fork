/**
 * Program-Outcome Links Management JavaScript
 * 
 * Handles UI interactions for creating and managing program-outcome links
 */

class ProgramOutcomeLinksManager {
    constructor() {
        this.programs = [];
        this.outcomes = [];
        this.links = [];
        this.selectedProgram = null;
        this.selectedOutcome = null;
        
        this.init();
    }

    async init() {
        try {
            await this.loadData();
            this.bindEvents();
            this.renderAll();
        } catch (error) {
            console.error('Failed to initialize:', error);
            this.showAlert('Failed to load data. Please refresh the page.', 'danger');
        }
    }

    async loadData() {
        const [programsResponse, outcomesResponse, linksResponse] = await Promise.all([
            fetch('/app/api/programs.php'),
            fetch('/app/api/outcomes.php'),
            fetch('/app/api/program_outcome_links.php')
        ]);

        if (!programsResponse.ok || !outcomesResponse.ok || !linksResponse.ok) {
            throw new Error('Failed to load data from API');
        }

        this.programs = await programsResponse.json();
        this.outcomes = await outcomesResponse.json();
        
        const linksData = await linksResponse.json();
        this.links = linksData.success ? linksData.data : [];
    }

    bindEvents() {
        // Search and filter events
        document.getElementById('programSearch').addEventListener('input', 
            debounce(() => this.renderPrograms(), 300));
        document.getElementById('outcomeSearch').addEventListener('input', 
            debounce(() => this.renderOutcomes(), 300));
        document.getElementById('sectorFilter').addEventListener('change', 
            () => this.renderAll());

        // Modal events
        document.getElementById('createLinkBtn').addEventListener('click', 
            () => this.createLink());
        
        // Form submission
        document.getElementById('createLinkForm').addEventListener('submit', 
            (e) => {
                e.preventDefault();
                this.createLink();
            });
    }

    renderAll() {
        this.renderPrograms();
        this.renderOutcomes();
        this.renderLinks();
        this.populateModalSelects();
    }

    renderPrograms() {
        const searchTerm = document.getElementById('programSearch').value.toLowerCase();
        const sectorFilter = document.getElementById('sectorFilter').value;
        
        const filteredPrograms = this.programs.filter(program => {
            const matchesSearch = program.program_name.toLowerCase().includes(searchTerm) ||
                                (program.agency_name && program.agency_name.toLowerCase().includes(searchTerm));
            const matchesSector = !sectorFilter || program.sector_id == sectorFilter;
            return matchesSearch && matchesSector;
        });

        const tbody = document.getElementById('programsList');
        tbody.innerHTML = '';

        filteredPrograms.forEach(program => {
            const linkCount = this.links.filter(link => link.program_id == program.program_id).length;
            
            const row = document.createElement('tr');
            row.dataset.programId = program.program_id;
            row.innerHTML = `
                <td>
                    <div class="fw-bold">${this.escapeHtml(program.program_name)}</div>
                    ${program.program_number ? `<small class="text-muted">${this.escapeHtml(program.program_number)}</small>` : ''}
                </td>
                <td>
                    <small>${this.escapeHtml(program.agency_name || 'Unknown')}</small>
                </td>
                <td class="text-center">
                    <span class="badge badge-link-count">${linkCount}</span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-link-action" 
                            onclick="programLinksManager.viewProgramLinks(${program.program_id})"
                            title="View Links">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success btn-link-action ms-1" 
                            onclick="programLinksManager.showCreateLinkModal(${program.program_id}, null)"
                            title="Create Link">
                        <i class="fas fa-plus"></i>
                    </button>
                </td>
            `;
            
            // Add click handler for row selection
            row.addEventListener('click', (e) => {
                if (!e.target.closest('button')) {
                    this.selectProgram(program.program_id);
                }
            });
            
            tbody.appendChild(row);
        });
    }

    renderOutcomes() {
        const searchTerm = document.getElementById('outcomeSearch').value.toLowerCase();
        
        const filteredOutcomes = this.outcomes.filter(outcome => 
            outcome.detail_name.toLowerCase().includes(searchTerm)
        );

        const tbody = document.getElementById('outcomesList');
        tbody.innerHTML = '';

        filteredOutcomes.forEach(outcome => {
            const linkCount = this.links.filter(link => link.outcome_id == outcome.detail_id).length;
            
            const row = document.createElement('tr');
            row.dataset.outcomeId = outcome.detail_id;
            row.innerHTML = `
                <td>
                    <div class="fw-bold">${this.escapeHtml(outcome.detail_name)}</div>
                </td>
                <td class="text-center">
                    <span class="badge ${outcome.is_cumulative ? 'bg-info' : 'bg-secondary'}">
                        ${outcome.is_cumulative ? 'Cumulative' : 'Standard'}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge badge-link-count">${linkCount}</span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-link-action" 
                            onclick="programLinksManager.viewOutcomeLinks(${outcome.detail_id})"
                            title="View Links">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success btn-link-action ms-1" 
                            onclick="programLinksManager.showCreateLinkModal(null, ${outcome.detail_id})"
                            title="Create Link">
                        <i class="fas fa-plus"></i>
                    </button>
                </td>
            `;
            
            // Add click handler for row selection
            row.addEventListener('click', (e) => {
                if (!e.target.closest('button')) {
                    this.selectOutcome(outcome.detail_id);
                }
            });
            
            tbody.appendChild(row);
        });
    }

    renderLinks() {
        const tbody = document.getElementById('linksList');
        tbody.innerHTML = '';

        this.links.forEach(link => {
            const program = this.programs.find(p => p.program_id == link.program_id);
            const outcome = this.outcomes.find(o => o.detail_id == link.outcome_id);
            
            if (!program || !outcome) return;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="fw-bold">${this.escapeHtml(program.program_name)}</div>
                    ${program.program_number ? `<small class="text-muted">${this.escapeHtml(program.program_number)}</small>` : ''}
                </td>
                <td>
                    <small>${this.escapeHtml(program.agency_name || 'Unknown')}</small>
                </td>
                <td>
                    <div class="fw-bold">${this.escapeHtml(outcome.detail_name)}</div>
                </td>
                <td class="text-center">
                    <span class="badge ${outcome.is_cumulative ? 'bg-info' : 'bg-secondary'}">
                        ${outcome.is_cumulative ? 'Cumulative' : 'Standard'}
                    </span>
                </td>
                <td>
                    <small>${this.formatDate(link.created_at)}</small>
                </td>
                <td>
                    <small>${this.escapeHtml(link.created_by_name || 'Unknown')}</small>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="programLinksManager.deleteLink(${link.link_id})"
                            title="Remove Link">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
        });
    }

    populateModalSelects() {
        // Populate program select
        const programSelect = document.getElementById('linkProgramId');
        programSelect.innerHTML = '<option value="">Select a program...</option>';
        
        this.programs.forEach(program => {
            const option = document.createElement('option');
            option.value = program.program_id;
            option.textContent = `${program.program_name} (${program.agency_name || 'Unknown'})`;
            programSelect.appendChild(option);
        });

        // Populate outcome select
        const outcomeSelect = document.getElementById('linkOutcomeId');
        outcomeSelect.innerHTML = '<option value="">Select an outcome...</option>';
        
        this.outcomes.forEach(outcome => {
            const option = document.createElement('option');
            option.value = outcome.detail_id;
            option.textContent = outcome.detail_name;
            outcomeSelect.appendChild(option);
        });
    }

    selectProgram(programId) {
        // Clear previous selection
        document.querySelectorAll('#programsList tr').forEach(row => 
            row.classList.remove('selected-item'));
        
        // Select new program
        const row = document.querySelector(`#programsList tr[data-program-id="${programId}"]`);
        if (row) {
            row.classList.add('selected-item');
            this.selectedProgram = programId;
        }
    }

    selectOutcome(outcomeId) {
        // Clear previous selection
        document.querySelectorAll('#outcomesList tr').forEach(row => 
            row.classList.remove('selected-item'));
        
        // Select new outcome
        const row = document.querySelector(`#outcomesList tr[data-outcome-id="${outcomeId}"]`);
        if (row) {
            row.classList.add('selected-item');
            this.selectedOutcome = outcomeId;
        }
    }

    showCreateLinkModal(programId = null, outcomeId = null) {
        // Pre-select values if provided
        if (programId) {
            document.getElementById('linkProgramId').value = programId;
        }
        if (outcomeId) {
            document.getElementById('linkOutcomeId').value = outcomeId;
        }

        const modal = new bootstrap.Modal(document.getElementById('createLinkModal'));
        modal.show();
    }

    async createLink() {
        const programId = document.getElementById('linkProgramId').value;
        const outcomeId = document.getElementById('linkOutcomeId').value;

        if (!programId || !outcomeId) {
            this.showAlert('Please select both a program and an outcome.', 'warning');
            return;
        }

        // Check if link already exists
        const existingLink = this.links.find(link => 
            link.program_id == programId && link.outcome_id == outcomeId);
        
        if (existingLink) {
            this.showAlert('A link between this program and outcome already exists.', 'warning');
            return;
        }

        try {
            const response = await fetch('/app/api/program_outcome_links.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    program_id: parseInt(programId),
                    outcome_id: parseInt(outcomeId)
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Program-outcome link created successfully!', 'success');
                
                // Close modal and reset form
                bootstrap.Modal.getInstance(document.getElementById('createLinkModal')).hide();
                document.getElementById('createLinkForm').reset();
                
                // Reload data and refresh display
                await this.loadData();
                this.renderAll();
            } else {
                this.showAlert(result.error || 'Failed to create link.', 'danger');
            }
        } catch (error) {
            console.error('Error creating link:', error);
            this.showAlert('An error occurred while creating the link.', 'danger');
        }
    }

    async deleteLink(linkId) {
        if (!confirm('Are you sure you want to remove this program-outcome link?')) {
            return;
        }

        try {
            const response = await fetch('/app/api/program_outcome_links.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    link_id: linkId
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Program-outcome link removed successfully!', 'success');
                
                // Reload data and refresh display
                await this.loadData();
                this.renderAll();
            } else {
                this.showAlert(result.error || 'Failed to remove link.', 'danger');
            }
        } catch (error) {
            console.error('Error removing link:', error);
            this.showAlert('An error occurred while removing the link.', 'danger');
        }
    }

    async viewProgramLinks(programId) {
        const program = this.programs.find(p => p.program_id == programId);
        const programLinks = this.links.filter(link => link.program_id == programId);

        const modal = new bootstrap.Modal(document.getElementById('viewLinksModal'));
        document.getElementById('viewLinksTitle').innerHTML = 
            `<i class="fas fa-tasks me-2"></i>Links for Program: ${this.escapeHtml(program.program_name)}`;

        let content = '';
        if (programLinks.length === 0) {
            content = '<p class="text-muted">No outcome links found for this program.</p>';
        } else {
            content = '<div class="list-group">';
            programLinks.forEach(link => {
                const outcome = this.outcomes.find(o => o.detail_id == link.outcome_id);
                content += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">${this.escapeHtml(outcome.detail_name)}</div>
                            <small class="text-muted">Created: ${this.formatDate(link.created_at)}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="programLinksManager.deleteLink(${link.link_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });
            content += '</div>';
        }

        document.getElementById('viewLinksContent').innerHTML = content;
        modal.show();
    }

    async viewOutcomeLinks(outcomeId) {
        const outcome = this.outcomes.find(o => o.detail_id == outcomeId);
        const outcomeLinks = this.links.filter(link => link.outcome_id == outcomeId);

        const modal = new bootstrap.Modal(document.getElementById('viewLinksModal'));
        document.getElementById('viewLinksTitle').innerHTML = 
            `<i class="fas fa-chart-bar me-2"></i>Links for Outcome: ${this.escapeHtml(outcome.detail_name)}`;

        let content = '';
        if (outcomeLinks.length === 0) {
            content = '<p class="text-muted">No program links found for this outcome.</p>';
        } else {
            content = '<div class="list-group">';
            outcomeLinks.forEach(link => {
                const program = this.programs.find(p => p.program_id == link.program_id);
                content += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">${this.escapeHtml(program.program_name)}</div>
                            <small class="text-muted">${this.escapeHtml(program.agency_name || 'Unknown')} | Created: ${this.formatDate(link.created_at)}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="programLinksManager.deleteLink(${link.link_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });
            content += '</div>';
        }

        document.getElementById('viewLinksContent').innerHTML = content;
        modal.show();
    }

    showAlert(message, type) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert at top of container
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }
}

// Utility function for debouncing
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.programLinksManager = new ProgramOutcomeLinksManager();
});
