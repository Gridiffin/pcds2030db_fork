# PCDS 2030 Dashboard - System Context and Design Document

## ORGANIZATIONAL CONTEXT

Sarawak's Ministry of Natural Resources and Urban Development oversees several agencies:

1) SFC
2) STIDC
3) FD

## PROBLEM STATEMENT

Currently, agencies compile quarterly reports using Excel, generating tables and graphs which are then copied/converted into PowerPoint slides. This process is time-consuming and inefficient.

The goal is to develop a progressive web app (PWA) that replaces Excel-based reporting with a streamlined web-based solution. The system should:
- Allow agencies to add submissions based on reporting period.
- Allow agencies to track programs with targets and achievements.
- Enable the Ministry (admin) to generate consolidated PowerPoint reports.

## SYSTEM ARCHITECTURE

- Frontend: HTML (in PHP), CSS, JavaScript, Bootstrap
- Backend: PHP
- Database: MySQL
- Server Environment: XAMPP, Laragon (Apache)
- Libraries: Chart.js (data visualization)
- Hosting (currently has a version being hosted live): cPanel

## Key Components
1. Authentication System - Handles user login and session management
2. Agency Module - Program, Submission: Standard CRUD operations that will be filtered through role-based access control.
3. Admin Module - User management, program management, outcomes management, reporting period management, audit logs, generating reports.
4. Reporting System - Periodical submissions and historical data visualization
5. Dashboard - Summary statistics and progress visualization for the current/open reporting period.

## Data Model
### Core Entities:
- Users - User accounts with roles (admin, agency, focal)
- Programs - Core entity representing agency initiatives.
- Program Submissions - Progress reports for programs in specific periods
- Reporting Periods - Quarterly reporting timeframes
- Outcomes - Connected to the initatives in some way (have not been specified)

### Schema Evolution:
The database schema: ...\app\database\currentpcds2030db.sql
- Initiatives, program, submission, targets has their own table and column

## User Roles
- Agency Users are divided into 2: Focal and Regular User
  a. focal: a focal person that has ultimate permissions to every CRUD  operations BUT only within their own agency. Only focals are allowed to finalize submission in their own agency.
  b. regular: basic CRUD permissions
- In some cases, particularly on programs, there are owner of the program, the editor and the viewer. These user permissions are configurable from Edit Program page but only focal and owner can decide who is the editor and the viewer among their own agency.
- An agency _can_ view other agencies' programs but NOT in the view programs (app\views\agency\programs\view_programs.php) page. 

- Admin Users: Literally all the standard administrative functions. Only admin can generate reports.

## Development Environment
- Local development with XAMPP, Laragon.

## File Structure
[Important Note: this whole system is currently messy. So, expect some files to randomly in a unsuitable directory]
- `/assets` - CSS, JavaScript, fonts, images, and other static resources
  - `/assets/css` - All CSS files (organized by admin, agency, base, components, custom, layout, outcomes, pages, etc.)
  - `/assets/fonts` - Font files (fontawesome, nunito)
  - `/assets/images` - Image assets (logos, icons)
  - `/assets/js` - JavaScript files (organized by admin, agency, charts, components, outcomes, report-modules, shared, utilities, etc.)
- `/app` - Main application code
  - `/app/ajax` - AJAX endpoint scripts for data operations
  - `/app/api` - API endpoint scripts (including outcomes, programs, reports, etc.)
  - `/app/config` - Application configuration and database schema files
  - `/app/controllers` - Controller classes
  - `/app/database` - SQL database schema and migration scripts
  - `/app/handlers` - Request handlers (admin, user processing)
  - `/app/lib` - Library and helper PHP files (admin, agencies, asset helpers, etc.)
  - `/app/migrations` - SQL migration scripts
  - `/app/reports` - Generated report files (pdf, pptx)
  - `/app/views` - User interface templates organized by role
    - `/app/views/admin` - Admin interfaces (dashboard, audit, initiatives, links, outcomes, periods, programs, reports, settings, style guide, users)
    - `/app/views/agency` - Agency user interfaces (dashboard, initiatives, outcomes, programs, reports, sectors, users)
    - `/app/views/layouts` - Shared layout templates (admin_nav, agency_nav, footer)
- `/scripts` - Maintenance and migration scripts (PHP and SQL)
- `/uploads` - Uploaded files (program attachments, organized by program ID)
- `index.php`, `login.php`, `logout.php`, `download.php` - Entry point and main scripts
- `README.md`, `system_context.md` - Documentation

## Full Workflow of the System

[Due to how the client requested we have 'divided' the workflow for this system into two: Agency-Level Reporting Workflow and Ministry-Level Reporting Workflow ]

1. Agency-Level Reporting Workflow
- As the name suggests, only users in their own respective agency themselves is involved in this workflow.
- In most cases, the owner of the program (who would be appointed by focal themselves outside the system) would create a new program and assigned a few users as editors of the said program.
- These editors would then responsible in creating a submission based on the reporting period.
- So, techinically, one program can only have 4 submissions across 4 periods.
- Once a submission is created, they would have to enter the information about the targets that is specific to that submission only. (in the cases of a target that can extend up to multiple periods, the backend would intelligently read the status of the targets. if a target is found to have status != completed, then the target would then be pushed into the next quarter's submission if a new submission is created in the next quarter.)
- Once everything is ready, the editors can then save the submission as a draft.
- The focal would then finalize the submission (again, according to which reporting period the focals want to finalize) so that the admins can see the submission based on the programs.

2. Ministry-Level Reporting Workflow
- The ministry level is a bit different. Admins (Ministry) rarely needs to do CRUD stuff except reading the data.
- Their job is to oversee programs and initatives, manage outcomes and generate reports. Only admin can set what would appear in the report pptx.
- Some of the important things in the reports are: Programs, their targets and status/achievement, outcomes
-  These are all depends on focal's finalized submission, Only finalized submission can be pushed into the generate reports module to be included in the report pptx.
- All of these still depends on the reporting period.


**NOTE**: these are only a heavily summarized workflow. 


## Development Notes

- Rating and status are two different things. Program has both of them. Initiative has 'status' field too. Targets also has 'status'. (Biggest difference between both is rating is pushed into ministry-level reporting but status only gets pushed into the fronten of both reporting workflow)
 

## Forestry Sector Implementation (May 2025)
- Current focus is exclusively on the Forestry sector
- Three main agencies are included: Forestry Department, SFC, and STIDC
- Forestry-specific metrics focus on timber exports, forest conservation, and sustainability

