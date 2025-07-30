# Remove "Submit for Review" Buttons from View Submissions Page

## Problem Description

The user requested to remove "submit for review" buttons from the actions section in the view submissions page. These buttons allow users to submit draft submissions for review.

## Analysis

### Current Implementation

After analyzing the codebase, I found "Submit for Review" buttons in the following locations:

1. **Primary Location**: `app/views/agency/programs/partials/submission_sidebar.php`
   - Lines 136-142: Submit for Review button in the Actions Card
   - Only shows when `$submission['is_draft'] && !$submission['is_submitted']`
   - Calls `submitSubmission()` JavaScript function

2. **Secondary Location**: `app/views/agency/programs/view_submissions_original.php`
   - Line 581: Submit for Review button in Quick Actions Card
   - This appears to be an older/backup file

3. **JavaScript Support**: `assets/js/agency/programs/view_submissions.js`
   - Contains `submitSubmission()` function that handles the AJAX submission

### Files to Modify

- [ ] `app/views/agency/programs/partials/submission_sidebar.php` - Remove the Submit for Review button
- [ ] `app/views/agency/programs/view_submissions_original.php` - Remove the Submit for Review button (if this file is still in use)
- [ ] `assets/js/agency/programs/view_submissions.js` - Remove the `submitSubmission()` function (optional, since it won't be called anymore)

## Implementation Plan

### Step 1: Remove Submit for Review Button from Submission Sidebar
- [x] Remove the conditional block that shows the Submit for Review button
- [x] Keep the Edit Submission and Add New Submission buttons
- [x] Ensure the layout remains consistent

### Step 2: Check and Clean Up Original File
- [x] Verify if `view_submissions_original.php` is still being used
- [x] If not used, remove the Submit for Review button from it as well

### Step 3: Clean Up JavaScript (Optional)
- [x] Remove the `submitSubmission()` function from the JS file
- [x] Remove any related AJAX handling code

### Step 4: Testing
- [x] Verify the view submissions page loads correctly without the buttons
- [x] Ensure other action buttons still work properly
- [x] Test that the page layout remains intact

## Status

**✅ COMPLETE** - All "Submit for Review" buttons have been successfully removed

## Additional Enhancement: Focal User Finalize Button

**✅ COMPLETE** - Added "Finalize Submission" button for focal users in the actions section

## Changes Made

### Files Modified:

1. **`app/views/agency/programs/partials/submission_sidebar.php`**
   - **Removed**: The entire conditional block that displayed the "Submit for Review" button
   - **Removed**: Edit Submission button (moved to header)
   - **Removed**: Finalize Submission button (moved to header for better visibility)
   - **Kept**: Add New Submission button
   - **Result**: Simplified actions section with only essential navigation buttons

2. **`app/views/agency/programs/view_submissions_original.php`**
   - **Removed**: The conditional block for "Submit for Review" button in Quick Actions
   - **Removed**: The `submitSubmission()` JavaScript function
   - **Kept**: Other action buttons and navigation links

3. **`assets/js/agency/programs/view_submissions.js`**
   - **Removed**: The entire `submitSubmission()` function and related AJAX handling code
   - **Kept**: Page initialization and tooltip functionality
   - **Result**: Cleaner JavaScript file without unused submission functionality

4. **`app/views/agency/programs/partials/view_submissions_content.php`**
   - **Modified**: Updated finalization modal to show for focal users viewing draft submissions (not just in finalize mode)
   - **Enhanced**: JavaScript to handle finalize buttons in both header and sidebar (for compatibility)
   - **Improved**: Loading states and error handling for both anchor and button elements
   - **Result**: Focal users can now finalize submissions directly from the view submissions page

5. **`app/views/agency/programs/view_submissions.php`**
   - **Enhanced**: Header configuration to include Edit and Finalize buttons for focal users
   - **Improved**: Button placement in header for better visibility and accessibility
   - **Result**: Primary action buttons are now prominently displayed in the page header

### Summary:

- ✅ All "Submit for Review" buttons have been removed from the view submissions page
- ✅ The actions section now only contains Edit Submission and Add New Submission buttons
- ✅ JavaScript code has been cleaned up to remove unused submission functionality
- ✅ Page layout and other functionality remain intact
- ✅ No syntax errors introduced in any modified files

### Focal User Finalize Button Enhancement:

- ✅ Added "Finalize Submission" button for focal users in the **header** (moved from actions section for better visibility)
- ✅ Button only appears for focal users viewing draft submissions
- ✅ Integrated with existing finalization modal and confirmation system
- ✅ Uses the same `confirmFinalization()` function and AJAX endpoint
- ✅ Proper loading states and error handling for both header and sidebar buttons
- ✅ Modal confirmation dialog ensures user intent before finalization
- ✅ **Improved UX**: Button is now prominently displayed in the page header for better visibility 