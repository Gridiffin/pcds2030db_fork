# Agency Group References Progress Tracker

Below are all references to `agency_group` and related columns in the codebase. Use the checkboxes to track progress as each reference is refactored or removed.

## ✅ COMPLETED SECTIONS

### User Management System (FULLY REFACTORED - January 2025)
**Status**: ✅ ALL REFERENCES UPDATED
**Files Modified**: 
- `app/lib/admins/users.php` - Core functions updated
- `app/views/admin/users/add_user.php` - Form updated
- `app/views/admin/users/edit_user.php` - Form updated
- `app/views/admin/users/manage_users.php` - Listing updated

**Changes Made**:
- Updated all `agency_group_id` references to `agency_id`
- Updated all `agency_group` table references to `agency` table
- Updated all `group_name` references to `agency_name`
- Fixed database queries and form fields
- Enhanced UI/UX with improved layouts

- [x] app/views/admin/users/edit_user.php:66 `$agency_groups = get_all_agency_groups($conn);`
- [x] app/views/admin/users/edit_user.php:183 `<label for="agency_group_id" class="form-label">Agency Group</label>`
- [x] app/views/admin/users/edit_user.php:184 `<select class="form-select" id="agency_group_id" name="agency_group_id">`
- [x] app/views/admin/users/edit_user.php:186 `<?php foreach($agency_groups as $group): ?>`
- [x] app/views/admin/users/edit_user.php:187 `<option value="<?php echo $group['agency_group_id']; ?>" <?php echo isset($user['agency_group_id']) && $user['agency_group_id'] == $group['agency_group_id'] ? 'selected' : ''; ?>>`
- [x] app/views/admin/users/edit_user.php:248 `const agencyGroupId = document.getElementById('agency_group_id'); // Add agency_group_id`
- [x] app/views/admin/users/edit_user.php:257 `// agency_group_id is optional, so no required attribute here`
- [x] app/views/admin/users/edit_user.php:262 `// agency_group_id remains optional`

- [x] app/views/admin/users/add_user.php:47 `$agency_groups = get_all_agency_groups($conn);`
- [x] app/views/admin/users/add_user.php:168 `<label for="agency_group_id" class="form-label">Agency Group</label>`
- [x] app/views/admin/users/add_user.php:169 `<select class="form-select" id="agency_group_id" name="agency_group_id">`
- [x] app/views/admin/users/add_user.php:171 `<?php foreach($agency_groups as $group): ?>`
- [x] app/views/admin/users/add_user.php:172 `<option value="<?php echo $group['agency_group_id']; ?>"><?php echo htmlspecialchars($group['group_name']); ?></option>`
- [x] app/views/admin/users/add_user.php:209 `const agencyGroupId = document.getElementById('agency_group_id');`
- [x] app/views/admin/users/add_user.php:217 `const agencyGroups = <?php echo json_encode($agency_groups); ?>;`
- [x] app/views/admin/users/add_user.php:229 `const option = new Option(group.group_name, group.agency_group_id);`

- [x] app/lib/admins/users.php:16 `function get_all_agency_groups(mysqli $conn): array {`
- [x] app/lib/admins/users.php:17 `$agency_groups = [];`
- [x] app/lib/admins/users.php:19 `$sql = "SELECT `agency_group_id`, `group_name`, `sector_id` FROM `agency_group` ORDER BY `group_name` ASC";`
- [x] app/lib/admins/users.php:27 `$agency_groups[] = $row;`
- [x] app/lib/admins/users.php:30 `return $agency_groups;`
- [x] app/lib/admins/users.php:44 `LEFT JOIN agency_group ag ON u.agency_group_id = ag.agency_group_id`
- [x] app/lib/admins/users.php:75 `$required_fields[] = 'agency_group_id';`
- [x] app/lib/admins/users.php:123 `$agency_group_id = null;`
- [x] app/lib/admins/users.php:128 `$agency_group_id = intval($data['agency_group_id']);`
- [x] app/lib/admins/users.php:140 `$group_check = "SELECT agency_group_id FROM agency_group WHERE agency_group_id = ?";`
- [x] app/lib/admins/users.php:142 `$stmt->bind_param("i", $agency_group_id);`
- [x] app/lib/admins/users.php:151 `$query = "INSERT INTO users (username, password, agency_name, role, sector_id, agency_group_id, is_active, created_at)`
- [x] app/lib/admins/users.php:155 `$stmt->bind_param("ssssiis", $username, $hashed_password, $agency_name, $role, $sector_id, $agency_group_id, $is_active);`
- [x] app/lib/admins/users.php:212 `$required_fields[] = 'agency_group_id';`
- [x] app/lib/admins/users.php:326 `// Handle agency_group_id if provided`
- [x] app/lib/admins/users.php:327 `if (isset($data['agency_group_id'])) {`
- [x] app/lib/admins/users.php:328 `$agency_group_id = !empty($data['agency_group_id']) ? intval($data['agency_group_id']) : null;`
- [x] app/lib/admins/users.php:329 `$update_fields[] = "agency_group_id = ?";`
- [x] app/lib/admins/users.php:330 `$bind_params[] = $agency_group_id;`
- [x] app/lib/admins/users.php:333 `if ($agency_group_id) {`
- [x] app/lib/admins/users.php:334 `$group_check = "SELECT agency_group_id FROM agency_group WHERE agency_group_id = ?";`
- [x] app/lib/admins/users.php:336 `$stmt->bind_param("i", $agency_group_id);`
- [x] app/lib/admins/users.php:490 `LEFT JOIN agency_group ag ON u.agency_group_id = ag.agency_group_id`

## 🔄 PENDING SECTIONS

### 1. Agency Programs (HIGH PRIORITY)
**Status**: 🔄 PENDING - IMMEDIATE ATTENTION NEEDED
**Files to Update**:
- `app/views/agency/programs/view_programs.php`
- `app/lib/agencies/programs.php`

#### app/views/agency/programs/view_programs.php
- [x] Line 52: `$agency_group_id = $_SESSION['agency_group_id'];` ✅ FIXED
- [x] Line 54: `if ($agency_group_id !== null) { $query = "SELECT p.*, ... WHERE u.agency_group_id = ? ...";` ✅ FIXED
- [x] Line 75: `WHERE u.agency_group_id = ?` ✅ FIXED
- [x] Line 78: `$stmt->bind_param("i", $agency_group_id);` ✅ FIXED
- [x] Line 87: `$agency_group_id = $_SESSION['agency_group_id'] ?? null;` ✅ FIXED
- [x] Line 89: `if ($agency_group_id !== null) {` ✅ FIXED
- [x] Line 112: `WHERE u.agency_group_id = ?` ✅ FIXED
- [x] Line 115: `$stmt->bind_param("i", $agency_group_id);` ✅ FIXED

#### app/lib/agencies/programs.php
- [ ] Line 93: `$agency_group_id = $user ? $user['agency_group_id'] : null;`
- [ ] Line 113: `$query = "INSERT INTO programs (program_name, program_number, sector_id, owner_agency_id, agency_group, is_assigned, content_json, created_at)`
- [ ] Line 116: `$stmt->bind_param("ssiiis", $program_name, $program_number, $sector_id, $user_id, $agency_group_id, $content_json);`
- [ ] Line 118: `$query = "INSERT INTO programs (program_name, program_number, sector_id, owner_agency_id, agency_group, is_assigned, created_at)`
- [ ] Line 121: `$stmt->bind_param("ssiii", $program_name, $program_number, $sector_id, $user_id, $agency_group_id);`
- [ ] Line 198: `$agency_group_id = $user ? $user['agency_group_id'] : null;`
- [ ] Line 202: `$stmt = $conn->prepare("INSERT INTO programs (program_name, program_number, start_date, end_date, owner_agency_id, agency_group, sector_id, initiative_id, is_assigned, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");`
- [ ] Line 203: `$stmt->bind_param("ssssiiii", $program_name, $program_number, $start_date, $end_date, $user_id, $agency_group_id, $sector_id, $initiative_id);`

### 2. Session Variables (MEDIUM PRIORITY)
**Status**: 🔄 PARTIALLY FIXED - NEEDS INVESTIGATION
**Issue**: Session variables still reference old structure
**Files to Check**:
- Session management files
- Login/logout processes
- User authentication flow

**References Found**:
- `$_SESSION['agency_group_id']` - Used in agency programs view ✅ FIXED
- `$_SESSION['agency_name']` - Used in agency navigation ✅ FIXED (changed to `$_SESSION['fullname']`)

### 3. Migration Scripts (LOW PRIORITY)
**Status**: ✅ DOCUMENTATION ONLY - No action needed
**Files**: These are migration scripts and documentation, not active code
- `scripts/migrate_users_data.php` - Migration documentation
- `scripts/update_old_db_schema.sql` - Migration script
- `scripts/simple_schema_update.sql` - Migration script
- `app/database/oldpcds2030db.sql` - Old database schema
- `app/database/migrate_agency_group_to_agency.sql` - Migration script

## 🔍 ADDITIONAL FINDINGS

### Session Management Issues
- **Problem**: `$_SESSION['agency_group_id']` is still being used
- **Impact**: Agency users may not see their programs correctly
- **Solution**: Update session variables to use `agency_id` instead

### Database Column References
- **Programs Table**: Still has `agency_group` column references in INSERT queries
- **Users Table**: References to `agency_group_id` in user data access

## 📊 PROGRESS SUMMARY

- **User Management**: ✅ 100% COMPLETED (All references updated)
- **Agency Programs**: 🔄 50% COMPLETED (view_programs.php fixed, programs.php pending)
- **Session Variables**: 🔄 75% COMPLETED (view_programs.php and agency_nav.php fixed, other files pending)
- **Migration Scripts**: ✅ 100% COMPLETED (Documentation only)
- **Overall Progress**: ~60% COMPLETED

## 🎯 IMMEDIATE ACTION ITEMS

### Priority 1: Agency Programs (CRITICAL)
1. **Update view_programs.php**:
   - Replace `$_SESSION['agency_group_id']` with `$_SESSION['agency_id']`
   - Update all SQL queries to use `agency_id` instead of `agency_group_id`
   - Test program visibility for agency users

2. **Update programs.php**:
   - Replace `$user['agency_group_id']` with `$user['agency_id']`
   - Update INSERT queries to use `agency_id` column
   - Test program creation functionality

### Priority 2: Session Management (HIGH)
1. **Investigate Session Variables**:
   - Find where `agency_group_id` is set in session
   - Update to use `agency_id` instead
   - Test login/logout flow

### Priority 3: Testing (HIGH)
1. **Test Agency User Functionality**:
   - Verify agency users can see their programs
   - Verify agency users can create programs
   - Verify program assignments work correctly

## 🚨 CRITICAL ISSUES

1. **Agency Programs Not Working**: Agency users likely cannot see or manage their programs due to old references
2. **Session Mismatch**: Session variables may not match new database structure
3. **Data Integrity**: Programs may be created with incorrect agency references

## 🚨 CRITICAL DATABASE MIGRATION REQUIRED

- [ ] Add `agency_id` column to `programs` table if not present
- [ ] Migrate data from `agency_group` to `agency_id` (if needed)
- [ ] Drop `agency_group` column from `programs` table
- [ ] Test program creation and auto-save after migration

## 📝 NOTES

- **Database Migration**: Successfully completed from `agency_group` to `agency` table
- **User Management**: Fully functional with new database structure
- **Agency Programs**: **CRITICAL** - Still using old references, likely broken
- **Testing**: User management thoroughly tested and working
- **Session Variables**: Need investigation and update

## 🔧 RECOMMENDED APPROACH

1. **Start with Agency Programs** - This is the most critical functionality
2. **Update Session Management** - Ensure proper agency identification
3. **Comprehensive Testing** - Test all agency user workflows
4. **Document Changes** - Update this tracker as work progresses 