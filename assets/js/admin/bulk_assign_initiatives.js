/**
 * Bulk Initiative Assignment JavaScript
 * Handles the bulk assignment of programs to initiatives
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initializeFiltering();
    initializeSelection();
    initializeBulkAssignment();
});

/**
 * Initialize filtering functionality
 */
function initializeFiltering() {
    const searchInput = document.getElementById('programSearch');
    const initiativeFilter = document.getElementById('initiativeFilter');
    const resetButton = document.getElementById('resetFilters');
    
    // Add event listeners
    searchInput?.addEventListener('input', filterPrograms);
    initiativeFilter?.addEventListener('change', filterPrograms);
    resetButton?.addEventListener('click', resetFilters);
}

/**
 * Initialize selection functionality
 */
function initializeSelection() {
    const selectAllVisible = document.getElementById('selectAllVisible');
    const selectAllPrograms = document.getElementById('selectAllPrograms');
    const clearSelectionBtn = document.getElementById('clearSelection');
    
    // Select all visible programs
    selectAllVisible?.addEventListener('change', function() {
        const visibleCheckboxes = document.querySelectorAll('.program-checkbox:not([data-filtered="true"])');
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedPrograms();
    });
    
    // Select all programs (including filtered)
    selectAllPrograms?.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.program-checkbox');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        selectAllVisible.checked = this.checked;
        updateSelectedPrograms();
    });
    
    // Clear selection
    clearSelectionBtn?.addEventListener('click', function() {
        const allCheckboxes = document.querySelectorAll('.program-checkbox');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllVisible.checked = false;
        selectAllPrograms.checked = false;
        updateSelectedPrograms();
    });
    
    // Add event listeners to individual checkboxes
    const programCheckboxes = document.querySelectorAll('.program-checkbox');
    programCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedPrograms);
    });
}

/**
 * Initialize bulk assignment functionality
 */
function initializeBulkAssignment() {
    const form = document.getElementById('bulkAssignForm');
    const initiativeSelect = document.getElementById('initiativeSelect');
    const assignButton = document.getElementById('assignButton');
    
    // Update button state when initiative is selected
    initiativeSelect?.addEventListener('change', function() {
        updateAssignButtonState();
    });
    
    // Handle form submission
    form?.addEventListener('submit', function(e) {
        const selectedPrograms = getSelectedPrograms();
        const selectedInitiative = initiativeSelect.value;
        
        if (selectedPrograms.length === 0) {
            e.preventDefault();
            alert('Please select at least one program.');
            return;
        }
        
        if (!selectedInitiative) {
            e.preventDefault();
            alert('Please select an initiative.');
            return;
        }
        
        // Confirm the action
        const initiativeText = initiativeSelect.options[initiativeSelect.selectedIndex].text;
        const confirmMessage = selectedInitiative === 'remove' 
            ? `Are you sure you want to remove initiative assignments from ${selectedPrograms.length} program(s)?`
            : `Are you sure you want to assign ${selectedPrograms.length} program(s) to "${initiativeText}"?`;
            
        if (!confirm(confirmMessage)) {
            e.preventDefault();
        }
    });
}

/**
 * Filter programs based on search and filter criteria
 */
function filterPrograms() {
    const searchValue = document.getElementById('programSearch')?.value.toLowerCase() || '';
    const initiativeValue = document.getElementById('initiativeFilter')?.value || '';
    
    const rows = document.querySelectorAll('#programsTable tbody tr');
    
    rows.forEach(row => {
        const programName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const initiativeId = row.getAttribute('data-initiative-id') || '';
        
        let visible = true;
        
        // Search filter
        if (searchValue && !programName.includes(searchValue)) {
            visible = false;
        }
        
        // Initiative filter
        if (initiativeValue) {
            if (initiativeValue === 'none' && initiativeId !== '') {
                visible = false;
            } else if (initiativeValue !== 'none' && initiativeId !== initiativeValue) {
                visible = false;
            }
        }
        
        // Show/hide row
        row.style.display = visible ? '' : 'none';
        row.querySelector('.program-checkbox').setAttribute('data-filtered', !visible);
    });
    
    // Update select all checkbox state
    updateSelectAllState();
}

/**
 * Reset all filters
 */
function resetFilters() {
    document.getElementById('programSearch').value = '';
    document.getElementById('initiativeFilter').value = '';
    
    // Show all rows
    const rows = document.querySelectorAll('#programsTable tbody tr');
    rows.forEach(row => {
        row.style.display = '';
        row.querySelector('.program-checkbox').removeAttribute('data-filtered');
    });
    
    updateSelectAllState();
}

/**
 * Update the selected programs display
 */
function updateSelectedPrograms() {
    const selectedPrograms = getSelectedPrograms();
    const selectedCount = document.getElementById('selectedCount');
    const selectedProgramsDiv = document.getElementById('selectedPrograms');
    
    // Update count
    selectedCount.textContent = `${selectedPrograms.length} selected`;
    
    // Update selected programs list
    if (selectedPrograms.length === 0) {
        selectedProgramsDiv.innerHTML = '<p class="text-muted text-center py-3">No programs selected</p>';
    } else {
        let html = '<div class="selected-program-tags">';
        selectedPrograms.forEach((program, index) => {
            if (index < 10) { // Show only first 10
                html += `
                    <span class="badge bg-light text-dark me-1 mb-1">
                        ${escapeHtml(program.name)}
                        <i class="fas fa-times ms-1" style="cursor: pointer;" onclick="deselectProgram(${program.id})"></i>
                    </span>
                `;
            }
        });
        
        if (selectedPrograms.length > 10) {
            html += `<span class="badge bg-secondary">+${selectedPrograms.length - 10} more</span>`;
        }
        
        html += '</div>';
        selectedProgramsDiv.innerHTML = html;
    }
    
    // Update button state
    updateAssignButtonState();
    updateSelectAllState();
}

/**
 * Get selected programs data
 */
function getSelectedPrograms() {
    const selectedPrograms = [];
    const checkboxes = document.querySelectorAll('.program-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const nameCell = row.querySelector('td:nth-child(2)');
        const programName = nameCell.textContent.replace(/^\s*\d+\s*/, '').trim(); // Remove badge number
        
        selectedPrograms.push({
            id: checkbox.value,
            name: programName
        });
    });
    
    return selectedPrograms;
}

/**
 * Deselect a specific program
 */
function deselectProgram(programId) {
    const checkbox = document.querySelector(`.program-checkbox[value="${programId}"]`);
    if (checkbox) {
        checkbox.checked = false;
        updateSelectedPrograms();
    }
}

/**
 * Update the assign button state
 */
function updateAssignButtonState() {
    const selectedPrograms = getSelectedPrograms();
    const initiativeSelect = document.getElementById('initiativeSelect');
    const assignButton = document.getElementById('assignButton');
    
    const hasSelection = selectedPrograms.length > 0;
    const hasInitiative = initiativeSelect?.value !== '';
    
    assignButton.disabled = !(hasSelection && hasInitiative);
}

/**
 * Update select all checkboxes state
 */
function updateSelectAllState() {
    const selectAllVisible = document.getElementById('selectAllVisible');
    const selectAllPrograms = document.getElementById('selectAllPrograms');
    
    const visibleCheckboxes = document.querySelectorAll('.program-checkbox:not([data-filtered="true"])');
    const allCheckboxes = document.querySelectorAll('.program-checkbox');
    const checkedVisible = document.querySelectorAll('.program-checkbox:not([data-filtered="true"]):checked');
    const checkedAll = document.querySelectorAll('.program-checkbox:checked');
    
    // Update select all visible
    if (visibleCheckboxes.length === 0) {
        selectAllVisible.indeterminate = false;
        selectAllVisible.checked = false;
    } else if (checkedVisible.length === visibleCheckboxes.length) {
        selectAllVisible.indeterminate = false;
        selectAllVisible.checked = true;
    } else if (checkedVisible.length > 0) {
        selectAllVisible.indeterminate = true;
        selectAllVisible.checked = false;
    } else {
        selectAllVisible.indeterminate = false;
        selectAllVisible.checked = false;
    }
    
    // Update select all programs
    if (checkedAll.length === allCheckboxes.length && allCheckboxes.length > 0) {
        selectAllPrograms.indeterminate = false;
        selectAllPrograms.checked = true;
    } else if (checkedAll.length > 0) {
        selectAllPrograms.indeterminate = true;
        selectAllPrograms.checked = false;
    } else {
        selectAllPrograms.indeterminate = false;
        selectAllPrograms.checked = false;
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
