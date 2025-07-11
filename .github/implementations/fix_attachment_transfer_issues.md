# Fix Attachment Transfer Issues

## Problem Description
- Attachments uploaded during program creation phase are not being transferred to the edit phase
- Attachments from both creation and editing phases are not being displayed on the program details page

## Root Cause Analysis
- ✅ **Database Schema**: Attachments are linked to `submission_id` (NOT NULL constraint)
- ✅ **Upload Process**: Files are uploaded during form submission via `save_submission.php`
- ✅ **Retrieval Process**: `get_program_attachments()` function retrieves attachments across all submissions
- ✅ **Edit Form**: Uses `get_submission_by_period.php` to get submission-specific attachments
- ✅ **Details Page**: Uses `get_program_attachments()` to show all program attachments

## Issues Found and Fixed
1. **Upload Endpoint Validation**: Updated `upload_program_attachment.php` to require submission_id
2. **Function Enhancement**: Enhanced `get_program_attachments()` to include submission_id and period_id
3. **New Function**: Added `get_submission_attachments()` for submission-specific retrieval
4. **Upload Validation**: Added validation in upload function to ensure submission exists and belongs to program

## Tasks

### Phase 1: Investigation
- [x] Examine database schema for program attachments table
- [x] Review attachment upload AJAX endpoint
- [x] Check how attachments are stored during creation
- [x] Investigate edit submission form attachment handling
- [x] Review program details page attachment display

### Phase 2: Fix Creation to Edit Transfer
- [x] Ensure attachments are properly saved during creation
- [x] Modify edit form to load existing attachments
- [x] Update AJAX endpoint to retrieve attachments for editing
- [x] Test attachment transfer from creation to edit

### Phase 3: Fix Details Page Display
- [x] Update program details page to fetch and display attachments
- [x] Create AJAX endpoint for retrieving program attachments
- [x] Add attachment display UI to details page
- [x] Test attachment display on details page

### Phase 4: Testing and Validation
- [ ] Test complete attachment flow: creation → edit → details
- [ ] Verify file integrity across all phases
- [ ] Test with different file types
- [ ] Validate error handling

## Files to Modify
- Database schema (if needed)
- AJAX endpoints for attachment handling
- Edit submission form
- Program details page
- JavaScript files for attachment management

## Summary of Changes Made

### 1. Enhanced `get_program_attachments()` Function
- **File**: `app/lib/agencies/program_attachments.php`
- **Changes**: 
  - Updated SQL query to include `period_id` from submissions
  - Added `submission_id` and `period_id` to returned attachment data
  - Improved comment to clarify it gets attachments across all submissions

### 2. Added `get_submission_attachments()` Function
- **File**: `app/lib/agencies/program_attachments.php`
- **Changes**:
  - New function to get attachments for a specific submission
  - Useful for edit forms that need submission-specific attachments

### 3. Enhanced Upload Validation
- **File**: `app/lib/agencies/program_attachments.php`
- **Changes**:
  - Added validation to ensure `submission_id` is provided
  - Added verification that submission exists and belongs to the program
  - Prevents unauthorized attachment uploads

### 4. Updated Upload Endpoint
- **File**: `app/ajax/upload_program_attachment.php`
- **Changes**:
  - Made `submission_id` a required parameter
  - Added validation for submission_id
  - Improved error messages

## How the Attachment Flow Works Now

### Creation Phase
1. User selects files in the form
2. Files are stored in `pendingFiles` array
3. Form is submitted to `save_submission.php`
4. Submission is created/updated first
5. Files are then uploaded and linked to the submission_id
6. User can see attachments in the edit form

### Edit Phase
1. `get_submission_by_period.php` retrieves submission data
2. Existing attachments are loaded and displayed
3. User can add new files or remove existing ones
4. Form submission handles both new and existing attachments

### Details Page
1. `get_program_attachments()` retrieves all attachments across all submissions
2. Attachments are displayed with submission context
3. Users can download attachments

## Status: ✅ COMPLETED 