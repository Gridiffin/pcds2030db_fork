/**
 * Report Generator
 * 
 * Main controller for the PPTX report generation functionality.
 * This file coordinates the modules and initializes the report generator.
 */

// Global initialization flag to prevent duplicate initialization
if (typeof reportGeneratorInitialized === 'undefined') {
    var reportGeneratorInitialized = false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations
    if (reportGeneratorInitialized) {
        console.log('Report generator already initialized, skipping duplicate initialization.');
        return;
    }
    reportGeneratorInitialized = true;
    console.log('Initializing report generator...');

    // Program selector logic
    const programSelector = document.getElementById('programSelector');
    const periodSelect = document.getElementById('periodSelect');
    const sectorSelect = document.getElementById('sectorSelect');
    const programContainerElement = programSelector ? programSelector.querySelector('.program-selector-container') : null;
    
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Please select a reporting period to load available programs.
                    </div>
                `;
            }
            return;
        }
        
        // Show loading indicator
        showProgramsLoading();
        
        // Fetch programs for this period
        const url = `../../api/get_period_programs.php?period_id=${periodId}${sectorId ? '&sector_id=' + sectorId : ''}`;
        
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
        if (!programContainerElement) return;
        
        // If there are no programs
        if (Object.keys(programs).length === 0) {
            programContainerElement.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No programs available for the selected period.
                </div>
            `;
            return;
        }
        
        // Build HTML for programs
        let html = `
            <div class="pb-2 mb-2 border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 program-selection-title">Programs <span id="programCount" class="badge bg-primary">0</span></h6>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPrograms">
                            <i class="fas fa-check-square me-1"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllPrograms">
                            <i class="fas fa-square me-1"></i> Deselect All
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add each sector's programs
        for (const sectorId in programs) {
            const sectorData = programs[sectorId];
            const isVisible = !selectedSectorId || sectorId === selectedSectorId;
            
            html += `
                <div class="sector-programs mb-2" data-sector-id="${sectorId}" style="display: ${isVisible ? 'block' : 'none'}">
                    <h6 class="sector-name fw-bold ms-2 mb-1">${sectorData.sector_name}</h6>
                    <div class="ms-3">
            `;
            
            // Add each program
            if (sectorData.programs && sectorData.programs.length > 0) {
                sectorData.programs.forEach(program => {
                    html += `
                        <div class="form-check">
                            <input class="form-check-input program-checkbox" type="checkbox" name="selected_program_ids[]" value="${program.program_id}" id="program_${program.program_id}">
                            <label class="form-check-label" for="program_${program.program_id}">
                                ${program.program_name}
                            </label>
                        </div>
                    `;
                });
            } else {
                html += `<p class="text-muted">No programs available for this sector.</p>`;
            }
            
            html += `
                    </div>
                </div>
            `;
        }
        
        // Update the UI
        programContainerElement.innerHTML = html;
        
        // Re-initialize the buttons
        initializeSelectButtons();
        
        // Update the count
        updateProgramCount();
    }
      // Filter programs by sector
    function filterProgramsBySector(selectedSectorId) {
        const sectorPrograms = programSelector.querySelectorAll('.sector-programs');
        
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
    }
    
    // Initialize the UI
    if (typeof ReportUI !== 'undefined') {
        ReportUI.initUI();
        
        // Initialize Select All / Deselect All buttons
        function initializeSelectButtons() {
            const selectAllBtn = document.getElementById('selectAllPrograms');
            const deselectAllBtn = document.getElementById('deselectAllPrograms');
            
            if (selectAllBtn && deselectAllBtn) {
                // Select all visible programs
                selectAllBtn.addEventListener('click', function() {
                    const visibleSectors = document.querySelectorAll('.sector-programs:not([style*="display: none"])');
                    visibleSectors.forEach(sector => {
                        const checkboxes = sector.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = true;
                        });
                    });
                    updateProgramCount();
                });
                
                // Deselect all visible programs
                deselectAllBtn.addEventListener('click', function() {
                    const visibleSectors = document.querySelectorAll('.sector-programs:not([style*="display: none"])');
                    visibleSectors.forEach(sector => {
                        const checkboxes = sector.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = false;
                        });
                    });
                    updateProgramCount();
                });
            }
            
            // Add change event listeners to all program checkboxes
            const programCheckboxes = document.querySelectorAll('#programSelector input[name="selected_program_ids[]"]');
            programCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateProgramCount);
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
                if (periodSelect.value) {
                    // If a period is selected, reload programs with the new sector filter
                    loadPrograms();
                } else {
                    // Otherwise just filter existing programs
                    filterProgramsBySector(this.value);
                }
            });
        }
        
        // Show default state in program selector
        if (programContainerElement) {
            programContainerElement.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please select a reporting period to view available programs.
                </div>
            `;
        }
    } else {
        console.error('ReportUI module not found. Make sure report-ui.js is loaded before report-generator.js');
    }
});