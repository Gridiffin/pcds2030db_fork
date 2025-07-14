# Report Data and Report Modules Functionality Analysis

## Objective
Analyze and understand the report data and report modules functionality in the PCDS2030 Dashboard system.

## Tasks
- [x] Explore report-related API endpoints
- [x] Analyze report modules JavaScript files
- [x] Examine report generation functionality
- [x] Review report data handling
- [x] Document the complete report workflow
- [x] Fix function redeclaration error
- [ ] Identify areas for improvement

## Progress
- [x] Initial exploration started
- [x] Analyzed API endpoints (report_data.php, generate_report.php, save_report.php)
- [x] Examined JavaScript modules (report-api.js, report-slide-populator.js, report-slide-styler.js)
- [x] Reviewed admin interface (generate_reports.php)
- [x] Documented complete workflow
- [x] Fixed function redeclaration error in get_all_agencies()

## Notes
- Focus on understanding how reports are generated, stored, and displayed
- Identify the relationship between report data and report modules
- Document any issues or areas for enhancement

## Report System Architecture

### API Endpoints
1. **report_data.php** - Main data provider for report generation
   - Retrieves program data, outcomes, and chart data
   - Handles half-yearly period logic (combines Q1+Q2 or Q3+Q4)
   - Supports program ordering and target filtering
   - Returns JSON data for client-side processing

2. **generate_report.php** - Report generation orchestrator
   - Processes form submissions from admin interface
   - Validates program and target selections
   - Calls report_data.php to fetch data
   - Returns processed data for client-side PPTX generation

3. **save_report.php** - Report file storage
   - Handles PPTX file uploads
   - Saves files to app/reports/pptx/ directory
   - Records report metadata in database
   - Includes audit logging

### JavaScript Modules
1. **report-api.js** - API communication layer
   - fetchReportData() - Retrieves report data from server
   - uploadPresentation() - Saves generated PPTX files
   - deleteReport() - Removes reports from system
   - refreshReportsTable() - Updates reports list

2. **report-slide-populator.js** - Content population
   - populateSlide() - Main slide population function
   - addKpiBoxes() - Adds KPI data boxes
   - addProgramDataTable() - Creates program summary table
   - generatePresentation() - Orchestrates full presentation creation

3. **report-slide-styler.js** - Visual styling and layout
   - getThemeColors() - Defines color scheme
   - defineReportMaster() - Sets up slide master template
   - createKpiBox() - Renders KPI boxes with different layouts
   - addTimberExportChart() - Creates line charts
   - addTotalDegradedAreaChart() - Creates specialized charts

### Admin Interface
- **generate_reports.php** - Main admin interface
  - Period and program selection
  - Program ordering functionality
  - Target filtering options
  - Report generation controls
  - Recent reports display

### Data Flow
1. Admin selects period, programs, and targets
2. generate_report.php processes selections
3. report_data.php fetches and formats data
4. Client-side JavaScript receives JSON data
5. report-slide-populator.js populates slide content
6. report-slide-styler.js applies styling and layout
7. PptxGenJS generates PPTX file
8. report-api.js uploads file to server
9. save_report.php stores file and records metadata

### Key Features
- Half-yearly period support (combines quarterly data)
- Program ordering and target filtering
- Multiple KPI layout types (simple, detailed_list, comparison)
- Chart generation (timber export, degraded area)
- Audit logging for all operations
- Public/private report visibility
- File management and cleanup

## Issues Fixed
- **Function Redeclaration Error**: Fixed `get_all_agencies()` function conflict between `app/lib/admins/users.php` and `app/lib/admins/agencies.php`
  - Renamed function in `agencies.php` to `get_all_agency_users()` to avoid conflicts
  - Removed unused `$agencies` variable assignment in `generate_reports.php`
  - Both functions now serve distinct purposes:
    - `get_all_agencies()` in `users.php`: Gets agency groups from `agency` table
    - `get_all_agency_users()` in `agencies.php`: Gets agency users from `users` table 