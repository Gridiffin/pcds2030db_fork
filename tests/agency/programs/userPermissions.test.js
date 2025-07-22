/**
 * User Permissions Tests
 * Tests for user permission selection and management functionality
 */

import { initUserPermissions } from '../../../assets/js/agency/programs/userPermissions.js';

describe('User Permissions', () => {
    beforeEach(() => {
        // Clear DOM before each test
        document.body.innerHTML = '';
        
        // Clear console mock
        console.warn = jest.fn();
    });

    describe('Initialization', () => {
        test('initializes successfully with required elements', () => {
            createUserPermissionsDOM();
            
            expect(() => initUserPermissions()).not.toThrow();
        });

        test('logs warning when required elements are missing', () => {
            // Empty DOM - no required elements
            
            initUserPermissions();
            
            expect(console.warn).toHaveBeenCalledWith('User permissions elements not found');
        });

        test('initializes user selection visibility based on checkbox state', () => {
            createUserPermissionsDOM();
            
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            // Checkbox initially unchecked
            restrictCheckbox.checked = false;
            initUserPermissions();
            
            expect(userSection.style.display).toBe('none');
            
            // Checkbox initially checked
            restrictCheckbox.checked = true;
            initUserPermissions();
            
            expect(userSection.style.display).toBe('block');
        });
    });

    describe('User Selection Toggle', () => {
        beforeEach(() => {
            createUserPermissionsDOM();
            initUserPermissions();
        });

        test('shows user selection section when restriction is enabled', () => {
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            restrictCheckbox.checked = true;
            restrictCheckbox.dispatchEvent(new Event('change'));
            
            expect(userSection.style.display).toBe('block');
        });

        test('hides user selection section when restriction is disabled', () => {
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            // First enable to show
            restrictCheckbox.checked = true;
            restrictCheckbox.dispatchEvent(new Event('change'));
            expect(userSection.style.display).toBe('block');
            
            // Then disable to hide
            restrictCheckbox.checked = false;
            restrictCheckbox.dispatchEvent(new Event('change'));
            
            expect(userSection.style.display).toBe('none');
        });

        test('unchecks all users when hiding section', () => {
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
            
            // Check some users first
            userCheckboxes[0].checked = true;
            userCheckboxes[1].checked = true;
            
            // Disable restrictions (hide section)
            restrictCheckbox.checked = false;
            restrictCheckbox.dispatchEvent(new Event('change'));
            
            // All users should be unchecked
            userCheckboxes.forEach(checkbox => {
                expect(checkbox.checked).toBe(false);
            });
        });

        test('removes error messages when toggling restrictions', () => {
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            // Add an error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = 'Test error';
            userSection.appendChild(errorDiv);
            
            expect(userSection.querySelector('.alert-danger')).toBeTruthy();
            
            // Toggle checkbox to trigger error removal
            restrictCheckbox.checked = true;
            restrictCheckbox.dispatchEvent(new Event('change'));
            
            expect(userSection.querySelector('.alert-danger')).toBeNull();
        });
    });

    describe('Select All/None Functionality', () => {
        beforeEach(() => {
            createUserPermissionsDOM();
            initUserPermissions();
        });

        test('selects all users when select all button is clicked', () => {
            const selectAllBtn = document.querySelector('[data-action="select-all-users"]');
            const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
            
            // Initially unchecked
            userCheckboxes.forEach(checkbox => {
                expect(checkbox.checked).toBe(false);
            });
            
            selectAllBtn.click();
            
            // All should be checked
            userCheckboxes.forEach(checkbox => {
                expect(checkbox.checked).toBe(true);
            });
        });

        test('deselects all users when select none button is clicked', () => {
            const selectNoneBtn = document.querySelector('[data-action="select-no-users"]');
            const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
            
            // Check all users first
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            selectNoneBtn.click();
            
            // All should be unchecked
            userCheckboxes.forEach(checkbox => {
                expect(checkbox.checked).toBe(false);
            });
        });

        test('handles clicking elements that do not match selectors', () => {
            const randomButton = document.createElement('button');
            randomButton.textContent = 'Random Button';
            document.body.appendChild(randomButton);
            
            // Should not throw error
            expect(() => randomButton.click()).not.toThrow();
        });
    });

    describe('Form Validation', () => {
        beforeEach(() => {
            createUserPermissionsDOM();
            initUserPermissions();
        });

        test('allows form submission when restrictions are disabled', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            
            let formSubmitted = false;
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                formSubmitted = true;
            });
            
            restrictCheckbox.checked = false;
            form.dispatchEvent(new Event('submit'));
            
            expect(formSubmitted).toBe(true);
        });

        test('allows form submission when restrictions are enabled and users are selected', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
            
            let formSubmitted = false;
            form.addEventListener('submit', (e) => {
                if (!e.defaultPrevented) {
                    e.preventDefault();
                    formSubmitted = true;
                }
            });
            
            restrictCheckbox.checked = true;
            userCheckboxes[0].checked = true; // Select at least one user
            
            form.dispatchEvent(new Event('submit'));
            
            expect(formSubmitted).toBe(true);
        });

        test('prevents form submission when restrictions are enabled but no users selected', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            let formSubmitted = false;
            form.addEventListener('submit', (e) => {
                if (!e.defaultPrevented) {
                    e.preventDefault();
                    formSubmitted = true;
                }
            });
            
            restrictCheckbox.checked = true;
            // No users selected
            
            form.dispatchEvent(new Event('submit'));
            
            expect(formSubmitted).toBe(false);
            expect(userSection.querySelector('.alert-danger')).toBeTruthy();
        });

        test('displays appropriate error message for validation failure', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            restrictCheckbox.checked = true;
            form.dispatchEvent(new Event('submit'));
            
            const errorMessage = userSection.querySelector('.alert-danger');
            expect(errorMessage).toBeTruthy();
            expect(errorMessage.textContent).toContain('Please select at least one user when restricting editors');
            expect(errorMessage.querySelector('.fas')).toBeTruthy(); // Should have icon
        });

        test('removes existing error message before adding new one', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            restrictCheckbox.checked = true;
            
            // Trigger validation failure twice
            form.dispatchEvent(new Event('submit'));
            form.dispatchEvent(new Event('submit'));
            
            // Should only have one error message
            const errorMessages = userSection.querySelectorAll('.alert-danger');
            expect(errorMessages.length).toBe(1);
        });

        test('scrolls to user section on validation failure', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            // Mock scrollIntoView
            userSection.scrollIntoView = jest.fn();
            
            restrictCheckbox.checked = true;
            form.dispatchEvent(new Event('submit'));
            
            expect(userSection.scrollIntoView).toHaveBeenCalledWith({
                behavior: 'smooth',
                block: 'center'
            });
        });

        test('handles missing form element gracefully', () => {
            // Remove form from DOM
            const form = document.getElementById('createProgramForm');
            form.remove();
            
            // Re-initialize without form
            expect(() => initUserPermissions()).not.toThrow();
        });
    });

    describe('Edge Cases and Error Handling', () => {
        test('handles missing user section gracefully', () => {
            // Create DOM without user section
            document.body.innerHTML = `
                <input type="checkbox" id="restrict_editors" />
                <form id="createProgramForm"></form>
            `;
            
            expect(() => initUserPermissions()).not.toThrow();
            
            const restrictCheckbox = document.getElementById('restrict_editors');
            expect(() => {
                restrictCheckbox.checked = true;
                restrictCheckbox.dispatchEvent(new Event('change'));
            }).not.toThrow();
        });

        test('handles case with no user checkboxes', () => {
            // Create DOM without user checkboxes
            document.body.innerHTML = `
                <input type="checkbox" id="restrict_editors" />
                <div id="userSelectionSection"></div>
                <button data-action="select-all-users">Select All</button>
                <button data-action="select-no-users">Select None</button>
                <form id="createProgramForm"></form>
            `;
            
            initUserPermissions();
            
            const selectAllBtn = document.querySelector('[data-action="select-all-users"]');
            const selectNoneBtn = document.querySelector('[data-action="select-no-users"]');
            
            // Should not throw errors
            expect(() => selectAllBtn.click()).not.toThrow();
            expect(() => selectNoneBtn.click()).not.toThrow();
        });

        test('handles multiple initialization calls gracefully', () => {
            createUserPermissionsDOM();
            
            // Initialize multiple times
            initUserPermissions();
            initUserPermissions();
            initUserPermissions();
            
            const restrictCheckbox = document.getElementById('restrict_editors');
            
            // Should still work correctly
            restrictCheckbox.checked = true;
            restrictCheckbox.dispatchEvent(new Event('change'));
            
            const userSection = document.getElementById('userSelectionSection');
            expect(userSection.style.display).toBe('block');
        });

        test('validates without crashing when sections are missing', () => {
            // Minimal DOM setup
            document.body.innerHTML = `
                <input type="checkbox" id="restrict_editors" checked />
                <form id="createProgramForm"></form>
            `;
            
            initUserPermissions();
            
            const form = document.getElementById('createProgramForm');
            
            // Should handle missing user section gracefully
            expect(() => form.dispatchEvent(new Event('submit'))).not.toThrow();
        });
    });

    describe('Accessibility and UX', () => {
        beforeEach(() => {
            createUserPermissionsDOM();
            initUserPermissions();
        });

        test('error message includes accessible icon', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            restrictCheckbox.checked = true;
            form.dispatchEvent(new Event('submit'));
            
            const errorMessage = userSection.querySelector('.alert-danger');
            const icon = errorMessage.querySelector('.fas.fa-exclamation-circle');
            
            expect(icon).toBeTruthy();
            expect(icon.classList.contains('me-2')).toBe(true); // Has proper spacing
        });

        test('error message has proper Bootstrap classes', () => {
            const form = document.getElementById('createProgramForm');
            const restrictCheckbox = document.getElementById('restrict_editors');
            const userSection = document.getElementById('userSelectionSection');
            
            restrictCheckbox.checked = true;
            form.dispatchEvent(new Event('submit'));
            
            const errorMessage = userSection.querySelector('.alert-danger');
            
            expect(errorMessage.classList.contains('alert')).toBe(true);
            expect(errorMessage.classList.contains('alert-danger')).toBe(true);
            expect(errorMessage.classList.contains('mt-3')).toBe(true);
        });
    });
});

// Helper function to create DOM structure for testing
function createUserPermissionsDOM() {
    document.body.innerHTML = `
        <form id="createProgramForm">
            <div class="form-check">
                <input type="checkbox" id="restrict_editors" class="form-check-input" />
                <label for="restrict_editors">Restrict Editors</label>
            </div>
            
            <div id="userSelectionSection" style="display: none;">
                <div class="mb-3">
                    <button type="button" data-action="select-all-users" class="btn btn-sm btn-outline-primary">Select All</button>
                    <button type="button" data-action="select-no-users" class="btn btn-sm btn-outline-secondary">Select None</button>
                </div>
                
                <div class="user-checkboxes">
                    <div class="form-check">
                        <input type="checkbox" name="assigned_editors[]" value="1" id="user_1" />
                        <label for="user_1">User 1</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="assigned_editors[]" value="2" id="user_2" />
                        <label for="user_2">User 2</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="assigned_editors[]" value="3" id="user_3" />
                        <label for="user_3">User 3</label>
                    </div>
                </div>
            </div>
        </form>
    `;
}
