# Fix Targets Not Retrieving Issue

## Problem Description

User reports that targets are "not retrieving right" in the admin edit program interface. The program_targets table exists with 8 rows, but targets are not displaying correctly in the edit_program.php interface.

## Root Cause Analysis

The targets in edit_program.php are loaded from the `content_json` field in the `program_submissions` table, not directly from the `program_targets` table. The system expects targets to be stored as JSON data in submissions.

## Investigation Steps

- [x] Confirm program_targets table exists with data (8 rows)
- [x] Verify get_admin_program_details() function loads submissions correctly
- [x] Check how targets are extracted from content_json in edit_program.php
- [x] Check if program_submissions table has data for the current program
- [x] **FOUND ISSUE**: content_json column doesn't exist in program_submissions table
- [x] Verified program_targets table structure and data exists

## Solution Steps

### 1. Database Investigation

- [x] Check program_submissions table for current program data
- [x] **FOUND**: content_json column doesn't exist in program_submissions table
- [x] Verified program_targets table structure and data exists

### 2. Code Updates (Completed)

- [x] Modified edit_program.php target loading to use program_targets table as fallback
- [x] Updated target saving to write to program_targets table
- [x] Fixed content_json references in POST handling to avoid database errors
- [x] Updated get_admin_program_details() to remove content_json processing
- [x] Added defensive programming for missing content_json field

### 3. Agency Pattern Implementation (In Progress)

- [ ] Simplify admin edit_program.php to follow agency pattern
- [ ] Separate basic program editing from submission management
- [ ] Use program_targets table directly instead of content_json approach
- [ ] Remove complex submission handling from basic edit form

### 4. Testing

- [ ] Test target display functionality
- [ ] Verify target saving works correctly
- [ ] Test with different data scenarios

## Files to Modify

- `app/views/admin/programs/edit_program.php` - Add fallback target loading
- Potentially create migration script if needed

## Expected Outcome

Targets will display correctly in the admin edit program interface, with proper fallback mechanisms for different data storage scenarios.
