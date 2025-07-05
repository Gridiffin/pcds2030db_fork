# Database Structure Comparison Analysis

## Executive Summary
Complete structural comparison between old database (`pcds2030_dashboard`) and new database (`pcds2030_db`) to guide full migration strategy.

## Tables Overview

### Tables in OLD Database (18 total):
1. **agency** ✅ *Already aligned and migrated*
2. **audit_logs** 
3. **initiatives**
4. **metrics_details** ❌ *Missing in new database*
5. **notifications**
6. **outcome_history** ❌ *Missing in new database*
7. **outcomes_details**
8. **program_attachments**
9. **program_outcome_links**
10. **program_submissions**
11. **programs**
12. **reporting_periods**
13. **reports**
14. **sector_outcomes_data** ❌ *Missing in new database*
15. **sectors** ❌ *Missing in new database*
16. **users** ✅ *Already aligned and migrated*
17. **sector_outcomes_data_backup_2025_06_29_15_11_05** ❌ *Backup table - skip*
18. **sector_outcomes_data_backup_2025_06_30_04_59_21** ❌ *Backup table - skip*

### Tables in NEW Database (12 total):
1. **agency** ✅ *Exists in old*
2. **audit_logs** ✅ *Exists in old*
3. **initiatives** ✅ *Exists in old*
4. **notifications** ✅ *Exists in old*
5. **outcomes_details** ✅ *Exists in old*
6. **program_attachments** ✅ *Exists in old*
7. **program_outcome_links** ✅ *Exists in old*
8. **program_submissions** ✅ *Exists in old*
9. **programs** ✅ *Exists in old*
10. **reporting_periods** ✅ *Exists in old*
11. **reports** ✅ *Exists in old*
12. **targets** ❌ *Missing in old database*
13. **users** ✅ *Exists in old*

## Detailed Table Comparison

### 1. **audit_logs** - Identical Structure ✅
- Same columns, types, and indexes
- No changes needed

### 2. **initiatives** - Identical Structure ✅ 
- Same columns, types, and indexes
- No changes needed

### 3. **notifications** - Identical Structure ✅
- Same columns, types, and indexes
- Foreign key relationships match
- No changes needed

### 4. **outcomes_details** - MAJOR DIFFERENCES ⚠️
**OLD has additional columns:**
- `outcome_type` (enum: simple/complex)
- `outcome_template_id` (int)
- `is_draft` (int)

**NEW has additional columns:**
- `indicator_type` (varchar)
- `agency_id` (int) with FK to agency

**Missing indexes:** NEW database missing several indexes from old

### 5. **program_attachments** - MAJOR DIFFERENCES ⚠️
**OLD has additional columns:**
- `submission_id` (int, nullable) - for version control
- `original_filename` (varchar)
- `stored_filename` (varchar)
- `mime_type` (varchar)
- `upload_date` (timestamp)
- `description` (text)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**NEW has different columns:**
- `file_name` (varchar) - replaces original/stored filename
- `uploaded_at` (timestamp) - replaces upload_date
- `file_size` is BIGINT in new vs INT in old

**Missing FK:** OLD has FK to program_submissions table

### 6. **program_outcome_links** - Identical Structure ✅
- Same columns, types, indexes, and FKs
- No changes needed

### 7. **program_submissions** - Identical Structure ✅
- Same columns, types, indexes, and FKs
- No changes needed

### 8. **programs** - MAJOR DIFFERENCES ⚠️
**OLD has additional columns:**
- `sector_id` (int, not null)
- `owner_agency_id` (int, not null) 
- `start_date` (date)
- `end_date` (date)
- `is_assigned` (tinyint)
- `edit_permissions` (text)
- `status_indicator` (set)

**NEW has additional columns:**
- `users_assigned` (int) with FK to users
- `targets_linked` (int, default 0)
- `status` (set) - similar to status_indicator

**Column differences:**
- OLD has `owner_agency_id`, NEW has `agency_id` (same purpose)

### 9. **reporting_periods** - MAJOR DIFFERENCES ⚠️
**OLD structure:**
- `quarter` (int, not null)
- `is_standard_dates` (tinyint)

**NEW structure:**
- `period_type` (enum: quarter/half/yearly)
- `period_number` (int, not null)

**Different indexing strategy**

### 10. **reports** - Identical Structure ✅
- Same columns, types, indexes, and FKs
- No changes needed

## Missing Tables Analysis

### Tables to CREATE in OLD database:
1. **targets** - Simple table with 5 columns, no dependencies

### Tables to DROP from OLD database:
1. **metrics_details** - Legacy system, check for code dependencies
2. **outcome_history** - Audit trail for outcomes, check for code dependencies  
3. **sector_outcomes_data** - Legacy sector-based outcomes, complex structure
4. **sectors** - Legacy sector system, replaced by agency-centric approach
5. **sector_outcomes_data_backup_*** - Backup tables, safe to drop

## Migration Priority Matrix

### Phase 1: Simple Migrations (Identical/Minor Changes)
- ✅ audit_logs - No changes needed
- ✅ initiatives - No changes needed  
- ✅ notifications - No changes needed
- ✅ program_outcome_links - No changes needed
- ✅ program_submissions - No changes needed
- ✅ reports - No changes needed

### Phase 2: Complex Structural Changes
- ⚠️ outcomes_details - Column additions/removals
- ⚠️ program_attachments - Major structural changes
- ⚠️ programs - Column changes and FK updates
- ⚠️ reporting_periods - Complete restructure

### Phase 3: Table Additions/Removals
- ➕ Add targets table
- ➖ Drop legacy tables (metrics_details, outcome_history, sector_outcomes_data, sectors)

## Data Migration Considerations

### Critical Data Preservation:
1. **program_attachments**: Need to map old filename fields to new structure
2. **programs**: Map owner_agency_id → agency_id, handle sector_id removal
3. **reporting_periods**: Convert quarter-based to period_type system
4. **outcomes_details**: Handle outcome_type/template removal, add agency_id

### Data Dependencies:
1. **Foreign Key Chain**: agency → users → programs → submissions
2. **sector_outcomes_data**: Contains sector_id references that need agency mapping
3. **outcome_history**: References that may become invalid

## Risk Assessment

### HIGH RISK:
- **reporting_periods**: Fundamental structure change affects entire submission system
- **programs**: Core table with multiple dependency changes
- **sector_outcomes_data removal**: Contains historical outcome data

### MEDIUM RISK:  
- **program_attachments**: File management system changes
- **outcomes_details**: Structural changes with FK additions

### LOW RISK:
- **targets**: Simple table addition
- **Legacy table drops**: After code verification

## Next Steps for Phase 1.2

1. Create detailed migration scripts for each complex table
2. Plan data transformation logic for structural changes
3. Identify all code dependencies for legacy tables
4. Create rollback procedures for each phase
5. Set up development testing environment
