/**
 * Report Generator
 * 
 * Main controller for the PPTX report generation functionality.
 * This file coordinates the modules and initializes the report generator.
 * 
 * Update 2025-06-18: Modified the backend query to exclude draft programs from selection.
 * Now only finalized (non-draft) programs will be available for report generation.
 */

// Global initialization flag to prevent duplicate initialization
if (typeof reportGeneratorInitialized === 'undefined') {
    var reportGeneratorInitialized = false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations
    if (reportGeneratorInitialized) {
        console.log('Report generator already initialized, skipping duplicate initialization.');
        // The program container element might not be defined here yet if this is the first run
        // and the DOMContentLoaded listener is firing a second time for some reason.
        // It's safer to re-query it or ensure it's defined before this block.
        const existingProgramContainer = document.getElementById('programSelector')?.querySelector('.program-selector-container');
        if (existingProgramContainer) {
            existingProgramContainer.innerHTML = `
                <div class="alert alert-info border-primary">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Getting Started:</strong> Please select a reporting period above to load available programs for selection.
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            Tip: Programs are filtered by the selected reporting period to show only relevant submissions.
                        </small>
                    </div>
                </div>
            `;
        }
        return;
    }
    reportGeneratorInitialized = true;
    console.log('Initializing report generator...');    // Moved declarations up
    const programSelector = document.getElementById('programSelector');
    const periodSelect = document.getElementById('periodSelect');
    const sectorSelect = document.getElementById('sectorSelect');
    const agencySelect = document.getElementById('agencySelect');
    const clearAgencyFilterBtn = document.getElementById('resetAgencyFilter');
    const programContainerElement = programSelector ? programSelector.querySelector('.program-selector-container') : null;

    // Show default state in program selector if it exists and no period is selected
    // This check should happen after programContainerElement is defined.
    if (programContainerElement && (!periodSelect || !periodSelect.value)) {
        programContainerElement.innerHTML = `
            <div class="alert alert-info border-primary">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Getting Started:</strong> Please select a reporting period above to load available programs for selection.
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb me-1"></i>
                        Tip: Programs are filtered by the selected reporting period to show only relevant submissions.
                    </small>
                </div>
            </div>
        `;
    }
    
    // Add loading indicator for program selector
    function showProgramsLoading() {
        if (programContainerElement) {
            programContainerElement.innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading programs...</span>
                    </div>
                    <p class="mt-2">Loading available programs...</p>
                </div>
            `;
        }
    }    // Global state to track program selections across agency changes
    let globalProgramSelections = new Map(); // Map of program_id -> {selected: bool, order: number, agency: string, program_name: string}
    let nextOrderNumber = 1;
    
    // Expose global state to window for access by other modules
    window.globalProgramSelections = globalProgramSelections;
    
    // Debug function to log current global state
    function debugGlobalState() {
        console.log('=== DEBUG: Global Program Selections ===');
        console.log('Total entries in globalProgramSelections:', globalProgramSelections.size);
        
        const selectedPrograms = [];
        const allPrograms = [];
        
        globalProgramSelections.forEach((data, programId) => {
            allPrograms.push({
                programId,
                selected: data.selected,
                order: data.order,
                agency: data.agency,
                program_name: data.program_name
            });
            
            if (data.selected) {
                selectedPrograms.push({
                    programId,
                    order: data.order,
                    agency: data.agency,
                    program_name: data.program_name
                });
            }
        });
        
        console.log('All programs in global state:', allPrograms);
        console.log('Selected programs:', selectedPrograms);
        console.log('Selected programs count:', selectedPrograms.length);
        console.log('==========================================');
        
        return selectedPrograms;
    }
    
    // Expose debug function to window for manual testing
    window.debugGlobalState = debugGlobalState;
    
    // Load programs based on selected period and sector
    function loadPrograms() {
        const periodId = periodSelect.value;
        const sectorId = sectorSelect.value;
        let agencyIds = [];
        if (agencySelect) {
            agencyIds = Array.from(agencySelect.selectedOptions).map(opt => opt.value).filter(Boolean);
        }
        
        // Need at least a period to load programs
        if (!periodId) {
            if (programContainerElement) {
                programContainerElement.innerHTML = `
                    <div class="alert alert-info border-primary">
                        <strong>Getting Started:</strong> Please select a reporting period above to load available programs for selection.
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Tip: Programs are filtered by the selected reporting period to show only relevant submissions.
                            </small>
                        </div>
                    </div>
                `;
            }
            return;
        }
        
        // Show loading indicator
        showProgramsLoading();
        
        // Build URL - always load all programs for the period, then filter on frontend
        let url = `${APP_URL}/app/api/get_period_programs.php?period_id=${periodId}${sectorId ? '&sector_id=' + sectorId : ''}`;
        // Don't filter by agency in API call - we'll handle filtering on frontend
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success || !data.programs) {
                    throw new Error('Invalid response from server');
                }
                
                // Store all available programs
                window.allAvailablePrograms = data.programs;
                
                // Render programs with filtering
                renderProgramsWithFiltering(data.programs, sectorId, agencyIds);
            })
            .catch(error => {
                console.error('Error loading programs:', error);
                
                if (programContainerElement) {
                    programContainerElement.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading programs: ${error.message}
                        </div>
                    `;
                }
            });
    }
      // Render programs with multi-agency filtering support
    function renderProgramsWithFiltering(allPrograms, selectedSectorId, selectedAgencyIds) {
        let html = '';
        let hasVisiblePrograms = false;
        
        for (const sector in allPrograms) {
            if (selectedSectorId && sector !== selectedSectorId) continue;
            
            // Group programs by agency
            const agencyGroups = {};
            allPrograms[sector].programs.forEach(program => {
                const agencyId = program.owner_agency_id;
                const agencyName = program.agency_name || 'Unknown Agency';
                
                // Filter by selected agencies if any are selected
                if (selectedAgencyIds.length > 0 && !selectedAgencyIds.includes(agencyId.toString())) {
                    return; // Skip this program
                }
                
                if (!agencyGroups[agencyName]) {
                    agencyGroups[agencyName] = [];
                }
                agencyGroups[agencyName].push(program);
            });
            
            // Only show sector if it has visible programs
            if (Object.keys(agencyGroups).length === 0) continue;
            
            hasVisiblePrograms = true;
            html += `
            <div class="sector-programs mb-3" data-sector-id="${sector}">
                <h6 class="sector-name fw-bold ms-2 mb-2">${allPrograms[sector].sector_name}</h6>
            `;
            
            // Render each agency group
            for (const agencyName in agencyGroups) {
                html += `<div class="agency-group mb-2">
                    <div class="fw-semibold text-primary ms-3 d-flex justify-content-between align-items-center">
                        <span>${agencyName}</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-success btn-sm select-agency-programs" 
                                    data-agency-name="${agencyName}" title="Select all programs from ${agencyName}">
                                <i class="fas fa-check-square me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm deselect-agency-programs" 
                                    data-agency-name="${agencyName}" title="Deselect all programs from ${agencyName}">
                                <i class="fas fa-square me-1"></i>Deselect All
                            </button>
                        </div>
                    </div>`;
                
                agencyGroups[agencyName].forEach(program => {
                    const programId = program.program_id;
                    const isSelected = globalProgramSelections.has(programId) && globalProgramSelections.get(programId).selected;
                    const orderValue = isSelected ? globalProgramSelections.get(programId).order : '';
                    
                    html += `
                        <div class="program-checkbox-container ms-4" data-program-id="${programId}" data-agency-name="${agencyName}">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input program-checkbox" 
                                    id="program_${programId}" 
                                    name="selected_program_ids[]" 
                                    value="${programId}"
                                    ${isSelected ? 'checked' : ''}>
                                <label class="form-check-label" for="program_${programId}">
                                    ${program.program_name}
                                </label>
                            </div>
                            <div class="program-order-badge ${isSelected ? 'active' : ''}" title="Click to edit order">
                                ${isSelected ? orderValue : '#'}
                            </div>
                            <input type="number" min="1" class="program-order-input" 
                                name="program_order_${programId}" 
                                id="order_${programId}" 
                                value="${orderValue}"
                                style="display: ${isSelected ? 'inline-block' : 'none'};"
                                aria-label="Program display order">
                        </div>
                    `;
                });
                html += `</div>`;
            }
            
            html += `</div>`;
        }
        
        // Show message if no programs are visible due to filtering
        if (!hasVisiblePrograms) {
            html = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No programs available for the selected criteria. Try selecting different agencies or check if programs exist for this period.
                </div>
            `;
        }
        
        // Add summary of currently selected programs
        if (globalProgramSelections.size > 0) {
            const selectedCount = Array.from(globalProgramSelections.values()).filter(p => p.selected).length;
            html = `
                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>${selectedCount} programs selected for report</strong>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-success btn-sm" id="viewSelectedPrograms">
                                <i class="fas fa-list me-1"></i>View Selected
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="clearAllSelections">
                                <i class="fas fa-times me-1"></i>Clear All
                            </button>
                        </div>
                    </div>
                </div>
            ` + html;
        }
        
        if (programSelector) {
            const programContainerElement = programSelector.querySelector('.program-selector-container');
            if (programContainerElement) {
                programContainerElement.innerHTML = html;
                // Initialize event listeners after HTML is inserted
                initializeSelectButtons();
                initializeAgencySelectButtons();
            }
        }
    }
    
    // Render programs in the UI (legacy function - keeping for compatibility)
    function renderPrograms(programs, selectedSectorId) {
        // Convert to new format and call new function
        renderProgramsWithFiltering(programs, selectedSectorId, []);
    }
    
    function updateProgramOrder() {
        // This function will be called when program order changes
        updateOrderNumbers();
    }
    // Filter programs by sector
    function filterProgramsBySector(selectedSectorId) {
        const sectorPrograms = programSelector.querySelectorAll('.sector-programs');
        
        // If no programs are loaded yet, show appropriate message
        if (sectorPrograms.length === 0) {
            if (programContainerElement) {
                programContainerElement.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Please select a reporting period first to load programs for this sector.
                    </div>
                `;
            }
            return;
        }
        
        // Show all sectors' programs initially (if none selected)
        if (!selectedSectorId) {
            sectorPrograms.forEach(sector => {
                sector.style.display = 'block';
            });
            return;
        }
        
        // Otherwise, only show programs from the selected sector
        sectorPrograms.forEach(sector => {
            const sectorId = sector.getAttribute('data-sector-id');
            sector.style.display = sectorId === selectedSectorId ? 'block' : 'none';
            
            // Uncheck programs from other sectors when filtering
            if (sectorId !== selectedSectorId) {
                const checkboxes = sector.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            }
        });
        
        // Update the count after filtering
        updateProgramCount();
        
        // Display message if no visible programs for selected sector
        const visiblePrograms = programSelector.querySelectorAll(`.sector-programs[data-sector-id="${selectedSectorId}"]`);
        if (visiblePrograms.length === 0) {
            if (programContainerElement) {
                const existingContent = programContainerElement.innerHTML;
                programContainerElement.innerHTML = `
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No programs available for the selected sector. Please select a different sector.
                    </div>
                ` + existingContent;
            }
        }
    }
    
    // Initialize the UI
    if (typeof ReportUI !== 'undefined') {
        ReportUI.initUI();
        
        // Initialize Select All / Deselect All buttons
        function initializeSelectButtons() {
            const selectAllBtn = document.getElementById('selectAllPrograms');
            const deselectAllBtn = document.getElementById('deselectAllPrograms');
            const sortProgramOrderBtn = document.getElementById('sortProgramOrder');
            
            if (selectAllBtn && deselectAllBtn) {
                // Select all visible programs
                selectAllBtn.addEventListener('click', function() {
                    const visibleSectors = document.querySelectorAll('.sector-programs:not([style*="display: none"])');
                    let index = 1;
                    
                    // First pass: check all boxes
                    visibleSectors.forEach(sector => {
                        const checkboxes = sector.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = true;
                            // Show order inputs
                            const programId = checkbox.value;
                            const orderInput = document.getElementById(`order_${programId}`);
                            if (orderInput) {
                                orderInput.style.display = 'inline-block';
                                // Assign sequential numbers
                                if (!orderInput.value) {
                                    orderInput.value = index++;
                                }
                            }
                        });
                    });
                    
                    updateProgramCount();
                    updateOrderNumbers(); // Ensure order numbers are valid
                });
                
                // Deselect all visible programs
                deselectAllBtn.addEventListener('click', function() {
                    const visibleSectors = document.querySelectorAll('.sector-programs:not([style*="display: none"])');
                    visibleSectors.forEach(sector => {
                        const checkboxes = sector.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = false;
                            // Hide order inputs
                            const programId = checkbox.value;
                            const orderInput = document.getElementById(`order_${programId}`);
                            if (orderInput) {
                                orderInput.style.display = 'none';
                                orderInput.value = '';
                            }
                        });
                    });
                    
                    updateProgramCount();
                });
            }            // Add change event listeners to all program checkboxes
            const programCheckboxes = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]');
            programCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateProgramCount();
                    toggleOrderInput(this);
                });
            });
            
            // Add change event listeners to all order inputs
            const orderInputs = document.querySelectorAll('#programSelector .program-order-input');
            orderInputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateOrderBadges();
                });
                input.addEventListener('change', function() {
                    updateOrderNumbers();
                });
            });
                
                // Add sort functionality
                if (sortProgramOrderBtn) {
                    sortProgramOrderBtn.addEventListener('click', function() {
                        sortProgramsByOrder();
                    });
                }
            }
              // Function to sort programs numerically by their order values
            function sortProgramsByOrder() {
                // Get all visible and checked program checkboxes
                const checkedPrograms = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]:checked');
                if (checkedPrograms.length < 2) {
                    // Show a message if less than 2 programs are selected
                    const programContainer = document.querySelector('.program-selector-container');
                    const existingNotice = document.querySelector('.sort-notice');
                    if (existingNotice) {
                        existingNotice.remove();
                    }
                    
                    const notice = document.createElement('div');
                    notice.className = 'alert alert-warning mb-2 sort-notice';
                    notice.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Select at least two programs to sort them.';
                    
                    // Insert at the top
                    if (programContainer && programContainer.firstChild) {
                        programContainer.insertBefore(notice, programContainer.firstChild);
                        
                        // Auto-dismiss after 3 seconds
                        setTimeout(() => {
                            notice.classList.add('fade');
                            setTimeout(() => notice.remove(), 500);
                        }, 3000);
                    }
                    return;
                }
                
                // Collect program IDs and their order values
                const programOrders = [];
                let hasDuplicates = false;
                const orderValues = new Set();
                
                checkedPrograms.forEach(checkbox => {
                    const programId = checkbox.value;
                    const orderInput = document.getElementById(`order_${programId}`);
                    if (orderInput) {
                        // Use either the input value or a high default value for blank inputs
                        let orderValue = parseInt(orderInput.value);
                        
                        if (!isNaN(orderValue) && orderValue > 0) {
                            if (orderValues.has(orderValue)) {
                                hasDuplicates = true;
                            }
                            orderValues.add(orderValue);
                        } else {
                            orderValue = 999999; // Default high value for blank or invalid inputs
                        }
                        
                        programOrders.push({ programId, orderValue });
                    }
                });
                
                // Sort by order value
                programOrders.sort((a, b) => a.orderValue - b.orderValue);
                
                // Reassign sequential order values
                programOrders.forEach((program, index) => {
                    const orderInput = document.getElementById(`order_${program.programId}`);
                    if (orderInput) {
                        orderInput.value = index + 1;
                    }
                });
                
                // Show a brief confirmation message
                const programContainer = document.querySelector('.program-selector-container');
                const existingNotice = document.querySelector('.sort-notice');
                if (existingNotice) {
                    existingNotice.remove(); // Remove any existing notice
                }
                
                const notice = document.createElement('div');
                notice.className = 'alert alert-success mb-2 sort-notice';
                
                if (hasDuplicates) {
                    notice.innerHTML = '<i class="fas fa-check-circle me-2"></i>Programs sorted numerically! Duplicate numbers were resolved.';
                } else {
                    notice.innerHTML = '<i class="fas fa-check-circle me-2"></i>Programs sorted numerically! Programs will appear in this order in the report.';
                }
                
                // Insert at the top
                if (programContainer && programContainer.firstChild) {
                    programContainer.insertBefore(notice, programContainer.firstChild);
                    
                    // Auto-dismiss after 3 seconds
                    setTimeout(() => {
                        notice.classList.add('fade');
                        setTimeout(() => notice.remove(), 500);
                    }, 3000);
                }
            }        // Show/hide order input when checkbox is checked/unchecked
        function toggleOrderInput(checkbox) {
            const programId = checkbox.value;
            const orderInput = document.getElementById(`order_${programId}`);
            const orderBadge = checkbox.closest('.program-checkbox-container').querySelector('.program-order-badge');
            const programContainer = checkbox.closest('.program-checkbox-container');
            const agencyName = programContainer.getAttribute('data-agency-name');
            const programName = checkbox.nextElementSibling.textContent.trim();
            
            if (checkbox.checked) {
                // Add to global selections
                if (!globalProgramSelections.has(programId)) {
                    globalProgramSelections.set(programId, {
                        selected: true,
                        order: nextOrderNumber++,
                        agency: agencyName,
                        program_name: programName
                    });
                } else {
                    const selection = globalProgramSelections.get(programId);
                    selection.selected = true;
                    if (!selection.order) {
                        selection.order = nextOrderNumber++;
                    }
                }
                
                const orderValue = globalProgramSelections.get(programId).order;
                if (orderInput) {
                    orderInput.style.display = 'inline-block';
                    orderInput.value = orderValue;
                }
                
                if (orderBadge) {
                    orderBadge.textContent = orderValue;
                    orderBadge.classList.add('active');
                }
            } else {
                // Update global selections
                if (globalProgramSelections.has(programId)) {
                    globalProgramSelections.get(programId).selected = false;
                }
                
                if (orderInput) {
                    orderInput.style.display = 'none';
                }
                
                if (orderBadge) {
                    orderBadge.textContent = '#';
                    orderBadge.classList.remove('active');
                }
            }
            
            // Update window reference
            window.globalProgramSelections = globalProgramSelections;
            
            updateProgramCount();
        }
        
        // Initialize agency-specific select buttons
        function initializeAgencySelectButtons() {
            // Select all programs from specific agency
            document.querySelectorAll('.select-agency-programs').forEach(btn => {
                btn.addEventListener('click', function() {
                    const agencyName = this.getAttribute('data-agency-name');
                    const agencyProgramContainers = document.querySelectorAll(`[data-agency-name="${agencyName}"]`);
                    
                    agencyProgramContainers.forEach(container => {
                        const checkbox = container.querySelector('input[type="checkbox"]');
                        if (checkbox && !checkbox.checked) {
                            checkbox.checked = true;
                            toggleOrderInput(checkbox);
                        }
                    });
                });
            });
            
            // Deselect all programs from specific agency
            document.querySelectorAll('.deselect-agency-programs').forEach(btn => {
                btn.addEventListener('click', function() {
                    const agencyName = this.getAttribute('data-agency-name');
                    const agencyProgramContainers = document.querySelectorAll(`[data-agency-name="${agencyName}"]`);
                    
                    agencyProgramContainers.forEach(container => {
                        const checkbox = container.querySelector('input[type="checkbox"]');
                        if (checkbox && checkbox.checked) {
                            checkbox.checked = false;
                            toggleOrderInput(checkbox);
                        }
                    });
                });
            });
            
            // View selected programs
            document.getElementById('viewSelectedPrograms')?.addEventListener('click', function() {
                showSelectedProgramsModal();
            });
            
            // Clear all selections
            document.getElementById('clearAllSelections')?.addEventListener('click', function() {
                clearAllProgramSelections();
            });
        }
        
        // Show modal with selected programs
        function showSelectedProgramsModal() {
            const selectedPrograms = Array.from(globalProgramSelections.entries())
                .filter(([id, data]) => data.selected)
                .sort((a, b) => a[1].order - b[1].order);
            
            let modalHtml = `
                <div class="modal fade" id="selectedProgramsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-list-check me-2"></i>Selected Programs (${selectedPrograms.length})
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Order</th>
                                                <th>Program Name</th>
                                                <th>Agency</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
            `;
            
            selectedPrograms.forEach(([programId, data]) => {
                modalHtml += `
                    <tr>
                        <td><span class="badge bg-primary">${data.order}</span></td>
                        <td>${data.program_name}</td>
                        <td>${data.agency}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-program" 
                                    data-program-id="${programId}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            modalHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal
            document.getElementById('selectedProgramsModal')?.remove();
            
            // Add new modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Add event listeners for remove buttons
            document.querySelectorAll('.remove-program').forEach(btn => {
                btn.addEventListener('click', function() {
                    const programId = this.getAttribute('data-program-id');
                    removeProgramSelection(programId);
                    this.closest('tr').remove();
                });
            });
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('selectedProgramsModal'));
            modal.show();
        }
          // Remove program selection
        function removeProgramSelection(programId) {
            if (globalProgramSelections.has(programId)) {
                globalProgramSelections.get(programId).selected = false;
            }
            
            // Update window reference
            window.globalProgramSelections = globalProgramSelections;
            
            // Update UI
            const checkbox = document.getElementById(`program_${programId}`);
            if (checkbox) {
                checkbox.checked = false;
                toggleOrderInput(checkbox);
            }
        }
          // Clear all program selections
        function clearAllProgramSelections() {
            globalProgramSelections.clear();
            nextOrderNumber = 1;
            
            // Update window reference
            window.globalProgramSelections = globalProgramSelections;
            
            // Update UI
            document.querySelectorAll('.program-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                const orderInput = document.getElementById(`order_${checkbox.value}`);
                const orderBadge = checkbox.closest('.program-checkbox-container').querySelector('.program-order-badge');
                
                if (orderInput) {
                    orderInput.style.display = 'none';
                    orderInput.value = '';
                }
                
                if (orderBadge) {
                    orderBadge.textContent = '#';
                    orderBadge.classList.remove('active');
                }
            });
            
            updateProgramCount();
            
            // Refresh the display
            if (window.allAvailablePrograms) {
                const agencyIds = Array.from(agencySelect?.selectedOptions || []).map(opt => opt.value).filter(Boolean);
                renderProgramsWithFiltering(window.allAvailablePrograms, sectorSelect.value, agencyIds);
            }
        }
          // Update all order numbers to ensure they're sequential and valid
        function updateOrderNumbers() {
            const orderInputs = document.querySelectorAll('#programSelector .program-order-input[style*="display: inline-block"]');
            if (orderInputs.length === 0) {
                return; // No visible order inputs, nothing to do
            }
            
            const usedNumbers = new Set();
            const duplicateInputs = [];
            
            // First pass: identify valid numbers and track duplicates
            orderInputs.forEach(input => {
                const value = parseInt(input.value);
                if (!isNaN(value) && value > 0) {
                    if (usedNumbers.has(value)) {
                        // This is a duplicate
                        duplicateInputs.push(input);
                    } else {
                        usedNumbers.add(value);
                    }
                } else {
                    // Invalid or empty value
                    duplicateInputs.push(input);
                }
            });
            
            // Second pass: resolve duplicates and invalid values
            if (duplicateInputs.length > 0) {
                let nextAvailable = 1;
                
                duplicateInputs.forEach(input => {
                    // Find next available number
                    while (usedNumbers.has(nextAvailable)) {
                        nextAvailable++;
                    }
                    
                    // Update the input
                    input.value = nextAvailable;
                    usedNumbers.add(nextAvailable);
                    
                    // Briefly highlight the input to show it was updated
                    input.classList.add('border-warning');
                    setTimeout(() => {
                        input.classList.remove('border-warning');
                    }, 1500);
                });
                
                // Show a notification about duplicates being resolved
                const programContainer = document.querySelector('.program-selector-container');
                if (duplicateInputs.length > 0 && programContainer) {
                    const existingDuplicateNotice = document.querySelector('.duplicate-notice');
                    if (!existingDuplicateNotice) {
                        const notice = document.createElement('div');
                        notice.className = 'alert alert-info mb-2 duplicate-notice';
                        notice.innerHTML = '<i class="fas fa-info-circle me-2"></i>Some order numbers were updated to resolve duplicates or invalid values.';
                        
                        // Insert after the header and buttons
                        const headerDiv = programContainer.querySelector('.pb-2.mb-2.border-bottom');
                        if (headerDiv && headerDiv.nextSibling) {
                            programContainer.insertBefore(notice, headerDiv.nextSibling);
                            
                            // Auto-dismiss after 3 seconds
                            setTimeout(() => {
                                notice.classList.add('fade');
                                setTimeout(() => notice.remove(), 500);
                            }, 3000);
                        }
                    }
                }
            }
            
            // Update all badges to reflect current order numbers
            updateOrderBadges();
        }
          // Update all order badges to show current numbers
        function updateOrderBadges() {
            const orderInputs = document.querySelectorAll('#programSelector .program-order-input[style*="display: inline-block"]');
            orderInputs.forEach(input => {
                const programContainer = input.closest('.program-checkbox-container');
                const badge = programContainer?.querySelector('.program-order-badge');
                if (badge && input.value) {
                    badge.textContent = input.value;
                }
            });
        }
        
        // Dynamically renumber all selected programs to maintain sequential order
        function renumberSelectedPrograms() {
            // Get all checked checkboxes and their corresponding order inputs
            const checkedBoxes = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]:checked');
            const programsWithOrder = [];
            
            // Collect all selected programs with their current order
            checkedBoxes.forEach(checkbox => {
                const programId = checkbox.value;
                const orderInput = document.getElementById(`order_${programId}`);
                if (orderInput) {
                    const currentOrder = parseInt(orderInput.value) || 999; // Use 999 for empty/invalid values
                    programsWithOrder.push({
                        checkbox: checkbox,
                        orderInput: orderInput,
                        currentOrder: currentOrder,
                        programId: programId
                    });
                }
            });
            
            // Sort by current order to maintain user's intended sequence
            programsWithOrder.sort((a, b) => a.currentOrder - b.currentOrder);
            
            // Renumber sequentially starting from 1
            programsWithOrder.forEach((program, index) => {
                const newOrder = index + 1;
                program.orderInput.value = newOrder;
                
                // Update the badge
                const orderBadge = program.checkbox.closest('.program-checkbox-container').querySelector('.program-order-badge');
                if (orderBadge) {
                    orderBadge.textContent = newOrder;
                    orderBadge.classList.add('active');
                }
            });
        }
        
        // Update the program count badge
        function updateProgramCount() {
            const programCountBadge = document.getElementById('programCount');
            if (programCountBadge) {
                const selectedCount = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]:checked').length;
                programCountBadge.textContent = selectedCount;
            }
        }
          // Set up period and sector change events
        if (periodSelect && programSelector) {
            // Load programs when period changes
            periodSelect.addEventListener('change', function() {
                loadPrograms();
            });
        }
          if (sectorSelect && programSelector) {
            // Filter programs when sector changes
            sectorSelect.addEventListener('change', function() {
                if (window.allAvailablePrograms) {
                    const agencyIds = Array.from(agencySelect?.selectedOptions || []).map(opt => opt.value).filter(Boolean);
                    renderProgramsWithFiltering(window.allAvailablePrograms, this.value, agencyIds);
                } else if (periodSelect.value) {
                    // If a period is selected, reload programs with the new sector filter
                    loadPrograms();
                } else {
                    // If no period is selected yet, prompt user
                    filterProgramsBySector(this.value);
                    // Highlight the period selector to indicate it should be selected
                    if (periodSelect) {
                        periodSelect.classList.add('border-warning');
                        setTimeout(() => {
                            periodSelect.classList.remove('border-warning');
                        }, 2000);
                    }
                }
            });
        }          // Add event listener for agencySelect
    if (agencySelect) {
        agencySelect.addEventListener('change', function() {
            if (window.allAvailablePrograms) {
                const agencyIds = Array.from(this.selectedOptions).map(opt => opt.value).filter(Boolean);
                renderProgramsWithFiltering(window.allAvailablePrograms, sectorSelect.value, agencyIds);
            } else {
                loadPrograms();
            }
            toggleResetAgencyButton();
        });
    }
    
    // Add event listener for reset agency filter button
    if (clearAgencyFilterBtn) {
        clearAgencyFilterBtn.addEventListener('click', function() {
            resetAgencySelection();
        });
    }
    
    // Function to toggle reset button visibility
    function toggleResetAgencyButton() {
        if (agencySelect && clearAgencyFilterBtn) {
            const hasSelectedAgencies = agencySelect.selectedOptions.length > 0;
            clearAgencyFilterBtn.style.display = hasSelectedAgencies ? 'inline-block' : 'none';
        }
    }
    
    // Function to reset all agency selections
    function resetAgencySelection() {
        if (agencySelect) {
            // Clear all selections
            for (let option of agencySelect.options) {
                option.selected = false;
            }
            // Trigger change event to reload programs
            agencySelect.dispatchEvent(new Event('change'));
            // Hide reset button
            toggleResetAgencyButton();
            
            // Show a brief confirmation message
            showResetConfirmation();
        }
    }
    
    // Function to show reset confirmation
    function showResetConfirmation() {
        const button = clearAgencyFilterBtn;
        const originalText = button.innerHTML;
        
        // Temporarily change button text to show confirmation
        button.innerHTML = '<i class="fas fa-check me-1"></i>Reset!';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        // Revert after 1.5 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 1500);
    }
    
    // Initialize reset button state
    toggleResetAgencyButton();
    
          // Show default state in program selector
        if (programContainerElement) {
            programContainerElement.innerHTML = `
                <div class="alert alert-info border-primary">
                    <strong>Getting Started:</strong> Please select a reporting period above to load available programs for selection.
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            Tip: Programs are filtered by the selected reporting period to show only relevant submissions.
                        </small>
                    </div>
                </div>
            `;
        }
        
        // Add visual highlighting when users click in program area without selecting period
        if (programSelector) {
            programSelector.addEventListener('click', function(e) {
                const periodId = periodSelect ? periodSelect.value : null;
                if (!periodId && periodSelect) {
                    // Highlight the period selector to draw attention
                    periodSelect.classList.add('border-warning', 'shadow-sm');
                    periodSelect.focus();
                    
                    // Remove highlight after 3 seconds
                    setTimeout(() => {
                        periodSelect.classList.remove('border-warning', 'shadow-sm');
                    }, 3000);
                    
                    // Show a brief tooltip-like message
                    const tooltip = document.createElement('div');
                    tooltip.className = 'alert alert-warning alert-dismissible fade show position-absolute';
                    tooltip.style.cssText = 'top: -60px; left: 0; right: 0; z-index: 1050; font-size: 0.875rem;';
                    tooltip.innerHTML = `
                        <i class="fas fa-arrow-up me-1"></i>
                        <strong>Please select a reporting period first!</strong>
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                    `;
                      // Position relative to period select
                    const periodContainer = periodSelect.closest('.report-form-group');
                    if (periodContainer) {
                        periodContainer.style.position = 'relative';
                        periodContainer.appendChild(tooltip);
                        
                        // Auto-dismiss after 4 seconds
                        setTimeout(() => {
                            if (tooltip.parentNode) {
                                tooltip.classList.remove('show');
                                setTimeout(() => tooltip.remove(), 150);
                            }
                        }, 4000);
                    }
                }
            });
        }
        // End of ReportUI check
    } else {
        console.error('ReportUI module not found. Make sure report-ui.js is loaded before report-generator.js');
    }
});