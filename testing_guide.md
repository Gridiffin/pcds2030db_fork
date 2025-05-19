# Program Selection for Report Generation - Testing Guide

To test the newly implemented program selection feature for report generation, please follow these steps:

## Feature Overview

The system now allows admins to select specific programs to include in slide reports when generating reports. Programs are filtered based on the selected reporting period, and the UI has been updated to ensure program names don't get truncated. Additionally, admins can now specify the order in which programs appear in the generated reports using the new program ordering feature.

## What Changed

1. Removed redundant program dropdown which conflicted with the program selector checkboxes
2. Modified CSS to ensure program names don't get truncated:
   - Changed text display from `overflow: hidden; text-overflow: ellipsis;` to `word-break: break-word; white-space: normal;`
3. Created API endpoint `get_period_programs.php` to fetch programs by reporting period
4. Updated report-generator.js to:
   - Load programs dynamically when a period is selected
   - Filter programs by sector after period selection
   - Maintain proper select/deselect functionality
   - Allow custom ordering of programs in reports
   - Provide automatic and manual ordering capabilities

## Testing Steps

### Test the Program Loading and Selection

1. Log in as an administrator
2. Navigate to "Reports" > "Generate Reports" in the admin dashboard
3. Select a reporting period from the dropdown menu
   - Program list should load automatically based on the selected period
   - Programs should be grouped by sector
   - Program names should display fully without truncation
4. Select a sector from the dropdown menu
   - Only programs from that sector should be visible
   - Previously selected programs from other sectors should be deselected
5. Click "Select All" button
   - All visible programs (from selected sector) should be checked
   - Order number inputs should appear automatically next to each checked program
   - Order numbers should be assigned sequentially (1, 2, 3, etc.)
6. Click "Deselect All" button
   - All visible programs should be unchecked
   - Order number inputs should disappear

### Test the Program Ordering Feature

1. Select a reporting period and sector
2. Check 3-5 specific programs
   - Verify that order input fields appear next to each checked program
   - Verify that each program is automatically assigned a sequential number
3. Manually change the order numbers
   - Assign different numbers to programs (e.g., 5, 2, 10)
   - Create a duplicate number (assign "3" to two different programs)
4. Click the "Sort Numerically" button
   - Verify that the programs are resequenced with clean sequential numbers
   - Verify that duplicate numbers are resolved
5. Uncheck a program in the middle
   - Verify that its number input disappears
6. Check a new program
   - Verify it gets assigned the next available number

### Test Report Generation with Selected Programs

1. Select a reporting period and sector
2. Select a few specific programs from the list (not all)
3. Enter a report name 
4. Click "Generate PPTX Report"
5. Wait for report generation to complete
6. Verify that the generated report includes only the selected programs
   - You can do this by downloading and opening the PPTX file

### Alternative Testing Method

For a quicker way to test just the program selection functionality:

1. Open the test page at: http://localhost/pcds2030_dashboard/test_program_filter.html
2. Select a reporting period from the dropdown
3. Verify that programs load correctly, grouped by sector
4. Select a sector to filter the program list
5. Test the Select All and Deselect All buttons
6. Click "Log Selected Programs" to see which programs are currently selected

## Expected Results

- Programs should load based on the selected reporting period
- Program names should display in full without truncation
- Sector filtering should work correctly
- Program selection should be maintained when generating reports
- Generated reports should only include the selected programs when specific programs are chosen
- Programs should appear in the specified order in the generated report

## Additional Test Cases

### Edge Case Testing

1. **Very Long Program Names**:
   - Find or create a program with an extremely long name (50+ characters)
   - Verify that the name wraps properly and doesn't overflow the container
   - Check that the tooltip appears when hovering over the program name

2. **Large Number of Programs**:
   - Select a reporting period with many programs (20+)
   - Verify that the UI handles scrolling properly
   - Check that "Select All" correctly assigns sequential numbers to all programs

3. **Invalid Order Numbers**:
   - Enter non-numeric values in the order input fields
   - Enter negative numbers or zero
   - Verify that the system handles these cases gracefully and provides appropriate feedback

4. **Sector Switching**:
   - Select programs from Sector A and assign order numbers
   - Switch to Sector B and select programs
   - Switch back to Sector A and verify that selections and order numbers are maintained

### Usability Testing

1. **Notification Testing**:
   - Create duplicate order numbers
   - Click "Sort Numerically"
   - Verify that the notification correctly indicates that duplicates were resolved
   - Check that the notification disappears after a few seconds

2. **Error Handling**:
   - Try to generate a report without selecting any programs
   - Try to generate a report with invalid order inputs
   - Verify appropriate error messages are displayed

If you encounter any issues or have questions, please let me know!
