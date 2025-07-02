# Implementation Plan: Quarter Dropdown in Program Creation

## Objective
To add a quarter dropdown in the "Create New Program" form, allowing users to select a specific quarter for the program submission. The selected quarter will be linked to the `period_id` in the `program_submissions` table.

---

## Steps

### Step 1: Database Preparation
1. Ensure the `reporting_periods` table is populated with all quarters (Q1-Q4) for the relevant years.
2. Verify that the `program_submissions` table has a `period_id` column to store the selected quarter.

---

### Step 2: Backend Changes

#### 2.1 Fetch Available Quarters
- Create a function in `functions.php` or `programs.php` to fetch all available quarters (Q1-Q4) from the `reporting_periods` table.
- The function should return a list of quarters with their `period_id`, `year`, and `quarter` values.

#### 2.2 Modify Program Creation Logic
- Update the `create_wizard_program_draft` function to accept a `period_id` parameter from the form.
- Use the provided `period_id` to create the initial `program_submissions` record instead of relying on the current logic.

---

### Step 3: Frontend Changes

#### 3.1 Add Quarter Dropdown
- Add a dropdown in the "Create New Program" form to allow users to select a quarter.
- Populate the dropdown with the list of quarters fetched from the backend.

#### 3.2 Form Submission
- Ensure the selected `period_id` is included in the form submission.

---

### Step 4: Validation

#### 4.1 Server-Side Validation
- Validate the selected `period_id` on the server side to ensure it corresponds to a valid quarter.
- Provide appropriate error messages if the validation fails.

#### 4.2 Client-Side Validation
- Optionally, add client-side validation to ensure a quarter is selected before form submission.

---

### Step 5: Testing

#### 5.1 Dropdown Functionality
- Test the dropdown to ensure it displays the correct quarters.

#### 5.2 Data Integrity
- Verify that the selected quarter is correctly stored in the `program_submissions` table.

#### 5.3 Edge Cases
- Test edge cases, such as invalid or missing `period_id` values.

---

### Step 6: Documentation

#### 6.1 Update Project Documentation
- Update the project documentation to include details about the new quarter selection feature.

#### 6.2 Admin Instructions
- Provide instructions for administrators on how to manage quarters in the `reporting_periods` table.

---

## Notes
- This implementation will replace the existing logic for automatic quarter assignment.
- Ensure that the dropdown is user-friendly and integrates seamlessly with the existing form design.

---

## Checklist

- [x] Create a function to fetch available quarters.
- [x] Update `create_wizard_program_draft` to accept `period_id`.
- [x] Add a quarter dropdown to the form in step 1.
- [x] Validate the selected `period_id` on the client side.
- [x] Validate the selected `period_id` on the server side.
- [x] Enhance UI with styling for open/closed periods.
- [x] Update project documentation.
