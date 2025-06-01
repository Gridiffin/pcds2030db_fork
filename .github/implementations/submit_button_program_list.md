# Submit Button for Program List

## Problem
Agency users need a way to submit draft programs directly from the program list view. This requires updating the `is_draft` column in the `program_submissions` table.

## Solution
Implement a submit button for each draft program in the program list. When clicked, the button will:
1. Send an AJAX request to update the `is_draft` column.
2. Reload the page to reflect the changes.

## Steps

### 1. Add Submit Button
- File: `app/views/agency/view_programs.php`
- Add a button for each draft program in the table.

### 2. Create AJAX Handler
- File: `app/views/agency/ajax/submit_program.php`
- Handle the AJAX request and update the database.

### 3. Update JavaScript
- File: `assets/js/agency/view_programs.js`
- Add functionality to send the AJAX request when the button is clicked.

### 4. Style the Button
- Ensure the button uses existing CSS classes for consistent styling.

### 5. Test
- Verify the button updates the database and reloads the page correctly.

## Status
- [x] Submit button added
- [x] AJAX handler created
- [x] JavaScript updated
- [x] Styling verified
- [x] Testing completed
