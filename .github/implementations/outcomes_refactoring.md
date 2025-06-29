# Outcomes Pages Refactoring Plan

## Overview
Refactoring the view_outcome.php file by separating the view and edit functionality into separate files, and extracting inline scripts into dedicated JavaScript files.

## Task Breakdown

### 1. File Structure Changes
- [x] Keep existing `submit_outcomes.php` as the main outcomes list page
- [x] Create new `edit_outcome.php` for editing functionality
- [x] Refactor `view_outcome.php` to only handle viewing (remove edit mode)
- [x] Extract JavaScript code into separate files

### 2. JavaScript Files to Create
- [x] Create `assets/js/outcomes/view-outcome.js` - For view page functionality
- [x] Create `assets/js/outcomes/edit-outcome.js` - For edit page functionality  
- [x] Create `assets/js/outcomes/chart-manager.js` - For chart functionality

### 3. PHP Files to Create/Modify
- [x] `app/views/agency/outcomes/edit_outcome.php` - New edit page
- [x] Modify `app/views/agency/outcomes/view_outcome.php` - Remove edit functionality
- [x] Update references in other files (`submit_outcomes.php`)

### 4. CSS Considerations
- [x] Ensure existing CSS files are properly referenced
- [x] Use existing outcome-specific CSS files

## Implementation Steps

1. [x] Create the new edit_outcome.php file with edit functionality from view_outcome.php
2. [x] Remove edit functionality from view_outcome.php 
3. [x] Extract all inline JavaScript into separate files
4. [x] Update additionalScripts references
5. [x] Update navigation links in submit_outcomes.php
6. [x] Clean up any redundant code

## Files to be Modified
- `app/views/agency/outcomes/view_outcome.php`
- `app/views/agency/outcomes/edit_outcome.php` (new)
- `assets/js/outcomes/` (new directory and files)

## Testing Checklist
- [x] View outcome functionality works correctly
- [x] Edit outcome functionality works correctly  
- [x] JavaScript files load properly
- [x] No console errors in view mode
- [x] All features from original file are preserved
- [x] Navigation between view and edit modes works

## Summary

The outcomes pages refactoring has been completed successfully:

### Files Created/Modified:
1. **`assets/js/outcomes/view-outcome.js`** - View-only functionality and chart initialization
2. **`assets/js/outcomes/edit-outcome.js`** - Complete edit functionality with table structure management
3. **`assets/js/outcomes/chart-manager.js`** - Chart rendering and download functionality
4. **`app/views/agency/outcomes/edit_outcome.php`** - Dedicated edit page with full editing capabilities
5. **`app/views/agency/outcomes/view_outcome.php`** - Simplified view-only page
6. **`app/views/agency/outcomes/submit_outcomes.php`** - Updated navigation links

### Key Improvements:
- **Separation of Concerns**: View and edit functionality are now in separate files
- **Modular JavaScript**: All inline scripts extracted to dedicated JS files
- **Better Navigation**: Clear distinction between view and edit actions
- **Reduced Complexity**: Each file now has a single, focused responsibility
- **Maintainable Code**: Easier to modify and extend individual components

### User Experience:
- Users can view outcomes in a clean, read-only interface
- Editing is accessed via a dedicated edit page with full functionality
- Chart and structure viewing capabilities preserved
- All original features maintained while improving code organization
