# Fix Fatal Error in Agency View Outcome Page

## Problem
Multiple fatal errors `Uncaught TypeError: Cannot access offset of type string on string` occur on `app/views/agency/outcomes/view_outcome.php` at lines 212, 341, and 394. These happen when trying to access array properties on string values in three different sections of the page.

## Analysis
- The errors are caused by the code attempting to access array keys (`$column['label']`, `$column['id']`, etc.) on variables (`$column`) that are sometimes strings.
- The `$columns` variable is populated from a JSON object stored in the database.
- The structure of the `columns` property in the JSON object appears to be inconsistent. In some cases, it's an array of strings (e.g., `["col1", "col2"]`), and in others, it's an array of objects (e.g., `[{"id": "col1", "label": "Column 1"}, ...]`).
- The code in multiple places assumes every element in the `$columns` array is an object (an associative array in PHP), leading to the fatal error when it encounters a string.

## Solution Steps

### Step 1: Identify the problematic code
- [x] Locate the `foreach` loops that iterate over the `$columns` array in `app/views/agency/outcomes/view_outcome.php`. These are around lines 210, 341, and 394.

### Step 2: Implement a robust fix
- [x] Modify the code inside each loop to handle both string and array types for the `$column` variable.
- [x] Use `is_array()` to check the type of `$column` in each location.
- [x] If it's an array, use the existing logic to display the column properties.
- [x] If it's a string, display the string value directly as appropriate for each context.
- [x] This prevents the fatal error and ensures the page renders correctly for both data formats.

### Step 3: Test the change
- [x] Verify that the "View Outcome" page loads without errors for outcomes with both the old and new `data_json` column formats.
- [x] Confirm that the table headers, structure info, and chart options display correctly in all cases.

## Files Modified
- `app/views/agency/outcomes/view_outcome.php`

## Fixes Made
1. **Table View (line ~212)**: Added conditional checks to handle string columns when displaying table headers.
2. **Structure Info Tab (line ~341)**: Added conditional checks to handle string columns when displaying column configuration details.
3. **Chart View Tab (line ~394)**: Added conditional checks to handle string columns when populating chart data series options.

These changes maintain backward compatibility with both older string-based column formats and newer object-based column formats, ensuring the page functions correctly in all cases.
