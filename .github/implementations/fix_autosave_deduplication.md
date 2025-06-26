# Fix Auto-Save Deduplication Issue in Create Program

## Problem Description
The auto-save functionality is failing with "Program number is already in use" error because:
1. Each auto-save attempt is trying to create a new program instead of updating the existing draft
2. The program number validation is triggering on subsequent auto-saves of the same program
3. The auto-save logic isn't properly tracking whether this is a new program or an update to an existing draft

## Root Cause Analysis
- ✅ First auto-save creates a new program with program_id and program_number
- ❌ Subsequent auto-saves don't properly use the existing program_id 
- ❌ Program number uniqueness validation triggers even for updates to the same program
- ❌ The auto-save might not be setting the program_id hidden field correctly after first save

## Implementation Plan

### ✅ Step 1: Create Implementation Document
- [x] Document the auto-save deduplication problem

### ✅ Step 2: Fix Auto-Save Program ID Tracking
- [x] Ensure the program_id hidden field is updated after first successful auto-save
- [x] Check that subsequent auto-saves use UPDATE instead of INSERT for the same program
- [x] Fix JavaScript to properly track and send the program_id
- [x] Fix auto-save target data format to match new backend structure
- [x] Add program_id to collectFormData() function

### ✅ Step 3: Fix Program Number Validation for Updates
- [x] Ensure program number validation excludes the current program_id when checking for duplicates
- [x] Update validation logic in backend functions to handle auto-save updates correctly
- [x] Add program number validation to update_program_draft_only function for consistency

### ⚠️ Step 4: Test Auto-Save Flow
- [ ] Test that first auto-save creates new program and returns program_id
- [ ] Test that subsequent auto-saves update the existing program
- [ ] Verify program number validation works correctly for updates

## Summary of Fixes Applied

### ✅ Frontend Changes (create_program.php):
1. **Fixed collectFormData()**: Added `program_id` to the basic inputs collection
2. **Fixed auto-save target format**: Changed from old `targets[${index}][target]` format to new array format (`target_text[]`, `target_number[]`, etc.) to match backend expectations
3. **Proper program_id tracking**: Auto-save response properly sets the program_id hidden field after first successful save

### ✅ Backend Changes (programs.php):
1. **Added program number validation to update_program_draft_only()**: Now validates program numbers and excludes current program_id from duplicate checks
2. **Consistent validation logic**: All update functions now use `is_program_number_available($program_number, $program_id)` to exclude the current program from duplicate checks

### ✅ Expected Result:
- **First Auto-Save**: Creates new program, returns program_id, updates hidden field
- **Subsequent Auto-Saves**: Uses existing program_id, calls update function, bypasses duplicate number validation for same program
- **No More "Program number already in use" Errors**: Program can be auto-saved multiple times without conflicts

The auto-save deduplication issue should now be resolved. The system will properly track the program_id and use update operations instead of trying to create duplicate programs.

## Expected Fix
The auto-save should:
1. **First Save**: Create new program, return program_id, update hidden field
2. **Subsequent Saves**: Update existing program using program_id, bypass duplicate number check for same program
3. **No Duplicates**: Same program number should be allowed for updates to the same program

## Files to Check
- `c:\laragon\www\pcds2030_dashboard\app\views\agency\programs\create_program.php` - Auto-save JavaScript
- `c:\laragon\www\pcds2030_dashboard\app\lib\agencies\programs.php` - Backend auto-save functions
- `c:\laragon\www\pcds2030_dashboard\app\lib\numbering_helpers.php` - Program number validation
