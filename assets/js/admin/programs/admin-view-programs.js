/**
 * Admin View Programs JavaScript
 * Functionality for admin programs overview page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize program filtering
    initializeProgramFiltering();
    
    // Initialize dropdown toggles
    initializeDropdownToggles();
    
    // Initialize tooltips
    initializeTooltips();
});

/**
 * Initialize program filtering functionality
 */
function initializeProgramFiltering() {
    const filterPrefix = 'admin';
    const programsContainer = document.getElementById('adminProgramsContainer');
    
    if (!programsContainer) return;
    
    // Set up filter event listeners
    const agencyFilter = document.getElementById(`${filterPrefix}-agency-filter`);
    const initiativeFilter = document.getElementById(`${filterPrefix}-initiative-filter`);
    const statusFilter = document.getElementById(`${filterPrefix}-status-filter`);
    const searchInput = document.getElementById(`${filterPrefix}-search`);
    
    if (agencyFilter) agencyFilter.addEventListener('change', () => filterPrograms(filterPrefix));
    if (initiativeFilter) initiativeFilter.addEventListener('change', () => filterPrograms(filterPrefix));
    if (statusFilter) statusFilter.addEventListener('change', () => filterPrograms(filterPrefix));
    if (searchInput) searchInput.addEventListener('input', debounce(() => filterPrograms(filterPrefix), 300));
}

/**
 * Filter programs based on selected criteria
 */
function filterPrograms(filterPrefix) {
    const agencyFilter = document.getElementById(`${filterPrefix}-agency-filter`);
    const initiativeFilter = document.getElementById(`${filterPrefix}-initiative-filter`);
    const statusFilter = document.getElementById(`${filterPrefix}-status-filter`);
    const searchInput = document.getElementById(`${filterPrefix}-search`);
    
    if (!agencyFilter || !initiativeFilter || !statusFilter || !searchInput) return;
    
    const selectedAgency = agencyFilter.value;
    const selectedInitiative = initiativeFilter.value;
    const selectedStatus = statusFilter.value;
    const searchTerm = searchInput.value.toLowerCase();
    
    const programBoxes = document.querySelectorAll('.admin-program');
    let visibleCount = 0;
    
    programBoxes.forEach(box => {
        const agencyId = box.dataset.agencyId;
        const agencyName = box.dataset.agencyName ? box.dataset.agencyName.toLowerCase() : '';
        const initiativeId = box.dataset.initiativeId;
        const status = box.dataset.status;
        const programName = box.querySelector('.program-name')?.textContent.toLowerCase() || '';
        
        let visible = true;
        
        // Agency filter
        if (selectedAgency && agencyId !== selectedAgency) {
            visible = false;
        }
        
        // Initiative filter
        if (selectedInitiative && initiativeId !== selectedInitiative) {
            visible = false;
        }
        
        // Status filter
        if (selectedStatus && status !== selectedStatus) {
            visible = false;
        }
        
        // Search filter
        if (searchTerm && !programName.includes(searchTerm) && !agencyName.includes(searchTerm)) {
            visible = false;
        }
        
        box.style.display = visible ? 'block' : 'none';
        if (visible) visibleCount++;
    });
    
    // Update counter
    updateFilterSummary(filterPrefix, visibleCount, programBoxes.length);
}

/**
 * Update filter summary display
 */
function updateFilterSummary(filterPrefix, visibleCount, totalCount) {
    const summary = document.getElementById(`${filterPrefix}-filter-summary`);
    const clearButton = document.getElementById(`${filterPrefix}-clear-filters`);
    
    if (summary) {
        if (visibleCount === totalCount) {
            summary.textContent = `Showing all ${totalCount} programs`;
            if (clearButton) clearButton.style.display = 'none';
        } else {
            summary.textContent = `Showing ${visibleCount} of ${totalCount} programs`;
            if (clearButton) clearButton.style.display = 'inline';
        }
    }
}

/**
 * Clear all filters
 */
function clearAllFilters(filterPrefix) {
    const agencyFilter = document.getElementById(`${filterPrefix}-agency-filter`);
    const initiativeFilter = document.getElementById(`${filterPrefix}-initiative-filter`);
    const statusFilter = document.getElementById(`${filterPrefix}-status-filter`);
    const searchInput = document.getElementById(`${filterPrefix}-search`);
    
    if (agencyFilter) agencyFilter.value = '';
    if (initiativeFilter) initiativeFilter.value = '';
    if (statusFilter) statusFilter.value = '';
    if (searchInput) searchInput.value = '';
    
    filterPrograms(filterPrefix);
}

/**
 * Clear search input
 */
function clearSearch(filterPrefix) {
    const searchInput = document.getElementById(`${filterPrefix}-search`);
    if (searchInput) {
        searchInput.value = '';
        filterPrograms(filterPrefix);
    }
}

/**
 * Initialize dropdown toggles for program actions
 */
function initializeDropdownToggles() {
    window.toggleDropdown = function(button) {
        const dropdown = button.nextElementSibling;
        if (!dropdown) return;
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu-custom').forEach(menu => {
            if (menu !== dropdown) {
                menu.style.display = 'none';
            }
        });
        
        // Toggle current dropdown
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    };
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-info')) {
            document.querySelectorAll('.dropdown-menu-custom').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Refresh programs list
 */
function refreshPrograms() {
    location.reload();
}

/**
 * Export programs functionality
 */
function exportPrograms() {
    // Placeholder for export functionality
    if (typeof showToast === 'function') {
        showToast('Info', 'Export functionality will be implemented soon.', 'info');
    } else {
        alert('Export functionality will be implemented soon.');
    }
}

/**
 * Utility function to debounce function calls
 */
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

// Make functions globally available
window.filterPrograms = filterPrograms;
window.clearAllFilters = clearAllFilters;
window.clearSearch = clearSearch;
window.refreshPrograms = refreshPrograms;
window.exportPrograms = exportPrograms;