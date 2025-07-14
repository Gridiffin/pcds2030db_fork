# Multi-Agency Permission System Rollout

## Objective
Update all program views to use the new multi-agency permission system with focal user privileges.

## Implementation Plan

### Phase 1: Core Permission System ✅
- [x] Create program_agency_assignments.php library
- [x] Add database migration scripts
- [x] Include focal user logic (treat focal users as owners for all programs)
- [x] Update view_submissions.php to use new permission system

### Phase 2: Update Key Program Views ✅
- [x] Update program_details.php to use new permission system
- [x] Update edit_submission.php to use new permission system
- [x] Update edit_program.php to use new permission system
- [x] Update add_submission.php to use new permission system

### Phase 3: Update Secondary Views ✅
- [x] Update view_programs.php to use new permission system
- [x] Update delete_program.php to use new permission system
- [x] Update create_program.php to consider new permission system

### Phase 4: Test and Validate
- [ ] Test permission system with different user roles
- [ ] Validate focal user access to all programs
- [ ] Test multi-agency assignments
- [ ] Verify edit/view restrictions work correctly

## Technical Details

### Permission Functions Available:
- `can_edit_program($program_id, $agency_id = null)` - checks if user can edit
- `can_view_program($program_id, $agency_id = null)` - checks if user can view
- `is_program_owner($program_id, $agency_id = null)` - checks if user is owner
- `get_user_program_role($program_id, $agency_id = null)` - gets user role

### Focal User Logic:
- Focal users return `true` for all permission checks
- Focal users are treated as owners for all programs
- Focal users can assign/remove agencies from programs

### Files to Update:
1. program_details.php - Replace owner_agency_id logic
2. edit_submission.php - Use can_edit_program()
3. edit_program.php - Update permission checks
4. add_submission.php - Use can_edit_program()
5. view_programs.php - Use can_view_program()
6. delete_program.php - Use is_program_owner()

## Files Updated

### Core Permission System:
- [x] `app/lib/agencies/program_agency_assignments.php` - Complete permission management library
- [x] `app/migrations/create_program_agency_assignments.sql` - Database migration script

### Updated Views:
- [x] `app/views/agency/programs/view_submissions.php` - Uses new permission system with can_edit_program()
- [x] `app/views/agency/programs/program_details.php` - Replaced owner_agency_id with new permission functions
- [x] `app/views/agency/programs/edit_submission.php` - Added can_edit_program() permission check
- [x] `app/views/agency/programs/edit_program.php` - Updated to use new permission system
- [x] `app/views/agency/programs/add_submission.php` - Added can_edit_program() permission check
- [x] `app/views/agency/programs/view_programs.php` - Modified query for multi-agency access and focal users
- [x] `app/views/agency/programs/delete_program.php` - Updated to use is_program_owner() function

### Key Changes Made:
1. **Focal User Logic**: All permission functions now return `true` for focal users
2. **Multi-Agency Access**: Programs can now be accessed by multiple agencies with different roles
3. **Permission-Based Queries**: view_programs.php now shows assigned programs for regular users, all programs for focal users
4. **Consistent Error Messages**: All views now use standardized permission error messages
5. **Database Migration**: Existing programs migrated to new assignment system with original agency as owner

## Next Steps
1. Update program_details.php
2. Update edit_submission.php
3. Continue with remaining views
4. Test system functionality
