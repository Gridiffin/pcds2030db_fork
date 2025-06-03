# Fix Admin Button Logic and Remove Status/Description Fields

## Problem Description
The admin side of the program management system has issues with button logic and contains references to status and description fields that have been manually deleted from the database tables.

## Issues Identified
1. **Button Logic Issues**: 
   - Buttons are shown even when there are no submissions
   - Draft programs are showing the whole program interface when they shouldn't
   
2. **Removed Fields References**:
   - Status field references still exist in code but column was deleted from table
   - Description field references still exist in code but column was deleted from table

## Solution Steps

### Step 1: Analyze Current Button Logic
- [x] Review admin programs view (`app/views/admin/programs/programs.php`)
- [x] Identify button display conditions
- [x] Document current logic flow
- [x] Confirmed database schema - `programs.description` and `program_submissions.status` columns do NOT exist

### Step 2: Fix Button Logic
- [x] Update button display conditions to check for submissions existence
- [x] Fix rating display to use content_json instead of deleted status column
- [x] Implement proper conditional rendering
- [x] Remove description field display (column doesn't exist)

### Step 3: Remove Status/Description Field References
- [x] Search and identify all status field references
- [x] Search and identify all description field references  
- [x] Remove or update references to deleted columns
- [x] Update database queries to exclude deleted columns

### Step 4: Update Related Files
- [x] Update view_program.php (removed description field references and fixed status references)
- [x] Update edit_program.php (removed description field and form, fixed database query)
- [x] Update assign_programs.php (removed description field and form, fixed database query)
- [x] Update resubmit.php (removed status field reference in logging)
- [x] Update reopen_program.php (replaced status display with draft status)

### Step 5: Test Changes
- [x] All files updated to remove references to deleted columns
- [x] Button logic fixed to only show when submissions exist
- [x] Rating display fixed to use content_json instead of deleted status column
- [x] Database queries updated to exclude deleted columns

## Current Button Logic Analysis

In `programs.php`, the button logic is:
```php
<?php if (isset($program['submission_id'])): // Ensure there is a submission record ?>
    <?php if (!empty($program['is_draft'])): ?>
        // Show Resubmit button
    <?php elseif (isset($program['status']) && $program['status'] !== null): ?>
        // Show Unsubmit button  
    <?php endif; ?>
<?php endif; ?>
```

## Proposed Fix
1. **No Submissions**: Don't show any submit/unsubmit buttons
2. **Draft Submissions**: Show resubmit button only
3. **Submitted Programs**: Show unsubmit button only
4. **Remove all references to deleted status/description columns**

## Files to Modify
- `app/views/admin/programs/programs.php`
- `app/views/admin/programs/view_program.php`
- `app/views/admin/programs/edit_program.php`
- `app/views/admin/programs/assign_programs.php`
- `app/views/admin/programs/resubmit.php`
- `app/views/admin/programs/reopen_program.php`

## Summary of Changes Made

### Button Logic Fixes
1. **Fixed submission button logic**: Buttons now only appear when submissions exist
2. **Improved draft handling**: Clear distinction between draft and submitted programs
3. **Added "No submissions" indicator**: Shows when no submissions exist for a program

### Database Column References Removed
1. **Removed `programs.description` references**: 
   - Removed from forms in edit_program.php and assign_programs.php
   - Removed from database INSERT/UPDATE queries
   - Removed from display in view_program.php and programs.php

2. **Removed `program_submissions.status` references**:
   - Updated rating display to use content_json instead
   - Fixed logging in resubmit.php
   - Updated status display in reopen_program.php

### Edit Permissions Updated
- Removed "description" and "status_text" from edit permissions since columns don't exist
- Updated permission checkboxes in admin forms

### Rating Display Fixed
- Programs now get rating from content_json['rating'] field
- Fallback to 'not-started' when no rating is available
- Consistent rating display across all admin views

All changes maintain backward compatibility and ensure the system works correctly without the deleted database columns.