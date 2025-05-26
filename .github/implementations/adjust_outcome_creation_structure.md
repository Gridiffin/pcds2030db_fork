# Adjust Outcome Creation to Reflect Table Structure

This document outlines the changes needed to adjust the outcome creation interface to better reflect the actual design of outcomes in table form.

## Current Issues
1. The current outcome creation process is a two-step process:
   - First step: Create basic metadata (table name, sector, period)
   - Second step: Edit structure to add columns after creation
2. There's no way to see or preview the table structure during the initial creation
3. The button for adding columns is missing from the UI

## Implementation Tasks

- [x] Enhance the outcome creation interface to show a preview of the table structure
- [x] Add an "Add Column" button to the initial creation form
- [x] Update the outcome-editor.js to properly initialize for new outcomes
- [x] Ensure the outcome structure can be defined during initial creation 
- [x] Include styling consistent with the program tables and outcome view

## Changes Implemented

### 1. Updated UI Components
- Added a table preview to the outcome creation interface with month columns
- Implemented "Add Column" button directly in the creation form
- Ensured styling matches the actual outcome display in the view

### 2. JavaScript Functionality
- Extended outcome-editor.js to support both editing existing outcomes and creating new ones
- Added support for adding/removing columns in the preview table
- Implemented collection of structured data from the table preview
- Added hidden field updates to store structure data for form submission

### 3. Backend Processing
- Modified edit_outcome.php to process structure data from the form
- Updated structure initialization to use provided column data instead of empty structure
- Added data_json initialization with proper month structure

### 4. User Experience Improvements
- Improved visual feedback with proper column styling and sample data
- Added confirmation dialogs when deleting columns
- Interactive live preview that matches the actual outcome data structure

These changes allow administrators to define the structure of outcomes during initial creation, seeing exactly how the data will be presented to agencies. This improves the workflow by eliminating the need to create an empty outcome first and then edit its structure afterward.

## Specific Changes Required

1. Update edit_outcome.php to:
   - Add a table preview section for new outcomes
   - Include an "Add Column" button in the interface
   - Allow defining columns during initial creation

2. Enhance outcome-editor.js to:
   - Initialize properly for both new and existing outcomes
   - Ensure the column collection and saving work correctly
   - Support adding/removing columns before the outcome is created

3. Styling updates:
   - Ensure table preview matches the actual outcome table view
   - Update buttons to match the program table styles
