# Restrict Program Deletion to Owners and Focal Users

## Problem
Currently, all users can see and use the delete button for programs in the view_programs.php page. This needs to be restricted to only owners and focal users for security and data integrity.

## Solution Steps

### 1. ✅ Analyze Current Delete Button Implementation
- [x] Found delete buttons in three sections of view_programs.php:
  - Program Templates section (lines ~378)
  - Draft Programs section (lines ~619) 
  - Finalized Programs section (lines ~826)

### 2. ✅ Implement Permission-Based Delete Button Visibility
- [x] Add permission check using `is_program_owner()` and `is_focal_user()` functions
- [x] Wrap delete buttons in conditional PHP blocks
- [x] Ensure consistent implementation across all three program sections

### 3. ✅ Update Backend Delete Handler
- [x] Updated delete_program.php permission check to allow focal users
- [x] Added server-side validation to prevent unauthorized deletions
- [x] Updated error messages to reflect new permission rules

### 4. ✅ Testing & Bug Fixes
- [x] Fixed fatal error in view_programs.php caused by undefined variables during edit process
- [x] Cleaned up duplicate array initializations
- [x] Verified file structure integrity
- [ ] Test as focal user (should see delete buttons)
- [ ] Test as program owner (should see delete buttons)
- [ ] Test as program editor (should NOT see delete buttons)
- [ ] Test as program viewer (should NOT see delete buttons)

## Functions Available
- `is_focal_user()`: Returns true if user role is 'focal'
- `is_program_owner($program_id)`: Returns true if user is owner of specific program (includes focal users)

## Files to Modify
- `app/views/agency/programs/view_programs.php`: Add permission checks around delete buttons
- `app/views/agency/programs/delete_program.php`: Verify server-side permissions (if exists)

## Security Considerations
- Frontend restrictions are for UX only - server-side validation is critical
- Both focal users and program owners should have delete permissions
- Editors and viewers should not have delete access
