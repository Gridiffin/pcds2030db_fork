# Refactor Report Data & Report Modules After DB Schema Changes

## Problem Statement
Recent changes to the database schema have caused major issues in the report data and report modules. The SQL queries and logic are no longer compatible with the new schema, although the ydata structure remains unchanged. This has resulted in broken or incorrect report generation and module functionality.

## Solution Approach
We need to:
- Identify all files and modules related to report data and report modules.
- Analyze the new DB schema to understand the changes.
- Update all SQL queries and related logic to match the new schema, ensuring the ydata logic is preserved.
- Test the updated modules for correctness and performance.
- Suggest and implement improvements for maintainability and performance.

## TODO List
- [x] Document the current issues and create this plan
- [x] Scan and list all files related to report data and report modules (including SQL queries, PHP, JS, and view files)
- [x] Analyze the new DB schema and identify all changes relevant to report data and modules
- [x] Update all SQL queries in report data and report modules to match the new schema, ensuring ydata logic remains unchanged
- [ ] Test the updated report data and modules for correctness and performance
- [ ] Suggest and implement codebase improvements for maintainability and performance in report-related modules
- [ ] Update this file to reflect progress and mark completed tasks 

## Files to Review & Update

### PHP (Backend)
- app/api/report_data.php
- app/api/generate_report.php
- app/api/save_report.php
- app/api/delete_report.php
- app/views/admin/reports/generate_reports.php
- app/views/agency/reports/view_reports.php
- app/views/agency/reports/public_reports.php
- app/views/layouts/admin_nav.php (report links)
- app/views/layouts/agency_nav.php (report links)
- app/views/admin/periods/reporting_periods.php
- app/views/admin/settings/reporting_periods.php
- [x] app/lib/admins/statistics.php (program rating now aggregates status_indicator from program_targets for latest submission; most severe status is used)
- [x] Fixed SQL syntax in app/lib/admins/statistics.php: WHERE now comes before GROUP BY, which comes before ORDER BY in get_admin_programs_list

### JS (Frontend)
- assets/js/report-modules/report-ui.js
- assets/js/report-modules/report-api.js
- assets/js/report-modules/report-slide-populator.js
- assets/js/report-modules/report-slide-styler.js
- assets/js/report-generator.js
- assets/js/admin/reports-pagination.js
- assets/js/admin/reporting_periods.js

### CSS
- assets/css/pages/report-generator.css

### SQL
- app/database/program_logic_redesign_schema.sql (reference for new schema)

### Other
- download.php (handles report file downloads)

> Note: This list is based on file and content searches for 'report', 'module', 'generate', and 'outcome'. Additional files may be added as discovered during the refactor process. 

> Next: Analyze the new DB schema and identify all changes relevant to report data and modules.

---

## Notes on New DB Logic
- **Submissions**: Now linked to reporting periods directly in the `program_submissions` table (not just extracted from JSON).
- **Targets**: Now stored in the `program_targets` table, can be filtered by submission, and are no longer just embedded in JSON.
- **Reporting**: Generating PPTX reports now requires joining across these tables to get the correct targets and submission data for a given period.

## Additional Step
- [x] Study how the current reporting PPTX functionality works (end-to-end: data flow, SQL, and file generation)

### PPTX Reporting Data Flow (Current Logic)
- The report generation process is triggered via `generate_report.php`, which collects selected period, sector, programs, and targets.
- `report_data.php` is the main data provider for PPTX generation. It:
  - Accepts period, sector, selected programs, and targets as parameters.
  - Handles half-yearly/quarterly period logic.
  - Fetches the latest non-draft submission for each program and period using `program_submissions` (now directly linked to reporting periods).
  - Aggregates targets and statuses from the JSON content of each submission, but the new schema allows for more direct SQL access to targets via `program_targets`.
  - Returns a structured JSON with all program, target, status, and outcome data for the frontend (PptxGenJS).
- The generated PPTX is uploaded and saved via `save_report.php`, which stores the file and metadata in the `reports` table.

#### Key Schema Changes Impacting Reports
- **Submissions**: Now in `program_submissions`, each with a `period_id` and `content_json`.
- **Targets**: Now in `program_targets`, can be filtered by `submission_id` and are no longer just embedded in JSON.
- **Reporting Periods**: All report logic now references `reporting_periods` for period context.

> Next: Analyze the new DB schema and identify all changes relevant to report data and modules. 

### Relevant DB Schema Changes for Reporting
- **program_submissions**: Stores each program's submission for a given reporting period. Key fields: `submission_id`, `program_id`, `period_id`, `content_json`, `is_draft`, `submission_date`.
- **program_targets**: Stores targets for each program submission. Key fields: `target_id`, `submission_id`, `program_id`, `target_text`, `status_description`, `achieved`, `created_at`.
- **reporting_periods**: Stores all reporting periods. Key fields: `period_id`, `period_type`, `period_number`, `year`.
- **reports**: Stores metadata for generated reports. Key fields: `report_id`, `period_id`, `report_name`, `pptx_path`, `generated_by`, `is_public`.

#### Impact on Reporting Logic
- Targets should now be fetched directly from `program_targets` using the relevant `submission_id` (not just parsed from JSON in `program_submissions`).
- Submissions are now directly linked to periods, making period-based filtering more efficient and reliable.
- All report data should be joined using these new relationships for accuracy and performance.

> Next: Update all SQL queries in report data and report modules to match the new schema, ensuring ydata logic remains unchanged. 

#### SQL Query Update Checklist (In Progress)
- [x] app/api/report_data.php (fetches targets from program_targets using submission_id, ydata structure preserved)
- [x] app/api/generate_report.php (compatible with new report_data.php and schema)
- [ ] app/views/admin/reports/generate_reports.php
- [ ] app/views/agency/reports/view_reports.php
- [ ] app/views/agency/reports/public_reports.php
- [ ] assets/js/report-modules/report-api.js (review and update for new targets array per program)
- [ ] assets/js/report-modules/report-ui.js (review and update for new targets array per program)
- [ ] assets/js/report-modules/report-slide-populator.js (review and update for new targets array per program)
- [ ] assets/js/report-modules/report-slide-styler.js (review and update for new targets array per program)
- [ ] Any other modules discovered during refactor

> Each file/module will be updated to:
> - Fetch targets directly from `program_targets` using `submission_id`
> - Use `program_submissions` for period-based filtering
> - Join/report using the new relationships for accuracy and performance 

> Note: Backend now returns targets as an array per program. All JS modules must expect and process this format for correct report generation. 