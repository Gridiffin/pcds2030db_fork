# Remove UI Elements and Fix Redirect Flow

## Problem Analysis
Three specific UI/UX improvements needed:

1. **Remove Draft/Submitted Badge**: 
   - Badge in view outcome header is unnecessary visual clutter
   - Status information available elsewhere in the interface

2. **Remove Save as Draft Button**: 
   - Simplify edit outcomes interface by removing draft functionality
   - Focus on single save action for cleaner UX

3. **Fix Redirect After Save**:
   - Currently redirects to main outcomes page after saving
   - Should redirect back to view details page for better workflow

## Solution Implementation

### ✅ Phase 1: Remove Draft/Submitted Badge from View Details
- [x] **Task 1.1**: Locate badge in view_outcome.php header
- [x] **Task 1.2**: Remove badge HTML and keep only the Flexible Structure badge
- [x] **Task 1.3**: Test view outcome page display

### ✅ Phase 2: Remove Save as Draft Button from Edit Page
- [x] **Task 2.1**: Find edit_outcomes.php file
- [x] **Task 2.2**: Locate and remove "Save as Draft" button
- [x] **Task 2.3**: Keep only "Save Changes" button
- [x] **Task 2.4**: Update any related JavaScript/form handling

### ✅ Phase 3: Fix Save Redirect Flow
- [x] **Task 3.1**: Find form submission handler in edit_outcomes.php
- [x] **Task 3.2**: Change redirect destination from outcomes list to view details
- [x] **Task 3.3**: Ensure outcome_id is properly passed in redirect
- [x] **Task 3.4**: Test save and redirect flow

### ✅ Phase 4: Testing and Validation
- [x] **Task 4.1**: Test view outcome page without badge
- [x] **Task 4.2**: Test edit outcomes page without draft button
- [x] **Task 4.3**: Test save redirect flow works correctly
- [x] **Task 4.4**: Verify no broken functionality

## ✅ Implementation Complete

### Changes Made:

1. **Removed Draft/Submitted Badge from View Details Header**
   - Removed conditional badge display logic in `view_outcome.php`
   - Kept only the "Flexible Structure" badge for cleaner UI
   - Simplified header layout and reduced visual clutter

2. **Removed Save as Draft Button from Edit Page**
   - Removed "Save as Draft" button from `edit_outcomes.php`
   - Updated button text from "Save Outcome" to "Save Changes" for clarity
   - Removed JavaScript event handler for draft button functionality
   - Simplified form submission to single save action

3. **Fixed Save Redirect Flow**
   - Changed redirect destination from `submit_outcomes.php` to `view_outcome.php`
   - Added `outcome_id` parameter to redirect URL for proper navigation
   - Added `saved=1` parameter to show success message on view page
   - Improved user workflow by returning to details page after save

4. **Code Cleanup**
   - Removed unused draft-related JavaScript code
   - Maintained clean form structure and functionality
   - Preserved all error handling and audit logging

### UI/UX Improvements:
- ✅ Cleaner view outcome header without status badges
- ✅ Simplified edit page with single save action
- ✅ Better workflow with redirect back to details view
- ✅ Success message shown after save operation
- ✅ Maintained all core functionality without draft complexity

## Expected Changes
- ✅ View outcome header will show only "Flexible Structure" badge
- ✅ Edit outcomes page will have single "Save Changes" button
- ✅ Saving changes will redirect to view details page instead of outcomes list

---
**Status**: ✅ **COMPLETE**  
**Priority**: Medium  
**Completion Time**: 30 minutes
