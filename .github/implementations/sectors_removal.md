# Sectors Functionality Removal Implementation Plan

## Objective
Remove all references to the deleted `sectors` table and disconnect all sector-based logic, UI, and documentation from the codebase. Replace with agency-based or default logic where necessary.

---

## Checklist: Files & Logic to Update/Remove

### PHP Backend
- [x] `app/lib/agencies/statistics.php`: Remove/refactor `get_all_sectors`, `get_sector_name`, `get_all_sectors_programs`, and all sector-based logic.
- [x] `app/lib/admins/statistics.php`: Remove sector-based queries, filters, and dropdowns.
- [x] `app/lib/agencies/programs.php`: Remove sector-based logic and queries.
- [x] `app/lib/admins/outcomes.php`: Remove sector-based stats and queries.
- [x] `app/api/report_data.php`: Remove sector-based SQL and replace with agency/default logic.
- [x] `app/api/get_period_programs.php`: Remove sector grouping and sector_name logic.
- [x] `app/views/admin/reports/generate_reports.php`: Remove sector dropdown and logic.
- [x] `app/views/admin/programs/edit_program.php`: Remove sector dropdown and queries.
- [x] `app/views/admin/outcomes/edit_outcome_backup.php`: Remove sector dropdown.
- [x] `app/views/admin/programs/bulk_assign_initiatives.php`: Remove sector filter dropdown.
- [x] `app/views/agency/sectors/view_all_sectors.php`: Remove or repurpose page.
- [x] `app/views/agency/sectors/ajax/sectors_data.php`: Remove AJAX endpoint.
- [x] `app/views/layouts/agency_nav.php`: Remove "All Sectors" nav link.
- [x] `app/views/admin/settings/system_settings.php`: Remove multi-sector settings.
- [x] `app/handlers/admin/get_user.php`: Remove sector JOINs and logic.

### JavaScript (Frontend)
- [x] `assets/js/report-modules/report-ui.js`: Remove sectorSelect logic.
- [x] `assets/js/report-modules/report-api.js`: Remove sectorSelect logic.
- [x] `assets/js/report-generator.js`: Remove sectorId logic.
- [x] `assets/js/agency/all_sectors.js`: Remove sector filtering.
- [x] `assets/js/admin/bulk_assign_initiatives.js`: Remove sector filtering.

### CSS
- [x] `assets/css/main.css`: Remove import of sectors-view.css if unused.
- [x] `assets/css/components/sectors-view.css`: Remove if unused.

### Documentation & Config
- [x] `system_context.txt`, `README.md`: Remove or update sector references.
- [x] Any migration or validation scripts referencing sectors.

---

## Step-by-Step Refactor Plan
1. **Scan and list all sector-related code and UI (done above).**
2. **Remove or refactor all backend PHP functions, queries, and logic referencing sectors.**
3. **Remove or refactor all frontend JS and UI elements related to sectors.**
4. **Remove or update CSS for sector-specific styles.**
5. **Update documentation and configs to remove sector references.**
6. **Test all affected features (admin dashboard, reports, program creation, etc).**
7. **Mark each item as complete in this checklist.**

---

> **Note:** The codebase is now fully agency-based and all sector logic has been removed or replaced. 