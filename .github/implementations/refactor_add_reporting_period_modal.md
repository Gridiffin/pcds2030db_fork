# Refactor Add New Reporting Period Modal

This task involves refactoring the "Add New Reporting Period" modal to improve user experience and data consistency.

## TODO

-   [x] **Identify Files:**
    -   [x] Locate the HTML/PHP file for the modal. (`app/views/admin/periods/reporting_periods.php`)
    -   [x] Locate the relevant JavaScript file for frontend logic. (`assets/js/admin/reporting_periods.js`)
    -   [x] Confirm `app/ajax/save_period.php` is the correct backend script.
-   [x] **Frontend Changes (Modal HTML/PHP):**
    -   [x] Remove the "Period Name" text input.
    -   [x] Add a dropdown select field for "Period Type" (options: Q1, Q2, Q3, Q4, HY1, HY2).
    -   [x] Add a number input field for "Year" (e.g., placeholder "YYYY").
    -   [x] Modify "Start Date" and "End Date" fields to be read-only.
-   [x] **Frontend Changes (JavaScript):**
    -   [x] Add event listeners to the new "Period Type" dropdown and "Year" input.
    -   [x] Implement logic to automatically calculate and populate "Start Date" and "End Date" based on Period Type and Year selection.
        -   Q1: `YYYY-01-01` to `YYYY-03-31`
        -   Q2: `YYYY-04-01` to `YYYY-06-30`
        -   Q3: `YYYY-07-01` to `YYYY-09-30`
        -   Q4: `YYYY-10-01` to `YYYY-12-31`
        -   HY1 (Jan-Jun): `YYYY-01-01` to `YYYY-06-30`
        -   HY2 (Jul-Dec): `YYYY-07-01` to `YYYY-12-31`
    -   [x] Ensure dates are formatted correctly (e.g., `YYYY-MM-DD`) for the backend.
    -   [x] On form submission, ensure `quarter` (as period type value 1-6), `year`, `start_date`, `end_date`, and `status` are sent.
-   [x] **Backend Changes (`app/ajax/save_period.php`):**
    -   [x] Modify the script to receive `quarter` (as period type value 1-6) and `year` as separate POST parameters.
    -   [x] Remove the existing `preg_match` logic for extracting quarter and year from `period_name`.
    -   [x] Construct the `period_name` string (e.g., "Q1 YYYY", "HY1 YYYY") on the backend using the new parameters.
    -   [x] Update validation logic to use the new `quarter` (1-6) and `year` inputs directly.
    -   [x] Ensure `start_date` and `end_date` are still correctly received and validated.
-   [ ] **Testing:**
    -   [ ] Test creating new reporting periods with various Quarter, Half Yearly, and Year combinations.
    -   [ ] Verify Start and End dates are correctly calculated and saved for all period types.
    -   [ ] Verify the `period_name` is correctly constructed and saved for all period types.
    -   [ ] Test validation for incorrect inputs (e.g., invalid year, missing period type).
    -   [ ] Test status handling (default to inactive, setting to active).
-   [ ] **Documentation & Cleanup:**
    -   [ ] Update any relevant comments in the code (e.g., clarify that 'quarter' field/variable now means 'period type').
    -   [ ] Delete any test files created during implementation.
    -   [ ] Mark tasks in this .md file as complete.
