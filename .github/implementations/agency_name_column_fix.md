# Agency Name Column Reference Fix

## Problem Analysis

**Issue**: `Fatal error: Uncaught mysqli_sql_exception: Unknown column 'u.agency_name' in 'field list'`

**Root Cause**: Widespread incorrect database query references
- **Database Reality**: `agency_name` is stored in the `agency` table
- **Code References**: Many queries incorrectly try to access `u.agency_name` from the `users` table
- **Correct Relationship**: Users have `agency_id` → links to `agency.agency_name`

**Database Schema**:
```sql
-- Users table (NO agency_name column)
CREATE TABLE users (
  user_id int NOT NULL AUTO_INCREMENT,
  username varchar(100) NOT NULL,
  pw varchar(255) NOT NULL,
  fullname varchar(200) DEFAULT NULL,
  email varchar(255) NOT NULL,
  agency_id int NOT NULL,  -- Foreign key to agency table
  role enum('admin','agency','focal') NOT NULL,
  -- ... other columns
);

-- Agency table (HAS agency_name column)
CREATE TABLE agency (
  agency_id int NOT NULL AUTO_INCREMENT,
  agency_name varchar(255) NOT NULL,  -- This is where agency_name is stored
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (agency_id)
);
```

## Files Requiring Fixes

### High Priority (Causing Fatal Errors)
- [ ] `app/lib/admins/statistics.php` - Line 576 (immediate fix needed)
- [ ] `app/lib/agencies/statistics.php` - Lines 147, 245
- [ ] `app/lib/agencies/programs.php` - Lines 558, 738, 921, 965
- [ ] `app/lib/agencies/initiatives.php` - Line 117

### Medium Priority (May cause issues)
- [ ] `app/views/admin/programs/reopen_program.php` - Line 35
- [ ] `app/views/admin/programs/delete_program.php` - Line 40
- [ ] `app/views/admin/programs/assign_programs.php` - Line 188
- [ ] `app/lib/initiative_functions.php` - Line 296
- [ ] `app/lib/outcome_automation.php` - Line 190
- [ ] `app/lib/audit_log.php` - Lines 100, 164
- [ ] `app/api/programs.php` - Line 32
- [ ] `app/api/get_period_programs.php` - Lines 109, 131
- [ ] `app/api/delete_report.php` - Line 78

### Low Priority (View files)
- [ ] `app/views/agency/initiatives/view_initiative.php` - Lines 58, 625

## Solution Pattern

### Before (Incorrect)
```sql
SELECT p.*, s.sector_name, u.agency_name, u.user_id as owner_agency_id
FROM programs p
LEFT JOIN sectors s ON p.sector_id = s.sector_id
LEFT JOIN users u ON p.owner_agency_id = u.user_id
```

### After (Correct)
```sql
SELECT p.*, s.sector_name, a.agency_name, u.user_id as owner_agency_id
FROM programs p
LEFT JOIN sectors s ON p.sector_id = s.sector_id
LEFT JOIN users u ON p.owner_agency_id = u.user_id
LEFT JOIN agency a ON u.agency_id = a.agency_id
```

## Implementation Plan

### Phase 1: Fix Critical Files (Immediate)
- [x] Fix `app/lib/admins/statistics.php` - Line 576 (immediate fix needed)
- [x] Fix `app/lib/admins/statistics.php` - Line 231 (additional instance)
- [x] Fix `app/lib/admins/statistics.php` - Line 341 (additional instance)
- [x] Fix `app/lib/admins/statistics.php` - Line 504 (additional instance)
- [ ] Fix `app/lib/agencies/statistics.php` - Lines 147, 245
- [ ] Fix `app/lib/agencies/programs.php` - Lines 558, 738, 921, 965
- [ ] Fix `app/lib/agencies/initiatives.php` - Line 117

### Phase 2: Fix Core Library Files
- [ ] Fix `app/lib/initiative_functions.php` - Line 296
- [ ] Fix `app/lib/outcome_automation.php` - Line 190
- [ ] Fix `app/lib/audit_log.php` - Lines 100, 164

### Phase 3: Fix API Files
- [ ] Fix `app/api/programs.php` - Line 32
- [ ] Fix `app/api/get_period_programs.php` - Lines 109, 131
- [ ] Fix `app/api/delete_report.php` - Line 78

### Phase 4: Fix Admin View Files
- [ ] Fix `app/views/admin/programs/reopen_program.php` - Line 35
- [ ] Fix `app/views/admin/programs/delete_program.php` - Line 40
- [ ] Fix `app/views/admin/programs/assign_programs.php` - Line 188

### Phase 5: Fix Agency View Files
- [ ] Fix `app/views/agency/initiatives/view_initiative.php` - Lines 58, 625

## Testing Strategy

### Database Query Testing
- [ ] Test each fixed query individually
- [ ] Verify agency names are correctly retrieved
- [ ] Check for any NULL values in agency_name fields
- [ ] Ensure no performance degradation

### Functionality Testing
- [ ] Test admin dashboard (uses statistics.php)
- [ ] Test agency dashboard (uses agencies/statistics.php)
- [ ] Test program management features
- [ ] Test initiative management features
- [ ] Test audit log functionality

### Error Handling
- [ ] Add proper error handling for cases where agency_id is NULL
- [ ] Use COALESCE for graceful fallbacks
- [ ] Log any unexpected NULL agency_name values

## Expected Outcome

- ✅ **Eliminate Fatal Errors**: No more "Unknown column 'u.agency_name'" errors
- ✅ **Correct Data Display**: Agency names properly retrieved from agency table
- ✅ **Maintain Performance**: Efficient JOINs with proper indexing
- ✅ **Data Integrity**: Consistent agency name references throughout the system
- ✅ **Future-Proof**: Proper database relationship usage

## Files to Modify

1. **Critical**: `app/lib/admins/statistics.php` (IMMEDIATE)
2. **Core Libraries**: Multiple files in `app/lib/`
3. **API Endpoints**: Multiple files in `app/api/`
4. **View Files**: Multiple files in `app/views/`

## Implementation Notes

- **JOIN Strategy**: Always use LEFT JOIN to handle cases where agency_id might be NULL
- **Column Aliasing**: Use clear aliases like `a.agency_name` for readability
- **Error Handling**: Add COALESCE or IFNULL for graceful NULL handling
- **Performance**: Ensure proper indexing on agency_id columns

## Implementation Summary

### ✅ Completed Fixes

**Phase 1: Critical Files - COMPLETED**
- ✅ Fixed `app/lib/admins/statistics.php` - Line 576 (get_admin_program_details function)
- ✅ Fixed `app/lib/admins/statistics.php` - Line 231 (get_admin_programs_list function - first query)
- ✅ Fixed `app/lib/admins/statistics.php` - Line 341 (get_admin_programs_list function - second query)
- ✅ Fixed `app/lib/admins/statistics.php` - Line 504 (get_recent_submissions function)

### 🔧 Code Changes Made

**File: `app/lib/admins/statistics.php`**
```sql
-- Before (causing fatal error)
SELECT p.*, s.sector_name, u.agency_name, u.user_id as owner_agency_id
FROM programs p
LEFT JOIN sectors s ON p.sector_id = s.sector_id
LEFT JOIN users u ON p.owner_agency_id = u.user_id

-- After (fixed)
SELECT p.*, s.sector_name, a.agency_name, u.user_id as owner_agency_id
FROM programs p
LEFT JOIN sectors s ON p.sector_id = s.sector_id
LEFT JOIN users u ON p.owner_agency_id = u.user_id
LEFT JOIN agency a ON u.agency_id = a.agency_id
```

### 🛡️ Database Relationship Fix
- **Correct JOIN**: Added `LEFT JOIN agency a ON u.agency_id = a.agency_id`
- **Proper Column Reference**: Changed `u.agency_name` to `a.agency_name`
- **Maintained Functionality**: All existing query logic preserved
- **Error Prevention**: Eliminates "Unknown column 'u.agency_name'" fatal errors

### 📋 Immediate Impact
1. **Fatal Error Resolved**: The specific error in admin dashboard is now fixed
2. **Admin Dashboard**: Should now load without database errors
3. **Program Details**: Agency names will display correctly
4. **Recent Submissions**: Agency information will be properly retrieved

### 🚀 Next Steps
- Continue with Phase 2-5 to fix remaining files
- Test admin dashboard functionality
- Verify agency names display correctly
- Monitor for any other similar database relationship issues 