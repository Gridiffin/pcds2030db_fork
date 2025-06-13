## Update Admin Assign Program to Wizard-like Interface

**Objective:** Refactor the admin program assignment page (`app/views/admin/programs/assign_programs.php`) to use a multi-step wizard interface, similar to the agency-side program creation page (`app/views/agency/programs/create_program.php`). Maintain existing admin-specific functionalities like assigning to an agency and setting edit permissions.

**Steps:**

1.  **[ ] Analyze Existing Admin Page (`assign_programs.php`):**
    *   Identify all form fields and their current layout.
    *   Note server-side processing logic (PHP).
    *   Note client-side JavaScript for interactions (e.g., adding targets, validation).
    *   Identify admin-specific fields:
        *   Assign to Agency (dropdown)
        *   Edit Permissions (checkboxes)
        *   Program Rating (pills)

2.  **[ ] Analyze Agency Wizard Page (`create_program.php`):**
    *   Understand the HTML structure of the wizard (steps, progress bar, navigation buttons).
    *   Examine the JavaScript controlling the wizard (step transitions, validation per step, review summary).
    *   Note the CSS used for styling the wizard.

3.  **[ ] Plan Wizard Steps for Admin Assign Program:**
    *   **Step 1: Basic Information**
        *   Program Name
        *   Assign to Agency
        *   Start Date
        *   End Date
    *   **Step 2: Program Details & Targets**
        *   Program Rating
        *   Program Targets (dynamic add/remove)
        *   Remarks (Optional)
    *   **Step 3: Permissions & Review**
        *   Agency Edit Permissions
        *   Review Summary of all entered data.

4.  **[ ] Implement HTML Structure for Wizard in `assign_programs.php`:**
    *   Copy the wizard progress indicator HTML from `create_program.php`.
    *   Create `div` elements for each step (`wizard-step`).
    *   Move existing form fields from `assign_programs.php` into the appropriate steps.
        *   Ensure admin-specific fields (Agency dropdown, Edit Permissions) are correctly placed.
    *   Copy navigation buttons (Previous, Next, Assign Program) HTML.
    *   Add a "Review" section in the final step, similar to `create_program.php`.

5.  **[ ] Adapt/Create CSS for Admin Wizard:**
    *   Copy relevant wizard CSS from `create_program.php` (inline `<style>` block or a separate CSS file if preferred).
    *   Adjust styles if needed to fit the admin page layout or theme.
    *   Ensure styles for admin-specific elements are maintained or integrated.

6.  **[ ] Implement JavaScript for Wizard Functionality in `assign_programs.php`:**
    *   Adapt the JavaScript from `create_program.php` to manage the new wizard.
    *   Key JavaScript functions to adapt/create:
        *   `initializeWizard()`
        *   `showStep(step)`
        *   `updateStepIndicators(step)`
        *   `updateNavigationButtons(step)`
        *   `updateProgressBar()`
        *   `collectFormData()`: Modify to include admin-specific fields (agency_id, edit_permissions).
        *   `updateReviewSummary()`: Modify to display all fields, including admin-specific ones.
        *   `validateStep(step)`: Implement validation logic for each step.
            *   Step 1: Program Name, Agency ID required. Date validation.
            *   Step 2: (Optional) Target validation if any targets are added.
            *   Step 3: No specific validation, just review.
        *   Event listeners for Next, Previous, and Assign Program buttons.
    *   **Maintain Existing JavaScript:**
        *   Integrate the existing JavaScript for rating pills selection.
        *   Integrate the existing JavaScript for adding/removing targets. Ensure it works within the wizard step.
        *   Integrate existing form validation for program name, agency, and dates, adapting it to the per-step validation model.

7.  **[ ] Update Server-Side PHP (`assign_programs.php`):**
    *   The core PHP logic for processing the form submission should largely remain the same, as all data will still be submitted together when the "Assign Program" button is clicked on the final step.
    *   Ensure that `$_POST` variables for all fields (including those from different steps) are correctly accessed.
    *   No changes should be needed for database interaction or notification logic unless new fields are introduced or existing ones are fundamentally changed (which is not the plan here).

8.  **[ ] Testing:**
    *   Test each step of the wizard.
    *   Verify that data entered in previous steps is retained when navigating back and forth.
    *   Test form validation for each step.
    *   Test the "Add Another Target" functionality within its step.
    *   Test the rating pill selection.
    *   Test the "Agency Edit Permissions" checkboxes.
    *   Verify the review summary on the final step accurately reflects all entered data.
    *   Test successful program assignment.
    *   Test error handling (e.g., if required fields are missed).
    *   Ensure admin-specific functionalities are working as expected.

9.  **[ ] Code Cleanup and Refinement:**
    *   Ensure consistent coding style.
    *   Add comments where necessary.
    *   Optimize JavaScript if possible.
    *   Remove any redundant or unused code.
    *   Ensure all file includes and paths are correct.
    *   Verify that `APP_URL` and other constants are used correctly for asset paths.
    *   Check that `$additionalScripts` correctly includes any new JS files or that inline JS is properly placed.

**Admin-Specific Functions to Maintain:**
*   Selection of an agency to assign the program to.
*   Setting edit permissions for the agency (e.g., can edit targets, description, timeline).
*   The overall "Assign Program" action, which creates the program and links it to the agency.
*   Program rating selection by the admin.
*   The PHP logic that handles these admin-specific aspects during form processing.
*   Audit logging for program assignment.
*   Notification to the agency upon assignment.
*   The fields for `start_date` and `end_date` should remain optional for admin assignment, as per current logic.
*   Targets are not strictly required for admin assignment, as per current logic.
