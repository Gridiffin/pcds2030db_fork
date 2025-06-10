/**
 * Report Generator
 * 
 * Main controller for the PPTX report generation functionality.
 * This file coordinates the modules and initializ                programs[sector].programs.forEach(program => {
                    html += `
                        <div class="program-checkbox-container" data-program-id="${program.program_id}">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input program-checkbox" 
                                    id="program_${program.program_id}" 
                                    name="selected_program_ids[]" 
                                    value="${program.program_id}">`port generator.
 */

// Global initialization flag to prevent duplicate initialization
if (typeof reportGeneratorInitialized === 'undefined') {
    var reportGeneratorInitialized = false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations
    if (reportGeneratorInitialized) {
        console.log('Report generator already initialized, skipping duplicate initialization.');
        // The programContainerElement might not be defined here yet if this is the first run
        // and the DOMContentLoaded listener is firing a second time for some reason.
        // It's safer to re-query it or ensure it's defined before this block.
        // For now, we'll assume it might be null and proceed if it was set by a previous init.
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
    console.log('Initializing report generator...');

    // Moved declarations up
    const programSelector = document.getElementById('programSelector');
    const periodSelect = document.getElementById('periodSelect');
    const sectorSelect = document.getElementById('sectorSelect');
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
    }
    
    // Load programs based on selected period and sector
    function loadPrograms() {
        const periodId = periodSelect.value;
        const sectorId = sectorSelect.value;
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
        
        // Fetch programs for this period
        const url = `${APP_URL}/app/api/get_period_programs.php?period_id=${periodId}${sectorId ? '&sector_id=' + sectorId : ''}`;
        
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
                
                // Render programs
                renderPrograms(data.programs, sectorId);
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
    
    // Render programs in the UI
    function renderPrograms(programs, selectedSectorId) {
        let html = '';
        
        for (const sector in programs) {
            if (selectedSectorId && sector !== selectedSectorId) continue;
            
            html += `
            <div class="sector-programs mb-3" data-sector-id="${sector}">
                <h6 class="sector-name fw-bold ms-2 mb-2">${programs[sector].sector_name}</h6>
            `;
            
            if (programs[sector].programs.length > 0) {
                programs[sector].programs.forEach(program => {
                    html += `
                        <div class="program-checkbox-container" draggable="true" data-program-id="${program.program_id}">
                            <i class="fas fa-grip-vertical drag-handle" title="Drag to reorder"></i>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input program-checkbox" 
                                    id="program_${program.program_id}" 
                                    name="selected_program_ids[]" 
                                    value="${program.program_id}">
                                <label class="form-check-label" for="program_${program.program_id}">
                                    ${program.program_name}
                                </label>
                            </div>
                            <div class="program-order-badge" title="Click to edit order">#</div>
                            <input type="number" min="1" class="program-order-input" 
                                name="program_order_${program.program_id}" 
                                id="order_${program.program_id}" 
                                aria-label="Program display order">
                        </div>
                    `;
                });
            } else {
                html += `<p class="text-muted">No programs available for this sector.</p>`;
            }
            
            html += `</div>`;        }
        
        if (programSelector) {
            programSelector.innerHTML = html;
            
            // Initialize select buttons and update count
            initializeSelectButtons();
            updateProgramCount();
        }
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
        ReportUI.initUI();            // Initialize Select All / Deselect All buttons
            function initializeSelectButtons() {
                const selectAllBtn = document.getElementById('selectAllPrograms');
                const deselectAllBtn = document.getElementById('deselectAllPrograms');
                const sortProgramOrderBtn = document.getElementById('sortProgramOrder');
                
                if (selectAllBtn && deselectAllBtn) {// Select all visible programs
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
            }              // Add change event listeners to all program checkboxes
            const programCheckboxes = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]');
            programCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateProgramCount();
                    toggleOrderInput(this);
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
            }
        
        // Show/hide order input when checkbox is checked/unchecked
        function toggleOrderInput(checkbox) {
            const programId = checkbox.value;
            const orderInput = document.getElementById(`order_${programId}`);
            if (orderInput) {
                orderInput.style.display = checkbox.checked ? 'inline-block' : 'none';
                if (checkbox.checked && !orderInput.value) {
                    // Assign the next available number
                    const checkedCount = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]:checked').length;
                    orderInput.value = checkedCount;
                } else if (!checkbox.checked) {
                    orderInput.value = '';
                }
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
                if (periodSelect.value) {
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
        }
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
    } else {
        console.error('ReportUI module not found. Make sure report-ui.js is loaded before report-generator.js');
    }
});