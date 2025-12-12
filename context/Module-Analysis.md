# PCDS 2030 Dashboard - Comprehensive Module Analysis

## Executive Summary

This document provides a comprehensive analysis of all modules in the PCDS 2030 Dashboard system, mapping the complete data flow, file dependencies, user workflows, and integration patterns for both admin and agency roles. The system follows a traditional MVC-like pattern with separate admin and agency interfaces.

## System Architecture Overview

### Core Structure
- **Entry Point**: `index.php` - Routes users to appropriate dashboards based on authentication
- **Authentication**: Session-based with role-based access control (admin, agency, focal)
- **Database**: MySQL with centralized schema definition in `app/config/database_schema.php`
- **File Organization**: Views separated by role (`admin/`, `agency/`), shared components in `layouts/` and `shared/`
- **Asset Management**: CSS and JS organized by role and module, with bundle system for optimization

### User Roles & Permissions
1. **Admin**: Full system access, can manage all entities, generate reports, view all data
2. **Agency**: Limited to own agency data, can create/edit programs, submit reports
3. **Focal**: Agency-level user with specific permissions for program management

---

## Shared/Common Modules

### 1. Login/Logout Module

#### Core Files
- **Views**: `login.php`, `logout.php`
- **Auth Logic**: `app/lib/session.php`, `app/lib/functions.php`
- **Role Functions**: `app/lib/admins/core.php`, `app/lib/agencies/core.php`
- **CSS**: `assets/css/shared/login.css`, `assets/css/shared/login/` (modular CSS)
- **JavaScript**: `assets/js/login.js`, `assets/js/shared/login.js`

#### Database Integration
- **Tables Used**: `users`, `agency`, `audit_logs`
- **Authentication Flow**:
  ```sql
  SELECT user_id, username, pw, role, agency_id, fullname, is_active 
  FROM users 
  WHERE username = ? AND is_active = 1
  ```

#### Business Logic
1. **Login Process**:
   - Form validation (username/password required)
   - Credential verification using `validate_login()` function
   - Session creation with user data
   - Role-based redirection (admin → admin dashboard, agency → agency dashboard)
   - Audit logging via `log_activity()`

2. **Session Management**:
   - Auto-start sessions across application
   - Role checking functions: `is_admin()`, `is_agency()`, `is_focal_user()`
   - Session destruction on logout with cookie cleanup

3. **Security Features**:
   - Input sanitization
   - Password hashing verification
   - Active user status checking
   - IP address logging in audit trail

#### User Interface
- **Modern Split Design**: Welcome section + login form
- **Features Showcase**: Performance tracking, reporting, program management
- **Error Handling**: Contextual error messages
- **Accessibility**: Proper form labels, keyboard navigation
- **Responsive**: Mobile-friendly layout with breakpoints

#### Dependencies
- Bootstrap 5.2.3 for UI components
- Font Awesome for icons
- Custom animation system for interactions
- Vite bundle system for asset optimization

---

### 2. Layouts & Navigation Module

#### Core Files
- **Layouts**: `app/views/layouts/base.php`, `app/views/layouts/base_admin.php`
- **Navigation**: `app/views/layouts/navbar-modern.php`, `app/views/layouts/admin-navbar-modern.php`
- **Components**: `app/views/layouts/page_header.php`, `app/views/layouts/footer-modern.php`

#### Navigation Structure
- **Admin Navigation**: Dashboard, Programs, Outcomes, Initiatives, Reports, Users, Settings
- **Agency Navigation**: Dashboard, Programs, Outcomes, Initiatives, Reports, Profile
- **Breadcrumb System**: Dynamic breadcrumb generation based on current page

#### Shared Components
- **Command Palette**: Global search and navigation (`assets/js/components/command-palette.js`)
- **Notification System**: Real-time notifications (`assets/js/components/notification-system.js`)
- **Modal System**: Shared modal components for confirmations and forms

---

## Admin Modules

### 3. Admin Dashboard Module

#### Core Files
- **Controller**: `app/controllers/AdminDashboardController.php`
- **View**: `app/views/admin/dashboard/dashboard.php`
- **Content**: `app/views/admin/dashboard/partials/dashboard_content.php`
- **Data Sources**: `app/lib/admins/statistics.php`, `app/lib/admins/index.php`
- **CSS**: `assets/css/admin/dashboard/dashboard.css`
- **JavaScript**: `assets/js/admin/admin-dashboard-modern.js`

#### Database Integration
```sql
-- Key queries for dashboard data
SELECT COUNT(*) as total_programs FROM programs WHERE is_deleted = 0;
SELECT COUNT(*) as submitted_count FROM program_submissions WHERE is_submitted = 1;
SELECT COUNT(*) as draft_count FROM program_submissions WHERE is_draft = 1;
SELECT COUNT(*) as total_users FROM users WHERE is_active = 1;
```

#### Business Logic
1. **Statistics Aggregation**:
   - Total programs across all agencies
   - Submission statistics by status (draft, submitted, finalized)
   - User counts by role and agency
   - Period-based performance metrics

2. **Data Visualization**:
   - Programs overview with modern card layout
   - Recent submissions table with pagination
   - Quick action buttons for common tasks
   - KPI overview for key metrics

3. **Real-time Updates**:
   - AJAX refresh for statistics
   - Live notification system
   - Automatic period management

#### User Interface
- **Modern Bento Grid Layout**: Card-based dashboard with statistics
- **Interactive Charts**: Program status distribution, submission trends
- **Quick Actions**: Direct links to common admin tasks
- **Period Selector**: Toggle between reporting periods
- **Responsive Cards**: Mobile-optimized statistics display

#### API Integration
- **Endpoints**:
  - `app/ajax/admin_dashboard_data.php` - Dashboard statistics
  - `app/ajax/get_stat_details.php` - Detailed statistics modal
  - `app/views/admin/ajax/recent_reports_table_new.php` - Recent reports

---

### 4. Admin Programs Module

#### Core Files
- **Views**: `app/views/admin/programs/` (manage_programs.php, edit_program.php, etc.)
- **Model**: `app/lib/admins/AdminProgramsModel.php`
- **Data Processing**: `app/lib/admins/program_management.php`
- **CSS**: `assets/css/admin/programs/` (programs.css, edit_program.css, etc.)
- **JavaScript**: `assets/js/admin/programs/` (admin-view-programs.js, etc.)

#### Database Integration
- **Primary Tables**: `programs`, `program_submissions`, `program_targets`, `program_attachments`
- **Relationships**: 
  - programs → initiatives (initiative_id)
  - programs → agency (agency_id) 
  - programs → users (created_by)
  - program_submissions → programs (program_id)
  - program_submissions → reporting_periods (period_id)

#### Business Logic
1. **Program Management**:
   - Create/Edit/Delete programs
   - Bulk assignment to initiatives
   - Program status tracking (draft, finalized, closed)
   - Cross-agency program visibility

2. **Submission Management**:
   - View all submissions across agencies
   - Edit submission content and ratings
   - Approve/reject submissions
   - Reopen finalized submissions

3. **Attachment Handling**:
   - File upload/download system
   - File type validation
   - Storage in `uploads/programs/attachments/`

#### User Interface
- **Program List**: Filterable table with status indicators
- **Program Details**: Comprehensive view with submission history
- **Bulk Operations**: Multi-select for bulk actions
- **Status Grid**: Visual program status overview
- **Advanced Filters**: Filter by agency, status, period, rating

#### API Integration
- **Endpoints**:
  - `app/api/programs.php` - Program CRUD operations
  - `app/ajax/get_programs_list.php` - Program listing with filters
  - `app/handlers/admin/save_program.php` - Program save handler

---

### 5. Admin Outcomes Module

#### Core Files
- **Views**: `app/views/admin/outcomes/` (manage_outcomes.php, edit_outcome.php, etc.)
- **Logic**: `app/lib/admins/outcomes.php`
- **CSS**: `assets/css/admin/outcomes/outcomes.css`
- **JavaScript**: `assets/js/admin/admin-outcomes.js`

#### Database Integration
- **Primary Table**: `outcomes_details`
- **Structure**: Flexible JSON-based outcome storage
```sql
CREATE TABLE outcomes_details (
  detail_id INT AUTO_INCREMENT PRIMARY KEY,
  detail_name VARCHAR(255) NOT NULL,
  detail_json LONGTEXT NOT NULL, -- JSON structure for outcome data
  display_config JSON, -- Chart/KPI display configuration
  is_cumulative TINYINT(1) DEFAULT 0,
  indicator_type VARCHAR(100),
  agency_id INT
);
```

#### Business Logic
1. **Outcome Types**:
   - **KPI Outcomes**: Single value indicators with targets
   - **Chart Outcomes**: Multi-period data visualization
   - **Table Outcomes**: Structured data presentation

2. **Data Management**:
   - JSON-based flexible schema for different outcome types
   - Cumulative vs. period-specific data handling
   - Cross-agency outcome visibility for admin

3. **Visualization Engine**:
   - Dynamic chart generation based on outcome type
   - Configurable display settings
   - Export capabilities for reporting

#### User Interface
- **Outcome Manager**: CRUD interface for all outcomes
- **Visual Editor**: Chart/KPI configuration interface
- **Data Input Forms**: Dynamic forms based on outcome structure
- **Preview System**: Real-time preview of outcome displays

---

### 6. Admin Initiatives Module

#### Core Files
- **Views**: `app/views/admin/initiatives/` (manage_initiatives.php, create.php, etc.)
- **Logic**: `app/lib/initiative_functions.php`
- **CSS**: `assets/css/admin/initiatives/initiatives.css`
- **JavaScript**: `assets/js/admin/initiatives/manageInitiatives.js`

#### Database Integration
- **Primary Table**: `initiatives`
- **Relationships**: initiatives → programs (one-to-many)
```sql
CREATE TABLE initiatives (
  initiative_id INT AUTO_INCREMENT PRIMARY KEY,
  initiative_name VARCHAR(255) NOT NULL,
  initiative_number VARCHAR(20),
  initiative_description TEXT,
  start_date DATE, end_date DATE,
  is_active TINYINT(1) DEFAULT 1,
  created_by INT NOT NULL
);
```

#### Business Logic
1. **Initiative Management**:
   - Create strategic initiatives with numbering system
   - Define start/end dates and descriptions
   - Activate/deactivate initiatives
   - Track initiative performance through linked programs

2. **Program Linking**:
   - Assign multiple programs to initiatives
   - Bulk assignment operations
   - Track initiative progress through program submissions

#### User Interface
- **Initiative List**: Comprehensive table with search/filter
- **Create/Edit Forms**: Rich text editor for descriptions
- **Program Assignment**: Modal for bulk program assignment
- **Status Management**: Toggle active/inactive status

---

### 7. Admin Reports Module

#### Core Files
- **Views**: `app/views/admin/reports/generate_reports.php`
- **Logic**: `app/lib/admin_reports.php`
- **Generator**: `assets/js/report-generator.js`
- **API**: `app/api/generate_report.php`, `app/api/report_data.php`

#### Database Integration
- **Primary Table**: `reports`
- **File Storage**: `app/reports/pptx/` for generated files
```sql
CREATE TABLE reports (
  report_id INT AUTO_INCREMENT PRIMARY KEY,
  period_id INT NOT NULL,
  report_name VARCHAR(255) NOT NULL,
  description TEXT,
  pdf_path VARCHAR(255) NOT NULL,
  pptx_path VARCHAR(255) NOT NULL,
  generated_by INT NOT NULL,
  is_public TINYINT(1) DEFAULT 0
);
```

#### Business Logic
1. **Report Generation**:
   - PowerPoint template-based report creation
   - Data aggregation from programs, submissions, outcomes
   - Period-specific report generation
   - Automatic file naming with timestamps

2. **Template System**:
   - PPTX template processing
   - Dynamic slide population with data
   - Chart generation and embedding
   - Custom styling and branding

3. **Distribution**:
   - Public/private report settings
   - Download management
   - Archive organization

#### User Interface
- **Generation Form**: Period selection and report configuration
- **Progress Indicator**: Real-time generation progress
- **Report Library**: Generated reports with download links
- **Preview System**: Report preview before finalization

#### API Integration
- **Endpoints**:
  - `app/api/generate_report.php` - Trigger report generation
  - `app/api/report_data.php` - Fetch report data
  - `app/ajax/get_reports.php` - Report listing

---

### 8. Admin Users Module

#### Core Files
- **Views**: `app/views/admin/users/` (manage_users.php, add_user.php, edit_user.php)
- **Logic**: `app/lib/admins/users.php`
- **CSS**: `assets/css/admin/users/users.css`
- **JavaScript**: `assets/js/admin/user_form_manager.js`, `assets/js/admin/user_table_manager.js`

#### Database Integration
- **Primary Tables**: `users`, `agency`
- **User Management Queries**:
```sql
-- User listing with agency info
SELECT u.*, a.agency_name 
FROM users u 
LEFT JOIN agency a ON u.agency_id = a.agency_id 
WHERE u.is_active = 1;

-- Role-based access control
SELECT role FROM users WHERE user_id = ? AND is_active = 1;
```

#### Business Logic
1. **User Management**:
   - Create/Edit/Deactivate user accounts
   - Role assignment (admin, agency, focal)
   - Agency association
   - Password management

2. **Bulk Operations**:
   - Bulk user import
   - Bulk role changes
   - Bulk agency assignments

3. **Audit Trail**:
   - User action logging
   - Login/logout tracking
   - Permission change history

#### User Interface
- **User Table**: Sortable, filterable user listing
- **User Forms**: Comprehensive user creation/editing
- **Role Management**: Visual role assignment interface
- **Activity Monitoring**: User activity dashboard

---

### 9. Admin Settings Module

#### Core Files
- **Views**: `app/views/admin/settings/` (system_settings.php, reporting_periods.php, audit_log.php)
- **Logic**: `app/lib/admins/settings.php`, `app/lib/admins/periods.php`
- **CSS**: `assets/css/admin/settings/settings.css`
- **JavaScript**: `assets/js/admin/system_settings.js`, `assets/js/admin/reporting_periods.js`

#### Business Logic
1. **Reporting Periods**:
   - Create/Edit quarterly, half-yearly, yearly periods
   - Open/Close periods for submissions
   - Automatic period creation
   - Period overlap validation

2. **System Configuration**:
   - Application settings management
   - Feature flags and toggles
   - Email notification settings

3. **Audit Log Management**:
   - View all system activities
   - Export audit logs
   - Filter by user, action, date range

---

## Agency Modules

### 10. Agency Dashboard Module

#### Core Files
- **Controller**: `app/controllers/DashboardController.php`
- **View**: `app/views/agency/dashboard/dashboard.php`
- **Content**: `app/views/agency/dashboard/dashboard_content.php`
- **CSS**: `assets/css/agency/dashboard/` (dashboard.css, bento-grid.css, etc.)
- **JavaScript**: `assets/js/agency/dashboard/` (dashboard.js, programs.js, etc.)

#### Database Integration
```sql
-- Agency-specific program statistics
SELECT COUNT(*) as program_count 
FROM programs 
WHERE agency_id = ? AND is_deleted = 0;

-- Submission status for current period
SELECT rating, COUNT(*) as count
FROM program_submissions ps
JOIN programs p ON ps.program_id = p.program_id
WHERE p.agency_id = ? AND ps.period_id = ?
GROUP BY rating;
```

#### Business Logic
1. **Agency-Specific Data**:
   - Filter all data by current user's agency_id
   - Period-specific submission tracking
   - Program performance overview
   - Outcome progress monitoring

2. **Bento Grid Layout**:
   - Programs section with status distribution
   - Initiatives progress tracking
   - Outcomes visualization
   - Recent activity feed

3. **Interactive Features**:
   - Program status filters
   - Quick submission access
   - Real-time data refresh

#### User Interface
- **Modern Bento Grid**: Card-based responsive layout
- **Status Indicators**: Color-coded program statuses
- **Progress Charts**: Visual progress tracking
- **Action Cards**: Quick access to common tasks

---

### 11. Agency Programs Module

#### Core Files
- **Views**: `app/views/agency/programs/` (view_programs.php, create_program.php, etc.)
- **Logic**: `app/lib/agencies/programs.php`
- **CSS**: `assets/css/agency/programs/` (programs.css, edit_program.css, etc.)
- **JavaScript**: `assets/js/agency/programs/` (createLogic.js, editProgramLogic.js, etc.)

#### Database Integration
- **Same tables as admin but filtered by agency_id**
- **Agency-specific queries**:
```sql
-- Agency programs only
SELECT * FROM programs 
WHERE agency_id = ? AND is_deleted = 0;

-- Agency submissions for current period
SELECT ps.*, p.program_name 
FROM program_submissions ps
JOIN programs p ON ps.program_id = p.program_id
WHERE p.agency_id = ? AND ps.period_id = ?;
```

#### Business Logic
1. **Program Creation**:
   - Agency-restricted program creation
   - Initiative assignment
   - Program numbering system
   - File attachment support

2. **Submission Management**:
   - Period-based submission system
   - Draft/Submit workflow
   - Target tracking and status updates
   - Rating self-assessment

3. **Collaboration Features**:
   - User assignment to programs
   - Permission management within agency
   - Comment system for program discussions

#### User Interface
- **Program Cards**: Visual program representation
- **Create Wizard**: Step-by-step program creation
- **Submission Forms**: Dynamic target-based forms
- **File Manager**: Drag-drop attachment system
- **Timeline View**: Program progress visualization

#### API Integration
- **Endpoints**:
  - `app/api/programs.php` - Agency program operations
  - `app/ajax/save_submission.php` - Submission save handler
  - `app/ajax/finalize_submission.php` - Submission finalization

---

### 12. Agency Outcomes Module

#### Core Files
- **Views**: `app/views/agency/outcomes/` (edit_outcome.php, submit_outcomes.php, etc.)
- **Logic**: `app/lib/agencies/outcomes.php`
- **CSS**: `assets/css/agency/outcomes/` (outcomes.css, charts.css, etc.)
- **JavaScript**: `assets/js/agency/outcomes/` (edit.js, submit.js, chart-manager.js)

#### Database Integration
- **Agency-filtered outcome access**
- **Chart data aggregation for agency-specific outcomes**

#### Business Logic
1. **Outcome Submission**:
   - Period-based outcome reporting
   - KPI target vs. actual tracking
   - Chart data input and validation
   - Cumulative vs. period-specific calculations

2. **Data Visualization**:
   - Agency-specific outcome charts
   - Progress trend analysis
   - Target achievement tracking

#### User Interface
- **Outcome Dashboard**: Visual outcome overview
- **Data Entry Forms**: Period-specific data input
- **Chart Views**: Interactive outcome visualization
- **Target Tracking**: Progress indicators

---

### 13. Agency Initiatives Module

#### Core Files
- **Views**: `app/views/agency/initiatives/` (initiatives.php, view_initiative.php)
- **Logic**: `app/lib/agencies/initiatives.php`
- **CSS**: `assets/css/agency/initiatives/initiatives.css`
- **JavaScript**: `assets/js/agency/initiatives/` (logic.js, view.js)

#### Business Logic
1. **Initiative Viewing**:
   - View initiatives relevant to agency
   - See assigned programs under each initiative
   - Track initiative progress through program submissions

2. **Performance Tracking**:
   - Initiative-level metrics aggregation
   - Program contribution to initiative success
   - Timeline and milestone tracking

#### User Interface
- **Initiative Cards**: Visual initiative representation
- **Program Listing**: Programs assigned to initiative
- **Progress Tracking**: Initiative completion status
- **Activity Feed**: Recent initiative-related activities

---

### 14. Agency Reports Module

#### Core Files
- **Views**: `app/views/agency/reports/` (public_reports.php, view_reports.php)
- **Logic**: `app/lib/agencies/reports.php`
- **CSS**: `assets/css/agency/reports/reports.css`
- **JavaScript**: `assets/js/agency/reports/` (public_reports.js, view_reports.js)

#### Business Logic
1. **Public Report Access**:
   - View public reports generated by admin
   - Download reports in PDF/PPTX format
   - Filter reports by period and type

2. **Agency Report Generation**:
   - Generate agency-specific reports
   - Include only agency programs and outcomes
   - Export capabilities

#### User Interface
- **Report Library**: Grid view of available reports
- **Download Manager**: Secure report download system
- **Filter System**: Report filtering by criteria

---

### 15. Agency Notifications Module

#### Core Files
- **Views**: `app/views/agency/users/` (all_notifications.php, profile.php)
- **Logic**: `app/lib/agencies/notifications.php`
- **CSS**: `assets/css/agency/users/notifications.css`
- **JavaScript**: `assets/js/agency/users/` (notifications.js, ajax.js)

#### Database Integration
- **Primary Table**: `notifications`
```sql
CREATE TABLE notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(50) DEFAULT 'update',
  read_status TINYINT(1) DEFAULT 0,
  action_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Business Logic
1. **Notification System**:
   - Real-time notifications for user actions
   - System-generated alerts for deadlines
   - Email notification integration
   - Read/unread status tracking

2. **Notification Types**:
   - Submission deadlines
   - Program status changes
   - System announcements
   - Approval notifications

#### User Interface
- **Notification Bell**: Real-time notification indicator
- **Notification Center**: Full notification listing
- **Mark as Read**: Bulk mark operations
- **Action Links**: Direct links to relevant pages

---

## Cross-Module Dependencies & Data Flow

### Authentication Flow
1. **Login** → Session creation → Role-based dashboard redirect
2. **Session validation** on every page load
3. **Permission checking** for admin vs. agency access
4. **Logout** → Session destruction → Login redirect

### Data Relationships
```
agencies → users → programs → program_submissions → program_targets
                          → initiatives
                          → program_attachments
          → outcomes_details
          → notifications
          → reports (via program data aggregation)
          → audit_logs
```

### API Architecture
- **RESTful endpoints** in `/app/api/`
- **AJAX handlers** in `/app/ajax/`
- **Shared utilities** in `/app/lib/`
- **Role-based API access** with permission checks

### File Organization Patterns
```
app/
├── views/
│   ├── admin/[module]/     # Admin-specific views
│   ├── agency/[module]/    # Agency-specific views
│   ├── layouts/           # Shared layouts
│   └── shared/            # Common components
├── lib/
│   ├── admins/            # Admin business logic
│   ├── agencies/          # Agency business logic
│   └── [shared files]     # Common utilities
├── api/                   # API endpoints
├── ajax/                  # AJAX handlers
└── controllers/           # Page controllers
```

### Asset Management
```
assets/
├── css/
│   ├── admin/[module]/    # Admin-specific styles
│   ├── agency/[module]/   # Agency-specific styles
│   ├── components/        # Shared components
│   └── shared/           # Common styles
├── js/
│   ├── admin/[module]/    # Admin-specific scripts
│   ├── agency/[module]/   # Agency-specific scripts
│   ├── components/        # Shared components
│   └── shared/           # Common utilities
└── images/               # Static assets
```

## Feature-Based Architecture Recommendations

### Recommended Module Structure for New System
```
src/
├── features/
│   ├── authentication/
│   │   ├── controllers/
│   │   ├── views/
│   │   ├── services/
│   │   ├── models/
│   │   └── assets/
│   ├── dashboard/
│   │   ├── admin/
│   │   ├── agency/
│   │   └── shared/
│   ├── programs/
│   │   ├── crud/
│   │   ├── submissions/
│   │   ├── attachments/
│   │   └── reporting/
│   ├── outcomes/
│   │   ├── kpis/
│   │   ├── charts/
│   │   └── visualization/
│   ├── initiatives/
│   ├── reports/
│   ├── notifications/
│   └── user-management/
├── shared/
│   ├── components/
│   ├── services/
│   ├── utilities/
│   └── layouts/
└── core/
    ├── database/
    ├── config/
    └── middleware/
```

### Migration Strategy
1. **Extract shared components** first (authentication, layouts)
2. **Modularize by feature** rather than by role
3. **Implement service layer** for business logic
4. **Create unified API layer** with role-based permissions
5. **Consolidate asset management** with modern build tools

This analysis provides the foundation for understanding the current system architecture and planning the migration to a feature-based structure that eliminates duplication while maintaining role-based access control.