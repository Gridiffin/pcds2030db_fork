# Restrict Program Deletion to Creators and Focal Users

## Problem
Currently, the `is_program_owner()` function checks if the user's agency has "owner" role for a program, meaning any user from an agency with "owner" role can delete programs. This is too permissive - we want only the actual program creator (individual user who created it) and focal users to delete programs.

## Current Behavior
- User "sfc3" (ID: 7) can see delete button for "sfc program 1" (ID: 19)
- Program was created by user "sfc1" (ID: 5) 
- Both users belong to Agency 2 (SFC) which has "owner" role for the program
- `is_program_owner(19)` returns TRUE for sfc3 because their agency has owner role

## Required Solution
Delete permission should be restricted to:
1. **Program Creator**: The individual user who created the program (`programs.created_by`)
2. **Focal Users**: Users with role "focal" (cross-agency oversight)

## Implementation Steps

### ✅ Step 1: Create new permission function
- [ ] Add `is_program_creator($program_id)` function to check if current user created the program
- [ ] This function checks `$_SESSION['user_id']` against `programs.created_by`

### ✅ Step 2: Update delete permission logic
- [ ] Change delete button logic from `is_focal_user() || is_program_owner($program_id)` 
- [ ] To: `is_focal_user() || is_program_creator($program_id)`

### ✅ Step 3: Update frontend (view_programs.php)
- [ ] Replace `is_program_owner($program_id)` with `is_program_creator($program_id)` in all delete button checks
- [ ] Apply to templates, drafts, and finalized sections

### ✅ Step 4: Update backend (delete_program.php)
- [ ] Update server-side validation to use new permission logic
- [ ] Ensure error messages are clear about creator-only restrictions

### ✅ Step 5: Test and cleanup
- [ ] Test with different users (creator, non-creator from same agency, focal user)
- [ ] Remove debug files and debug banners
- [ ] Verify delete buttons only show for appropriate users

## Files to Modify
1. `app/lib/agencies/program_agency_assignments.php` - Add `is_program_creator()` function
2. `app/views/agency/programs/view_programs.php` - Update delete button logic
3. `app/views/agency/programs/delete_program.php` - Update server-side validation

## Testing Scenarios
- **sfc3** (non-creator): Should NOT see delete button
- **sfc1** (creator): Should see delete button  
- **focal user**: Should see delete button for any program
- **admin**: Should see delete button for any program
