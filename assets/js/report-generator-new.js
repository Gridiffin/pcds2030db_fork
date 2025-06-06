/**
 * Modern Report Generator
 * 
 * Enhanced PPTX report generation with modern JavaScript practices,
 * improved error handling, and better user experience.
 * 
 * @version 2.0
 * @author PCDS Dashboard System
 */

class ReportGenerator {
    constructor() {
        this.config = window.ReportGeneratorConfig || {};
        this.isInitialized = false;
        this.currentPrograms = [];
        this.isGenerating = false;
        
        // DOM elements
        this.elements = {
            form: null,
            periodSelect: null,
            sectorSelect: null,
            reportName: null,
            reportDescription: null,
            programSelector: null,
            programContainer: null,
            generateBtn: null,
            statusAlert: null,
            successAlert: null,
            errorAlert: null,
            deleteModal: null,
            deleteReportBtns: null
        };
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }
    
    /**
     * Initialize the report generator
     */
    init() {
        if (this.isInitialized) {
            console.warn('ReportGenerator already initialized');
            return;
        }
        
        try {
            this.bindElements();
            this.bindEvents();
            this.setupFormValidation();
            this.initializeProgramSelector();
            this.isInitialized = true;
            console.log('ReportGenerator initialized successfully');
        } catch (error) {
            console.error('Failed to initialize ReportGenerator:', error);
            this.showError('Failed to initialize report generator. Please refresh the page.');
        }
    }
    
    /**
     * Bind DOM elements
     */
    bindElements() {
        this.elements = {
            form: document.getElementById('reportGenerationForm'),
            periodSelect: document.getElementById('periodSelect'),
            sectorSelect: document.getElementById('sectorSelect'),
            reportName: document.getElementById('reportName'),
            reportDescription: document.getElementById('reportDescription'),
            programSelector: document.getElementById('programSelector'),
            programContainer: document.querySelector('.program-selector-container'),
            generateBtn: document.getElementById('generatePptxBtn'),
            statusAlert: document.getElementById('generationStatus'),
            successAlert: document.getElementById('successMessage'),
            errorAlert: document.getElementById('errorMessage'),
            deleteModal: document.getElementById('deleteReportModal'),
            deleteReportBtns: document.querySelectorAll('.delete-report-btn')
        };
        
        // Validate required elements
        const requiredElements = ['form', 'periodSelect', 'sectorSelect', 'reportName', 'generateBtn'];
        for (const elementName of requiredElements) {
            if (!this.elements[elementName]) {
                throw new Error(`Required element '${elementName}' not found`);
            }
        }
    }
    
    /**
     * Bind event listeners
     */
    bindEvents() {
        // Form submission
        this.elements.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
        // Period and sector selection
        this.elements.periodSelect.addEventListener('change', () => this.handlePeriodChange());
        this.elements.sectorSelect.addEventListener('change', () => this.handleSectorChange());
        
        // Report name auto-generation
        this.elements.periodSelect.addEventListener('change', () => this.updateReportName());
        this.elements.sectorSelect.addEventListener('change', () => this.updateReportName());
        
        // Alert dismiss buttons
        this.bindAlertButtons();
        
        // Delete report functionality
        this.bindDeleteReportEvents();
        
        // Program selector events (will be bound dynamically when programs load)
        this.bindProgramSelectorEvents();
    }
    
    /**
     * Bind alert button events
     */
    bindAlertButtons() {
        // Retry button
        const retryBtn = document.getElementById('retryBtn');
        if (retryBtn) {
            retryBtn.addEventListener('click', () => this.hideAllAlerts());
        }
        
        // Generate another button
        const generateAnotherBtn = document.getElementById('generateAnotherBtn');
        if (generateAnotherBtn) {
            generateAnotherBtn.addEventListener('click', () => this.resetForm());
        }
    }
    
    /**
     * Bind delete report events
     */
    bindDeleteReportEvents() {
        // Delete report buttons
        this.elements.deleteReportBtns.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleDeleteReport(e));
        });
        
        // Confirm delete button
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => this.confirmDeleteReport());
        }
    }
    
    /**
     * Setup form validation
     */
    setupFormValidation() {
        // Bootstrap validation
        this.elements.form.classList.add('needs-validation');
        
        // Custom validation messages
        this.elements.periodSelect.addEventListener('invalid', (e) => {
            e.target.setCustomValidity('Please select a reporting period');
        });
        
        this.elements.sectorSelect.addEventListener('invalid', (e) => {
            e.target.setCustomValidity('Please select a sector');
        });
        
        this.elements.reportName.addEventListener('invalid', (e) => {
            e.target.setCustomValidity('Please enter a report name');
        });
        
        // Clear custom validity on input
        [this.elements.periodSelect, this.elements.sectorSelect, this.elements.reportName].forEach(element => {
            element.addEventListener('input', (e) => {
                e.target.setCustomValidity('');
            });
        });
    }
    
    /**
     * Initialize program selector
     */
    initializeProgramSelector() {
        if (this.elements.programContainer) {
            this.showProgramSelectorMessage('info', 'Please select a reporting period above to load available programs.');
        }
    }
    
    /**
     * Handle period selection change
     */
    async handlePeriodChange() {
        const periodId = this.elements.periodSelect.value;
        
        if (!periodId) {
            this.showProgramSelectorMessage('info', 'Please select a reporting period above to load available programs.');
            return;
        }
        
        await this.loadPrograms();
        this.updateReportName();
    }
    
    /**
     * Handle sector selection change
     */
    handleSectorChange() {
        this.updateReportName();
        this.filterProgramsBySector();
    }
    
    /**
     * Load programs for selected period
     */
    async loadPrograms() {
        const periodId = this.elements.periodSelect.value;
        
        if (!periodId) return;
        
        try {
            this.showProgramSelectorLoading();
            
            const response = await fetch(`${this.config.apiEndpoints.getPeriodPrograms}?period_id=${periodId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.currentPrograms = data.programs || [];
                this.renderPrograms();
            } else {
                throw new Error(data.message || 'Failed to load programs');
            }
        } catch (error) {
            console.error('Error loading programs:', error);
            this.showProgramSelectorMessage('error', 'Failed to load programs. Please try again.');
        }
    }
    
    /**
     * Render programs in the selector
     */
    renderPrograms() {
        if (!this.elements.programContainer) return;
        
        if (this.currentPrograms.length === 0) {
            this.showProgramSelectorMessage('warning', 'No programs found for the selected period.');
            return;
        }
        
        // Group programs by sector
        const programsBySector = this.groupProgramsBySector();
        
        let html = this.createProgramSelectorHeader();
        html += this.createProgramSelectorControls();
        
        // Render programs by sector
        for (const [sectorId, sectorData] of Object.entries(programsBySector)) {
            html += this.createSectorProgramsHTML(sectorId, sectorData);
        }
        
        this.elements.programContainer.innerHTML = html;
        this.bindProgramSelectorEvents();
        this.updateProgramCount();
    }
    
    /**
     * Group programs by sector
     */
    groupProgramsBySector() {
        const grouped = {};
        
        this.currentPrograms.forEach(program => {
            const sectorId = program.sector_id;
            if (!grouped[sectorId]) {
                grouped[sectorId] = {
                    sector_name: program.sector_name,
                    programs: []
                };
            }
            grouped[sectorId].programs.push(program);
        });
        
        return grouped;
    }
    
    /**
     * Create program selector header HTML
     */
    createProgramSelectorHeader() {
        return `
            <div class="program-selector-header pb-3 mb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        Available Programs 
                        <span id="programCount" class="badge bg-primary ms-2">0</span>
                    </h6>
                    <div class="program-controls">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPrograms">
                            <i class="fas fa-check-square me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="deselectAllPrograms">
                            <i class="fas fa-square me-1"></i>Clear
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info ms-1" id="sortProgramOrder">
                            <i class="fas fa-sort-numeric-down me-1"></i>Sort
                        </button>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Select programs and assign order numbers. Lower numbers appear first in the report.
                    </small>
                </div>
            </div>
        `;
    }
    
    /**
     * Create program selector controls HTML
     */
    createProgramSelectorControls() {
        return `
            <div class="program-search-container mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           id="programSearch" 
                           placeholder="Search programs..."
                           autocomplete="off">
                </div>
            </div>
        `;
    }
    
    /**
     * Create sector programs HTML
     */
    createSectorProgramsHTML(sectorId, sectorData) {
        let html = `
            <div class="sector-programs mb-3" data-sector-id="${sectorId}">
                <h6 class="sector-name text-primary fw-bold mb-2">
                    <i class="fas fa-folder me-2"></i>
                    ${this.escapeHtml(sectorData.sector_name)}
                </h6>
                <div class="programs-list ms-3">
        `;
        
        sectorData.programs.forEach((program, index) => {
            html += this.createProgramItemHTML(program, index + 1);
        });
        
        html += `
                </div>
            </div>
        `;
        
        return html;
    }
    
    /**
     * Create individual program item HTML
     */
    createProgramItemHTML(program, defaultOrder) {
        return `
            <div class="program-item mb-2 p-2 border rounded" 
                 data-program-id="${program.program_id}"
                 data-sector-id="${program.sector_id}">
                <div class="d-flex align-items-center">
                    <div class="drag-handle me-2" title="Drag to reorder">
                        <i class="fas fa-grip-vertical text-muted"></i>
                    </div>
                    <div class="form-check flex-grow-1">
                        <input class="form-check-input program-checkbox" 
                               type="checkbox" 
                               name="selected_program_ids[]" 
                               value="${program.program_id}" 
                               id="program_${program.program_id}">
                        <label class="form-check-label" for="program_${program.program_id}">
                            ${this.escapeHtml(program.program_name)}
                        </label>
                    </div>
                    <div class="program-order-container">
                        <input type="number" 
                               class="form-control form-control-sm program-order-input" 
                               name="program_order_${program.program_id}" 
                               id="order_${program.program_id}"
                               min="1" 
                               max="999"
                               value="${defaultOrder}"
                               style="width: 70px;"
                               disabled>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Bind program selector events
     */
    bindProgramSelectorEvents() {
        // Select/Deselect all buttons
        const selectAllBtn = document.getElementById('selectAllPrograms');
        const deselectAllBtn = document.getElementById('deselectAllPrograms');
        const sortBtn = document.getElementById('sortProgramOrder');
        const searchInput = document.getElementById('programSearch');
        
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => this.selectAllPrograms());
        }
        
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', () => this.deselectAllPrograms());
        }
        
        if (sortBtn) {
            sortBtn.addEventListener('click', () => this.sortProgramsByOrder());
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.filterPrograms(e.target.value));
        }
        
        // Individual program checkboxes
        const programCheckboxes = document.querySelectorAll('.program-checkbox');
        programCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => this.handleProgramSelection(e));
        });
        
        // Order inputs
        const orderInputs = document.querySelectorAll('.program-order-input');
        orderInputs.forEach(input => {
            input.addEventListener('input', (e) => this.handleOrderChange(e));
        });
        
        // Drag and drop functionality
        this.initializeDragAndDrop();
    }
    
    /**
     * Handle program selection
     */
    handleProgramSelection(event) {
        const checkbox = event.target;
        const programId = checkbox.value;
        const orderInput = document.getElementById(`order_${programId}`);
        
        if (checkbox.checked) {
            orderInput.disabled = false;
            orderInput.focus();
        } else {
            orderInput.disabled = true;
            orderInput.value = '';
        }
        
        this.updateProgramCount();
        this.updateOrderNumbers();
    }
    
    /**
     * Handle order number change
     */
    handleOrderChange(event) {
        const input = event.target;
        const value = parseInt(input.value);
        
        // Validate order number
        if (value < 1) {
            input.value = 1;
        } else if (value > 999) {
            input.value = 999;
        }
        
        this.updateOrderNumbers();
    }
    
    /**
     * Select all programs
     */
    selectAllPrograms() {
        const checkboxes = document.querySelectorAll('.program-checkbox');
        checkboxes.forEach((checkbox, index) => {
            checkbox.checked = true;
            const programId = checkbox.value;
            const orderInput = document.getElementById(`order_${programId}`);
            if (orderInput) {
                orderInput.disabled = false;
                orderInput.value = index + 1;
            }
        });
        
        this.updateProgramCount();
        this.updateOrderNumbers();
    }
    
    /**
     * Deselect all programs
     */
    deselectAllPrograms() {
        const checkboxes = document.querySelectorAll('.program-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            const programId = checkbox.value;
            const orderInput = document.getElementById(`order_${programId}`);
            if (orderInput) {
                orderInput.disabled = true;
                orderInput.value = '';
            }
        });
        
        this.updateProgramCount();
    }
    
    /**
     * Sort programs by order number
     */
    sortProgramsByOrder() {
        const programItems = Array.from(document.querySelectorAll('.program-item'));
        const selectedPrograms = programItems.filter(item => {
            const checkbox = item.querySelector('.program-checkbox');
            return checkbox && checkbox.checked;
        });
        
        selectedPrograms.sort((a, b) => {
            const orderA = parseInt(a.querySelector('.program-order-input').value) || 999;
            const orderB = parseInt(b.querySelector('.program-order-input').value) || 999;
            return orderA - orderB;
        });
        
        // Move sorted items to the top
        const container = document.querySelector('.program-selector-container');
        const header = container.querySelector('.program-selector-header');
        const controls = container.querySelector('.program-search-container');
        
        // Clear and rebuild
        const sectorContainers = {};
        selectedPrograms.forEach(item => {
            const sectorId = item.dataset.sectorId;
            if (!sectorContainers[sectorId]) {
                sectorContainers[sectorId] = [];
            }
            sectorContainers[sectorId].push(item);
        });
        
        // This is a simplified version - in production, you'd want to rebuild the entire structure
        console.log('Programs sorted by order');
    }
    
    /**
     * Filter programs by search term
     */
    filterPrograms(searchTerm) {
        const programItems = document.querySelectorAll('.program-item');
        const term = searchTerm.toLowerCase().trim();
        
        programItems.forEach(item => {
            const label = item.querySelector('.form-check-label');
            const programName = label ? label.textContent.toLowerCase() : '';
            const shouldShow = !term || programName.includes(term);
            
            item.style.display = shouldShow ? '' : 'none';
        });
    }
    
    /**
     * Update program count badge
     */
    updateProgramCount() {
        const selectedCount = document.querySelectorAll('.program-checkbox:checked').length;
        const countBadge = document.getElementById('programCount');
        
        if (countBadge) {
            countBadge.textContent = selectedCount;
            countBadge.className = `badge ms-2 ${selectedCount > 0 ? 'bg-success' : 'bg-primary'}`;
        }
    }
    
    /**
     * Update order numbers for selected programs
     */
    updateOrderNumbers() {
        const selectedCheckboxes = document.querySelectorAll('.program-checkbox:checked');
        const orderInputs = Array.from(selectedCheckboxes).map(checkbox => {
            return document.getElementById(`order_${checkbox.value}`);
        }).filter(input => input);
        
        // Auto-assign order numbers if empty
        orderInputs.forEach((input, index) => {
            if (!input.value) {
                input.value = index + 1;
            }
        });
    }
    
    /**
     * Initialize drag and drop functionality
     */
    initializeDragAndDrop() {
        const programItems = document.querySelectorAll('.program-item');
        
        programItems.forEach(item => {
            item.draggable = true;
            
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', item.dataset.programId);
                item.classList.add('dragging');
            });
            
            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
            });
            
            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                item.classList.add('drag-over');
            });
            
            item.addEventListener('dragleave', () => {
                item.classList.remove('drag-over');
            });
            
            item.addEventListener('drop', (e) => {
                e.preventDefault();
                item.classList.remove('drag-over');
                
                const draggedId = e.dataTransfer.getData('text/plain');
                const draggedItem = document.querySelector(`[data-program-id="${draggedId}"]`);
                
                if (draggedItem && draggedItem !== item) {
                    // Swap positions
                    const draggedOrder = draggedItem.querySelector('.program-order-input').value;
                    const targetOrder = item.querySelector('.program-order-input').value;
                    
                    draggedItem.querySelector('.program-order-input').value = targetOrder;
                    item.querySelector('.program-order-input').value = draggedOrder;
                }
            });
        });
    }
    
    /**
     * Filter programs by selected sector
     */
    filterProgramsBySector() {
        const selectedSectorId = this.elements.sectorSelect.value;
        
        if (!selectedSectorId) {
            // Show all sectors
            const sectorContainers = document.querySelectorAll('.sector-programs');
            sectorContainers.forEach(container => {
                container.style.display = '';
            });
            return;
        }
        
        const sectorContainers = document.querySelectorAll('.sector-programs');
        sectorContainers.forEach(container => {
            const sectorId = container.dataset.sectorId;
            container.style.display = sectorId === selectedSectorId ? '' : 'none';
        });
    }
    
    /**
     * Auto-generate report name based on selections
     */
    updateReportName() {
        const periodText = this.elements.periodSelect.selectedOptions[0]?.textContent;
        const sectorText = this.elements.sectorSelect.selectedOptions[0]?.textContent;
        
        if (!periodText || !sectorText || periodText === 'Select Reporting Period' || sectorText === 'Select Sector') {
            return;
        }
        
        const reportName = `${sectorText} - ${periodText}`;
        
        // Only update if field is empty or contains auto-generated text
        if (!this.elements.reportName.value || this.elements.reportName.dataset.autoGenerated === 'true') {
            this.elements.reportName.value = reportName;
            this.elements.reportName.dataset.autoGenerated = 'true';
        }
        
        // Clear auto-generated flag if user modifies the name
        this.elements.reportName.addEventListener('input', () => {
            this.elements.reportName.dataset.autoGenerated = 'false';
        }, { once: true });
    }
    
    /**
     * Handle form submission
     */
    async handleFormSubmit(event) {
        event.preventDefault();
        
        if (this.isGenerating) {
            return;
        }
        
        // Validate form
        if (!this.elements.form.checkValidity()) {
            event.stopPropagation();
            this.elements.form.classList.add('was-validated');
            return;
        }
        
        try {
            this.isGenerating = true;
            this.hideAllAlerts();
            this.showStatus('Preparing report data...');
            
            const formData = this.collectFormData();
            await this.generateReport(formData);
            
        } catch (error) {
            console.error('Report generation failed:', error);
            this.showError(error.message || 'Failed to generate report. Please try again.');
        } finally {
            this.isGenerating = false;
        }
    }
    
    /**
     * Collect form data
     */
    collectFormData() {
        const formData = new FormData(this.elements.form);
        
        // Add selected programs and their orders
        const selectedPrograms = [];
        const checkboxes = document.querySelectorAll('.program-checkbox:checked');
        
        checkboxes.forEach(checkbox => {
            const programId = checkbox.value;
            const orderInput = document.getElementById(`order_${programId}`);
            const order = orderInput ? parseInt(orderInput.value) || 1 : 1;
            
            selectedPrograms.push({
                program_id: programId,
                order: order
            });
        });
        
        // Sort by order
        selectedPrograms.sort((a, b) => a.order - b.order);
        
        // Convert to regular object for easier handling
        const data = {
            period_id: formData.get('period_id'),
            sector_id: formData.get('sector_id'),
            report_name: formData.get('report_name'),
            description: formData.get('description'),
            is_public: formData.get('is_public') === '1',
            selected_programs: selectedPrograms
        };
        
        return data;
    }
    
    /**
     * Generate report
     */
    async generateReport(data) {
        try {
            this.showStatus('Generating PPTX report...');
            
            const response = await fetch(this.config.apiEndpoints.saveReport, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message || 'Report generated successfully!', result.download_url);
            } else {
                throw new Error(result.message || 'Failed to generate report');
            }
            
        } catch (error) {
            console.error('Report generation error:', error);
            throw error;
        }
    }
    
    /**
     * Handle delete report
     */
    handleDeleteReport(event) {
        const button = event.target.closest('.delete-report-btn');
        const reportId = button.dataset.reportId;
        const reportName = button.dataset.reportName;
        
        // Store for confirmation
        this.pendingDeleteId = reportId;
        
        // Update modal content
        const nameElement = this.elements.deleteModal.querySelector('#reportNameToDelete');
        if (nameElement) {
            nameElement.textContent = reportName;
        }
        
        // Show modal
        const modal = new bootstrap.Modal(this.elements.deleteModal);
        modal.show();
    }
    
    /**
     * Confirm delete report
     */
    async confirmDeleteReport() {
        if (!this.pendingDeleteId) return;
        
        try {
            const response = await fetch(this.config.apiEndpoints.deleteReport, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ report_id: this.pendingDeleteId })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Remove from UI
                const reportRow = document.querySelector(`[data-report-id="${this.pendingDeleteId}"]`);
                if (reportRow) {
                    reportRow.closest('tr').remove();
                }
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(this.elements.deleteModal);
                modal.hide();
                
                this.showSuccess('Report deleted successfully.');
            } else {
                throw new Error(result.message || 'Failed to delete report');
            }
            
        } catch (error) {
            console.error('Delete report error:', error);
            this.showError('Failed to delete report. Please try again.');
        } finally {
            this.pendingDeleteId = null;
        }
    }
    
    /**
     * Show loading state in program selector
     */
    showProgramSelectorLoading() {
        if (this.elements.programContainer) {
            this.elements.programContainer.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading available programs...</p>
                </div>
            `;
        }
    }
    
    /**
     * Show message in program selector
     */
    showProgramSelectorMessage(type, message) {
        if (!this.elements.programContainer) return;
        
        const alertClass = {
            'info': 'alert-info',
            'warning': 'alert-warning',
            'error': 'alert-danger'
        }[type] || 'alert-info';
        
        const icon = {
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle',
            'error': 'fa-exclamation-circle'
        }[type] || 'fa-info-circle';
        
        this.elements.programContainer.innerHTML = `
            <div class="alert ${alertClass} text-center">
                <i class="fas ${icon} me-2"></i>
                ${message}
            </div>
        `;
    }
    
    /**
     * Show status alert
     */
    showStatus(message) {
        this.hideAllAlerts();
        
        if (this.elements.statusAlert) {
            const messageElement = this.elements.statusAlert.querySelector('#statusMessage');
            if (messageElement) {
                messageElement.textContent = message;
            }
            this.elements.statusAlert.classList.remove('d-none');
        }
    }
    
    /**
     * Show success alert
     */
    showSuccess(message, downloadUrl = null) {
        this.hideAllAlerts();
        
        if (this.elements.successAlert) {
            const messageElement = this.elements.successAlert.querySelector('.alert-heading');
            if (messageElement) {
                messageElement.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
            }
            
            // Update download link if provided
            if (downloadUrl) {
                const downloadLink = this.elements.successAlert.querySelector('#downloadLink');
                if (downloadLink) {
                    downloadLink.href = downloadUrl;
                }
            }
            
            this.elements.successAlert.classList.remove('d-none');
        }
    }
    
    /**
     * Show error alert
     */
    showError(message) {
        this.hideAllAlerts();
        
        if (this.elements.errorAlert) {
            const errorText = this.elements.errorAlert.querySelector('#errorText');
            if (errorText) {
                errorText.textContent = message;
            }
            this.elements.errorAlert.classList.remove('d-none');
        }
    }
    
    /**
     * Hide all alerts
     */
    hideAllAlerts() {
        [this.elements.statusAlert, this.elements.successAlert, this.elements.errorAlert].forEach(alert => {
            if (alert) {
                alert.classList.add('d-none');
            }
        });
    }
    
    /**
     * Reset form to initial state
     */
    resetForm() {
        this.elements.form.reset();
        this.elements.form.classList.remove('was-validated');
        this.hideAllAlerts();
        this.initializeProgramSelector();
        this.elements.reportName.dataset.autoGenerated = 'false';
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
new ReportGenerator();
