# File Structure & Organization Guide

Detailed breakdown of the PCDS2030 Dashboard file organization, naming conventions, and directory purposes.

## 📋 Table of Contents
1. [Project Root Overview](#project-root-overview)
2. [Application Core (`app/`)](#application-core-app)
3. [Static Assets (`assets/`)](#static-assets-assets)
4. [Configuration Files](#configuration-files)
5. [Testing Structure (`tests/`)](#testing-structure-tests)
6. [Documentation (`docs/` & `devs-docs/`)](#documentation)
7. [Build & Dependencies](#build--dependencies)
8. [File Naming Conventions](#file-naming-conventions)
9. [Module Organization Patterns](#module-organization-patterns)

## Complete Project Structure

```
pcds2030_dashboard_fork/
├── 📁 app/                    # Application logic & views
├── 📁 assets/                 # Static resources (CSS, JS, images)
├── 📁 devs-docs/             # Developer documentation
├── 📁 docs/                  # Project documentation & implementation notes
├── 📁 tests/                 # Test suites (PHPUnit & Jest)
├── 📁 scripts/               # Maintenance & utility scripts
├── 📁 vendor/                # PHP Composer dependencies
├── 📁 node_modules/          # Node.js dependencies
├── 📁 coverage/              # Test coverage reports
├── 📁 live/                  # Live deployment helpers
├── 📁 uploads/               # User-uploaded files
├── 📄 index.php              # Main application entry point
├── 📄 login.php              # Authentication entry point
├── 📄 package.json           # Node.js project configuration
├── 📄 composer.json          # PHP dependencies configuration
├── 📄 vite.config.js         # Asset build configuration
├── 📄 phpunit.xml            # PHP testing configuration
├── 📄 jest.config.json       # JavaScript testing configuration
└── 📄 .htaccess              # Apache web server configuration
```

## Application Core (`app/`)

### Directory Structure
```
app/
├── 📁 ajax/                  # AJAX request handlers
├── 📁 api/                   # REST-like API endpoints
├── 📁 config/                # Application configuration
├── 📁 controllers/           # MVC controllers (limited usage)
├── 📁 database/              # Database schemas & migrations
├── 📁 handlers/              # Specialized request processors
├── 📁 lib/                   # Business logic libraries
├── 📁 migrations/            # Database migration scripts
├── 📁 reports/               # Generated reports storage
└── 📁 views/                 # User interface templates
```

### 📁 `app/ajax/` - Real-time Request Handlers
```
ajax/
├── 📄 admin_dashboard_data.php       # Admin dashboard statistics
├── 📄 admin_outcomes.php             # Admin outcome management
├── 📄 admin_user_tables.php          # User management tables
├── 📄 get_program_submissions.php    # Program submission data
├── 📄 get_user_notifications.php     # User notification system
├── 📄 save_submission.php            # Save program submissions
├── 📄 finalize_submission.php        # Finalize submissions
├── 📄 notifications.php              # General notifications
├── 📄 periods_data.php               # Reporting periods data
└── 📄 upload_program_attachment.php  # File upload handler

# Agency-specific AJAX handlers
├── 📁 agency/
│   └── 📄 check_program_number.php   # Program number validation
```

**Purpose**: Handle asynchronous requests from frontend JavaScript. Each file typically:
- Validates user permissions
- Processes POST/GET data
- Interacts with business logic
- Returns JSON responses

### 📁 `app/api/` - API Endpoints
```
api/
├── 📄 programs.php                   # Program CRUD operations
├── 📄 program_submissions.php        # Submission management
├── 📄 report_data.php                # Report generation data
├── 📄 login.php                      # Authentication endpoint
├── 📄 initiatives.php                # Initiative management
├── 📄 numbering.php                  # Auto-numbering system
└── 📄 save_outcome_json.php          # Outcome data persistence

# Specialized API modules
├── 📁 agency/
│   └── 📄 submit_submission.php      # Agency submission endpoint
└── 📁 outcomes/
    ├── 📄 get_outcome.php            # Outcome retrieval
    └── 📄 get_outcome_history_data.php # Outcome history
```

**Purpose**: RESTful-style endpoints for data operations. More structured than AJAX handlers.

### 📁 `app/config/` - Configuration Management
```
config/
├── 📄 config.php                     # Main application configuration
├── 📄 database_schema.php            # Database schema definitions
└── 📄 db_names.php                   # Database table name constants
```

**Key Configuration Areas**:
- Database connection settings (dynamic based on environment)
- Application URLs and paths
- Feature flags and constants
- Security settings

### 📁 `app/lib/` - Business Logic Layer
```
lib/
├── 📁 admins/                        # Admin-specific functionality
│   ├── 📄 core.php                   # Core admin functions
│   ├── 📄 users.php                  # User management
│   ├── 📄 programs.php               # Program administration
│   ├── 📄 outcomes.php               # Outcome management
│   ├── 📄 periods.php                # Reporting period management
│   └── 📄 statistics.php             # Statistical calculations
├── 📁 agencies/                      # Agency-specific functionality
│   ├── 📄 core.php                   # Core agency functions
│   ├── 📄 programs.php               # Program operations
│   ├── 📄 outcomes.php               # Outcome operations
│   ├── 📄 reports.php                # Report generation
│   ├── 📄 statistics.php             # Agency statistics
│   └── 📄 program_validation.php     # Data validation
├── 📄 functions.php                  # Shared utility functions
├── 📄 db_connect.php                 # Database connection
├── 📄 session.php                    # Session management
├── 📄 user_functions.php             # User-related operations
├── 📄 audit_log.php                  # Audit trail functionality
├── 📄 email_notifications.php        # Email system
└── 📄 rating_helpers.php             # Program rating calculations
```

**Organization Strategy**:
- **Separation by user role**: `admins/` vs `agencies/`
- **Function-based approach**: Each file contains related functions
- **Shared utilities**: Common functions in root `lib/` directory

### 📁 `app/views/` - User Interface Templates
```
views/
├── 📁 admin/                         # Admin interface
│   ├── 📁 dashboard/                 # Admin dashboard
│   │   ├── 📄 dashboard.php          # Main dashboard page
│   │   └── 📁 partials/              # Dashboard components
│   ├── 📁 programs/                  # Program management
│   │   ├── 📄 programs.php           # Program listing
│   │   ├── 📄 edit_program.php       # Program editing
│   │   ├── 📄 view_submissions.php   # Submission overview
│   │   └── 📁 partials/              # Program components
│   ├── 📁 users/                     # User management
│   ├── 📁 outcomes/                  # Outcome management
│   ├── 📁 reports/                   # Report generation
│   └── 📁 settings/                  # System settings
├── 📁 agency/                        # Agency interface
│   ├── 📁 dashboard/                 # Agency dashboard
│   ├── 📁 programs/                  # Program operations
│   ├── 📁 outcomes/                  # Outcome management
│   └── 📁 users/                     # Profile & notifications
├── 📁 layouts/                       # Shared layouts
│   ├── 📄 header.php                 # Common header
│   ├── 📄 footer.php                 # Common footer
│   ├── 📄 navbar.php                 # Navigation bar
│   └── 📄 base.php                   # Base layout template
└── 📁 shared/                        # Shared components
    └── 📁 login/                     # Login interface components
```

**View Organization**:
- **Role-based separation**: Admin vs Agency interfaces
- **Partial templates**: Reusable components in `partials/` subdirectories
- **Layout system**: Common layouts in `layouts/` directory

## Static Assets (`assets/`)

### Directory Structure
```
assets/
├── 📁 css/                           # Stylesheets
├── 📁 js/                            # JavaScript files
├── 📁 images/                        # Images and icons
└── 📁 fonts/                         # Web fonts
```

### 📁 `assets/css/` - Stylesheet Organization
```
css/
├── 📄 base.css                       # Main CSS import file
├── 📄 main.css                       # Legacy main stylesheet
├── 📁 base/                          # Foundation styles
│   ├── 📄 reset.css                  # CSS reset
│   ├── 📄 typography.css             # Typography rules
│   ├── 📄 utilities.css              # Utility classes
│   └── 📄 variables.css              # CSS custom properties
├── 📁 components/                    # Reusable components
│   ├── 📄 buttons.css                # Button styles
│   ├── 📄 cards.css                  # Card components
│   ├── 📄 tables.css                 # Table styling
│   ├── 📄 modals.css                 # Modal dialogs
│   ├── 📄 forms.css                  # Form elements
│   └── 📄 notifications.css          # Notification system
├── 📁 admin/                         # Admin-specific styles
│   ├── 📁 dashboard/                 # Dashboard styles
│   ├── 📁 programs/                  # Program management styles
│   ├── 📁 users/                     # User management styles
│   └── 📁 components/                # Admin components
├── 📁 agency/                        # Agency-specific styles
│   ├── 📁 dashboard/                 # Agency dashboard
│   ├── 📁 programs/                  # Program operation styles
│   └── 📁 users/                     # Profile styles
└── 📁 layout/                        # Layout-specific styles
    ├── 📄 navigation.css             # Navigation styles
    ├── 📄 footer.css                 # Footer styles
    └── 📄 grid.css                   # Grid system
```

**CSS Architecture**:
- **Base-first approach**: Foundation styles first
- **Component-based**: Reusable component styles
- **Role separation**: Admin vs Agency specific styles
- **Import system**: `base.css` imports all other files

### 📁 `assets/js/` - JavaScript Organization
```
js/
├── 📄 main.js                        # Main application initialization
├── 📁 admin/                         # Admin-specific JavaScript
│   ├── 📄 dashboard.js               # Admin dashboard logic
│   ├── 📄 programs.js                # Program management
│   ├── 📄 users.js                   # User management
│   ├── 📄 reports.js                 # Report generation
│   └── 📁 programs/                  # Program-specific modules
│       ├── 📄 edit_program.js        # Program editing
│       └── 📄 view_submissions.js    # Submission viewing
├── 📁 agency/                        # Agency-specific JavaScript
│   ├── 📄 dashboard.js               # Agency dashboard
│   ├── 📄 programs.js                # Program operations
│   ├── 📄 submissions.js             # Data submissions
│   └── 📁 programs/                  # Program modules
├── 📁 components/                    # Reusable components
│   ├── 📄 charts.js                  # Chart.js utilities
│   ├── 📄 modals.js                  # Modal functionality
│   ├── 📄 forms.js                   # Form utilities
│   └── 📄 notifications.js           # Notification system
├── 📁 shared/                        # Common utilities
│   ├── 📄 utils.js                   # General utilities
│   ├── 📄 ajax.js                    # AJAX helpers
│   ├── 📄 validation.js              # Form validation
│   └── 📄 login.js                   # Authentication
└── 📁 utilities/                     # Utility modules
    ├── 📄 form_validation.js         # Form validation helpers
    ├── 📄 table_sorting.js           # Table sorting functionality
    └── 📄 responsive_tables.js        # Responsive table handling
```

**JavaScript Architecture**:
- **Module pattern**: Each file represents a functional module
- **Role-based organization**: Admin vs Agency specific code
- **Shared utilities**: Common functions in `shared/` and `utilities/`
- **Component approach**: Reusable components in `components/`

## Configuration Files

### Root Configuration Files
```
📄 .htaccess                          # Apache configuration
📄 package.json                       # Node.js project settings
📄 composer.json                      # PHP dependencies
📄 vite.config.js                     # Build tool configuration
📄 phpunit.xml                        # PHP testing configuration
📄 jest.config.json                   # JavaScript testing configuration
📄 babel.config.json                  # JavaScript transpilation
```

### Application Configuration
```
app/config/
├── 📄 config.php                     # Main configuration
│   ├── Database settings (dynamic)
│   ├── Application URLs
│   ├── File paths
│   ├── Feature flags
│   └── Helper functions
├── 📄 database_schema.php            # Schema definitions
└── 📄 db_names.php                   # Table name constants
```

## Testing Structure (`tests/`)

### Test Organization
```
tests/
├── 📄 bootstrap.php                  # Test bootstrap file
├── 📄 setup.js                       # JavaScript test setup
├── 📁 admin/                         # Admin functionality tests
│   ├── 📄 dashboardLogic.test.js     # Dashboard testing
│   ├── 📄 programsLogic.test.js      # Program management tests
│   └── 📄 usersLogic.test.js         # User management tests
├── 📁 agency/                        # Agency functionality tests
│   ├── 📄 dashboardLogic.test.js     # Agency dashboard tests
│   ├── 📄 outcomesSubmit.test.js     # Outcome submission tests
│   └── 📁 programs/                  # Program-specific tests
├── 📁 php/                           # PHP backend tests
│   ├── 📁 admin/                     # Admin PHP tests
│   │   ├── 📄 AdminAuthTest.php      # Authentication tests
│   │   ├── 📄 AdminProgramsTest.php  # Program management tests
│   │   └── 📄 AdminUsersTest.php     # User management tests
│   └── 📁 agency/                    # Agency PHP tests
├── 📁 shared/                        # Shared functionality tests
│   ├── 📄 loginLogic.test.js         # Login functionality tests
│   └── 📄 loginDOM.test.js           # DOM manipulation tests
└── 📁 setup/                         # Test setup helpers
    └── 📄 dashboardMocks.js           # Mock data for testing
```

**Testing Strategy**:
- **Frontend Tests**: Jest for JavaScript functionality
- **Backend Tests**: PHPUnit for PHP business logic
- **Separation by role**: Admin vs Agency specific tests
- **Mock data**: Consistent test data in setup files

## Documentation

### Developer Documentation (`devs-docs/`)
```
devs-docs/
├── 📄 README.md                      # Documentation hub
├── 📄 SETUP.md                       # Installation guide
├── 📄 ARCHITECTURE.md                # System architecture
├── 📄 FILE-STRUCTURE.md              # This file
├── 📄 API.md                         # API documentation
└── 📄 TESTING.md                     # Testing guide
```

### Project Documentation (`docs/`)
```
docs/
├── 📄 PROBLEMS.md                    # Known issues & solutions
├── 📄 bugs_tracker.md                # Bug tracking
├── 📄 system_context.md              # System context
├── 📁 implementations/               # Implementation guides
└── Various implementation & analysis files
```

## Build & Dependencies

### Node.js Dependencies (`node_modules/`)
```
node_modules/                         # NPM packages
├── Development dependencies (Vite, Jest, Babel)
├── Build tools and transpilers
└── Testing frameworks
```

### PHP Dependencies (`vendor/`)
```
vendor/                               # Composer packages
├── 📁 phpunit/                       # PHPUnit testing framework
└── Other PHP packages as needed
```

### Generated Files
```
📁 coverage/                          # Test coverage reports
📁 dist/                              # Built assets (production)
```

## File Naming Conventions

### PHP Files
```
# Descriptive, snake_case naming
admin_dashboard_data.php              # Clear purpose indication
get_program_submissions.php           # Action-oriented naming
save_submission.php                   # Verb-noun pattern

# Namespace-like organization
admin/users.php                       # Role-based grouping
agencies/programs.php                 # Context-specific functions

# Template files
dashboard.php                         # Simple, descriptive
edit_program.php                      # Action-oriented
```

### JavaScript Files
```
# Module-based naming
dashboard.js                          # Feature-based
programsLogic.js                      # Logic separation
formValidation.js                     # Utility-based

# Component naming
charts.js                             # Component type
modals.js                             # UI component
notifications.js                      # Feature component

# Test files
dashboardLogic.test.js                # Test suffix pattern
loginDOM.test.js                      # Specific test focus
```

### CSS Files
```
# Component-based naming
buttons.css                           # Component type
cards.css                             # UI component
tables.css                            # Element-specific

# Page-specific naming
dashboard.css                         # Page-based
programs.css                          # Feature-based

# Utility naming
utilities.css                         # Helper classes
variables.css                         # CSS custom properties
```

## Files Actually Used by Views

*This section filters the complete project structure to show only files that are actually included, required, or called from the view files - avoiding confusion with unused legacy code.*

### 📁 `app/lib/` - PHP Libraries (Actually Used)

**Core Libraries (Universally Included):**
```
app/lib/
├── 📄 config.php                      # Main configuration (all views)
├── 📄 db_connect.php                  # Database connection (all views)
├── 📄 session.php                     # Session management (all views)
├── 📄 functions.php                   # Shared utility functions
├── 📄 user_functions.php              # User operations
└── 📄 asset_helpers.php               # Asset URL generation
```

**Specialized Libraries (Feature-Specific):**
```
app/lib/
├── 📄 rating_helpers.php              # Program rating calculations
├── 📄 program_status_helpers.php      # Status display helpers
├── 📄 initiative_functions.php        # Initiative operations
├── 📄 admin_functions.php             # Admin-specific functions
├── 📄 notifications_core.php          # Notification system
└── 📄 audit_log.php                   # Activity logging
```

**Admin-Specific Libraries:**
```
app/lib/admins/
├── 📄 admin_edit_program_data.php     # Program editing data
├── 📄 admin_edit_submission_data.php  # Submission editing data
├── 📄 admin_program_details_data.php  # Program details data
└── 📄 admin_submission_data.php       # Submission processing data
```

### 📁 `app/ajax/` - AJAX Endpoints (Actually Called)

**Real-time Data Operations:**
```
app/ajax/
├── 📄 get_program_submissions.php     # Fetch submission data
├── 📄 download_program_attachment.php # File downloads
├── 📄 get_user_notifications.php      # Notification loading
├── 📄 notifications.php               # Notification management
├── 📄 simple_finalize.php             # Quick finalization
├── 📄 toggle_period_status.php        # Period management
└── 📄 delete_period.php               # Period deletion
```

### 📁 `app/api/` - API Endpoints (Actually Called)

**Structured Data Operations:**
```
app/api/
├── 📄 simple_gantt_data.php           # Gantt chart data
├── 📄 get_period_programs.php         # Period-based program data
├── 📄 save_report.php                 # Report saving
└── 📄 delete_report.php               # Report deletion
```

### 📁 `app/handlers/` - Request Handlers (Actually Used)

```
app/handlers/
└── 📄 profile_handler.php             # Profile management handler
```

### 📁 `app/views/` - Complete View Structure (All Files Listed)

**Note: All view files are included as they represent the complete user interface**

```
app/views/
├── 📁 admin/                          # Admin interface
│   ├── 📁 dashboard/
│   │   ├── 📄 dashboard.php
│   │   └── 📁 partials/
│   │       └── 📄 dashboard_content.php
│   ├── 📁 users/
│   │   ├── 📄 manage_users.php
│   │   ├── 📄 edit_user.php
│   │   ├── 📄 add_user.php
│   │   └── 📁 partials/
│   │       ├── 📄 manage_users_content.php
│   │       ├── 📄 edit_user_content.php
│   │       └── 📄 add_user_content.php
│   ├── 📁 outcomes/
│   │   ├── 📄 manage_outcomes.php
│   │   ├── 📄 edit_outcome.php
│   │   ├── 📄 edit_kpi.php
│   │   ├── 📄 view_outcome.php
│   │   └── 📁 partials/
│   │       ├── 📄 manage_outcomes_content.php
│   │       ├── 📄 edit_outcome_content.php
│   │       ├── 📄 edit_kpi_content.php
│   │       └── 📄 view_outcome_content.php
│   ├── 📁 notifications/
│   │   ├── 📄 manage_notifications.php
│   │   └── 📁 partials/
│   │       └── 📄 manage_notifications_content.php
│   ├── 📁 settings/
│   │   ├── 📄 system_settings.php
│   │   ├── 📄 audit_log.php
│   │   ├── 📄 reporting_periods.php
│   │   └── 📁 partials/
│   │       ├── 📄 system_settings_content.php
│   │       ├── 📄 audit_log_content.php
│   │       └── 📄 reporting_periods_content.php
│   ├── 📁 reports/
│   │   ├── 📄 generate_reports.php
│   │   └── 📁 partials/
│   │       └── 📄 generate_reports_content.php
│   ├── 📁 periods/
│   │   ├── 📄 reporting_periods.php
│   │   └── 📁 partials/
│   │       └── 📄 reporting_periods_content.php
│   └── 📁 programs/
│       ├── 📄 programs.php
│       ├── 📄 program_details.php
│       ├── 📄 edit_submission.php
│       ├── 📄 view_submissions.php
│       ├── 📄 add_submission.php
│       ├── 📄 edit_program.php
│       └── 📁 partials/
│           ├── 📄 programs_content.php
│           ├── 📄 admin_program_details_content.php
│           ├── 📄 admin_edit_submission_content.php
│           ├── 📄 admin_view_submissions_content.php
│           ├── 📄 admin_select_submission_period_content.php
│           ├── 📄 admin_select_view_submission_period_content.php
│           ├── 📄 admin_program_filters.php
│           └── 📄 add_submission_content.php
├── 📁 agency/                         # Agency interface
│   ├── 📁 users/
│   │   ├── 📄 profile.php
│   │   ├── 📄 all_notifications_simple.php
│   │   └── 📁 partials/
│   │       ├── 📄 profile_content.php
│   │       ├── 📄 notifications_content_simple.php
│   │       └── 📄 notification_item.php
│   ├── 📁 reports/
│   │   ├── 📄 view_reports.php
│   │   ├── 📄 public_reports.php
│   │   └── 📁 partials/
│   │       ├── 📄 view_reports_content.php
│   │       └── 📄 public_reports_content.php
│   ├── 📁 initiatives/
│   │   ├── 📄 initiatives.php
│   │   ├── 📄 view_initiative.php
│   │   └── 📁 partials/
│   │       ├── 📄 initiatives_content.php
│   │       └── 📄 view_initiative_content.php
│   ├── 📁 outcomes/
│   │   ├── 📄 submit_outcomes.php
│   │   ├── 📄 view_outcome.php
│   │   ├── 📄 edit_kpi.php
│   │   ├── 📄 edit_outcome.php
│   │   └── 📁 partials/
│   │       ├── 📄 submit_content.php
│   │       ├── 📄 view_content.php
│   │       ├── 📄 edit_kpi_content.php
│   │       └── 📄 edit_outcome_content.php
│   ├── 📁 dashboard/
│   │   └── 📄 dashboard.php
│   └── 📁 programs/
│       ├── 📄 view_programs.php
│       ├── 📄 view_submissions.php
│       ├── 📄 edit_submission.php
│       ├── 📄 program_details.php
│       ├── 📄 edit_program.php
│       ├── 📄 create_program.php
│       ├── 📄 add_submission.php
│       └── 📁 partials/
│           ├── 📄 view_submissions_content.php
│           ├── 📄 edit_submission_content.php
│           ├── 📄 program_details_content.php
│           ├── 📄 edit_program_content.php
│           ├── 📄 create_program_content.php
│           ├── 📄 add_submission_content.php
│           ├── 📄 program_row.php
│           ├── 📄 delete_modal.php
│           ├── 📄 finalization_tutorial_modal.php
│           ├── 📄 quick_finalize_modal.php
│           ├── 📄 simple_finalize_modal.php
│           ├── 📄 initiatives_section.php
│           └── 📄 programs_section.php
└── 📁 layouts/                        # Shared layouts
    ├── 📄 header.php
    ├── 📄 footer.php
    ├── 📄 navbar.php
    ├── 📄 base.php
    └── 📁 partials/
```

### 📁 `assets/js/` - JavaScript Files (Actually Loaded)

**Bundle Files (Generated by Vite):**
```
assets/js/ (bundled files loaded via $jsBundle system)
├── admin-dashboard.bundle.js          # Admin dashboard functionality
├── admin-users.bundle.js              # User management
├── admin-outcomes.bundle.js           # Outcome management
├── admin-notifications.bundle.js      # Notification management
├── admin-settings.bundle.js           # System settings
├── admin-reports.bundle.js            # Report generation
├── admin-periods.bundle.js            # Period management
├── admin-view-submissions.bundle.js   # Submission viewing
├── admin-edit-program.bundle.js       # Program editing
├── admin-add-submission.bundle.js     # Submission creation
├── admin-edit-submission.bundle.js    # Submission editing
├── admin-program-details.bundle.js    # Program details
├── admin-view-programs.bundle.js      # Program listing
├── admin-common.bundle.js             # Common admin functions
├── agency-dashboard.bundle.js         # Agency dashboard
├── agency-users-profile.bundle.js     # Profile management
├── agency-notifications.bundle.js     # Agency notifications
├── agency-reports.bundle.js           # Agency reports
├── agency-initiatives.bundle.js       # Initiatives listing
├── agency-view-initiative.bundle.js   # Initiative details
├── agency-submit-outcomes.bundle.js   # Outcome submission
├── agency-edit-kpi.bundle.js          # KPI editing
├── agency-edit-outcomes.bundle.js     # Outcome editing
├── agency-view-submissions.bundle.js  # Submission viewing
├── agency-edit-submission.bundle.js   # Submission editing
├── agency-view-programs.bundle.js     # Program listing
├── agency-program-details.bundle.js   # Program details
├── agency-edit-program.bundle.js      # Program editing
├── agency-create-program.bundle.js    # Program creation
├── agency-add-submission.bundle.js    # Submission creation
├── outcomes.bundle.js                 # Shared outcomes functionality
├── main.bundle.js                     # Main application
└── login.bundle.js                    # Login functionality
```

**Individual Files (Loaded Directly):**
```
assets/js/
├── 📁 components/
│   ├── 📄 command-palette.js          # Command palette component
│   └── 📄 status-grid.js              # Status grid component
└── 📄 program_outcome_links.js        # Program-outcome linking
```

### 📁 `assets/css/` - CSS Files (Actually Loaded)

**Bundle Files (Generated by Vite):**
```
assets/css/ (bundled files loaded via $cssBundle system)
├── admin-dashboard.bundle.css         # Admin dashboard styles
├── admin-users.bundle.css             # User management styles
├── admin-outcomes.bundle.css          # Outcome management styles
├── admin-notifications.bundle.css     # Notification styles
├── admin-settings.bundle.css          # Settings styles
├── admin-reports.bundle.css           # Report generation styles
├── admin-periods.bundle.css           # Period management styles
├── admin-view-submissions.bundle.css  # Submission viewing styles
├── admin-edit-program.bundle.css      # Program editing styles
├── admin-add-submission.bundle.css    # Submission creation styles
├── admin-edit-submission.bundle.css   # Submission editing styles
├── admin-program-details.bundle.css   # Program details styles
├── admin-view-programs.bundle.css     # Program listing styles
├── admin-common.bundle.css            # Common admin styles
├── agency-dashboard.bundle.css        # Agency dashboard styles
├── agency-users-profile.bundle.css    # Profile management styles
├── agency-notifications.bundle.css    # Agency notification styles
├── agency-reports.bundle.css          # Agency report styles
├── agency-initiatives.bundle.css      # Initiative styles
├── agency-view-initiative.bundle.css  # Initiative detail styles
├── agency-submit-outcomes.bundle.css  # Outcome submission styles
├── agency-edit-kpi.bundle.css         # KPI editing styles
├── agency-edit-outcomes.bundle.css    # Outcome editing styles
├── agency-view-submissions.bundle.css # Submission viewing styles
├── agency-edit-submission.bundle.css  # Submission editing styles
├── agency-view-programs.bundle.css    # Program listing styles
├── agency-program-details.bundle.css  # Program detail styles
├── agency-edit-program.bundle.css     # Program editing styles
├── agency-create-program.bundle.css   # Program creation styles
├── agency-add-submission.bundle.css   # Submission creation styles
├── outcomes.bundle.css                # Shared outcome styles
└── main.bundle.css                    # Main application styles
```

**Individual Files (Loaded Directly):**
```
assets/css/
├── 📁 custom/
│   └── 📄 audit_log.css               # Audit log specific styles
├── 📁 admin/
│   └── 📄 reports-pagination.css      # Report pagination styles
└── 📄 main.css                        # Legacy main stylesheet
```

### 📁 `assets/images/` - Images (Actually Referenced)

```
assets/images/
├── 📄 favicon.ico                     # Site favicon
└── 📄 apple-touch-icon.png            # iOS home screen icon
```

### External Dependencies (Actually Loaded in Views)

**CDN Resources:**
- Bootstrap 5.2.3 (CSS & JS)
- jQuery 3.6.0
- Chart.js 3.9.1 & 4.4.0
- Font Awesome 6.4.0, 6.5.2, 5.15.4
- Google Fonts (Poppins family)

## Module Organization Patterns

### Business Logic Modules
```php
// File: app/lib/agencies/programs.php
// Pattern: Related functions grouped by feature

function createProgram($agency_id, $data) { }
function updateProgram($program_id, $data) { }
function deleteProgram($program_id) { }
function getProgramsByAgency($agency_id) { }
function calculateProgramProgress($program_id) { }
```

### JavaScript Modules
```javascript
// File: assets/js/admin/dashboard.js
// Pattern: Module pattern with public interface

const AdminDashboard = (function() {
    // Private functions
    function loadChartData() { }
    function updateStatistics() { }
    
    // Public interface
    return {
        init: function() { },
        refresh: function() { }
    };
})();
```

### View Templates
```php
// File: app/views/admin/dashboard/dashboard.php
// Pattern: Layout inclusion with content sections

<?php include '../layouts/header.php'; ?>

<div class="dashboard-content">
    <?php include 'partials/statistics.php'; ?>
    <?php include 'partials/charts.php'; ?>
    <?php include 'partials/recent-activity.php'; ?>
</div>

<?php include '../layouts/footer.php'; ?>
```

### CSS Organization
```css
/* File: assets/css/base.css */
/* Pattern: Import-based organization */

@import 'base/variables.css';
@import 'base/reset.css';
@import 'base/typography.css';

@import 'components/buttons.css';
@import 'components/cards.css';
@import 'components/tables.css';

@import 'admin/dashboard.css';
@import 'agency/programs.css';
```

## Best Practices

### File Organization Principles
1. **Separation of Concerns**: Each file has a single, clear purpose
2. **Role-Based Grouping**: Admin vs Agency separation throughout
3. **Feature Grouping**: Related functionality grouped together
4. **Consistent Naming**: Predictable naming patterns
5. **Modular Structure**: Easy to find and modify specific functionality

### Maintenance Guidelines
1. **New Features**: Follow existing directory patterns
2. **Shared Code**: Place in appropriate shared directories
3. **Role-Specific**: Use admin/ or agency/ subdirectories
4. **Documentation**: Update this file when adding new structure
5. **Testing**: Mirror structure in test directories

---

This file structure documentation serves as a map for navigating and understanding the PCDS2030 Dashboard codebase organization.