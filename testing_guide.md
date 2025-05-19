# Program Selection for Report Generation - Testing Guide

To test the newly implemented program selection feature for report generation, please follow these steps:

## Feature Overview

The system now allows admins to select specific programs to include in slide reports when generating reports. Programs are filtered based on the selected reporting period, and the UI has been updated to ensure program names don't get truncated.

## What Changed

1. Removed redundant program dropdown which conflicted with the program selector checkboxes
2. Modified CSS to ensure program names don't get truncated:
   - Changed text display from `overflow: hidden; text-overflow: ellipsis;` to `word-break: break-word; white-space: normal;`
3. Created API endpoint `get_period_programs.php` to fetch programs by reporting period
4. Updated report-generator.js to:
   - Load programs dynamically when a period is selected
   - Filter programs by sector after period selection
   - Maintain proper select/deselect functionality

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
6. Click "Deselect All" button
   - All visible programs should be unchecked

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

If you encounter any issues or have questions, please let me know!
