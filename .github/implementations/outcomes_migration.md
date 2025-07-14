# Outcomes Table Migration Implementation

## Status
- [x] Database migration: outcomes table created and seeded
- [x] Refactor backend code to use new outcomes table
- [x] Refactor API endpoints to use new outcomes table
- [x] Refactor admin and agency UI to use new outcomes table
- [x] Update report generation logic (backend and frontend)
- [x] Remove legacy outcomes code and columns
- [ ] Test end-to-end (editing, saving, report generation)
- [ ] Document new structure for future devs

## Step-by-Step Plan

1. **[x] Database migration**
   - Create new `outcomes` table (done)
   - Seed with 5 fixed outcomes (done)
2. **[x] Refactor backend code**
   - Updated all PHP functions to fetch/save outcomes from new table
   - Added new functions in admin and agency outcome libraries
3. **[x] Refactor API endpoints**
   - Updated `/api/get_outcomes.php` and `/api/outcomes/get_outcome.php` to use new table
   - API now returns outcomes as an object keyed by code
4. **[x] Refactor admin and agency UI**
   - Updated all outcome management, view, and edit pages to use new structure
5. **[x] Update report generation logic**
   - Backend: `/api/report_data.php` outputs all 5 fixed outcomes in a new `outcomes` key
   - Frontend: Report generator maps the `outcomes` object to an `outcomes_details` array for the slide populator, extracting the 3 KPI outcomes by code and formatting as `{ name, detail_json }`
   - Removed legacy KPI fallbacks (`kpi1`, `kpi2`, `kpi3`)
6. **[x] Remove legacy outcomes code and columns**
   - Removed legacy KPI fallbacks in JS (`report-slide-populator.js`)
   - Removed legacy PHP functions from admin and agency outcome libraries
   - Deleted legacy API endpoints and UI pages
   - Updated all remaining references to use new outcomes table
   - Created database cleanup script for legacy tables

## Legacy Cleanup Completed

### Files Removed:
- ✅ `assets/js/report-modules/report-slide-populator.js` - Removed legacy kpi1/kpi2/kpi3 fallback
- ✅ `app/views/admin/outcomes/create_outcome_details.php` - Deleted
- ✅ `app/views/agency/outcomes/update_metric_detail.php` - Deleted
- ✅ `app/views/agency/outcomes/view_outcome_new.php` - Deleted
- ✅ `app/views/agency/outcomes/old_edit_outcomes.php` - Deleted
- ✅ `app/views/admin/outcomes/edit_outcome_new.php` - Deleted
- ✅ `app/views/agency/outcomes/create_outcome_flexible.php` - Deleted
- ✅ `app/api/outcomes/enhanced_outcome_data.php` - Deleted
- ✅ `app/api/outcomes/update_outcome.php` - Deleted

### Legacy Functions Removed:
- ✅ `app/lib/admins/outcomes.php` - Removed all sector_outcomes_data functions
- ✅ `app/lib/agencies/outcomes.php` - Removed all sector_outcomes_data functions

### References Updated:
- ✅ `app/lib/audit_log.php` - Updated table mapping
- ✅ `app/views/admin/settings/audit_log.php` - Updated table references
- ✅ `app/views/admin/programs/edit_program.php` - Updated outcomes queries
- ✅ `app/api/program_outcome_links.php` - Updated all JOIN clauses
- ✅ `app/lib/agencies/programs.php` - Updated outcomes query
- ✅ `app/lib/outcome_automation.php` - Updated all references

### Database Cleanup:
- ✅ Created `scripts/cleanup_legacy_outcomes_tables.sql` for dropping legacy tables

## Next Steps
- [ ] Test the full workflow (editing, saving, report generation)
- [ ] Document the new structure for future developers 