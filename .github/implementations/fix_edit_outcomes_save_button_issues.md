# Fix Edit Outcomes Save Button Issues

## Problem Description
1. Save changes button doesn't work when editing an outcome
2. Duplicate save changes buttons in edit outcomes page

## Analysis
Based on examination of `edit_outcomes.php`:

### Current State
- Form has correct submission handling in JavaScript
- Form properly collects data with `collectCurrentData()` function
- Two buttons exist: "Save Outcome" and "Save as Draft"
- Form submission event listener is properly attached

### Potential Issues
1. JavaScript might not be loading properly due to deprecated metric-editor.js
2. Form validation might be preventing submission
3. Data collection might be failing
4. CSS styling might be hiding buttons

## Investigation Steps
- [x] Check if JavaScript is loading correctly
- [x] Check console for JavaScript errors
- [x] Verify form submission is working
- [x] Check if data collection is working properly
- [x] Look for duplicate button elements in HTML
- [ ] Test form submission functionality

## Root Cause Found
The issue is a JavaScript conflict:
1. `edit_outcomes.php` has embedded JavaScript for form handling
2. It also loads `metric-editor.js` which is deprecated
3. `metric-editor.js` tries to load `outcome-editor.js` 
4. `outcome-editor.js` looks for `metricEditorContainer` which doesn't exist in edit_outcomes.php
5. This creates JavaScript conflicts and prevents proper form submission

## Solution Steps
- [x] Fix deprecated JavaScript file loading
- [x] Ensure proper event handlers are attached
- [x] Remove conflicting JavaScript files
- [x] Fix PHP code formatting issues
- [x] Add form validation and debugging
- [x] Improve button handling with proper event listeners
- [ ] Test save functionality
- [ ] Verify both "Save Outcome" and "Save as Draft" work correctly

## Changes Made
1. **Removed conflicting JavaScript**: Removed `metric-editor.js` from additionalScripts array to prevent conflicts
2. **Fixed PHP formatting**: Fixed malformed lines in the UPDATE query section
3. **Added form validation**: Added validation to check for table name and at least one column
4. **Added debugging**: Added console.log statements to help track form submission issues
5. **Improved button handling**: Replaced inline onclick handlers with proper event listeners
6. **Added icons**: Added FontAwesome icons to buttons for better UX

## Files to Check
- `app/views/agency/outcomes/edit_outcomes.php` - Main edit form
- `assets/js/metric-editor.js` - Deprecated JavaScript file
- `assets/js/outcome-editor.js` - New JavaScript file (if exists)
- Browser console for JavaScript errors
