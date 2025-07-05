# Post-Backup Migration Steps

## Current Status ✅
- [x] Database backed up safely
- [x] Using same database name (no config changes needed)
- [x] Ready for in-place migration

## Step 1: Validate Current Database State

Run this script to confirm your current database structure:

**File:** `validate_current_db.php`

## Step 2: Execute Migration

Since you have your backup, you can safely run the migration script directly on your current database:

### 2.1 Run Migration Script
- [ ] Open phpMyAdmin or MySQL client
- [ ] Select your current database: `pcds2030_dashboard` 
- [ ] Import/Execute: `.github/implementations/master_migration_script.sql`
- [ ] Verify no errors during execution

### 2.2 Validate Migration Success
- [ ] Run the validation script again to confirm new structure
- [ ] Check that old columns are removed and new ones added
- [ ] Verify data integrity and record counts

## Step 3: Code Refactoring (Your Task)

Here are ALL the files you need to update, in priority order:

### 🔥 **CRITICAL - Fix These FIRST** (Application will be broken until these are fixed)

1. **`login.php`** - Remove references to `sector_id`, `agency_group_id` from user queries
2. **`app/lib/admin_functions.php`** - Update all user and agency management functions
3. **`app/config/config.php`** - Verify database connection works with new structure

### 🚨 **HIGH PRIORITY - Core Functionality**

4. **`app/ajax/admin_dashboard_data.php`** - Update admin dashboard queries
5. **`app/ajax/agency_dashboard_data.php`** - Fix agency-related data fetching
6. **`app/lib/agency_functions.php`** - Update agency management functions
7. **`logout.php`** - Verify session handling works
8. **`index.php`** - Check dashboard loading

### 📊 **MEDIUM PRIORITY - Program Management**

9. **`app/api/programs.php`** - Replace `owner_agency_id` with `agency_id`
10. **`app/ajax/get_program_submission.php`** - Update program queries
11. **`app/ajax/submit_outcome.php`** - Fix outcome submission
12. **`app/api/get_outcomes.php`** - Update outcome queries

### 📈 **MEDIUM PRIORITY - Admin Functions**

13. **`app/handlers/admin/manage_agencies.php`** - Update agency management
14. **`app/handlers/admin/manage_users.php`** - Fix user management
15. **`app/handlers/admin/manage_programs.php`** - Update program management
16. **Files in `app/handlers/admin/` directory** - Check all admin handlers

### 📋 **LOW PRIORITY - Reporting & Secondary Features**

17. **`app/api/report_data.php`** - Update report generation
18. **`app/ajax/dashboard_data.php`** - Fix main dashboard
19. **`app/ajax/export_audit_logs.php`** - Update audit functionality
20. **`app/lib/audit_log.php`** - Check audit logging

## Step 4: Testing Each Fix

For each file you update:
- [ ] Test the specific functionality
- [ ] Check for PHP errors
- [ ] Verify database queries work
- [ ] Test user workflows

## Quick Reference: Key Changes Needed

### Column Changes to Look For:
- ❌ `users.sector_id` → ✅ Remove (no replacement)
- ❌ `users.agency_group_id` → ✅ Remove (no replacement) 
- ❌ `agencies.agency_group_id` → ✅ `agencies.agency_group` (VARCHAR)
- ❌ `programs.owner_agency_id` → ✅ `programs.agency_id`

### Table Changes:
- ❌ `agency_groups` table → ✅ Removed (data moved to agencies.agency_group)
- ❌ `sectors` table → ✅ Removed (data moved to agencies.sector)

### New Columns Added:
- ✅ `users.is_super_admin` (TINYINT)
- ✅ `agencies.agency_group` (VARCHAR)
- ✅ `agencies.sector` (VARCHAR)

## Expected Errors After Migration

Until you fix the code, you'll see these errors:
- "Unknown column 'sector_id'" 
- "Unknown column 'agency_group_id'"
- "Unknown column 'owner_agency_id'"
- "Table 'agency_groups' doesn't exist"
- "Table 'sectors' doesn't exist"

## Recovery Plan

If something goes wrong:
- [ ] You have your backup 
- [ ] Restore from backup if needed
- [ ] Rollback script available: `rollback_migration_script.sql`

Ready to proceed? Run the validation script first!
