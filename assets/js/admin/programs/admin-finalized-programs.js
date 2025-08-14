/**
 * Admin Finalized Programs JavaScript
 * Functionality for admin programs overview page filtering and interactions
 */

// Pagination configuration
let currentPage = 1;
let itemsPerPage = 10;
let allPrograms = [];
let filteredPrograms = [];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize program filtering
    initializeFinalizedProgramFiltering();
    
    // Initialize dropdown toggles
    initializeAdminDropdownToggles();
    
    // Initialize reset button
    initializeResetButton();
    
    // Initialize pagination
    initializePagination();
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
    if (ratingFilter) {
        ratingFilter.addEventListener('change', () => {
            filterFinalizedPrograms();
        });
    }
    if (agencyFilter) agencyFilter.addEventListener('change', () => filterFinalizedPrograms());
    if (initiativeFilter) initiativeFilter.addEventListener('change', () => filterFinalizedPrograms());
}

/**
 * Initialize pagination system
 */
function initializePagination() {
    // Store all program elements
    allPrograms = Array.from(document.querySelectorAll('.admin-program-box'));
    filteredPrograms = [...allPrograms];
    
    // Initial display
    updatePaginatedDisplay();
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
    
    // Filter programs based on criteria
    filteredPrograms = allPrograms.filter(box => {
        const rating = box.dataset.rating;
        const agencyId = box.dataset.agencyId;
        const initiativeId = box.dataset.initiativeId;
        
        // Get program name and number for search
        const programNameEl = box.querySelector('.admin-program-name');
        const programNumberEl = box.querySelector('.admin-program-number');
        const programName = programNameEl ? programNameEl.textContent.toLowerCase() : '';
        const programNumber = programNumberEl ? programNumberEl.textContent.toLowerCase() : '';
        
        // Search filter
        if (searchTerm && 
            !programName.includes(searchTerm) && 
            !programNumber.includes(searchTerm)) {
            return false;
        }
        
        // Rating filter
        if (selectedRating && rating !== selectedRating) {
            return false;
        }
        
        // Agency filter
        if (selectedAgency && agencyId !== selectedAgency) {
            return false;
        }
        
        // Initiative filter
        if (selectedInitiative) {
            if (selectedInitiative === 'no-initiative') {
                if (initiativeId && initiativeId !== '0') {
                    return false;
                }
            } else if (initiativeId !== selectedInitiative) {
                return false;
            }
        }
        
        return true;
    });
    
    // Reset to first page when filtering
    currentPage = 1;
    
    // Update display with loading for significant filter changes
    const hasFilters = searchTerm || selectedRating || selectedAgency || selectedInitiative;
    updatePaginatedDisplay(hasFilters);
    
    // Update counter
    updateFinalizedCounter(filteredPrograms.length);
    
    // Update filter badges
    updateFilterBadges();
}

/**
 * Update paginated display of programs with smooth loading
 */
function updatePaginatedDisplay(showLoading = false) {
    if (showLoading) {
        showLoadingState();
        
        // Simulate loading delay for smooth UX
        setTimeout(() => {
            performPaginationUpdate();
            hideLoadingState();
        }, 400);
    } else {
        performPaginationUpdate();
    }
}

/**
 * Perform the actual pagination update
 */
function performPaginationUpdate() {
    // Add fade-out effect to current programs
    const visiblePrograms = Array.from(document.querySelectorAll('.admin-program-box[style*="block"]'));
    visiblePrograms.forEach(box => {
        box.classList.add('fade-out');
    });
    
    // After fade-out, update the display
    setTimeout(() => {
        // Hide all programs first
        allPrograms.forEach(box => {
            box.style.display = 'none';
            box.classList.remove('fade-out', 'fade-in');
        });
        
        // Calculate pagination
        const totalPages = Math.ceil(filteredPrograms.length / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        
        // Show programs for current page with fade-in effect
        const programsToShow = filteredPrograms.slice(startIndex, endIndex);
        programsToShow.forEach((box, index) => {
            box.style.display = 'block';
            // Stagger the fade-in animation slightly
            setTimeout(() => {
                box.classList.add('fade-in');
            }, index * 50);
        });
        
        // Update pagination controls
        updatePaginationControls(totalPages);
    }, 150); // Wait for fade-out to complete
}

/**
 * Show loading state
 */
function showLoadingState() {
    const loadingOverlay = document.getElementById('paginationLoadingOverlay');
    const progressBar = document.getElementById('loadingProgressBar');
    
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
        
        // Animate progress bar
        if (progressBar) {
            progressBar.style.width = '0%';
            setTimeout(() => progressBar.style.width = '30%', 50);
            setTimeout(() => progressBar.style.width = '60%', 150);
            setTimeout(() => progressBar.style.width = '90%', 250);
            setTimeout(() => progressBar.style.width = '100%', 350);
        }
    }
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    const loadingOverlay = document.getElementById('paginationLoadingOverlay');
    const progressBar = document.getElementById('loadingProgressBar');
    
    if (loadingOverlay) {
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
            if (progressBar) {
                progressBar.style.width = '0%';
            }
        }, 100);
    }
}

/**
 * Update pagination controls
 */
function updatePaginationControls(totalPages) {
    const paginationContainer = document.getElementById('finalizedPaginationContainer');
    const pagination = document.getElementById('finalizedPagination');
    
    if (!paginationContainer || !pagination) return;
    
    // Show/hide pagination based on number of pages
    if (totalPages <= 1) {
        paginationContainer.style.display = 'none';
        return;
    }
    
    paginationContainer.style.display = 'flex';
    
    // Clear existing pagination
    pagination.innerHTML = '';
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = currentPage === 1 
        ? '<span class="page-link">Previous</span>'
        : '<a class="page-link" href="#" onclick="event.preventDefault(); goToPage(' + (currentPage - 1) + ')">Previous</a>';
    pagination.appendChild(prevLi);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = i === currentPage
            ? `<span class="page-link">${i}</span>`
            : `<a class="page-link" href="#" onclick="event.preventDefault(); goToPage(${i})">${i}</a>`;
        pagination.appendChild(li);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = currentPage === totalPages
        ? '<span class="page-link">Next</span>'
        : '<a class="page-link" href="#" onclick="event.preventDefault(); goToPage(' + (currentPage + 1) + ')">Next</a>';
    pagination.appendChild(nextLi);
}

/**
 * Navigate to specific page
 */
function goToPage(page) {
    const totalPages = Math.ceil(filteredPrograms.length / itemsPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    updatePaginatedDisplay(true); // Show loading state for page navigation
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
    
    // Reset filtered programs to all programs
    filteredPrograms = [...allPrograms];
    currentPage = 1;
    
    // Update display
    updatePaginatedDisplay();
    updateFinalizedCounter(filteredPrograms.length);
    updateFilterBadges();
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
window.goToPage = goToPage;
