/**
 * Admin Programs Management - Consolidated Entry Point
 * Combines all program management functionality from multiple files
 */

// Import Core Layout & Base Styles
import '../../css/main.css';

// Import Admin-Specific Styles (consolidated)
import '../../css/components/admin-common.css';
import '../../css/pages/admin.css';
import '../../css/admin/programs.css';

// Import Component Styles  
import '../../css/components/tables.css';
import '../../css/components/cards.css';
import '../../css/components/forms.css';
import '../../css/components/buttons.css';
import '../../css/components/page-header.css';

// Import JavaScript - Core utilities and functionality
import '../main.js';
import './bootstrap_modal_fix.js';
import './programs_delete.js';

// Import essential utilities
import '../utilities/initialization.js';
import '../utilities/dropdown_init.js';
import './admin-common.js';

console.log('Admin Programs Management bundle loaded successfully');

/**
 * Initialize all program management functionality
 */
document.addEventListener('DOMContentLoaded', () => {
    // Initialize filtering and sorting for all three program sections
    ['draft', 'finalized', 'empty'].forEach(section => {
        initializeFilteringForSection(section);
        initializeSortingForSection(section);
    });

    // Initialize shared modals
    initializeDeleteModal();
    initializeMoreActionsModal();
});

/**
 * Sets up event listeners for filter inputs for a specific section
 * @param {string} section - The section to initialize ('draft', 'finalized', or 'empty')
 */
function initializeFilteringForSection(section) {
    const searchInput = document.getElementById(`${section}ProgramSearch`);
    const ratingSelect = document.getElementById(`${section}RatingFilter`);
    const typeSelect = document.getElementById(`${section}TypeFilter`);
    const agencySelect = document.getElementById(`${section}AgencyFilter`);
    const initiativeSelect = document.getElementById(`${section}InitiativeFilter`);
    const resetButton = document.getElementById(`reset${capitalizeFirstLetter(section)}Filters`);

    const filters = [searchInput, ratingSelect, typeSelect, agencySelect, initiativeSelect];

    filters.forEach(filter => {
        if (filter) {
            const event = filter.tagName === 'SELECT' ? 'change' : 'input';
            filter.addEventListener(event, () => applyFilters(section));
        }
    });

    if (resetButton) {
        resetButton.addEventListener('click', () => {
            filters.forEach(filter => {
                if (filter) filter.value = '';
            });
            applyFilters(section);
        });
    }
}

/**
 * Applies all active filters to table rows for a given section
 * @param {string} section - The section to filter ('draft', 'finalized', or 'empty')
 */
function applyFilters(section) {
    const table = document.getElementById(`${section}ProgramsTable`);
    if (!table) return;

    // Gather filter values
    const searchValue = document.getElementById(`${section}ProgramSearch`)?.value.toLowerCase() || '';
    const ratingValue = document.getElementById(`${section}RatingFilter`)?.value || '';
    const typeValue = document.getElementById(`${section}TypeFilter`)?.value || '';
    const agencyValue = document.getElementById(`${section}AgencyFilter`)?.value || '';
    const initiativeValue = document.getElementById(`${section}InitiativeFilter`)?.value || '';

    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;

    // Remove existing "no results" message
    const noResultsRow = table.querySelector('.no-results-row');
    if (noResultsRow) noResultsRow.remove();
    
    rows.forEach(row => {
        if (row.classList.contains('no-results-row')) return;

        const programName = row.querySelector('.program-name')?.textContent.toLowerCase() || '';
        const programType = row.dataset.programType || '';
        const agencyId = row.dataset.agencyId || '';
        const initiativeId = row.dataset.initiativeId || '0';
        const rating = row.dataset.rating || '';

        const searchMatch = !searchValue || programName.includes(searchValue);
        const typeMatch = !typeValue || programType === typeValue;
        const agencyMatch = !agencyValue || agencyId === agencyValue;
        const initiativeMatch = !initiativeValue || 
            (initiativeValue === 'no-initiative' ? (initiativeId === '0' || initiativeId === '') : initiativeId === initiativeValue);
        const ratingMatch = !ratingValue || rating === ratingValue.replace(/-/g, '_');
        
        const isVisible = searchMatch && typeMatch && agencyMatch && initiativeMatch && ratingMatch;

        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });

    // Update count badge
    const countBadge = document.getElementById(`${section}-count`);
    if (countBadge) countBadge.textContent = visibleCount;

    // Add "no results" row if needed
    if (visibleCount === 0) {
        const thead = table.querySelector('thead');
        const colspan = thead ? thead.querySelectorAll('th').length : 5;
        const tbody = table.querySelector('tbody');
        const newNoResultsRow = document.createElement('tr');
        newNoResultsRow.className = 'no-results-row';
        newNoResultsRow.innerHTML = `<td colspan="${colspan}" class="text-center py-4">No programs match the current filters.</td>`;
        tbody.appendChild(newNoResultsRow);
    }
}

/**
 * Sets up click events for sortable headers in a specific table
 * @param {string} section - The section to initialize sorting for
 */
function initializeSortingForSection(section) {
    const table = document.getElementById(`${section}ProgramsTable`);
    if (!table) return;

    const headers = table.querySelectorAll('th.sortable');
    headers.forEach(header => {
        header.addEventListener('click', () => {
            const sortBy = header.dataset.sort;
            const currentDirection = header.dataset.direction || 'asc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            
            // Reset icons on all headers in this table
            headers.forEach(h => {
                h.dataset.direction = '';
                const icon = h.querySelector('i.fa-sort, i.fa-sort-up, i.fa-sort-down');
                if (icon) icon.className = 'fas fa-sort ms-1';
            });

            // Set icon for clicked header
            header.dataset.direction = newDirection;
            const icon = header.querySelector('i.fa-sort');
            if (icon) {
                icon.className = newDirection === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1';
            }

            sortTable(table, sortBy, newDirection);
        });
    });
}

/**
 * Sorts table rows based on column and direction
 * @param {HTMLTableElement} table - The table element to sort
 * @param {string} sortBy - The data attribute key to sort by
 * @param {string} direction - The sort direction ('asc' or 'desc')
 */
function sortTable(table, sortBy, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.no-results-row)'));
    
    rows.sort((a, b) => {
        let valA, valB;

        switch (sortBy) {
            case 'name':
                valA = a.querySelector('.program-name')?.textContent.trim().toLowerCase() || '';
                valB = b.querySelector('.program-name')?.textContent.trim().toLowerCase() || '';
                break;
            case 'agency':
                valA = a.querySelector('.agency-col')?.dataset.agency.trim().toLowerCase() || '';
                valB = b.querySelector('.agency-col')?.dataset.agency.trim().toLowerCase() || '';
                break;
            case 'initiative':
                valA = a.querySelector('.initiative-col')?.dataset.initiative.trim().toLowerCase() || 'zzz';
                valB = b.querySelector('.initiative-col')?.dataset.initiative.trim().toLowerCase() || 'zzz';
                break;
            case 'rating':
                valA = parseInt(a.querySelector('[data-rating-order]')?.dataset.ratingOrder || 999);
                valB = parseInt(b.querySelector('[data-rating-order]')?.dataset.ratingOrder || 999);
                break;
            case 'date':
                valA = new Date(a.querySelector('[data-date]')?.dataset.date || 0).getTime();
                valB = new Date(b.querySelector('[data-date]')?.dataset.date || 0).getTime();
                break;
            default:
                return 0;
        }
        
        const comparator = valA < valB ? -1 : (valA > valB ? 1 : 0);
        return direction === 'asc' ? comparator : -comparator;
    });

    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Initializes the delete confirmation modal
 */
function initializeDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    if (!deleteModal) return;
    // Modal is triggered through triggerDeleteFromModal function
}

/**
 * Initializes the "More Actions" modal
 */
function initializeMoreActionsModal() {
    document.body.addEventListener('click', event => {
        const moreActionsButton = event.target.closest('.more-actions-btn');
        if (moreActionsButton) {
            const programId = moreActionsButton.dataset.programId;
            const programName = moreActionsButton.dataset.programName;
            const programType = moreActionsButton.dataset.programType;

            showMoreActionsModal(programId, programName, programType);
        }
    });
}

/**
 * Creates and shows the "More Actions" modal
 * @param {string} programId - The ID of the program
 * @param {string} programName - The name of the program
 * @param {string} programType - The type of the program
 */
function showMoreActionsModal(programId, programName, programType) {
    let modalEl = document.getElementById('moreActionsModal');
    if (!modalEl) {
        modalEl = createMoreActionsModalElement();
        document.body.appendChild(modalEl);
    }

    // Update modal content
    const nameDisplay = modalEl.querySelector('.program-name-display');
    const typeDisplay = modalEl.querySelector('.program-type-display');
    const actionsList = modalEl.querySelector('.actions-list');

    if (nameDisplay) nameDisplay.textContent = programName;
    if (typeDisplay) typeDisplay.textContent = programType === 'assigned' ? 'Assigned Program' : 'Agency-Created Program';
    
    // Populate actions
    if (actionsList) {
        actionsList.innerHTML = `
            <div class="d-grid gap-2">
                <a href="view_program.php?id=${programId}" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-2"></i>View Program Details
                </a>
                <a href="edit_program.php?id=${programId}" class="btn btn-outline-secondary">
                    <i class="fas fa-edit me-2"></i>Edit Program
                </a>
                <hr>
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" onclick="triggerDeleteFromModal(${programId}, '${programName.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash me-2"></i>Delete Program
                </button>
            </div>
        `;
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

/**
 * Creates the DOM element for the "More Actions" modal
 * @returns {HTMLElement} The modal element
 */
function createMoreActionsModalElement() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'moreActionsModal';
    modal.tabIndex = -1;
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Program Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 class="program-name-display"></h6>
                        <small class="text-muted program-type-display"></small>
                    </div>
                    <div class="actions-list"></div>
                </div>
            </div>
        </div>
    `;
    return modal;
}

/**
 * Triggers the delete modal from the "More Actions" modal
 * @param {string} programId - The ID of the program to delete
 * @param {string} programName - The name of the program to delete
 */
function triggerDeleteFromModal(programId, programName) {
    console.log('triggerDeleteFromModal called with:', { programId, programName });
    
    const deleteModal = document.getElementById('deleteModal');
    if (!deleteModal) {
        console.error('Delete modal not found');
        return;
    }

    const programNameDisplay = deleteModal.querySelector('#program-name-display');
    const programIdInput = deleteModal.querySelector('#program-id-input');

    if (programNameDisplay) {
        programNameDisplay.textContent = programName;
        console.log('Set program name display to:', programName);
    } else {
        console.error('Program name display element not found');
    }
    
    if (programIdInput) {
        programIdInput.value = programId;
        console.log('Set program ID input to:', programId);
    } else {
        console.error('Program ID input element not found');
    }

    const modal = new bootstrap.Modal(deleteModal);
    modal.show();
}

// Make function globally accessible
window.triggerDeleteFromModal = triggerDeleteFromModal;

/**
 * Capitalizes the first letter of a string
 * @param {string} string - The string to capitalize
 * @returns {string} The capitalized string
 */
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}