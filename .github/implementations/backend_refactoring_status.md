# Backend Refactoring Status - January 2025

## Current State Summary

Based on the analysis of the current database schema and codebase, here's where we stand in the backend refactoring process:

### ✅ COMPLETED MIGRATIONS

#### 1. Database Schema Migration (COMPLETED)
- **Users Table**: Successfully migrated from `agency_group_id` to `agency_id`
- **Agency Table**: New centralized agency management table
- **Programs Table**: Updated to use `agency_id` instead of `agency_group` and `owner_agency_id`
- **Audit System**: Enhanced with field-level change tracking

#### 2. User Management System (COMPLETED)
- **Admin User Management**: Fully refactored to use new `agency` table
- **User Creation/Editing**: Updated forms and backend logic
- **Session Management**: Partially updated (see pending items)

### 🔄 IN PROGRESS / PARTIALLY COMPLETED

#### 1. Agency Programs System (CRITICAL - 95% COMPLETED)
**Status**: Fully migrated to new database schema

**Completed**:
- ✅ `app/views/agency/programs/view_programs.php` - Updated to use `agency_id`
- ✅ Session variables updated in view files
- ✅ `app/lib/agencies/programs.php` - Completely refactored for new schema
- ✅ Updated all functions to use new table structure:
  - `create_agency_program()` - Uses new programs + program_submissions + program_targets
  - `create_wizard_program_draft()` - Uses new schema with user assignments
  - `get_agency_programs_list()` - Uses program_user_assignments table
  - `update_program_draft_only()` - Updated for new schema
  - `get_current_program_state()` - Uses new submission and target structure
- ✅ Removed all deprecated column references (`sector_id`, `is_assigned`, `edit_permissions`, `users_assigned`)
- ✅ Added proper user assignment handling via `program_user_assignments` table
- ✅ Updated target management to use `program_targets` table

**Pending**:
- ❌ Testing to verify functionality works correctly

#### 2. Session Management (75% COMPLETED)
**Status**: Mostly updated but needs verification

**Completed**:
- ✅ Agency navigation updated
- ✅ View programs session handling updated

**Pending**:
- ❌ Login/logout process verification
- ❌ Session variable consistency across all files

### ❌ NOT STARTED / CRITICAL ISSUES

#### 1. Program Logic Redesign (NOT STARTED)
**Status**: Schema designed but not implemented

**Current State**:
- Database schema designed in `program_logic_redesign_schema.sql`
- New tables: `program_submissions`, `program_targets`, `program_user_assignments`
- Current system still uses old program structure

**Required Actions**:
- Implement new program submission workflow
- Migrate existing program data to new structure
- Update all program-related views and controllers

#### 2. Deprecated Column References (CRITICAL)
**Status**: Multiple files still reference old columns

**Files with `owner_agency_id` references**:
- `app/views/agency/programs/view_programs.php` (partially fixed)
- `app/views/agency/programs/update_program.php`
- `app/views/agency/programs/program_details.php`
- `app/views/agency/programs/delete_program.php`
- `app/views/admin/programs/*` (multiple files)
- `app/lib/admins/statistics.php`
- `app/api/programs.php`
- `app/api/get_period_programs.php`

## Database Schema Analysis

### Current Programs Table Structure
```sql
CREATE TABLE `programs` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) NOT NULL,
  `program_number` varchar(20) DEFAULT NULL,
  `initiative_id` int DEFAULT NULL,
  `agency_id` int NOT NULL,                    -- ✅ NEW: Replaces agency_group
  `users_assigned` int DEFAULT NULL,           -- ✅ NEW: Replaces owner_agency_id
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '1',
  `attachment_count` int DEFAULT '0',
  `status` set('not-started','in-progress','completed') DEFAULT NULL,
  `hold_point` json DEFAULT NULL,
  `targets_linked` int DEFAULT '0',
  PRIMARY KEY (`program_id`),
  CONSTRAINT `FK_programs_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`),
  CONSTRAINT `FK_programs_users` FOREIGN KEY (`users_assigned`) REFERENCES `users` (`user_id`)
);
```

### Target Programs Table Structure (Not Implemented)
```sql
-- From program_logic_redesign_schema.sql
CREATE TABLE `programs` (
  `program_id` int AUTO_INCREMENT PRIMARY KEY,
  `initiative_id` int NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `program_description` text,
  `agency_id` int NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` int NOT NULL,
  -- ... additional fields for new workflow
);
```

## Critical Issues Identified

### 1. Agency Programs Not Working
**Problem**: Agency users cannot see or manage their programs
**Root Cause**: `app/lib/agencies/programs.php` still uses old `agency_group_id` references
**Impact**: Core functionality broken for agency users

### 2. Inconsistent Column References
**Problem**: Codebase mixes old and new column names
**Examples**:
- `owner_agency_id` (deprecated) vs `agency_id` (new)
- `agency_group_id` (deprecated) vs `agency_id` (new)
- `users_assigned` (new) vs `owner_agency_id` (deprecated)

### 3. Program Logic Mismatch
**Problem**: Current system doesn't match the designed program logic
**Gap**: Missing period-specific submissions, draft/submitted workflow

## Immediate Action Plan

### Phase 1: Fix Critical Functionality (URGENT)
1. **Update `app/lib/agencies/programs.php`**
   - Replace `agency_group_id` with `agency_id`
   - Update INSERT queries to use new column structure
   - Test program creation and listing

2. **Fix Session Management**
   - Verify login process sets correct session variables
   - Update any remaining `agency_group_id` session references

3. **Update Deprecated Column References**
   - Replace `owner_agency_id` with `agency_id` or `users_assigned`
   - Update JOIN conditions in queries
   - Test all affected functionality

### Phase 2: Implement Program Logic Redesign (HIGH PRIORITY)
1. **Database Migration**
   - Create new tables from `program_logic_redesign_schema.sql`
   - Migrate existing program data
   - Update foreign key relationships

2. **Backend Logic Updates**
   - Implement period-specific submission workflow
   - Add draft/submitted state management
   - Update program creation and editing logic

3. **Frontend Updates**
   - Update program views to show submission status
   - Add period selection for submissions
   - Implement draft management UI

### Phase 3: Testing and Validation (MEDIUM PRIORITY)
1. **Comprehensive Testing**
   - Test agency user workflows
   - Test admin user workflows
   - Test program submission process

2. **Data Validation**
   - Verify data integrity after migrations
   - Check foreign key relationships
   - Validate audit logging

## Files Requiring Immediate Attention

### High Priority
1. `app/lib/agencies/programs.php` - Fix agency program functionality
2. `app/views/agency/programs/view_programs.php` - Complete migration
3. Session management files - Ensure consistency

### Medium Priority
1. All admin program management files
2. API endpoints for programs
3. Statistics and reporting functions

### Low Priority
1. Migration scripts (documentation only)
2. Old database schema files

## Success Criteria

### Phase 1 Complete When:
- [ ] Agency users can see their programs
- [ ] Agency users can create new programs
- [ ] No deprecated column references remain
- [ ] Session management is consistent

### Phase 2 Complete When:
- [ ] New program submission workflow is implemented
- [ ] Period-specific submissions work
- [ ] Draft/submitted states are functional
- [ ] All program views updated

### Phase 3 Complete When:
- [ ] All functionality tested and working
- [ ] Data integrity verified
- [ ] Performance acceptable
- [ ] Documentation updated

## Notes

- **Database Migration**: Successfully completed from old to new schema
- **User Management**: Fully functional with new structure
- **Agency Programs**: **CRITICAL** - Currently broken, needs immediate attention
- **Program Logic**: Designed but not implemented
- **Testing**: User management tested, agency programs need testing

## Next Steps

1. **Immediate**: Fix `app/lib/agencies/programs.php` to restore agency functionality
2. **Short-term**: Complete session management updates
3. **Medium-term**: Implement program logic redesign
4. **Long-term**: Comprehensive testing and optimization

---

*This document will be updated as progress is made on each phase.* 