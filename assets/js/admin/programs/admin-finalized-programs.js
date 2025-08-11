/**
 * Admin Finalized Programs JavaScript
 * Functionality for admin programs overview page filtering and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize program filtering
    initializeFinalizedProgramFiltering();
    
    // Initialize dropdown toggles
    initializeAdminDropdownToggles();
    
    // Initialize reset button
    initializeResetButton();
});

/**
 * Initialize finalized program filtering functionality
 */
function initializeFinalizedProgramFiltering() {
    const programsContainer = document.getElementById('finalizedProgramsContainer');
    
    if (!programsContainer) return;
    
    // Set up filter event listeners
    const searchInput = document.getElementById('finalizedProgramSearch');
    const ratingFilter = document.getElementById('finalizedRatingFilter');
    const agencyFilter = document.getElementById('finalizedAgencyFilter');
    const initiativeFilter = document.getElementById('finalizedInitiativeFilter');
    
    if (searchInput) searchInput.addEventListener('input', debounce(() => filterFinalizedPrograms(), 300));
    if (ratingFilter) ratingFilter.addEventListener('change', () => filterFinalizedPrograms());
    if (agencyFilter) agencyFilter.addEventListener('change', () => filterFinalizedPrograms());
    if (initiativeFilter) initiativeFilter.addEventListener('change', () => filterFinalizedPrograms());
}

/**
 * Filter finalized programs based on selected criteria
 */
function filterFinalizedPrograms() {
    const searchInput = document.getElementById('finalizedProgramSearch');
    const ratingFilter = document.getElementById('finalizedRatingFilter');
    const agencyFilter = document.getElementById('finalizedAgencyFilter');
    const initiativeFilter = document.getElementById('finalizedInitiativeFilter');
    
    if (!searchInput || !ratingFilter || !agencyFilter || !initiativeFilter) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const selectedRating = ratingFilter.value;
    const selectedAgency = agencyFilter.value;
    const selectedInitiative = initiativeFilter.value;
    
    const programBoxes = document.querySelectorAll('.admin-program-box');
    let visibleCount = 0;
    
    programBoxes.forEach(box => {
        const rating = box.dataset.rating;
        const agencyId = box.dataset.agencyId;
        const initiativeId = box.dataset.initiativeId;
        
        // Get program name and number for search
        const programNameEl = box.querySelector('.admin-program-name');
        const programNumberEl = box.querySelector('.admin-program-number');
        const programName = programNameEl ? programNameEl.textContent.toLowerCase() : '';
        const programNumber = programNumberEl ? programNumberEl.textContent.toLowerCase() : '';
        
        let visible = true;
        
        // Search filter
        if (searchTerm && 
            !programName.includes(searchTerm) && 
            !programNumber.includes(searchTerm)) {
            visible = false;
        }
        
        // Rating filter
        if (selectedRating && rating !== selectedRating) {
            visible = false;
        }
        
        // Agency filter
        if (selectedAgency && agencyId !== selectedAgency) {
            visible = false;
        }
        
        // Initiative filter
        if (selectedInitiative) {
            if (selectedInitiative === 'no-initiative') {
                if (initiativeId && initiativeId !== '0') {
                    visible = false;
                }
            } else if (initiativeId !== selectedInitiative) {
                visible = false;
            }
        }
        
        box.style.display = visible ? 'block' : 'none';
        if (visible) visibleCount++;
    });
    
    // Update counter
    updateFinalizedCounter(visibleCount);
    
    // Update filter badges
    updateFilterBadges();
}

/**
 * Update the finalized programs counter
 */
function updateFinalizedCounter(visibleCount) {
    const counter = document.getElementById('finalized-count');
    if (counter) {
        counter.textContent = visibleCount;
    }
}

/**
 * Update filter badges display
 */
function updateFilterBadges() {
    const badgesContainer = document.getElementById('finalizedFilterBadges');
    if (!badgesContainer) return;
    
    const searchInput = document.getElementById('finalizedProgramSearch');
    const ratingFilter = document.getElementById('finalizedRatingFilter');
    const agencyFilter = document.getElementById('finalizedAgencyFilter');
    const initiativeFilter = document.getElementById('finalizedInitiativeFilter');
    
    let badges = [];
    
    if (searchInput && searchInput.value) {
        badges.push(`Search: "${searchInput.value}"`);
    }
    
    if (ratingFilter && ratingFilter.value) {
        const selectedOption = ratingFilter.options[ratingFilter.selectedIndex];
        badges.push(`Rating: ${selectedOption.text}`);
    }
    
    if (agencyFilter && agencyFilter.value) {
        const selectedOption = agencyFilter.options[agencyFilter.selectedIndex];
        badges.push(`Agency: ${selectedOption.text}`);
    }
    
    if (initiativeFilter && initiativeFilter.value) {
        const selectedOption = initiativeFilter.options[initiativeFilter.selectedIndex];
        badges.push(`Initiative: ${selectedOption.text}`);
    }
    
    if (badges.length > 0) {
        badgesContainer.innerHTML = badges.map(badge => 
            `<span class="badge bg-primary me-1">${badge}</span>`
        ).join('');
        badgesContainer.style.display = 'block';
    } else {
        badgesContainer.innerHTML = '';
        badgesContainer.style.display = 'none';
    }
}

/**
 * Initialize reset button functionality
 */
function initializeResetButton() {
    const resetButton = document.getElementById('resetFinalizedFilters');
    if (resetButton) {
        resetButton.addEventListener('click', resetAllFinalizedFilters);
    }
}

/**
 * Reset all finalized program filters
 */
function resetAllFinalizedFilters() {
    const searchInput = document.getElementById('finalizedProgramSearch');
    const ratingFilter = document.getElementById('finalizedRatingFilter');
    const agencyFilter = document.getElementById('finalizedAgencyFilter');
    const initiativeFilter = document.getElementById('finalizedInitiativeFilter');
    
    if (searchInput) searchInput.value = '';
    if (ratingFilter) ratingFilter.value = '';
    if (agencyFilter) agencyFilter.value = '';
    if (initiativeFilter) initiativeFilter.value = '';
    
    filterFinalizedPrograms();
}

/**
 * Toggle admin dropdown function - Global function for onclick handlers
 */
function toggleAdminDropdown(button) {
    const dropdown = button.nextElementSibling;
    if (!dropdown) return;
    
    // Close all other dropdowns
    document.querySelectorAll('.admin-dropdown-menu-custom').forEach(menu => {
        if (menu !== dropdown) {
            menu.style.display = 'none';
        }
    });
    
    // Toggle current dropdown
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

/**
 * Initialize dropdown toggles for program actions
 */
function initializeAdminDropdownToggles() {
    // Make the function globally available for onclick handlers
    window.toggleAdminDropdown = toggleAdminDropdown;
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.admin-action-info')) {
            document.querySelectorAll('.admin-dropdown-menu-custom').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
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
window.filterFinalizedPrograms = filterFinalizedPrograms;
window.resetAllFinalizedFilters = resetAllFinalizedFilters;
window.toggleAdminDropdown = toggleAdminDropdown;
