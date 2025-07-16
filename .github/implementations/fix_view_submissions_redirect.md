# Fix View Submissions Redirect Issue

## Problem

When clicking "View Submissions" from the admin programs page, users are redirected back to the main page instead of the submissions view. This is caused by missing required parameters.

## Root Cause Analysis

- The `view_submissions.php` file requires both `program_id` and `period_id` parameters
- The JavaScript in `programs_admin.js` only passes `program_id`
- When `period_id` is missing, the validation logic redirects back to `programs.php`

## Solution Steps

### ✅ Step 1: Analyze the Issue

- [x] Examine the redirect logic in `view_submissions.php`
- [x] Check the JavaScript action generation in `programs_admin.js`
- [x] Identify the missing parameter requirement

### ✅ Step 2: Choose Implementation Approach

- [x] Option A: Create a program submissions list page (recommended)
- [x] Create `list_program_submissions.php` that shows all submissions for a program
- [x] Update JavaScript to use this new page
- [x] Provide links to individual submission details from there

### ✅ Step 3: Implement the Fix

- [x] Created `list_program_submissions.php` with comprehensive submission listing
- [x] Updated JavaScript action URL to point to new file
- [x] Included proper breadcrumb navigation and back links

### ✅ Step 4: Verify and Clean Up

- [x] Test the "View Submissions" functionality
- [x] Ensure proper navigation flow
- [x] Added missing include for statistics.php
- [x] Verified no syntax errors

## Files Modified

- `assets/js/admin/programs_admin.js` - Updated the view submissions URL
- `app/views/admin/programs/list_program_submissions.php` - NEW FILE: List all submissions for a program

## Expected Outcome

Users should be able to click "View Submissions" and see a list of all submissions for that program across different reporting periods.
