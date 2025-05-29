# Directory Structure Analysis - PCDS2030 Dashboard

## Overview
This document analyzes the complete directory structure of the PCDS2030 Dashboard project, focusing on the admin and agency views organization.

## Project Structure Summary

### Root Level
- **download.php** - File download handler
- **index.php** - Main entry point
- **login.php** - Authentication page
- **logout.php** - Session termination
- **system_context.txt** - System documentation

### App Directory Structure (`/app/`)

#### AJAX Handlers (`/app/ajax/`)
- `admin_dashboard_data.php` - Admin dashboard data endpoint
- `agency_dashboard_data.php` - Agency dashboard data endpoint
- `check_period_exists.php` - Period validation
- `check_period_overlap.php` - Period overlap validation
- `dashboard_data.php` - General dashboard data
- `delete_period.php` - Period deletion handler
- `periods_data.php` - Period data management
- `save_period.php` - Period saving handler
- `toggle_period_status.php` - Period status management
- `update_period.php` - Period update handler

#### API Endpoints (`/app/api/`)
- `delete_report.php` - Report deletion
- `get_period_programs.php` - Period-based program retrieval
- `report_data.php` - Report data management
- `save_outcome_json.php` - Outcome data saving
- `save_report.php` - Report saving
- **Outcomes Submodule** (`/app/api/outcomes/`)
  - `get_outcome_history_data.php` - Historical outcome data
  - `get_outcome.php` - Single outcome retrieval

#### Configuration (`/app/config/`)
- `config.php` - Main configuration file

#### Controllers (`/app/controllers/`)
- `DashboardController.php` - Dashboard logic controller

#### Database (`/app/database/`)
- `pcds2030_dashboard.sql` - Database schema

#### Request Handlers (`/app/handlers/`)
- **Admin Handlers** (`/app/handlers/admin/`)
  - `get_user.php` - User data retrieval
  - `process_user.php` - User data processing

#### Libraries (`/app/lib/`)
- **Core Libraries:**
  - `admin_dashboard_stats.php` - Admin dashboard statistics
  - `admin_functions.php` - Admin-specific functions
  - `agency_functions.php` - Agency-specific functions
  - `asset_helpers.php` - Asset management helpers
  - `dashboard_header.php` - Dashboard header component
  - `db_connect.php` - Database connection handler
  - `functions.php` - General utility functions
  - `period_selector.php` - Period selection component
  - `rating_helpers.php` - Rating system helpers
  - `session.php` - Session management
  - `status_helpers.php` - Status management helpers
  - `utilities.php` - General utilities

- **Admin Libraries** (`/app/lib/admins/`)
  - `core.php` - Admin core functionality
  - `index.php` - Admin index functions
  - `metrics.php` - Admin metrics management
  - `outcomes.php` - Admin outcomes management
  - `periods.php` - Admin period management
  - `settings.php` - Admin settings management
  - `statistics.php` - Admin statistics
  - `users.php` - Admin user management

- **Agency Libraries** (`/app/lib/agencies/`)
  - `core.php` - Agency core functionality
  - `index.php` - Agency index functions
  - `metrics.php` - Agency metrics management
  - `outcomes.php` - Agency outcomes management
  - `programs.php` - Agency program management
  - `statistics.php` - Agency statistics

#### Reports (`/app/reports/`)
- **PDF Reports** (`/app/reports/pdf/`)
- **PowerPoint Reports** (`/app/reports/pptx/`)
  - Contains generated presentation files

### Views Structure (`/app/views/`)

#### Admin Views (`/app/views/admin/`)

##### AJAX Views (`/app/views/admin/ajax/`)
- `admin_dashboard_data.php` - Admin dashboard data view
- `get_programs_list.php` - Programs list AJAX view
- `recent_reports_table.php` - Recent reports table view

##### Audit Views (`/app/views/admin/audit/`)
- `audit_log.php` - System audit log interface

##### Dashboard Views (`/app/views/admin/dashboard/`)
- `dashboard.php` - Main admin dashboard
- `dashboard.php.bak` - Dashboard backup

##### Metrics Views (`/app/views/admin/metrics/`)
- `manage_metrics.php` - Metrics management interface
- `view_metric.php` - Individual metric viewing

##### Outcomes Views (`/app/views/admin/outcomes/`)
- `create_outcome.php` - Outcome creation interface
- `delete_outcome.php` - Outcome deletion handler
- `edit_outcome.php` - Outcome editing interface
- `manage_outcomes.php` - Outcomes management dashboard
- `manage_outcomes.php.timestamp` - Timestamped backup
- `manage_outcomes_no_js.php` - Non-JavaScript version
- `outcome_history.php` - Outcome history view
- `unsubmit_outcome.php` - Outcome unsubmission handler
- `view_outcome.php` - Individual outcome viewing

##### Periods Views (`/app/views/admin/periods/`)
- `manage_periods.php` - Period management interface
- `reporting_periods.php` - Reporting periods configuration

##### Programs Views (`/app/views/admin/programs/`)
- `assign_programs.php` - Program assignment interface
- `delete_program.php` - Program deletion handler
- `edit_program.php` - Program editing interface
- `manage_programs.php` - Programs management dashboard
- `programs.php` - Programs overview
- `reopen_program.php` - Program reopening handler
- `resubmit.php` - Program resubmission handler
- `unsubmit.php` - Program unsubmission handler
- `view_program.php` - Individual program viewing

##### Reports Views (`/app/views/admin/reports/`)
- `generate_reports.php` - Report generation interface

##### Settings Views (`/app/views/admin/settings/`)
- `audit_log.php` - Audit log settings
- `manage_periods.php` - Period management settings
- `reporting_periods.php` - Reporting period settings
- `system_settings.php` - System configuration settings

##### Style Guide Views (`/app/views/admin/style_guide/`)
- `style-guide.php` - UI/UX style guide reference

##### Users Views (`/app/views/admin/users/`)
- `add_user.php` - User creation interface
- `edit_user.php` - User editing interface
- `manage_users.php` - User management dashboard

#### Agency Views (`/app/views/agency/`)

##### Main Agency Files:
- `all_notifications.php` - Notifications center
- `create_outcome.php` - Outcome creation (current focus)
- `create_outcomes_detail.php` - Detailed outcome creation
- `create_program.php` - Program creation
- `dashboard.php` - Agency dashboard
- `delete_metric.php` - Metric deletion
- `delete_metric_detail.php` - Detailed metric deletion
- `delete_program.php` - Program deletion
- `edit_outcomes.php` - Outcome editing
- `program_details.php` - Program details view
- `submit_draft_metric.php` - Draft metric submission
- `submit_outcomes.php` - Outcome submission
- `submit_program_data.php` - Program data submission
- `update_metric.php` - Metric updates
- `update_program.php` - Program updates
- `view_all_sectors.php` - Sector overview
- `view_metric.php` - Metric viewing
- `view_outcome.php` - Outcome viewing
- `view_programs.php` - Programs overview
- `view_reports.php` - Reports viewing

##### Agency AJAX (`/app/views/agency/ajax/`)
- `dashboard_data.php` - Dashboard data handler
- `sectors_data.php` - Sector data handler

##### Agency Dashboard (`/app/views/agency/dashboard/`)
- Currently empty

#### Layout Views (`/app/views/layouts/`)
- `admin_nav.php` - Admin navigation component
- `agency_nav.php` - Agency navigation component
- `footer.php` - Footer component
- `header.php` - Header component

### Assets Structure (`/assets/`)

#### CSS (`/assets/css/`)
- `base.css` - Base styling (centralized CSS reference)
- `main.css` - Main stylesheet
- `simple-header.css` - Simple header styles
- `variables.css` - CSS variables
- **Subdirectories:**
  - `admin/` - Admin-specific styles
  - `base/` - Base component styles
  - `components/` - UI component styles
  - `custom/` - Custom styles
  - `layout/` - Layout-specific styles
  - `pages/` - Page-specific styles

#### JavaScript (`/assets/js/`)
- `ajax-helpers.js` - AJAX utility functions
- `login.js` - Login functionality
- `main.js` - Main JavaScript
- `metric-editor.js` - Metric editing functionality
- `outcome-editor.js` - Outcome editing functionality
- `period_selector.js` - Period selection functionality
- `program-ordering.js` - Program ordering functionality
- `report-generator.js` - Report generation functionality
- `url_helpers.js` - URL utility functions
- **Subdirectories:**
  - `admin/` - Admin-specific scripts
  - `agency/` - Agency-specific scripts
  - `charts/` - Chart-related scripts
  - `report-modules/` - Report module scripts
  - `utilities/` - Utility scripts

## Key Observations

### ✅ Completed Analysis
- [x] Mapped complete directory structure
- [x] Identified admin vs agency separation
- [x] Documented file organization patterns
- [x] Analyzed asset structure

### 📋 Structure Patterns

1. **Clear Separation**: Admin and agency views are completely separated
2. **Modular Organization**: Each feature area has its own subdirectory
3. **Consistent Naming**: Files follow consistent naming conventions
4. **Component Reuse**: Shared layouts and components in `/layouts/`
5. **Asset Organization**: CSS and JS organized by purpose and user type

### 🎯 Key Findings

1. **Admin Structure** is more comprehensive with dedicated sections for:
   - User management
   - System settings
   - Audit trails
   - Comprehensive reporting

2. **Agency Structure** is focused on data entry and viewing:
   - Outcome creation and management
   - Program management
   - Basic reporting

3. **Current File Focus**: `create_outcome.php` in agency views is well-structured for creating outcomes with dynamic tables and monthly data.

### 🔄 Next Steps
- [ ] Review file dependencies and includes
- [ ] Analyze CSS/JS asset loading patterns
- [ ] Document database schema relationships
- [ ] Review authentication and session management
