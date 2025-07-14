# Codebase Impact Analysis for Database Migration

## Executive Summary
Comprehensive analysis of all code files that reference database tables, identifying specific changes needed for the full migration from old database structure to new database structure.

## Critical Column Changes Impact

### 1. `programs` Table Changes
**Impact: HIGH - 50+ files affected**

**Column Changes:**
- `owner_agency_id` → `agency_id` 
- Remove: `sector_id`, `start_date`, `end_date`, `is_assigned`, `edit_permissions`, `status_indicator`
- Add: `users_assigned`, `status`, `targets_linked`

**Affected Files & Required Changes:**

#### **Core Library Files:**
1. **`app/lib/agencies/statistics.php`** - Critical Impact
   - Lines 135, 164, 229: Update query references from `owner_agency_id` to `agency_id`
   - Remove all `sector_id` references and joins with sectors table
   - Update `s.sector_name` selection logic

2. **`app/lib/agencies/initiatives.php`** - Medium Impact  
   - Lines 32, 86, 114: Update `owner_agency_id` to `agency_id` in WHERE clauses
   - Update JOIN conditions

3. **`app/lib/agencies/programs.php`** - Critical Impact (likely)
   - Need to search and update all program-related queries

#### **Admin Views:**
4. **`app/views/admin/programs/assign_programs.php`** - High Impact
   - Lines 56, 78, 137: Update `owner_agency_id` references
   - Remove `sector_id` logic from program creation
   - Update all INSERT and UPDATE statements

5. **`app/views/admin/programs/edit_program.php`** - High Impact
   - Lines 372, 516: Update agency queries and references
   - Remove sector-related form fields and logic

6. **`app/views/admin/programs/programs.php`** - Medium Impact
   - Update program listing queries

#### **Agency Views:**
7. **`app/views/agency/programs/program_details.php`** - Medium Impact
   - Line 32: Update `owner_agency_id` permission checks
   - Update program ownership logic

8. **`app/views/agency/programs/update_program.php`** - High Impact  
   - Lines 53, 98: Update ownership verification logic
   - Remove sector-related functionality

9. **`app/views/agency/programs/create_program.php`** - Medium Impact
   - Remove sector field from creation form

#### **API Files:**
10. **`app/api/get_period_programs.php`** - High Impact
    - Line 119: Update `owner_agency_id` to `agency_id` in joins
    - Remove `sector_id` filtering logic

#### **JavaScript Files:**
11. **`assets/js/report-generator.js`** - High Impact
    - Lines 148, 188, 231, 1073, 1080, 1453, 1455: Update all `owner_agency_id` and `sector_id` references
    - Update filtering and display logic

12. **`assets/js/admin/programs_admin.js`** - Medium Impact
    - Lines 127, 194, 195: Update data attributes and filtering

### 2. `users` Table Changes  
**Impact: LOW-MEDIUM - Already completed migration**

**Changes Completed:**
- `agency_group_id` → `agency_id` ✅
- Remove: `agency_name`, `sector_id` ✅  
- Add: `fullname`, `email` ✅

### 3. `reporting_periods` Table Changes
**Impact: MEDIUM - 10+ files affected**

**Column Changes:**
- `quarter` → `period_type` + `period_number`
- Remove: `is_standard_dates`

**Affected Files:**
- `app/views/admin/programs/edit_program.php` - Lines 66, 86
- All period selection dropdowns and filters need updates

### 4. `outcomes_details` Table Changes
**Impact: MEDIUM - 10+ files affected**

**Column Changes:**
- Remove: `outcome_type`, `outcome_template_id`, `is_draft`
- Add: `indicator_type`, `agency_id`

**Affected Files:**
- `app/views/admin/outcomes/manage_outcomes.php` - Line 53
- All outcome management and display logic

### 5. `program_attachments` Table Changes
**Impact: HIGH - File upload/management system**

**Column Changes:**
- Remove: `submission_id`, `original_filename`, `stored_filename`, `mime_type`, `upload_date`, `description`, `created_at`, `updated_at`
- Add: `file_name`, `uploaded_at`
- Change: `file_size` INT → BIGINT

**Affected Files:**
- All file upload/download handlers
- Attachment display components

## Legacy Tables Removal Impact

### Tables to Remove:
1. **`sectors`** - HIGH IMPACT
   - Remove all sector-based filtering and categorization
   - Update navigation menus and dropdowns
   - Remove sector management admin pages

2. **`sector_outcomes_data`** - HIGH IMPACT
   - Migrate important data to new outcomes structure
   - Remove sector-based outcome reporting
   - Update outcome APIs and views

3. **`metrics_details`** - MEDIUM IMPACT
   - Check for any metric references in code
   - Update any reporting that depends on this table

4. **`outcome_history`** - LOW IMPACT
   - Audit trail functionality may be lost
   - Check if audit logging needs replacement

## Code Refactoring Priority Matrix

### Phase 1: Critical Core Functions (MUST FIX FIRST)
1. **Database connection and configuration files**
2. **Core library functions** (agencies/statistics.php, programs.php)
3. **Authentication and session management**
4. **Main program listing and details pages**

### Phase 2: Admin Functions
1. **Program assignment and editing**
2. **User management (already mostly done)**
3. **Reporting period management**
4. **Outcome management**

### Phase 3: Agency User Interface  
1. **Program creation and editing forms**
2. **Program details and attachments**
3. **Dashboard and statistics views**

### Phase 4: Reporting and Analytics
1. **Report generation APIs**
2. **Dashboard charts and widgets**
3. **Data export functionality**

### Phase 5: Secondary Features
1. **File attachment system**
2. **Notification system updates**
3. **Audit logging adjustments**

## File-by-File Refactoring Checklist

### High Priority Files (Break application if not updated):
- [ ] `app/lib/agencies/statistics.php`
- [ ] `app/lib/agencies/programs.php` (needs analysis)
- [ ] `app/views/admin/programs/assign_programs.php`
- [ ] `app/views/admin/programs/edit_program.php`
- [ ] `app/views/agency/programs/program_details.php`
- [ ] `app/api/get_period_programs.php`
- [ ] `assets/js/report-generator.js`

### Medium Priority Files (May cause errors):
- [ ] `app/views/agency/programs/update_program.php`
- [ ] `app/views/agency/programs/create_program.php`
- [ ] `app/views/admin/programs/programs.php`
- [ ] `assets/js/admin/programs_admin.js`
- [ ] All reporting period dropdown components

### Low Priority Files (Minor issues):
- [ ] `app/views/admin/outcomes/manage_outcomes.php`
- [ ] Documentation updates
- [ ] Help text and user guides

## Testing Strategy

### Unit Testing Priorities:
1. **Database connection and basic queries**
2. **Program CRUD operations**
3. **User authentication and permissions**
4. **Reporting period functionality**

### Integration Testing:
1. **Program assignment workflow**
2. **Data submission and editing**
3. **Report generation**
4. **File upload/download**

### User Acceptance Testing:
1. **Complete program management workflow**
2. **Report generation and export**
3. **Cross-agency data viewing**
4. **Admin user management**

## Risk Mitigation

### Backup Requirements:
1. **Full database dump before migration**
2. **Copy of all application files**
3. **Database rollback script (already created)**
4. **Documented rollback procedure**

### Staged Deployment Plan:
1. **Development environment testing**
2. **Staging environment validation** 
3. **Limited production pilot**
4. **Full production deployment**

## Success Criteria

### Technical Success:
- [ ] All database queries execute without errors
- [ ] No broken links or PHP errors
- [ ] All core workflows functional
- [ ] Performance maintained or improved

### Business Success:
- [ ] Users can create and manage programs
- [ ] Reports generate correctly
- [ ] Data integrity maintained
- [ ] No data loss during migration

### Post-Migration Tasks:
- [ ] Update user documentation
- [ ] Train users on any UI changes
- [ ] Monitor for performance issues
- [ ] Clean up old backup files
