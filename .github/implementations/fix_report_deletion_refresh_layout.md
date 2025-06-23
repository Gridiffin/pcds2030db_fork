# Fix Report Deletion Refresh Layout Issue

## Problem
When a report is deleted, the Recent Reports section refreshes but changes from the modern card-like structure to an old table layout. Manual page refresh brings back the correct card layout.

## Root Cause Analysis
The issue is likely in the JavaScript that handles report deletion and refreshes the Recent Reports section. It's probably:
1. Using an old AJAX endpoint that returns table HTML
2. Using old JavaScript code that generates table structure
3. Not using the new card-grid HTML structure

## Investigation Steps
- [ ] Find the JavaScript code that handles report deletion
- [ ] Locate the AJAX endpoint or function that refreshes Recent Reports
- [ ] Check what HTML structure it's returning/generating
- [ ] Update it to use the new card-grid structure

## Implementation Steps
- [ ] Review report-generator.js for deletion handling
- [ ] Check AJAX endpoints in app/ajax/ folder
- [ ] Update the refresh mechanism to use new card structure
- [ ] Test the deletion and refresh functionality

## Success Criteria
- Reports delete correctly
- Recent Reports section maintains card-grid layout after deletion
- No layout switching between table and card formats

## Files to Check
- assets/js/report-generator.js
- app/ajax/ (AJAX endpoints)
- Any functions that generate Recent Reports HTML
