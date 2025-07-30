/**
 * Report Generator
 * 
 * Main controller for the PPTX report generation functionality.
 * This file coordinates the modules and initializes the report generator.
 * 
 * Update 2025-06-18: Modified the backend query to exclude draft programs from selection.
 * Now only finalized (non-draft) programs will be available for report generation.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations
    if (window.reportGeneratorInitialized) {
        console.log('Report generator already initialized, skipping duplicate initialization.');
        return;
    }
    window.reportGeneratorInitialized = true;
    console.log('Initializing report generator...');

    // --- DOM Element References ---
    const periodSelect = document.getElementById('periodSelect');
    const sectorSelect = document.getElementById('sectorSelect');
    const programSelector = document.getElementById('programSelector');
    const programContainerElement = programSelector ? programSelector.querySelector('.program-selector-container') : null;
    const targetSelector = document.getElementById('targetSelector');
    const targetContainerElement = targetSelector ? targetSelector.querySelector('.target-selector-container') : null;
    const targetSelectionSection = document.getElementById('targetSelectionSection');
    const targetCountElement = document.getElementById('targetCount');
    const agencyFilterContainer = document.getElementById('agencyFilterTags');
    const searchInput = document.getElementById('programSearchInput');
    const programCountBadge = document.getElementById('programCount');

    // --- Global State ---
    let globalProgramSelections = new Map(); // Tracks selected programs and their order
    let globalTargetSelections = new Map(); // Tracks selected targets per program
    let allProgramTargets = []; // Stores targets for all selected programs
    // Note: nextOrderNumber removed - order is now calculated dynamically
    let allLoadedPrograms = []; // Stores all programs fetched from the API for a period
    let filteredPrograms = []; // Stores programs after applying filters
    let selectedAgencyIds = []; // Stores IDs of selected agencies for filtering
    let allAvailableAgencies = []; // Stores all unique agencies for the current program set
    let searchTimeout = null; // Debounce timer for search input

    // Show default state in program selector if it exists and no period is selected
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
                program_name: data.program_name,
                program_number: data.program_number
            });
            
            if (data.selected) {
                selectedPrograms.push({
                    programId,
                    order: data.order,
                    agency: data.agency,
                    program_name: data.program_name,
                    program_number: data.program_number
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
                // Clear stored programs
                allLoadedPrograms = [];
                filteredPrograms = [];
                updateVisibleProgramCount(0);
            }
            return;
        }

        // Show loading indicator
        showProgramsLoading();
        
        // Build URL - always load all programs for the period, then filter on frontend
        let url = `${APP_URL}/app/api/get_period_programs.php?period_id=${periodId}`;
        
        // Log the API request for debugging
        console.log(`Requesting programs for period_id: ${periodId}`);
        
        // Get period option text to debug what's actually being selected
        const selectedOption = periodSelect.options[periodSelect.selectedIndex];
        console.log(`Selected period text: "${selectedOption.textContent}"`);
        
        // Debug to check period type based on text
        const isHalfYearly1 = selectedOption.textContent.includes('Half Yearly 1') || selectedOption.textContent.includes('H1');
        const isHalfYearly2 = selectedOption.textContent.includes('Half Yearly 2') || selectedOption.textContent.includes('H2');
        
        // Log additional info for half-yearly periods
        if (periodId == 5 || isHalfYearly1) {
            console.log('Half Yearly 1 selected - backend should include Q1 and Q2 submissions');
        } else if (periodId == 6 || isHalfYearly2) {
            console.log('Half Yearly 2 selected - backend should include Q3 and Q4 submissions');
        }
        
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
                
                // Flatten and store all programs for enhanced filtering
                allLoadedPrograms = [];
                for (const sector in data.programs) {
                    const sectorData = data.programs[sector];
                    sectorData.programs.forEach(program => {
                        allLoadedPrograms.push({
                            ...program,
                            sector_name: 'Forestry',
                            sector_id: 1
                        });
                    });
                }
                
                // Populate agency filter buttons from flattened programs
                populateAgencyFilterButtons(allLoadedPrograms);
                
                // Apply initial filtering based on current selections
                applyAllFilters();
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

    // Load targets for selected programs
    function loadTargets() {
        const selectedPrograms = getSelectedPrograms();
        const periodId = periodSelect.value;

        if (!periodId || selectedPrograms.length === 0) {
            hideTargetSelection();
            return;
        }

        // Show target selection section
        showTargetSelection();
        showTargetsLoading();

        // Get selected program IDs
        const programIds = selectedPrograms.map(p => p.program_id).join(',');
        
        const url = `${APP_URL}/app/api/get_program_targets.php?period_id=${periodId}&selected_program_ids=${programIds}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success || !data.programs) {
                    throw new Error(data.error || 'Invalid response from server');
                }

                allProgramTargets = data.programs;
                displayTargets(allProgramTargets);
                updateTargetCount();
            })
            .catch(error => {
                console.error('Error loading targets:', error);
                
                if (targetContainerElement) {
                    targetContainerElement.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading targets: ${error.message}
                        </div>
                    `;
                }
            });
    }

    // Display targets in the UI
    function displayTargets(programsWithTargets) {
        if (!targetContainerElement) return;

        if (!programsWithTargets || programsWithTargets.length === 0) {
            targetContainerElement.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No targets found for the selected programs.
                </div>
            `;
            return;
        }

        let html = '';

        programsWithTargets.forEach(program => {
            html += `
                <div class="program-targets-group mb-4">
                    <div class="program-header bg-light p-2 rounded mb-2">
                        <h6 class="mb-0">
                            <i class="fas fa-folder me-2"></i>${program.program_name}
                            <span class="badge bg-secondary ms-2">${program.target_count} targets</span>
                        </h6>
                    </div>
                    <div class="targets-list">
            `;

            program.targets.forEach(target => {
                const targetId = `target_${program.program_id}_${target.target_id}`;
                html += `
                    <div class="target-item border rounded p-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input target-checkbox" 
                                   type="checkbox" 
                                   id="${targetId}"
                                   data-program-id="${program.program_id}"
                                   data-target-id="${target.target_id}"
                                   ${target.selected ? 'checked' : ''}>
                            <label class="form-check-label w-100" for="${targetId}">
                                <div class="target-content">
                                    <div class="target-header d-flex justify-content-between align-items-start mb-2">
                                        <strong class="target-number">Target ${target.target_number}</strong>
                                        <span class="badge bg-info">${target.period_label}</span>
                                    </div>
                                    <div class="target-text mb-2">
                                        <strong>Target:</strong>
                                        <div class="text-muted">${target.target_text.replace(/\n/g, '<br>')}</div>
                                    </div>
                                    <div class="status-text">
                                        <strong>Status:</strong>
                                        <div class="text-muted">${target.status_description.replace(/\n/g, '<br>')}</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        targetContainerElement.innerHTML = html;

        // Add event listeners to target checkboxes
        const targetCheckboxes = targetContainerElement.querySelectorAll('.target-checkbox');
        targetCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', handleTargetSelection);
        });
    }

    // Handle target selection
    function handleTargetSelection(event) {
        const checkbox = event.target;
        const programId = checkbox.dataset.programId;
        const targetId = checkbox.dataset.targetId;
        const isSelected = checkbox.checked;

        // Update global target selections
        if (!globalTargetSelections.has(programId)) {
            globalTargetSelections.set(programId, new Set());
        }

        const programTargets = globalTargetSelections.get(programId);
        
        if (isSelected) {
            programTargets.add(targetId);
        } else {
            programTargets.delete(targetId);
        }

        updateTargetCount();
    }

    // Update target count display
    function updateTargetCount() {
        if (!targetCountElement) return;

        let totalSelected = 0;
        for (const [programId, targetSet] of globalTargetSelections) {
            totalSelected += targetSet.size;
        }

        targetCountElement.textContent = totalSelected;
    }

    // Show target selection section
    function showTargetSelection() {
        if (targetSelectionSection) {
            targetSelectionSection.style.display = 'block';
        }
    }

    // Hide target selection section
    function hideTargetSelection() {
        if (targetSelectionSection) {
            targetSelectionSection.style.display = 'none';
        }
        // Clear target selections
        globalTargetSelections.clear();
        allProgramTargets = [];
    }

    // Show targets loading state
    function showTargetsLoading() {
        if (!targetContainerElement) return;

        targetContainerElement.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading targets...</span>
                </div>
                <div class="mt-2">Loading targets...</div>
            </div>
        `;
    }

    // Get selected targets data for report generation
    function getSelectedTargets() {
        const selectedTargets = {};
        
        for (const [programId, targetSet] of globalTargetSelections) {
            if (targetSet.size > 0) {
                selectedTargets[programId] = Array.from(targetSet);
            }
        }

        return selectedTargets;
    }

    // Select all targets
    function selectAllTargets() {
        const targetCheckboxes = document.querySelectorAll('.target-checkbox');
        targetCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    }

    // Clear all target selections
    function clearAllTargets() {
        const targetCheckboxes = document.querySelectorAll('.target-checkbox');
        targetCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    }
    
    function updateProgramOrder() {
        // This function will be called when program order changes
        updateOrderNumbers();
    }
    
    // Initialize the UI
    if (typeof ReportUI !== 'undefined') {
        ReportUI.initUI();
        
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
            const orderBadge = checkbox.closest('.program-checkbox-container').querySelector('.program-order-badge');
            const programContainer = checkbox.closest('.program-checkbox-container');
            const agencyName = programContainer.getAttribute('data-agency-name');
            const programName = programContainer.getAttribute('data-program-name');
            const programNumber = programContainer.getAttribute('data-program-number');
            
            if (checkbox.checked) {
                // Calculate the next available order based on currently selected programs
                const currentlySelected = Array.from(globalProgramSelections.values()).filter(p => p.selected);
                const nextOrder = currentlySelected.length + 1;
                
                // Add to global selections with the next sequential order
                globalProgramSelections.set(programId, {
                    selected: true,
                    order: nextOrder,
                    agency: agencyName,
                    program_name: programName,
                    program_number: programNumber
                });
                
                if (orderInput) {
                    orderInput.style.display = 'inline-block';
                    orderInput.value = nextOrder;
                }
                
                if (orderBadge) {
                    orderBadge.textContent = nextOrder;
                    orderBadge.classList.add('active');
                }
            } else {
                console.log(`=== DESELECTING PROGRAM ${programId} ===`);
                
                // Mark as deselected in global selections
                if (globalProgramSelections.has(programId)) {
                    globalProgramSelections.get(programId).selected = false;
                    console.log(`Marked program ${programId} as deselected`);
                }
                
                if (orderInput) {
                    orderInput.style.display = 'none';
                    orderInput.value = '';
                }
                
                if (orderBadge) {
                    orderBadge.textContent = '#';
                    orderBadge.classList.remove('active');
                }
                
                // Small delay to ensure DOM updates are complete, then renumber
                setTimeout(() => {
                    console.log('Starting renumbering after deselection...');
                    renumberSelectedPrograms();
                }, 50);
            }
            
            // Update window reference
            window.globalProgramSelections = globalProgramSelections;
            
            updateSelectedProgramCount();
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
                        <td>${data.program_number ? `<span class="badge bg-info me-2" title="Program Number">${data.program_number}</span>` : ''}${data.program_name}</td>
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
            console.log('=== RENUMBERING PROGRAMS ===');
            
            // Get all currently selected programs from global state
            const selectedPrograms = [];
            
            // Collect selected programs from global state
            globalProgramSelections.forEach((data, programId) => {
                if (data.selected) {
                    const checkbox = document.querySelector(`#programSelector input[value="${programId}"]`);
                    const orderInput = document.getElementById(`order_${programId}`);
                    
                    if (checkbox && checkbox.checked && orderInput && orderInput.style.display !== 'none') {
                        selectedPrograms.push({
                            programId: programId,
                            checkbox: checkbox,
                            orderInput: orderInput,
                            currentOrder: data.order || 999,
                            data: data
                        });
                        console.log(`Found selected program ${programId} with current order ${data.order}`);
                    }
                }
            });
            
            console.log(`Total selected programs found: ${selectedPrograms.length}`);
            
            // Sort by current order to maintain user's intended sequence
            selectedPrograms.sort((a, b) => a.currentOrder - b.currentOrder);
            
            // Renumber sequentially starting from 1
            selectedPrograms.forEach((program, index) => {
                const newOrder = index + 1;
                console.log(`Updating program ${program.programId} from order ${program.currentOrder} to ${newOrder}`);
                
                // Update the input field
                program.orderInput.value = newOrder;
                
                // Update the badge
                const orderBadge = program.checkbox.closest('.program-checkbox-container').querySelector('.program-order-badge');
                if (orderBadge) {
                    orderBadge.textContent = newOrder;
                    orderBadge.classList.add('active');
                }
                
                // Update global state
                program.data.order = newOrder;
                globalProgramSelections.set(program.programId, program.data);
            });
            
            // Force update window reference
            window.globalProgramSelections = globalProgramSelections;
            
            console.log(`=== RENUMBERING COMPLETE: ${selectedPrograms.length} programs ===`);
            
            // Debug: Log final state
            globalProgramSelections.forEach((data, id) => {
                if (data.selected) {
                    console.log(`Final state - Program ${id}: Order ${data.order}`);
                }
            });
        }
        
        // Update the program count badge for SELECTED programs
        function updateSelectedProgramCount() {
            const selectedCount = Array.from(globalProgramSelections.values()).filter(p => p.selected).length;
            // This function can be expanded to update a dedicated UI element for selected count if one exists.
            // For now, the count is mainly shown in the 'View Selected' modal and the dynamic banner.
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
                if (allLoadedPrograms.length > 0) {
                    // Use enhanced filtering system
                    applyAllFilters();
                } else if (periodSelect.value) {
                    // If a period is selected, reload programs with the new sector filter
                    loadPrograms();
                } else {
                    // If no period is selected yet, show message
                    if (programContainerElement) {
                        programContainerElement.innerHTML = `
                            <div class="alert alert-info border-primary">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Getting Started:</strong> Please select a reporting period above to load available programs for selection.
                            </div>
                        `;
                    }
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

    // Enhanced Filter Bar Event Listeners
    function setupEnhancedFilterEventListeners() {
        // Search input with debouncing
        const searchInput = document.getElementById('programSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyAllFilters();
                }, 300); // 300ms debounce
            });
        }

        // Clear search button
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                if (searchInput) {
                    searchInput.value = '';
                    applyAllFilters();
                }
            });
        }

        // Select all visible programs
        const selectAllBtn = document.getElementById('selectAllPrograms');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                selectAllVisiblePrograms();
            });
        }

        // Clear all program selections
        const clearAllBtn = document.getElementById('clearAllSelections');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function() {
                clearAllProgramSelections();
            });
        }

        // Reset all filters
        const resetFiltersBtn = document.getElementById('resetAllFilters');
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function() {
                resetAllFilters();
            });
        }

        // Target selection event listeners
        const selectAllTargetsBtn = document.getElementById('selectAllTargets');
        if (selectAllTargetsBtn) {
            selectAllTargetsBtn.addEventListener('click', function() {
                selectAllTargets();
            });
        }

        const clearAllTargetsBtn = document.getElementById('clearAllTargets');
        if (clearAllTargetsBtn) {
            clearAllTargetsBtn.addEventListener('click', function() {
                clearAllTargets();
            });
        }
    }

    // Call the enhanced filter setup
    setupEnhancedFilterEventListeners();
    
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

    // Enhanced filtering functions
    function updateVisibleProgramCount(count) {
        const countElement = document.getElementById('programCount');
        const badgeElement = document.getElementById('programCountBadge');
        
        if (countElement) {
            countElement.textContent = count;
        }
        
        if (badgeElement) {
            // Update badge color based on count
            badgeElement.className = count > 0 ? 'badge bg-success' : 'badge bg-secondary';
        }
    }

    function searchPrograms(programs, searchText) {
        if (!searchText || searchText.length < 1) {
            return programs;
        }
        
        const searchLower = searchText.toLowerCase();
        return programs.filter(program => {
            // Search in program name, number, and agency
            const searchableText = [
                program.program_name || '',
                program.program_number || '',
                program.agency_name || ''
            ].join(' ').toLowerCase();
            
            return searchableText.includes(searchLower);
        });
    }
    function applyAllFilters() {
        let programs = [...allLoadedPrograms]; // Start with all loaded programs
        
        // Apply agency filter using new integrated system
        const selectedAgencies = getSelectedAgencyIds();
        if (selectedAgencies.length > 0) {
            programs = programs.filter(program => 
                selectedAgencies.includes(program.owner_agency_id?.toString()));
        }
        
        // Apply search filter
        const searchInput = document.getElementById('programSearchInput');
        if (searchInput && searchInput.value.trim()) {
            programs = searchPrograms(programs, searchInput.value.trim());
        }
        
        // Update filtered programs and count
        filteredPrograms = programs;
        updateVisibleProgramCount(programs.length);
        
        // Re-render the programs display
        renderProgramsList(programs);
        
        return programs;
    }

    function renderProgramsList(programs) {
        const programContainerElement = document.querySelector('#programSelector .program-selector-container');
        if (!programContainerElement) return;

        let html = '';

        // Add summary of currently selected programs
        const selectedCount = Array.from(globalProgramSelections.values()).filter(p => p.selected).length;
        if (selectedCount > 0) {
            html += `
                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>${selectedCount} programs selected for report</strong>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-success btn-sm" id="viewSelectedProgramsBtn">
                                <i class="fas fa-list me-1"></i>View Selected
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="clearAllSelectionsBtn">
                                <i class="fas fa-times me-1"></i>Clear All
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        if (programs.length === 0) {
            html += `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-search me-2"></i>
                    <strong>No programs found</strong><br>
                    <small>Try adjusting your filters or search terms.</small>
                </div>
            `;
        } else {
            // Group programs by sector for better organization
            const programsBySector = {};
            programs.forEach(program => {
                const sectorKey = program.sector_name || 'Unknown Sector';
                if (!programsBySector[sectorKey]) {
                    programsBySector[sectorKey] = [];
                }
                programsBySector[sectorKey].push(program);
            });

            Object.keys(programsBySector).sort().forEach(sectorName => {
                const sectorPrograms = programsBySector[sectorName];
                
                html += `
                    <div class="sector-group mb-3">
                        <h6 class="sector-header d-flex align-items-center mb-2">
                            <i class="fas fa-industry me-2 text-primary"></i>
                            <span>${sectorName}</span>
                            <span class="badge bg-light text-dark ms-2">${sectorPrograms.length}</span>
                        </h6>
                `;

                // Group by agency within sector
                const programsByAgency = {};
                sectorPrograms.forEach(program => {
                    const agencyKey = program.agency_name || 'Unknown Agency';
                    if (!programsByAgency[agencyKey]) {
                        programsByAgency[agencyKey] = [];
                    }
                    programsByAgency[agencyKey].push(program);
                });

                Object.keys(programsByAgency).sort().forEach(agencyName => {
                    const agencyPrograms = programsByAgency[agencyName];
                    
                    html += `
                        <div class="agency-group mb-2">
                            <div class="agency-header ms-2 mb-2">
                                <small class="text-muted fw-bold">
                                    <i class="fas fa-building me-1"></i>${agencyName}
                                </small>
                            </div>
                    `;

                    agencyPrograms.forEach(program => {
                        const programId = program.program_id;
                        const isSelected = globalProgramSelections.has(programId) && globalProgramSelections.get(programId).selected;
                        const orderValue = isSelected ? globalProgramSelections.get(programId).order : '';
                        
                        html += `
                            <div class="program-checkbox-container ms-4" data-program-id="${programId}" data-agency-name="${program.agency_name}" data-program-name="${program.program_name}" data-program-number="${program.program_number || ''}">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input program-checkbox" 
                                        id="program_${programId}" 
                                        name="selected_program_ids[]" 
                                        value="${programId}"
                                        ${isSelected ? 'checked' : ''}>
                                    <label class="form-check-label" for="program_${programId}">
                                        ${program.program_number ? `<span class="badge bg-info me-2" title="Program Number">${program.program_number}</span>` : ''}${program.program_name}
                                    </label>
                                </div>
                                <div class="program-order-badge ${isSelected ? 'active' : ''}" title="Click to edit order">
                                    ${isSelected ? orderValue : '#'}
                                </div>
                                <input type="number" min="1" class="program-order-input" 
                                    name="program_order_${programId}" 
                                    id="order_${programId}" 
                                    value="${isSelected ? orderValue : ''}"
                                    ${isSelected ? '' : 'style="display: none;"'}>
                            </div>
                        `;
                    });

                    html += '</div>'; // Close agency-group
                });

                html += '</div>'; // Close sector-group
            });
        }

        programContainerElement.innerHTML = html;

        // Re-attach event listeners
        attachProgramEventListeners();
        document.getElementById('viewSelectedProgramsBtn')?.addEventListener('click', showSelectedProgramsModal);
        document.getElementById('clearAllSelectionsBtn')?.addEventListener('click', clearAllProgramSelections);
    }
    function selectAllVisiblePrograms() {
        const visibleCheckboxes = document.querySelectorAll('.program-checkbox:not(:disabled)');
        let selectedCount = 0;
        
        visibleCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                checkbox.checked = true;
                
                // Get program data from the container
                const container = checkbox.closest('.program-checkbox-container');
                if (container) {
                    const programId = parseInt(container.dataset.programId);
                    const programName = container.dataset.programName;
                    const programNumber = container.dataset.programNumber;
                    const agencyName = container.dataset.agencyName;
                    
                    // Calculate next available order
                    const currentlySelected = Array.from(globalProgramSelections.values()).filter(p => p.selected);
                    const nextOrder = currentlySelected.length + 1;
                    
                    // Add to global selections
                    globalProgramSelections.set(programId, {
                        selected: true,
                        order: nextOrder,
                        program_name: programName,
                        program_number: programNumber,
                        agency: agencyName
                    });
                    
                    // Show order input
                    const orderInput = document.getElementById(`order_${programId}`);
                    const orderBadge = container.querySelector('.program-order-badge');
                    if (orderInput && orderBadge) {
                        orderInput.style.display = 'inline-block';
                        orderInput.value = globalProgramSelections.get(programId).order;
                        orderBadge.classList.add('active');
                        orderBadge.textContent = globalProgramSelections.get(programId).order;
                    }
                }
                selectedCount++;
            }
        });
        
        // Show success message
        if (selectedCount > 0) {
            showNotification(`Selected ${selectedCount} programs`, 'success');
        } else {
            showNotification('All visible programs are already selected', 'info');
        }
        applyAllFilters();
        
        // Load targets for newly selected programs
        loadTargets();
    }
    
    // Make clearAllProgramSelections globally accessible
    window.clearAllProgramSelections = function() {
        // Clear global selections state
        globalProgramSelections.clear();

        // Update UI
        const allCheckboxes = document.querySelectorAll('.program-checkbox');
        let clearedCount = 0;
        
        allCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.checked = false;
                clearedCount++;
                
                // Get program data from the container
                const container = checkbox.closest('.program-checkbox-container');
                if (container) {
                    const programId = parseInt(container.dataset.programId);
                    
                    // Hide order input
                    const orderInput = document.getElementById(`order_${programId}`);
                    const orderBadge = container.querySelector('.program-order-badge');
                    if (orderInput && orderBadge) {
                        orderInput.style.display = 'none';
                        orderInput.value = '';
                        orderBadge.classList.remove('active');
                        orderBadge.textContent = '#';
                    }
                }
            }
        });
        
        if (clearedCount > 0) {
            showNotification(`Cleared ${clearedCount} program selections`, 'info');
        }

        // Refresh the program list and count
        applyAllFilters();
        
        // Update target selection when programs are cleared
        loadTargets();
    }
    function resetAllFilters() {
        // Clear search
        const searchInput = document.getElementById('programSearchInput');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Reset agency filters
        selectedAgencyIds = [];
        const agencyButtons = document.querySelectorAll('.agency-filter-btn');
        agencyButtons.forEach(btn => {
            if (btn.dataset.agencyId === 'all') {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Reapply filters
        applyAllFilters();
        
        showNotification('All filters reset', 'info');
    }

    function showNotification(message, type = 'info') {
        // Simple notification - you can enhance this with a toast library
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 250px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Function to attach event listeners to program checkboxes and order inputs
    function attachProgramEventListeners() {
        const programCheckboxes = document.querySelectorAll('.program-checkbox');
        const orderInputs = document.querySelectorAll('.program-order-input');
        
        // Handle checkbox changes
        programCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const container = this.closest('.program-checkbox-container');
                if (!container) return;
                
                const programId = parseInt(container.dataset.programId);
                const programName = container.dataset.programName;
                const programNumber = container.dataset.programNumber;
                const agencyName = container.dataset.agencyName;
                
                const orderInput = document.getElementById(`order_${programId}`);
                const orderBadge = container.querySelector('.program-order-badge');
                
                if (this.checked) {
                    // Calculate next available order
                    const currentlySelected = Array.from(globalProgramSelections.values()).filter(p => p.selected);
                    const nextOrder = currentlySelected.length + 1;
                    
                    // Program selected
                    globalProgramSelections.set(programId, {
                        selected: true,
                        order: nextOrder,
                        program_name: programName,
                        program_number: programNumber,
                        agency: agencyName
                    });
                    
                    // Show order input and update badge
                    if (orderInput && orderBadge) {
                        orderInput.style.display = 'inline-block';
                        orderInput.value = globalProgramSelections.get(programId).order;
                        orderBadge.classList.add('active');
                        orderBadge.textContent = globalProgramSelections.get(programId).order;
                    }
                } else {
                    // Program deselected
                    console.log(`=== DESELECTING PROGRAM ${programId} (attachProgramEventListeners) ===`);
                    
                    if (globalProgramSelections.has(programId)) {
                        globalProgramSelections.set(programId, {
                            ...globalProgramSelections.get(programId),
                            selected: false
                        });
                    }
                    
                    // Hide order input and update badge
                    if (orderInput && orderBadge) {
                        orderInput.style.display = 'none';
                        orderInput.value = '';
                        orderBadge.classList.remove('active');
                        orderBadge.textContent = '#';
                    }
                    
                    // Renumber remaining selected programs after a small delay
                    setTimeout(() => {
                        console.log('Starting renumbering after deselection (attachProgramEventListeners)...');
                        renumberSelectedPrograms();
                    }, 50);
                }
                applyAllFilters(); // Re-render to update the selected banner
                
                // Load targets when program selection changes
                loadTargets();
            });
        });
        
        // Handle order input changes with immediate updates
        orderInputs.forEach(input => {
            let orderUpdateTimeout;
            
            input.addEventListener('input', function() {
                const programId = this.id.replace('order_', '');
                const newOrder = parseInt(this.value) || 1;
                
                console.log(`Manual order change: Program ${programId} → Order ${newOrder}`);
                
                // Clear previous timeout
                if (orderUpdateTimeout) {
                    clearTimeout(orderUpdateTimeout);
                }
                
                // Update immediately for visual feedback
                if (globalProgramSelections.has(programId)) {
                    const programData = globalProgramSelections.get(programId);
                    programData.order = newOrder;
                    globalProgramSelections.set(programId, programData);
                    
                    console.log(`Updated program ${programId} to order ${newOrder} in global state`);
                    
                    // Update badge immediately
                    const container = this.closest('.program-checkbox-container');
                    const orderBadge = container?.querySelector('.program-order-badge');
                    if (orderBadge) {
                        orderBadge.textContent = newOrder;
                    }
                } else {
                    console.log(`Program ${programId} not found in global selections`);
                }
                
                // Debounced validation to check for conflicts
                orderUpdateTimeout = setTimeout(() => {
                    console.log('Running conflict validation after manual order change');
                    updateOrderNumbers();
                }, 800);
            });
            
            // Handle blur for immediate conflict resolution
            input.addEventListener('blur', function() {
                if (orderUpdateTimeout) {
                    clearTimeout(orderUpdateTimeout);
                }
                console.log('Manual order input blur - running conflict validation');
                updateOrderNumbers();
            });
        });
        
        // Function to validate and fix order conflicts without full renumbering
        function validateAndFixOrderConflicts() {
            const selectedPrograms = Array.from(globalProgramSelections.entries())
                .filter(([id, data]) => data.selected)
                .map(([id, data]) => ({ id, data }));
            
            const orderCounts = new Map();
            const conflictedPrograms = [];
            
            // Count how many programs have each order number
            selectedPrograms.forEach(({ id, data }) => {
                const order = data.order;
                if (orderCounts.has(order)) {
                    orderCounts.set(order, orderCounts.get(order) + 1);
                } else {
                    orderCounts.set(order, 1);
                }
            });
            
            // Find programs with conflicting orders
            selectedPrograms.forEach(({ id, data }) => {
                if (orderCounts.get(data.order) > 1) {
                    conflictedPrograms.push(id);
                }
            });
            
            // Only renumber if there are actual conflicts
            if (conflictedPrograms.length > 0) {
                console.log('Order conflicts detected, fixing...', conflictedPrograms);
                updateOrderNumbers();
            }
        }
    }

// Integrated Agency Filtering Functions
    function populateAgencyFilterButtons(programs) {
        if (!agencyFilterContainer) return;
        
        // Extract unique agencies from programs
        const agencies = new Set();
        programs.forEach(program => {
            if (program.agency_name && program.owner_agency_id) {
                agencies.add(JSON.stringify({
                    id: program.owner_agency_id,
                    name: program.agency_name
                }));
            }
        });
        
        // Convert back to objects and sort
        allAvailableAgencies = Array.from(agencies)
            .map(str => JSON.parse(str))
            .sort((a, b) => a.name.localeCompare(b.name));
        
        // Build agency filter buttons HTML
        let buttonsHtml = `
            <button type="button" class="btn btn-outline-primary btn-sm me-1 mb-1 agency-filter-btn active" data-agency-id="all">
                <i class="fas fa-globe me-1"></i>All Agencies
            </button>
        `;
        
        allAvailableAgencies.forEach(agency => {
            buttonsHtml += `
                <button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1 agency-filter-btn" 
                        data-agency-id="${agency.id}" 
                        title="Filter programs from ${agency.name}">
                    <i class="fas fa-building me-1"></i>${agency.name}
                </button>
            `;
        });
        
        agencyFilterContainer.innerHTML = buttonsHtml;
        
        // Attach event listeners to new buttons
        attachAgencyFilterEventListeners();
    }
    
    function attachAgencyFilterEventListeners() {
        const agencyButtons = document.querySelectorAll('.agency-filter-btn');
        agencyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const agencyId = this.dataset.agencyId;
                
                if (agencyId === 'all') {
                    // Clear all agency filters
                    selectedAgencyIds = [];
                    agencyButtons.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                } else {
                    // De-select the 'All' button if a specific agency is chosen
                    document.querySelector('.agency-filter-btn[data-agency-id="all"]').classList.remove('active');

                    // Toggle agency selection
                    const index = selectedAgencyIds.indexOf(agencyId);
                    if (index > -1) {
                        selectedAgencyIds.splice(index, 1);
                        this.classList.remove('active');
                    } else {
                        selectedAgencyIds.push(agencyId);
                        this.classList.add('active');
                    }
                    
                    // If no specific agency is selected, re-select the 'All' button
                    if (selectedAgencyIds.length === 0) {
                        document.querySelector('.agency-filter-btn[data-agency-id="all"]').classList.add('active');
                    }
                }
                
                // Apply filters
                applyAllFilters();
            });
        });
    }
    
    function getSelectedAgencyIds() {
        return selectedAgencyIds.length > 0 ? selectedAgencyIds : [];
    }

    
    // Form submission handler
    const reportForm = document.getElementById('reportGenerationForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const selectedPrograms = getSelectedPrograms();
            const selectedTargets = getSelectedTargets();
            
            // Add selected programs with their order
            selectedPrograms.forEach((program, index) => {
                formData.append(`selected_programs[${index}][program_id]`, program.program_id);
                formData.append(`selected_programs[${index}][order]`, program.order || index + 1);
            });
            
            // Add selected targets
            for (const [programId, targetIds] of Object.entries(selectedTargets)) {
                targetIds.forEach((targetId, index) => {
                    formData.append(`selected_targets[${programId}][${index}]`, targetId);
                });
            }
            
            // Log for debugging
            console.log('Form submission data:');
            console.log('Selected programs:', selectedPrograms);
            console.log('Selected targets:', selectedTargets);
            
            // Show loading state
            const submitBtn = document.getElementById('generatePptxBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
            
            // Submit to report generation endpoint
            fetch(`${APP_URL}/app/api/generate_report.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Report generated successfully!', 'success');
                    
                    // Reset form if needed
                    // this.reset();
                    // clearAllProgramSelections();
                    // hideTargetSelection();
                    
                    // Optionally redirect or refresh reports list
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.error || 'Failed to generate report');
                }
            })
            .catch(error => {
                console.error('Error generating report:', error);
                showNotification('Error generating report: ' + error.message, 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Function to get selected programs data
    function getSelectedPrograms() {
        const selectedPrograms = [];
        for (const [programId, data] of globalProgramSelections) {
            if (data.selected) {
                selectedPrograms.push({
                    program_id: programId,
                    order: data.order,
                    agency: data.agency,
                    program_name: data.program_name,
                    program_number: data.program_number
                });
            }
        }
        
        // Sort by order
        selectedPrograms.sort((a, b) => (a.order || 0) - (b.order || 0));
        return selectedPrograms;
    }

}); // End of DOMContentLoaded

// Modern Dashboard Layout JavaScript Functions
    
    // Toggle Generate Report Section
    function setupGenerateReportToggle() {
        const toggleBtn = document.getElementById('generateReportToggle');
        const toggleBtnEmpty = document.getElementById('generateReportToggleEmpty');
        const generateSection = document.getElementById('generateReportSection');
        const closeBtn = document.getElementById('closeGenerateForm');
        
        function showGenerateForm() {
            if (generateSection) {
                generateSection.style.display = 'block';
                generateSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        
        function hideGenerateForm() {
            if (generateSection) {
                generateSection.style.display = 'none';
                // Scroll back to top smoothly
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
        
        // Event listeners
        if (toggleBtn) {
            toggleBtn.addEventListener('click', showGenerateForm);
        }
        
        if (toggleBtnEmpty) {
            toggleBtnEmpty.addEventListener('click', showGenerateForm);
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', hideGenerateForm);
        }
        
        // Close on successful report generation
        window.addEventListener('reportGenerated', hideGenerateForm);
    }
      // Report Search Functionality
    function initReportSearch() {
        const searchInput = document.getElementById('reportSearch');
        const clearButton = document.getElementById('clearSearch');
        const reportsContainer = document.getElementById('recentReportsContainer');
        
        if (!searchInput || !reportsContainer) return;
        
        let searchTimeout;
        
        // Debounced search function
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const reportCards = reportsContainer.querySelectorAll('.report-card');
            let visibleCount = 0;
            
            // Show/hide clear button
            clearButton.style.display = searchTerm ? 'block' : 'none';
            
            if (!searchTerm) {
                // Show all reports
                reportCards.forEach(card => {
                    card.style.display = 'block';
                    card.classList.remove('search-highlight');
                });
                removeSearchInfo();
                return;
            }
            
            // Filter reports
            reportCards.forEach(card => {
                const reportName = card.querySelector('.report-title')?.textContent.toLowerCase() || '';
                const periodText = card.querySelector('.period-badge')?.textContent.toLowerCase() || '';
                const dateText = card.querySelector('.date-badge')?.textContent.toLowerCase() || '';
                
                const matchesSearch = reportName.includes(searchTerm) || 
                                    periodText.includes(searchTerm) || 
                                    dateText.includes(searchTerm);
                
                if (matchesSearch) {
                    card.style.display = 'block';
                    card.classList.add('search-highlight');
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                    card.classList.remove('search-highlight');
                }
            });
            
            // Show search results info
            showSearchInfo(visibleCount, searchTerm);
        }
        
        function showSearchInfo(count, term) {
            removeSearchInfo();
            
            const infoDiv = document.createElement('div');
            infoDiv.className = 'search-results-info';
            infoDiv.id = 'searchResultsInfo';
            
            if (count === 0) {
                infoDiv.innerHTML = `
                    <div class="search-no-results">
                        <i class="fas fa-search"></i>
                        <h6>No reports found</h6>
                        <p>No reports match "${term}". Try a different search term.</p>
                    </div>
                `;
                infoDiv.className = 'search-no-results';
            } else {
                infoDiv.innerHTML = `
                    <i class="fas fa-filter me-2"></i>
                    Found ${count} report${count !== 1 ? 's' : ''} matching "${term}"
                `;
            }
            
            reportsContainer.insertBefore(infoDiv, reportsContainer.firstChild);
        }
        
        function removeSearchInfo() {
            const existingInfo = document.getElementById('searchResultsInfo');
            if (existingInfo) {
                existingInfo.remove();
            }
        }
        
        function clearSearch() {
            searchInput.value = '';
            performSearch();
            searchInput.focus();
        }
        
        // Event listeners
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 300); // 300ms debounce
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                clearSearch();
            }
        });
        
        clearButton.addEventListener('click', clearSearch);
        
        // Focus search on Ctrl+F (when not in other inputs)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f' && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });
    }
    
    // Make search initialization globally available
    window.initReportSearch = initReportSearch;

    // NEW Badge Management
    function initNewReportBadges() {
        // Clean up expired new reports from localStorage
        cleanupExpiredNewReports();
        
        // Apply badges to reports from localStorage
        applyNewReportBadges();
        
        // Set up automatic badge cleanup
        setTimeout(cleanupExpiredNewReports, 60000); // Check every minute
    }
    
    function addNewReportToTracker(reportId) {
        try {
            const newReports = getNewReportsFromStorage();
            const timestamp = Date.now();
            
            newReports[reportId] = timestamp;
            localStorage.setItem('pcds_new_reports', JSON.stringify(newReports));
            
            console.log('Added new report to tracker:', reportId);
        } catch (error) {
            console.error('Error adding new report to tracker:', error);
        }
    }
    
    function getNewReportsFromStorage() {
        try {
            const stored = localStorage.getItem('pcds_new_reports');
            return stored ? JSON.parse(stored) : {};
        } catch (error) {
            console.error('Error reading new reports from storage:', error);
            return {};
        }
    }
    
    function cleanupExpiredNewReports() {
        try {
            const newReports = getNewReportsFromStorage();
            const currentTime = Date.now();
            const tenMinutes = 10 * 60 * 1000; // 10 minutes in milliseconds
            
            let hasChanges = false;
            
            // Remove expired entries
            Object.keys(newReports).forEach(reportId => {
                if (currentTime - newReports[reportId] > tenMinutes) {
                    delete newReports[reportId];
                    hasChanges = true;
                    
                    // Also remove badge from DOM if present
                    const reportCard = document.querySelector(`[data-report-id="${reportId}"]`);
                    if (reportCard) {
                        const badge = reportCard.querySelector('.new-report-badge');
                        if (badge) {
                            badge.classList.add('fade-out');
                            setTimeout(() => badge.remove(), 1000);
                        }
                    }
                }
            });
            
            if (hasChanges) {
                localStorage.setItem('pcds_new_reports', JSON.stringify(newReports));
                console.log('Cleaned up expired new reports');
            }
        } catch (error) {
            console.error('Error cleaning up expired new reports:', error);
        }
    }
    
    function applyNewReportBadges() {
        try {
            const newReports = getNewReportsFromStorage();
            
            Object.keys(newReports).forEach(reportId => {
                const reportCard = document.querySelector(`[data-report-id="${reportId}"]`);
                if (reportCard && !reportCard.querySelector('.new-report-badge')) {
                    // Create and add badge if it doesn't exist
                    const badge = document.createElement('span');
                    badge.className = 'new-report-badge';
                    badge.textContent = 'NEW';
                    reportCard.appendChild(badge);
                }
            });
        } catch (error) {
            console.error('Error applying new report badges:', error);
        }
    }
    
    // Make functions globally available
    window.addNewReportToTracker = addNewReportToTracker;
    window.initNewReportBadges = initNewReportBadges;    // Enhanced Recent Reports Refresh
    function refreshRecentReports() {
        // Use the new pagination system if available
        if (typeof window.reportsPagination !== 'undefined' && window.reportsPagination.refresh) {
            return Promise.resolve(window.reportsPagination.refresh());
        }
        
        // Call the proper refresh function from ReportAPI
        if (typeof ReportAPI !== 'undefined' && ReportAPI.refreshReportsTable) {
            return ReportAPI.refreshReportsTable();
        } else {
            // Fallback for backwards compatibility
            const container = document.getElementById('recentReportsContainer');
            if (!container) return Promise.resolve();
            
            // Show loading state
            container.classList.add('loading');
            
            // Simple reload after a short delay
            return new Promise((resolve) => {
                setTimeout(() => {
                    container.classList.remove('loading');
                    console.log('Recent reports refreshed (fallback)');
                    resolve();
                }, 1000);
            });
        }
    }
      // Make function globally available for dynamic content
    window.setupGenerateReportToggle = setupGenerateReportToggle;    // Initialize modern dashboard features
    function initModernDashboard() {
        setupGenerateReportToggle();
        initNewReportBadges(); // Initialize badge system
        initReportSearch(); // Initialize search functionality
        
        // Add click handlers for report cards
        const reportCards = document.querySelectorAll('.report-card');
        reportCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on action buttons
                if (e.target.closest('.report-actions')) return;
                
                // You could add card interaction here
                console.log('Report card clicked');
            });
        });
        
        // Enhanced delete button handling
        const deleteButtons = document.querySelectorAll('.delete-report-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent card click
                const reportCard = this.closest('.report-card');
                if (reportCard) {
                    reportCard.classList.add('deleting');
                }
            });
        });
    }
    
    // Initialize when DOM is ready
    initModernDashboard();
    
    // Handle "Generate Another" button click
    document.getElementById('generateAnotherBtn')?.addEventListener('click', function() {
        // Hide success message
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.classList.add('d-none');
        }
        
        // Reset form
        const form = document.getElementById('reportGenerationForm');
        if (form) {
            form.reset();
        }
        
        // Clear program selections
        window.clearAllProgramSelections();
        
        // Scroll to top of generate form
        const generateSection = document.getElementById('generateReportSection');
        if (generateSection) {
            generateSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Refresh recent reports to show the new report
        refreshRecentReports();
    });