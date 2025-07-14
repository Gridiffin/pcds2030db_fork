# Initiatives Module DB Names Migration

## Overview
This document tracks the migration of the initiatives module to use the centralized `db_names.php` configuration for table and column names.

## Current Status
- [ ] **Files to be updated**
- [ ] **Migration completed**

## Files to Update

### Admin Views
- [ ] `app/views/admin/initiatives/manage_initiatives.php`
- [ ] `app/views/admin/initiatives/view_initiative.php`
- [ ] `app/views/admin/initiatives/edit.php`
- [ ] `app/views/admin/initiatives/create.php`
- [ ] `app/views/admin/initiatives/debug_ajax.php`

### Agency Views
- [ ] `app/views/agency/initiatives/view_initiative.php`
- [ ] `app/views/agency/initiatives/view_initiatives.php`
- [ ] `app/views/agency/initiatives/initiatives.php`

### Library Files
- [ ] `app/lib/initiative_functions.php`
- [ ] `app/lib/agencies/initiatives.php`

## Database Tables Used
- `initiatives` - Main initiatives table
- `programs` - Programs linked to initiatives
- `users` - Users who created initiatives
- `agency` - Agency information for users
- `program_submissions` - Submissions for programs

## Key Changes Required

### 1. Include db_names_helper.php
Add to all files:
```php
require_once ROOT_PATH . 'app/lib/db_names_helper.php';
```

### 2. Replace Hardcoded Table Names
- `initiatives` → `get_table_name('initiatives')`
- `programs` → `get_table_name('programs')`
- `users` → `get_table_name('users')`
- `agency` → `get_table_name('agency')`

### 3. Replace Hardcoded Column Names
- `initiative_id` → `get_column_name('initiatives', 'id')`
- `initiative_name` → `get_column_name('initiatives', 'name')`
- `initiative_number` → `get_column_name('initiatives', 'number')`
- `initiative_description` → `get_column_name('initiatives', 'description')`
- `program_id` → `get_column_name('programs', 'id')`
- `program_name` → `get_column_name('programs', 'name')`
- `user_id` → `get_column_name('users', 'id')`
- `username` → `get_column_name('users', 'username')`
- `agency_id` → `get_column_name('users', 'agency_id')`
- `agency_name` → `get_column_name('agency', 'name')`

### 4. Use Helper Functions
- Replace direct SQL queries with `build_select_query()` where appropriate
- Use `build_where_clause()` for WHERE conditions

## Progress Tracking

### Phase 1: Library Files
- [x] Update `app/lib/initiative_functions.php`
- [x] Update `app/lib/agencies/initiatives.php`

### Phase 2: Admin Views
- [x] Update `app/views/admin/initiatives/manage_initiatives.php`
- [x] Update `app/views/admin/initiatives/view_initiative.php`
- [x] Update `app/views/admin/initiatives/edit.php`
- [x] Update `app/views/admin/initiatives/create.php`
- [x] Update `app/views/admin/initiatives/debug_ajax.php`

### Phase 2 Complete ✅
All admin initiative views have been updated to use the centralized db_names configuration.

### Phase 3: Agency Views
- [x] Update `app/views/agency/initiatives/initiatives.php`
- [x] Update `app/views/agency/initiatives/view_initiatives.php`
- [x] Update `app/views/agency/initiatives/view_initiative.php`

### Phase 3 Complete ✅
All agency initiative views have been updated to use the centralized db_names configuration.

### Phase 4: Testing
- [x] Test all initiative functionality
- [x] Verify no broken queries
- [x] Check for any missing includes

### Phase 4 Complete ✅
Fixed SQL query issues in `initiative_functions.php`:
- **Changed approach**: Switched from db_names helper functions to direct SQL mapping (following users module pattern)
- **Direct config loading**: Now loads `db_names.php` directly and extracts table/column names as variables
- **Removed helper dependencies**: No longer uses `get_table_name()` and `get_column_name()` helper functions
- **Consistent pattern**: Now follows the same pattern as the users module for consistency
- All initiative functionality now working correctly with direct SQL mapping

## Notes
- Ensure backward compatibility during migration
- Test each file after updating
- Keep original queries as comments for reference during migration 