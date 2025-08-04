# System Architecture & Code Structure

Comprehensive guide to understanding the PCDS2030 Dashboard architecture, design patterns, and codebase organization.

## 📋 Table of Contents
1. [High-Level Architecture](#high-level-architecture)
2. [Directory Structure](#directory-structure)
3. [Core Architectural Patterns](#core-architectural-patterns)
4. [Key Abstractions](#key-abstractions)
5. [Data Flow](#data-flow)
6. [Database Design](#database-design)
7. [Frontend Architecture](#frontend-architecture)
8. [Backend Architecture](#backend-architecture)
9. [Security Architecture](#security-architecture)
10. [Decision Log](#decision-log)

## High-Level Architecture

### System Overview
```
┌─────────────────────────────────────────────────────────────┐
│                    PCDS2030 Dashboard                      │
├─────────────────────────────────────────────────────────────┤
│  Frontend Layer (Browser)                                  │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │ HTML5/CSS3  │ │ JavaScript  │ │ Bootstrap 5 │          │
│  │ Templates   │ │ ES6+ AJAX   │ │ Chart.js    │          │
│  └─────────────┘ └─────────────┘ └─────────────┘          │
├─────────────────────────────────────────────────────────────┤
│  Application Layer (PHP)                                   │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │ Controllers │ │ API Routes  │ │ AJAX Handlers│         │
│  │ (MVC)       │ │ (REST-like) │ │ (Real-time) │          │
│  └─────────────┘ └─────────────┘ └─────────────┘          │
├─────────────────────────────────────────────────────────────┤
│  Business Logic Layer                                      │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │ Admin Lib   │ │ Agency Lib  │ │ Shared Lib  │          │
│  │ Functions   │ │ Functions   │ │ Utilities   │          │
│  └─────────────┘ └─────────────┘ └─────────────┘          │
├─────────────────────────────────────────────────────────────┤
│  Data Layer (MySQL)                                        │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │ Core Tables │ │ JSON Fields │ │ Audit Logs  │          │
│  │ (Relational)│ │ (Flexible)  │ │ (Tracking)  │          │
│  └─────────────┘ └─────────────┘ └─────────────┘          │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack Integration
```
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   Browser    │◄──►│  Web Server  │◄──►│   Database   │
│              │    │              │    │              │
│ • HTML5/CSS3 │    │ • Apache     │    │ • MySQL 8.0+ │
│ • JavaScript │    │ • PHP 8.x    │    │ • InnoDB     │
│ • Bootstrap  │    │ • mod_rewrite│    │ • JSON fields│
│ • Chart.js   │    │ • Sessions   │    │ • Indexing   │
└──────────────┘    └──────────────┘    └──────────────┘
        │                    │                    │
        └────────────────────┼────────────────────┘
                            │
                    ┌──────────────┐
                    │ Build Tools  │
                    │              │
                    │ • Vite       │
                    │ • Node.js    │
                    │ • NPM        │
                    │ • Composer   │
                    └──────────────┘
```

## Directory Structure

### Project Root Structure
```
pcds2030_dashboard_fork/
├── 📁 app/                     # Application core
│   ├── 📁 ajax/               # AJAX request handlers
│   ├── 📁 api/                # API endpoints
│   ├── 📁 config/             # Configuration files
│   ├── 📁 controllers/        # MVC controllers
│   ├── 📁 database/           # Database schemas & migrations
│   ├── 📁 handlers/           # Specialized request handlers
│   ├── 📁 lib/                # Business logic libraries
│   ├── 📁 migrations/         # Database migration scripts
│   ├── 📁 reports/            # Generated reports storage
│   └── 📁 views/              # UI templates
├── 📁 assets/                 # Static assets
│   ├── 📁 css/               # Stylesheets
│   ├── 📁 js/                # JavaScript files
│   ├── 📁 images/            # Images and icons
│   └── 📁 fonts/             # Web fonts
├── 📁 devs-docs/             # Developer documentation
├── 📁 docs/                  # Project documentation
├── 📁 tests/                 # Test suites
├── 📁 scripts/               # Maintenance scripts
├── 📁 vendor/                # Composer dependencies
├── 📁 node_modules/          # Node.js dependencies
├── 📄 index.php              # Application entry point
├── 📄 package.json           # Node.js configuration
├── 📄 composer.json          # PHP dependencies
├── 📄 vite.config.js         # Build configuration
└── 📄 phpunit.xml            # Testing configuration
```

### Application Layer Structure
```
app/
├── ajax/                      # Real-time request handlers
│   ├── admin_*.php           # Admin-specific AJAX
│   ├── agency_*.php          # Agency-specific AJAX
│   ├── get_*.php             # Data retrieval endpoints
│   ├── save_*.php            # Data persistence endpoints
│   └── notifications.php     # Notification system
├── api/                       # RESTful API endpoints
│   ├── programs.php          # Program CRUD operations
│   ├── outcomes/             # Outcome management
│   ├── report_data.php       # Report generation data
│   └── login.php             # Authentication
├── lib/                       # Business logic layer
│   ├── admins/               # Admin-specific functions
│   │   ├── core.php          # Core admin functionality
│   │   ├── users.php         # User management
│   │   ├── programs.php      # Program administration
│   │   └── outcomes.php      # Outcome management
│   ├── agencies/             # Agency-specific functions
│   │   ├── core.php          # Core agency functionality
│   │   ├── programs.php      # Program operations
│   │   ├── outcomes.php      # Outcome operations
│   │   └── reports.php       # Report generation
│   ├── functions.php         # Shared utility functions
│   ├── db_connect.php        # Database connection
│   └── session.php           # Session management
└── views/                     # User interface templates
    ├── admin/                # Admin dashboard views
    │   ├── dashboard/        # Dashboard components
    │   ├── programs/         # Program management
    │   ├── users/            # User management
    │   └── outcomes/         # Outcome administration
    ├── agency/               # Agency user views
    │   ├── dashboard/        # Agency dashboard
    │   ├── programs/         # Program operations
    │   └── users/            # Profile management
    └── layouts/              # Shared layout templates
        ├── header.php        # Common header
        ├── footer.php        # Common footer
        └── navbar.php        # Navigation components
```

## Core Architectural Patterns

### 1. Modified MVC Pattern
```php
// Controller Layer (Simplified MVC)
class AdminProgramsController {
    public function index() {
        // Business logic delegation
        $programs = AdminPrograms::getAllPrograms();
        
        // View rendering
        include 'views/admin/programs/programs.php';
    }
}

// Model Layer (Functional approach)
// Located in: app/lib/admins/programs.php
function getAllPrograms($filters = []) {
    // Database interaction
    // Business logic
    return $programs;
}

// View Layer (PHP Templates)
// Located in: app/views/admin/programs/programs.php
include 'layouts/header.php';
// HTML template with embedded PHP
include 'layouts/footer.php';
```

### 2. AJAX-Driven Architecture
```javascript
// Frontend JavaScript Pattern
class ProgramManager {
    async loadPrograms(filters) {
        const response = await fetch('/app/ajax/get_programs.php', {
            method: 'POST',
            body: JSON.stringify(filters)
        });
        return await response.json();
    }
    
    updateUI(data) {
        // DOM manipulation
        // Chart updates
    }
}

// Backend AJAX Handler Pattern
// File: app/ajax/get_programs.php
<?php
require_once '../lib/admin/programs.php';

header('Content-Type: application/json');

$filters = json_decode(file_get_contents('php://input'), true);
$programs = getFilteredPrograms($filters);

echo json_encode([
    'success' => true,
    'data' => $programs
]);
```

### 3. Component-Based CSS Architecture
```css
/* Base styles */
@import 'base/variables.css';
@import 'base/reset.css';
@import 'base/typography.css';

/* Component styles */
@import 'components/buttons.css';
@import 'components/cards.css';
@import 'components/tables.css';

/* Page-specific styles */
@import 'admin/dashboard.css';
@import 'agency/programs.css';
```

### 4. Modular JavaScript Architecture
```javascript
// Module Pattern
const DashboardModule = (function() {
    let chartInstances = {};
    
    function initializeCharts() {
        // Chart initialization logic
    }
    
    function updateData() {
        // Data update logic
    }
    
    return {
        init: initializeCharts,
        update: updateData
    };
})();

// Usage
document.addEventListener('DOMContentLoaded', function() {
    DashboardModule.init();
});
```

## Key Abstractions

### 1. User Management System
```php
// Core User Abstraction
// File: app/lib/user_functions.php

class UserManager {
    // User roles: 'admin', 'agency'
    public static function authenticate($email, $password) {
        // Authentication logic
        // Session establishment
        // Role-based permissions
    }
    
    public static function hasPermission($user_id, $permission) {
        // Permission checking
        // Role-based access control
    }
    
    public static function getAgencyUsers($agency_id) {
        // Agency-specific user retrieval
    }
}
```

### 2. Program Management System
```php
// Core Program Abstraction
// File: app/lib/agencies/programs.php

class ProgramManager {
    // Program lifecycle: draft -> submitted -> finalized
    public static function createProgram($data) {
        // Program creation
        // Data validation
        // JSON content storage
    }
    
    public static function submitProgram($program_id, $submission_data) {
        // Submission workflow
        // Status updates
        // Audit logging
    }
    
    public static function getProgramsByPeriod($period_id, $agency_id = null) {
        // Period-based filtering
        // Agency-specific data
    }
}
```

### 3. Reporting System
```php
// Report Generation Abstraction
// File: app/lib/admin_reports.php

class ReportGenerator {
    public static function generatePowerPoint($params) {
        // Template-based report generation
        // Data aggregation
        // PPTX creation
    }
    
    public static function getReportData($period_id, $filters) {
        // Data compilation
        // Statistical calculations
        // Chart data preparation
    }
}
```

### 4. Notification System
```php
// Notification Abstraction
// File: app/lib/notifications_core.php

class NotificationManager {
    public static function createNotification($user_id, $type, $data) {
        // Notification creation
        // Type-based formatting
        // Delivery scheduling
    }
    
    public static function getUserNotifications($user_id, $unread_only = false) {
        // User-specific notifications
        // Read status filtering
    }
}
```

### 5. Audit System
```php
// Audit Trail Abstraction
// File: app/lib/audit_log.php

class AuditLogger {
    public static function log($action, $table, $record_id, $changes = []) {
        // Activity logging
        // Change tracking
        // User attribution
        // Timestamp recording
    }
    
    public static function getAuditTrail($table, $record_id) {
        // Historical changes
        // Timeline reconstruction
    }
}
```

## Data Flow

### 1. User Request Flow
```
User Action (Browser)
        ↓
Frontend JavaScript (AJAX)
        ↓
AJAX Handler (app/ajax/*.php)
        ↓
Business Logic (app/lib/*.php)
        ↓
Database Query (MySQL)
        ↓
JSON Response
        ↓
Frontend Update (DOM/Charts)
```

### 2. Page Load Flow
```
index.php (Router)
        ↓
Controller (app/controllers/*.php)
        ↓
Business Logic (app/lib/*.php)
        ↓
View Template (app/views/*.php)
        ↓
Layout Assembly (header/footer)
        ↓
Asset Loading (CSS/JS)
        ↓
Client Rendering
```

### 3. Data Submission Flow
```
Form Submit (Frontend)
        ↓
Validation (JavaScript)
        ↓
AJAX Post (JSON)
        ↓
Server Validation (PHP)
        ↓
Business Logic Processing
        ↓
Database Transaction
        ↓
Audit Log Entry
        ↓
Response Generation
        ↓
UI Feedback
```

## Database Design

### Complete Database Schema
```sql
-- Core Business Entities
agency (agency_id, agency_name, created_at, updated_at)
  ↓
users (user_id, username, pw, fullname, email, agency_id, role, created_at, updated_at, is_active)
  ↓
initiatives (initiative_id, initiative_name, initiative_number, initiative_description, start_date, end_date, is_active, created_by, created_at, updated_at)
  ↓
programs (program_id, initiative_id, program_name, status, program_number, rating, program_description, start_date, end_date, agency_id, is_deleted, created_by, created_at, updated_at, restrict_editors)
  ↓
program_submissions (submission_id, program_id, period_id, is_draft, is_submitted, description, submitted_by, submitted_at, updated_at, is_deleted)
  ↓
program_targets (target_id, target_number, submission_id, target_description, status_indicator, status_description, remarks, start_date, end_date, is_deleted)

-- Reporting Framework
reporting_periods (period_id, year, period_type, period_number, start_date, end_date, status, updated_at, created_at)
reports (report_id, period_id, report_name, description, pdf_path, pptx_path, generated_by, generated_at, is_public)

-- File Management
program_attachments (attachment_id, submission_id, file_name, file_path, file_size, file_type, uploaded_by, uploaded_at, is_deleted)

-- Access Control & User Management
program_user_assignments (assignment_id, program_id, user_id, role, assigned_at, assigned_by, updated_at, is_active, notes)

-- Status & History Tracking
program_status_history (id, program_id, status, changed_by, changed_at, remarks)
program_hold_points (id, program_id, reason, remarks, created_at, ended_at, created_by)

-- Outcomes & KPI Management
outcomes (id, code, type, title, description, data, updated_at)

-- Communication System
notifications (notification_id, user_id, message, type, read_status, created_at, action_url)

-- Comprehensive Audit System
audit_logs (id, user_id, action, details, ip_address, status, created_at)
audit_field_changes (change_id, audit_log_id, target_id, field_name, field_type, old_value, new_value, target_snapshot, change_type, created_at)
```

### JSON Content Storage Strategy (Used in Views)
```json
// program_submissions.content_json structure (Primary JSON field used)
{
  "targets": [
    {
      "target_id": 1,
      "target_number": "1.1",
      "target_description": "Plant 1000 trees in protected areas",
      "status_indicator": "on_track",
      "achievement_percentage": 75.5,
      "remarks": "Good progress despite weather challenges"
    },
    {
      "target_id": 2,
      "target_number": "1.2", 
      "target_description": "Establish 5 new nurseries",
      "status_indicator": "completed",
      "achievement_percentage": 100,
      "remarks": "All nurseries operational"
    }
  ],
  "rating": "on_track_for_year",
  "overall_progress": 82.3,
  "challenges": "Weather conditions affecting planting schedule",
  "next_steps": "Continue planting activities in Q3",
  "submission_metadata": {
    "version": "2.1",
    "last_updated": "2024-07-01T10:30:00Z"
  }
}

// Outcomes data structure (various metric fields)
{
  "metric_value": 1250,
  "metric_unit": "hectares",
  "metric_description": "Forest area restored",
  "target_value": 1500,
  "achievement_rate": 83.3
}
```

## Frontend Architecture

### JavaScript Module Organization
```
assets/js/
├── admin/                     # Admin-specific modules
│   ├── dashboard.js          # Dashboard functionality
│   ├── programs.js           # Program management
│   ├── users.js              # User administration
│   └── reports.js            # Report generation
├── agency/                   # Agency-specific modules
│   ├── dashboard.js          # Agency dashboard
│   ├── programs.js           # Program operations
│   └── submissions.js        # Data submissions
├── components/               # Reusable components
│   ├── charts.js             # Chart.js utilities
│   ├── modals.js             # Modal dialogs
│   └── forms.js              # Form utilities
├── shared/                   # Common utilities
│   ├── utils.js              # General utilities
│   ├── ajax.js               # AJAX helpers
│   └── validation.js         # Form validation
└── main.js                   # Application initialization
```

### CSS Architecture (BEM Methodology)
```css
/* Block */
.program-card { }

/* Element */
.program-card__title { }
.program-card__content { }
.program-card__actions { }

/* Modifier */
.program-card--draft { }
.program-card--submitted { }
.program-card--finalized { }

/* Responsive Design */
@media (max-width: 768px) {
    .program-card {
        /* Mobile styles */
    }
}
```

## Files Actually Used by Views

### PHP Library Files (Actually Included/Required)

#### Core Library Files
```php
// Universal includes in most view files
app/config/config.php                    // Main configuration
app/lib/db_connect.php                   // Database connection
app/lib/session.php                      // Session management
app/lib/functions.php                    // Shared utility functions
app/lib/user_functions.php               // User-related operations
app/lib/asset_helpers.php                // Asset URL generation
```

#### Specialized Function Libraries
```php
// Feature-specific libraries used in views
app/lib/rating_helpers.php               // Program rating calculations
app/lib/program_status_helpers.php       // Status display helpers
app/lib/initiative_functions.php         // Initiative operations
app/lib/admin_functions.php              // Admin-specific functions
app/lib/notifications_core.php           // Notification system
app/lib/audit_log.php                    // Activity logging
```

#### Admin-Specific Libraries
```php
// Admin view data processing
app/lib/admins/admin_edit_program_data.php
app/lib/admins/admin_edit_submission_data.php
app/lib/admins/admin_program_details_data.php
app/lib/admins/admin_submission_data.php
```

### AJAX Endpoints (Actually Called from Views)
```php
// Real-time data operations
app/ajax/get_program_submissions.php     // Fetch submission data
app/ajax/download_program_attachment.php  // File downloads
app/ajax/get_user_notifications.php      // Notification loading
app/ajax/notifications.php               // Notification management  
app/ajax/simple_finalize.php             // Quick finalization
app/ajax/toggle_period_status.php        // Period management
app/ajax/delete_period.php               // Period deletion
```

### API Endpoints (Actually Called from Views)
```php
// Structured data operations
app/api/simple_gantt_data.php            // Gantt chart data
app/api/get_period_programs.php          // Period-based program data
app/api/save_report.php                  // Report saving
app/api/delete_report.php                // Report deletion
```

### JavaScript Bundles (Actually Loaded)
```javascript
// Admin interface bundles
admin-dashboard.bundle.js
admin-users.bundle.js
admin-outcomes.bundle.js
admin-notifications.bundle.js
admin-settings.bundle.js
admin-reports.bundle.js
admin-periods.bundle.js
admin-view-submissions.bundle.js
admin-edit-program.bundle.js
admin-add-submission.bundle.js
admin-edit-submission.bundle.js
admin-program-details.bundle.js
admin-view-programs.bundle.js
admin-common.bundle.js

// Agency interface bundles
agency-dashboard.bundle.js
agency-users-profile.bundle.js
agency-notifications.bundle.js
agency-reports.bundle.js
agency-initiatives.bundle.js
agency-view-initiative.bundle.js
agency-submit-outcomes.bundle.js
agency-edit-kpi.bundle.js
agency-edit-outcomes.bundle.js
agency-view-submissions.bundle.js
agency-edit-submission.bundle.js
agency-view-programs.bundle.js
agency-program-details.bundle.js
agency-edit-program.bundle.js
agency-create-program.bundle.js
agency-add-submission.bundle.js

// Shared bundles
outcomes.bundle.js
main.bundle.js
login.bundle.js
```

### CSS Bundles (Actually Loaded)
```css
// Corresponding CSS bundles for each JS bundle
admin-dashboard.bundle.css
admin-users.bundle.css
admin-outcomes.bundle.css
// ... (mirrors JS bundle structure)
agency-dashboard.bundle.css
agency-users-profile.bundle.css
// ... (mirrors JS bundle structure)
outcomes.bundle.css
main.bundle.css
```

### Individual Asset Files (Actually Referenced)
```php
// Non-bundled assets loaded directly
assets/js/components/command-palette.js
assets/js/components/status-grid.js
assets/js/program_outcome_links.js
assets/css/custom/audit_log.css
assets/css/admin/reports-pagination.css
assets/images/favicon.ico
```

### External Dependencies (Actually Loaded)
```html
<!-- CDN resources used in views -->
Bootstrap 5.2.3 (CSS & JS)
jQuery 3.6.0
Chart.js 3.9.1 & 4.4.0
Font Awesome 6.4.0, 6.5.2, 5.15.4
Google Fonts (Poppins family)
```

### Function Calls (Actually Used in Views)
```php
// Library functions called directly in view files
get_outcome_by_id()
get_admin_submission_view_data()
get_all_outcomes()
get_user_by_id()
get_agency_name_by_id()
get_admin_program_details()
get_program_attachments()
get_related_programs_by_initiative()
get_period_display_name()
get_file_icon()
get_program_submission()
get_agency_initiatives()
get_agency_initiative_details()
get_initiative_programs_for_agency()
format_time_ago()
get_notification_icon()
get_priority_class()
get_priority_badge_color()
get_type_icon()
get_type_badge_color()
get_current_reporting_period()
update_user()
delete_user()
get_all_users()
```

## Backend Architecture

### PHP File Organization Strategy
```php
// Function-based approach (not OOP classes)
// File: app/lib/agencies/programs.php

// Core CRUD operations
function createProgram($agency_id, $program_data) {
    // Input validation
    // Data sanitization
    // Database insertion
    // Audit logging
}

function updateProgram($program_id, $program_data) {
    // Permission checking
    // Data validation
    // Update operation
    // Change tracking
}

// Business logic functions
function calculateProgramProgress($program_id, $period_id) {
    // Progress calculation
    // Status determination
    // Percentage calculations
}

// Utility functions
function formatProgramData($raw_data) {
    // Data formatting
    // JSON structure
    // Display preparation
}
```

### Database Interaction Pattern
```php
// Prepared statement pattern
function getProgramsByAgency($agency_id, $period_id = null) {
    global $pdo;
    
    $sql = "SELECT p.*, ps.status as submission_status 
            FROM programs p 
            LEFT JOIN program_submissions ps ON p.id = ps.program_id 
            WHERE p.agency_id = :agency_id";
    
    $params = ['agency_id' => $agency_id];
    
    if ($period_id) {
        $sql .= " AND ps.period_id = :period_id";
        $params['period_id'] = $period_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

## Security Architecture

### Authentication & Authorization
```php
// Session-based authentication
// File: app/lib/session.php

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdminRole() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied');
    }
}

// Agency-specific data access
function requireAgencyAccess($agency_id) {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin' && 
        $_SESSION['agency_id'] !== $agency_id) {
        http_response_code(403);
        exit('Access denied');
    }
}
```

### Data Protection
```php
// Input sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// SQL injection prevention
function safeQuery($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// CSRF protection
function generateCSRFToken() {
    return bin2hex(random_bytes(32));
}

function validateCSRFToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}
```

## Decision Log

### Technology Choices

#### Why PHP (Not Node.js/Python)?
- **Government requirement**: Existing infrastructure supports PHP
- **Hosting compatibility**: cPanel hosting supports PHP out-of-the-box
- **Team expertise**: Development team familiar with PHP
- **Rapid development**: Quick prototyping and deployment

#### Why MySQL (Not PostgreSQL/MongoDB)?
- **Hosting support**: Standard on cPanel hosting
- **JSON support**: MySQL 8.0+ provides JSON field types
- **Performance**: Adequate for current data volume
- **Familiarity**: Team experience with MySQL administration

#### Why Bootstrap 5 (Not Custom CSS Framework)?
- **Rapid development**: Pre-built responsive components
- **Government accessibility**: Built-in accessibility features
- **Browser compatibility**: Consistent cross-browser rendering
- **Documentation**: Extensive documentation and community

#### Why Chart.js (Not D3.js/Google Charts)?
- **Simplicity**: Easy to implement and maintain
- **Responsive**: Built-in responsive design
- **Licensing**: Open source, no licensing issues
- **Performance**: Adequate for dashboard requirements

### Architectural Decisions

#### Why JSON Content Storage?
- **Flexibility**: Program structures vary across agencies
- **Evolution**: Requirements change frequently
- **Migration**: Easier than schema changes
- **Performance**: MySQL JSON functions provide adequate querying

#### Why Function-based (Not OOP)?
- **Simplicity**: Easier for junior developers to understand
- **Maintenance**: Less complexity in debugging
- **Performance**: Lower memory footprint
- **Legacy**: Consistent with existing government systems

#### Why AJAX-heavy Frontend?
- **User experience**: Real-time updates without page reloads
- **Performance**: Reduced server load
- **Interactivity**: Dashboard-style interface requirements
- **Mobile**: Better mobile experience

#### Why Session-based Authentication?
- **Security**: Server-side session control
- **Simplicity**: No JWT complexity
- **Government standard**: Aligns with security requirements
- **Timeout**: Easy session timeout implementation

### Performance Optimizations

#### Database Indexing Strategy
```sql
-- Program queries optimization
CREATE INDEX idx_programs_agency_period ON programs(agency_id, created_at);
CREATE INDEX idx_submissions_period_status ON program_submissions(period_id, status);

-- User queries optimization  
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_agency ON users(agency_id, status);

-- Audit log performance
CREATE INDEX idx_audit_table_record ON audit_logs(table_name, record_id);
CREATE INDEX idx_audit_user_date ON audit_logs(user_id, created_at);
```

#### Asset Optimization
- **CSS bundling**: All CSS files combined into single bundle
- **JavaScript bundling**: Module bundling with Vite
- **Image optimization**: Compressed images and WebP format
- **Caching**: Browser caching headers for static assets

#### Query Optimization
- **Prepared statements**: All database queries use prepared statements
- **Limited result sets**: Pagination for large data sets
- **Selective loading**: Only load required fields
- **Connection pooling**: Database connection reuse

---

This architecture documentation provides the foundation for understanding and extending the PCDS2030 Dashboard system. For specific implementation details, refer to the respective source files and additional documentation.