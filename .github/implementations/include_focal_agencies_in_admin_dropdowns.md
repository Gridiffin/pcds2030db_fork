# Include Focal Agencies in Admin Dropdowns

## Overview
Update all agency dropdowns and filters in admin pages to include both regular agency users (role = 'agency') and focal agency users (role = 'focal') to provide comprehensive access and management capabilities.

## Implementation Steps

### ✅ Step 1: Update Admin Programs Page Filter
- **File**: `app/views/admin/programs/programs.php`
- **Change**: Updated agency filter dropdown query to include both 'agency' and 'focal' roles
- **Query Modified**: 
  ```sql
  -- Before:
  SELECT user_id, agency_name FROM users WHERE role = 'agency' ORDER BY agency_name
  
  -- After:
  SELECT user_id, agency_name FROM users WHERE role IN ('agency', 'focal') ORDER BY agency_name
  ```

### ✅ Step 2: Update Admin Edit Program Page
- **File**: `app/views/admin/programs/edit_program.php`
- **Change**: Updated agency owner selection dropdown to include both 'agency' and 'focal' roles
- **Query Modified**:
  ```sql
  -- Before:
  SELECT user_id AS agency_id, agency_name FROM users WHERE role = 'agency' AND is_active = 1 ORDER BY agency_name ASC
  
  -- After:
  SELECT user_id AS agency_id, agency_name FROM users WHERE role IN ('agency', 'focal') AND is_active = 1 ORDER BY agency_name ASC
  ```

### ✅ Step 3: Update Admin Assign Programs Page
- **File**: `app/views/admin/programs/assign_programs.php`
- **Change**: Updated agency selection for program assignment to include both 'agency' and 'focal' roles
- **Query Modified**:
  ```sql
  -- Before:
  SELECT u.user_id, u.agency_name, s.sector_id, s.sector_name FROM users u JOIN sectors s ON u.sector_id = s.sector_id WHERE u.role = 'agency' ORDER BY u.agency_name
  
  -- After:
  SELECT u.user_id, u.agency_name, s.sector_id, s.sector_name FROM users u JOIN sectors s ON u.sector_id = s.sector_id WHERE u.role IN ('agency', 'focal') ORDER BY u.agency_name
  ```

## Files Updated
1. `app/views/admin/programs/programs.php` - Agency filter for both unsubmitted and submitted programs sections
2. `app/views/admin/programs/edit_program.php` - Agency owner dropdown
3. `app/views/admin/programs/assign_programs.php` - Agency selection for program assignment

## Testing Verification
- [x] Syntax validation passed for all modified files
- [ ] Manual testing of agency dropdowns in admin interface
- [ ] Verify focal agencies appear in filter options
- [ ] Verify program assignment works with focal agencies
- [ ] Verify program editing allows selection of focal agencies as owners

## Impact
- Admin users can now see and interact with both regular agencies and focal agencies in all relevant dropdowns
- Program assignment, editing, and filtering now includes focal agencies
- Maintains consistency with the existing user role system
- No breaking changes to existing functionality

## Notes
- Focal agencies have role = 'focal' in the users table
- This update aligns with the system's existing support for focal users throughout the application
- All queries maintain the same ordering (by agency_name) for consistent user experience
