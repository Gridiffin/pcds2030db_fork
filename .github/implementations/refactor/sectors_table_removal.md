# Sectors Table Removal & Sector Logic Refactor Tracker

This document tracks every reference to the deleted `sectors` table and sector-related logic in the codebase. Use this as a checklist for systematic refactor/removal.

## PHP Backend (Database/Logic)
- [ ] `app/lib/admins/statistics.php` - Multiple functions (e.g., get_sector_data_for_period, get_sector_name, get_all_sectors, get_sector_by_id) use the `sectors` table.
- [ ] `app/lib/agencies/statistics.php` - Functions: get_sector_name, get_all_sectors, get_all_sectors_programs (sector_id, sector_name logic)
- [ ] `app/views/admin/reports/generate_reports.php` - `getSectors()` function and sector dropdown logic
- [ ] `app/views/admin/programs/edit_program.php` - Sector dropdown and sector query
- [ ] `app/views/admin/outcomes/edit_outcome_backup.php` - Sector dropdown
- [ ] `app/views/agency/sectors/view_all_sectors.php` - Uses get_all_sectors, sector_id, sector_name
- [ ] `app/views/agency/sectors/ajax/sectors_data.php` - Uses get_all_sectors, sector_id, sector_name
- [ ] `app/api/report_data.php` - Sector queries and sector_leads logic
- [ ] `app/api/get_period_programs.php` - Sector grouping and sector_name
- [ ] `app/lib/admins/outcomes.php` - Sector stats and sector_outcomes_data
- [ ] `app/views/admin/programs/bulk_assign_initiatives.php` - Sector filter dropdown
- [ ] `app/lib/agencies/programs.php` - LEFT JOIN sectors, sector_id, sector_name

## JavaScript (UI/UX)
- [ ] `assets/js/report-modules/report-ui.js` - sectorSelect logic
- [ ] `assets/js/report-modules/report-api.js` - sectorSelect logic
- [ ] `assets/js/report-generator.js` - sectorSelect, sectorId logic
- [ ] `assets/js/agency/all_sectors.js` - Filtering by sector
- [ ] `assets/js/admin/bulk_assign_initiatives.js` - Filtering by sector

## CSS
- [ ] `assets/css/main.css` - Imports sectors-view.css
- [ ] `assets/css/components/sectors-view.css` - Sectors view component styles

## Views & Navigation
- [ ] `app/views/layouts/agency_nav.php` - All Sectors nav link
- [ ] `app/views/agency/sectors/view_all_sectors.php` - All Sectors page
- [ ] `app/views/agency/sectors/ajax/sectors_data.php` - AJAX for sectors view
- [ ] `app/views/admin/settings/system_settings.php` - Multi-sector settings

## Miscellaneous/Config
- [ ] `system_context.txt`, `README.md`, and other docs referencing sectors
- [ ] `validate_current_db.php`, `dev_migration_test.php` - Table checks for sectors

---

## TODO Checklist
- [ ] For each reference above, decide: REMOVE, REPLACE (with agency logic), or REFACTOR (if sector logic is still needed)
- [ ] Remove all SQL queries and code that reference the deleted `sectors` table
- [ ] Update dropdowns, filters, and UI to use agency or new structure
- [ ] Update documentation and configs
- [ ] Test all affected features (admin dashboard, reports, program creation, etc)

---

**Update this file as you complete each item.**

- [x] `app/lib/period_selector_dashboard.php` - Uses `quarter` in SQL and logic (updated to use `period_type`/`period_number`) 