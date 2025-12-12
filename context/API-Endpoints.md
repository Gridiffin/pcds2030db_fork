# PCDS 2030 Dashboard API Endpoints Documentation

## Overview

This document provides comprehensive documentation of all API endpoints and integrations in the PCDS 2030 Dashboard system, including AJAX endpoints, REST API endpoints, and integration patterns.

## Table of Contents

1. [AJAX Endpoints Catalog](#ajax-endpoints-catalog)
2. [REST API Endpoints](#rest-api-endpoints)
3. [Integration Patterns](#integration-patterns)
4. [Security Implementation](#security-implementation)
5. [Data Formats](#data-formats)
6. [Feature-Specific APIs](#feature-specific-apis)

---

## AJAX Endpoints Catalog

### Authentication & Session Management

#### `/app/ajax/clear_session_message.php`
- **Method**: POST
- **Purpose**: Clears session messages after display
- **Authentication**: Session-based
- **Parameters**: None
- **Response**: JSON success/error status

### Admin Dashboard Endpoints

#### `/app/ajax/admin_dashboard_data.php`
- **Method**: GET
- **Purpose**: Provides admin dashboard statistics and data
- **Authentication**: Admin only (`is_admin()`)
- **Parameters**:
  - `period_id` (optional): Filter data by reporting period
- **Response**: JSON with HTML sections
  ```json
  {
    "stats_section": "HTML content",
    "period_info": "HTML content", 
    "sectors_section": "HTML content",
    "submissions_section": "HTML content"
  }
  ```
- **Security**: AJAX request validation, audit logging

#### `/app/ajax/admin_user_tables.php`
- **Method**: GET/POST
- **Purpose**: Manages admin user table operations
- **Authentication**: Admin only
- **Features**: User listing, filtering, pagination

#### `/app/ajax/admin_manage_initiatives.php`
- **Method**: GET/POST
- **Purpose**: Initiative management for admins
- **Authentication**: Admin only
- **Operations**: CRUD operations on initiatives

#### `/app/ajax/admin_outcomes.php`
- **Method**: GET/POST  
- **Purpose**: Outcome management for admins
- **Authentication**: Admin only
- **Operations**: Outcome data manipulation

### Program Management Endpoints

#### `/app/ajax/save_submission.php`
- **Method**: POST
- **Purpose**: Creates new submissions and updates existing ones
- **Authentication**: Agency or Admin (`is_agency() || is_admin()`)
- **Parameters**:
  - `program_id` (required): Program identifier
  - `period_id` (required): Reporting period identifier  
  - `submission_id` (optional): For updates
  - `description`: Submission description
  - `targets_json`: JSON-encoded targets array
  - `finalize_submission`: Boolean flag for draft/final status
  - `deleted_attachments`: JSON array of attachment IDs to remove
  - `attachments[]`: File uploads
- **Features**:
  - Transaction-based operations
  - Comprehensive audit logging
  - File upload handling
  - Target management (add/update/remove)
  - Notification system integration
- **Response**:
  ```json
  {
    "success": true,
    "message": "Submission saved as draft/finalized",
    "submission_id": 123,
    "is_draft": boolean,
    "is_submitted": boolean
  }
  ```

#### `/app/ajax/finalize_submission.php`
- **Method**: POST
- **Purpose**: Changes draft submissions to finalized status
- **Authentication**: Focal users only (`is_focal_user()`)
- **Parameters**:
  - `submission_id`: Submission to finalize
  - `program_id`: Program identifier
  - `period_id`: Period identifier
- **Features**:
  - Transaction-based updates
  - Audit logging
  - Notification system
- **Response**: JSON success/error status

#### `/app/ajax/get_program_submissions.php`
- **Method**: GET
- **Purpose**: Retrieves all submissions for a program
- **Authentication**: Agency users
- **Parameters**:
  - `program_id`: Program identifier
- **Authorization**: User must have view permissions for program
- **Response**:
  ```json
  {
    "success": true,
    "submissions": [...]
  }
  ```

#### `/app/ajax/get_program_submission.php`
- **Method**: GET
- **Purpose**: Retrieves specific submission data
- **Authentication**: Agency or Admin
- **Parameters**:
  - `submission_id`: Submission identifier
  - `program_id`: Program identifier
- **Response**: Detailed submission data including targets

#### `/app/ajax/get_program_stats.php`
- **Method**: GET
- **Purpose**: Program statistics for dashboards
- **Authentication**: Agency or Admin
- **Parameters**:
  - `program_id`: Program identifier
  - `period_id` (optional): Filter by period
- **Response**: Statistical data about program performance

### File Management Endpoints

#### `/app/ajax/upload_program_attachment.php`
- **Method**: POST
- **Purpose**: Handles file uploads for program submissions
- **Authentication**: Agency users
- **Parameters**:
  - `submission_id`: Target submission
  - File data via `$_FILES`
- **Features**:
  - File type validation
  - Size limits
  - Unique filename generation
  - Database record creation
- **Security**: File type restrictions, path sanitization

#### `/app/ajax/download_program_attachment.php`
- **Method**: GET
- **Purpose**: Secure file download
- **Authentication**: Agency or Admin
- **Parameters**:
  - `attachment_id`: File identifier
- **Security**: Permission verification, path validation

#### `/app/ajax/delete_program_attachment.php`
- **Method**: POST
- **Purpose**: Removes attachment files and records
- **Authentication**: Agency users
- **Parameters**:
  - `attachment_id`: File to delete
- **Features**: Physical file removal, database cleanup

### Reporting Period Management

#### `/app/ajax/get_reporting_periods.php`
- **Method**: GET
- **Purpose**: Retrieves available reporting periods
- **Authentication**: Any logged-in user
- **Parameters**:
  - `active_only` (optional): Filter for active periods
- **Response**: Array of reporting periods with metadata

#### `/app/ajax/periods_data.php`
- **Method**: GET
- **Purpose**: Comprehensive period data for admin interface
- **Authentication**: Admin only
- **Response**: Period data with statistics

#### `/app/ajax/add_period.php`
- **Method**: POST
- **Purpose**: Creates new reporting periods
- **Authentication**: Admin only
- **Parameters**: Period configuration data
- **Features**: Validation, conflict checking

#### `/app/ajax/update_period.php`
- **Method**: POST
- **Purpose**: Updates existing reporting periods
- **Authentication**: Admin only
- **Parameters**: Period data to update

#### `/app/ajax/delete_period.php`
- **Method**: POST
- **Purpose**: Removes reporting periods
- **Authentication**: Admin only
- **Security**: Cascade checks, dependency validation

#### `/app/ajax/toggle_period_status.php`
- **Method**: POST
- **Purpose**: Activates/deactivates reporting periods
- **Authentication**: Admin only
- **Parameters**:
  - `period_id`: Period to toggle
  - `status`: New status

### Data Retrieval Endpoints

#### `/app/ajax/dashboard_data.php`
- **Method**: GET
- **Purpose**: General dashboard data for agencies
- **Authentication**: Agency users
- **Parameters**:
  - `period_id` (optional): Filter by period
- **Response**: Dashboard statistics and visualizations

#### `/app/ajax/agency_dashboard_data.php`
- **Method**: GET
- **Purpose**: Agency-specific dashboard data
- **Authentication**: Agency users
- **Response**: Agency-scoped statistics

#### `/app/ajax/get_target_progress.php`
- **Method**: GET
- **Purpose**: Target progress tracking data
- **Authentication**: Agency or Admin
- **Parameters**:
  - `program_id`: Program identifier
  - `period_id`: Period identifier
- **Response**: Progress metrics for program targets

### Audit and History Endpoints

#### `/app/ajax/load_audit_logs.php`
- **Method**: GET
- **Purpose**: Retrieves audit log entries
- **Authentication**: Admin only
- **Parameters**: Pagination, filtering options
- **Response**: Paginated audit log data

#### `/app/ajax/get_audit_field_changes.php`
- **Method**: GET
- **Purpose**: Detailed field-level change history
- **Authentication**: Admin only
- **Parameters**:
  - `audit_log_id`: Audit entry identifier
- **Response**: Field-by-field change details

#### `/app/ajax/export_audit_logs.php`
- **Method**: GET
- **Purpose**: Exports audit logs to file formats
- **Authentication**: Admin only
- **Parameters**: Export format and filters
- **Response**: File download or export data

#### `/app/ajax/get_submission_audit_history.php`
- **Method**: GET
- **Purpose**: Submission change history
- **Authentication**: Agency or Admin
- **Parameters**:
  - `submission_id`: Submission identifier
- **Response**: Chronological change history

### Validation Endpoints

#### `/app/ajax/check_program_number_duplicate.php`
- **Method**: GET
- **Purpose**: Validates program number uniqueness
- **Authentication**: Agency or Admin
- **Parameters**:
  - `program_number`: Number to validate
  - `program_id` (optional): Exclude current program
- **Response**: Boolean duplicate status

#### `/app/ajax/check_period_exists.php`
- **Method**: GET
- **Purpose**: Validates period existence
- **Authentication**: Admin only
- **Parameters**: Period configuration
- **Response**: Existence status

#### `/app/ajax/check_period_overlap.php`
- **Method**: GET
- **Purpose**: Checks for period date overlaps
- **Authentication**: Admin only
- **Parameters**: Period date ranges
- **Response**: Overlap detection results

### Specialized Agency Endpoints

#### `/app/ajax/agency/check_program_number.php`
- **Method**: GET
- **Purpose**: Agency-specific program number validation
- **Authentication**: Agency users
- **Parameters**:
  - `program_number`: Number to validate
- **Response**: Availability status

#### `/app/ajax/simple_finalize.php`
- **Method**: POST
- **Purpose**: Simplified submission finalization
- **Authentication**: Focal users
- **Parameters**: Basic finalization data
- **Response**: Success/error status

#### `/app/ajax/simple_get_periods.php`
- **Method**: GET
- **Purpose**: Basic period listing
- **Authentication**: Any logged-in user
- **Response**: Simplified period array

#### `/app/ajax/simple_get_submission.php`
- **Method**: GET
- **Purpose**: Basic submission data
- **Authentication**: Agency users
- **Parameters**:
  - `submission_id`: Submission identifier
- **Response**: Basic submission information

---

## REST API Endpoints

### Authentication API

#### `/app/api/login.php`
- **Method**: POST
- **Purpose**: User authentication
- **Parameters**:
  ```json
  {
    "username": "string",
    "password": "string"
  }
  ```
- **Response**:
  ```json
  {
    "success": boolean,
    "role": "admin|agency",
    "error": "string (if failed)"
  }
  ```
- **Session Management**: Sets session variables on success

### Program APIs

#### `/app/api/programs.php`
- **Method**: GET
- **Purpose**: Retrieves program listings
- **Authentication**: Admin or Agency
- **Response**:
  ```json
  [
    {
      "program_id": number,
      "program_name": "string",
      "program_number": "string", 
      "sector_id": number,
      "agency_name": "string"
    }
  ]
  ```

#### `/app/api/get_program_targets.php`
- **Method**: GET
- **Purpose**: Retrieves targets for specific program
- **Authentication**: Any logged-in user
- **Parameters**:
  - `program_id`: Program identifier
  - `period_id` (optional): Filter by period
- **Response**: Array of program targets

#### `/app/api/program_submissions.php`
- **Method**: GET
- **Purpose**: Program submission data
- **Authentication**: Admin or Agency
- **Parameters**: Program filters
- **Response**: Submission listings

#### `/app/api/program_status.php`
- **Method**: GET/POST
- **Purpose**: Program status management
- **Authentication**: Admin or Agency
- **Operations**: Status queries and updates

#### `/app/api/program_user_assignments.php`
- **Method**: GET/POST
- **Purpose**: User-program assignment management
- **Authentication**: Admin only
- **Operations**: Assignment CRUD operations

#### `/app/api/program_outcome_links.php`
- **Method**: GET/POST
- **Purpose**: Links programs to outcomes
- **Authentication**: Admin or Agency
- **Operations**: Link management

### Outcome APIs

#### `/app/api/get_outcomes.php`
- **Method**: GET
- **Purpose**: Retrieves all available outcomes
- **Authentication**: Any logged-in user
- **Response**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": number,
        "code": "string",
        "type": "string",
        "title": "string",
        "description": "string",
        "data": "string",
        "updated_at": "datetime"
      }
    ]
  }
  ```

#### `/app/api/outcomes/get_outcome.php`
- **Method**: GET
- **Purpose**: Retrieves specific outcome data
- **Authentication**: Any logged-in user
- **Parameters**:
  - `id`: Outcome identifier
- **Response**: Detailed outcome information

#### `/app/api/outcomes/get_outcome_history_data.php`
- **Method**: GET
- **Purpose**: Outcome change history
- **Authentication**: Admin or Agency
- **Parameters**:
  - `outcome_id`: Outcome identifier
- **Response**: Historical change data

#### `/app/api/save_outcome_json.php`
- **Method**: POST
- **Purpose**: Saves outcome data in JSON format
- **Authentication**: Admin or Agency
- **Parameters**: Outcome data object
- **Response**: Save confirmation

### Report APIs

#### `/app/api/report_data.php`
- **Method**: GET
- **Purpose**: Report generation data
- **Authentication**: Any logged-in user
- **Parameters**:
  - `period_id`: Reporting period
  - `sector_id`: Sector filter
  - `selected_program_ids` (optional): Program filter
  - `program_orders` (optional): Display ordering
- **Response**: Comprehensive report data

#### `/app/api/generate_report.php`
- **Method**: POST
- **Purpose**: Generates reports in various formats
- **Authentication**: Admin or Agency
- **Parameters**: Report configuration
- **Response**: Generated report data/file

#### `/app/api/save_report.php`
- **Method**: POST
- **Purpose**: Saves generated reports
- **Authentication**: Admin or Agency
- **Parameters**: Report content and metadata
- **Response**: Save confirmation

#### `/app/api/delete_report.php`
- **Method**: DELETE
- **Purpose**: Removes saved reports
- **Authentication**: Admin or Agency
- **Parameters**:
  - `report_id`: Report identifier
- **Response**: Deletion confirmation

### Data Utility APIs

#### `/app/api/command_palette_data.php`
- **Method**: GET
- **Purpose**: Command palette search data
- **Authentication**: Any logged-in user
- **Parameters**:
  - `type`: Data type (`programs|submissions`)
- **Response**: Formatted search results
- **Features**:
  - Role-based data filtering
  - Optimized for quick search
  - Status indicators

#### `/app/api/get_period_programs.php`
- **Method**: GET
- **Purpose**: Programs available for specific period
- **Authentication**: Any logged-in user
- **Parameters**:
  - `period_id`: Reporting period
- **Response**: Period-specific program data

#### `/app/api/simple_gantt_data.php`
- **Method**: GET
- **Purpose**: Gantt chart visualization data
- **Authentication**: Any logged-in user
- **Parameters**: Chart configuration
- **Response**: Timeline data for visualization

#### `/app/api/numbering.php`
- **Method**: GET
- **Purpose**: Auto-numbering system for programs/initiatives
- **Authentication**: Admin or Agency
- **Parameters**: Numbering context
- **Response**: Next available numbers

#### `/app/api/initiatives.php`
- **Method**: GET/POST
- **Purpose**: Initiative management API
- **Authentication**: Admin or Agency
- **Operations**: Initiative CRUD operations

### Agency-Specific APIs

#### `/app/api/agency/submit_submission.php`
- **Method**: POST
- **Purpose**: Agency submission workflow
- **Authentication**: Agency users
- **Parameters**: Submission data
- **Response**: Submission confirmation
- **Features**: Agency-specific validation rules

---

## Integration Patterns

### JavaScript to PHP Communication Patterns

#### 1. AJAX Request Pattern
```javascript
// Standard AJAX request pattern used across the system
fetch(`${window.APP_URL}/app/ajax/endpoint.php`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-Requested-With': 'XMLHttpRequest'
  },
  body: new URLSearchParams(data)
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    // Handle success
  } else {
    // Handle error
  }
});
```

#### 2. FormData Pattern for File Uploads
```javascript
// File upload pattern
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('submission_id', submissionId);

fetch(`${window.APP_URL}/app/ajax/upload_program_attachment.php`, {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => {
  // Handle response
});
```

#### 3. API Helper Function Pattern
```javascript
// Centralized API URL generation
function apiUrl(endpoint, params = {}) {
  const baseUrl = `${window.APP_URL}/app/api/${endpoint}`;
  const urlParams = new URLSearchParams(params);
  return urlParams.toString() ? `${baseUrl}?${urlParams}` : baseUrl;
}
```

### File Upload Handling

#### Upload Process Flow
1. **Client-side validation**: File type, size checks
2. **FormData preparation**: Multi-file support
3. **Server-side validation**: Security checks
4. **File processing**: Unique naming, directory creation
5. **Database recording**: Metadata storage
6. **Response handling**: Success/error feedback

#### Security Measures
- File type whitelist validation
- File size limitations
- Unique filename generation to prevent conflicts
- Directory traversal protection
- Permission-based access control

### Real-time Data Updates

#### Dashboard Update Pattern
```javascript
// Periodic dashboard updates
function updateDashboardData(periodId) {
  return fetch(`${window.APP_URL}/app/ajax/admin_dashboard_data.php?period_id=${periodId}`)
    .then(response => response.json())
    .then(data => {
      // Update dashboard sections
      if (data.stats_section) {
        document.getElementById('stats-section').innerHTML = data.stats_section;
      }
      // Update other sections...
    });
}
```

#### Auto-save Pattern
```javascript
// Auto-save functionality for forms
let saveTimeout;
function autoSave() {
  clearTimeout(saveTimeout);
  saveTimeout = setTimeout(() => {
    // Collect form data
    const formData = new FormData(document.getElementById('submission-form'));
    
    // Save as draft
    fetch(`${window.APP_URL}/app/ajax/save_submission.php`, {
      method: 'POST',
      body: formData
    });
  }, 2000); // 2-second delay
}
```

### Cross-feature Data Dependencies

#### Program-Submission Relationship
- Programs serve as templates for submissions
- Submissions inherit program structure and targets
- Changes to programs affect draft submissions
- Finalized submissions maintain snapshot integrity

#### Period-based Data Filtering
- All data operations filtered by reporting periods
- Period status affects data availability
- Historical data preservation across period changes

#### User Role-based Access
- Data filtering based on user roles (admin/agency)
- Agency users see only their organization's data
- Admin users have system-wide visibility
- Permission checks at both API and UI levels

---

## Security Implementation

### Authentication Mechanisms

#### Session-based Authentication
- PHP session management with secure cookies
- Session validation on every request
- Automatic session timeout handling
- Session hijacking protection

```php
// Standard authentication check pattern
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}
```

#### Role-based Authorization
- Three primary roles: Admin, Agency, Focal User
- Function-based role checking: `is_admin()`, `is_agency()`, `is_focal_user()`
- Granular permission system for specific operations

### Authorization Checks

#### Endpoint-level Authorization
```php
// Admin-only endpoint protection
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}
```

#### Resource-level Authorization
```php
// Program access validation
if (!can_view_program($program_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied']);
    exit;
}
```

#### Data Scope Authorization
- Agency users limited to their organization's data
- Program ownership validation
- Submission access controls
- File download permission checks

### Input Sanitization

#### Parameter Validation
```php
// Standard input validation pattern
$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (!$program_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Program ID required']);
    exit;
}
```

#### SQL Injection Prevention
- Prepared statements used universally
- Parameter binding for all user inputs
- No direct query string interpolation

```php
// Safe database query pattern
$stmt = $conn->prepare("SELECT * FROM programs WHERE program_id = ? AND agency_id = ?");
$stmt->bind_param("ii", $program_id, $agency_id);
$stmt->execute();
$result = $stmt->get_result();
```

#### File Upload Security
- File type validation using whitelist
- File size restrictions
- Malicious file content scanning
- Secure file storage outside web root
- Unique filename generation

### CSRF Protection

#### AJAX Request Validation
```php
// AJAX request verification
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}
```

#### HTTP Method Validation
```php
// Method-specific endpoint protection
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
```

---

## Data Formats

### JSON Response Structures

#### Standard Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response-specific data
  }
}
```

#### Standard Error Response
```json
{
  "success": false,
  "error": "Error message",
  "code": "ERROR_CODE" // Optional error code
}
```

#### Paginated Response
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_records": 47,
    "per_page": 10
  }
}
```

### Error Response Patterns

#### HTTP Status Codes
- `200` - Success
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found (resource doesn't exist)
- `405` - Method Not Allowed
- `500` - Internal Server Error

#### Error Categories
1. **Validation Errors**: Missing or invalid parameters
2. **Authentication Errors**: Session expired or invalid credentials
3. **Authorization Errors**: Insufficient permissions
4. **Resource Errors**: Requested resource not found
5. **System Errors**: Database or server errors

### Success Response Formats

#### Data Retrieval Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Item Name",
      "status": "active",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

#### Operation Response
```json
{
  "success": true,
  "message": "Record updated successfully",
  "affected_id": 123,
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Pagination Patterns

#### Request Parameters
- `page`: Current page number (default: 1)
- `per_page`: Records per page (default: 10, max: 100)
- `sort`: Sort field
- `order`: Sort direction (`asc`|`desc`)

#### Response Metadata
```json
{
  "pagination": {
    "current_page": 2,
    "total_pages": 8,
    "total_records": 78,
    "per_page": 10,
    "has_next": true,
    "has_prev": true
  }
}
```

---

## Feature-Specific APIs

### Dashboard Data APIs

#### Admin Dashboard
- **Endpoint**: `/app/ajax/admin_dashboard_data.php`
- **Features**:
  - System-wide statistics
  - Agency reporting status
  - Program performance metrics
  - Period-based filtering
  - Real-time data updates

#### Agency Dashboard
- **Endpoint**: `/app/ajax/agency_dashboard_data.php`
- **Features**:
  - Organization-specific metrics
  - Program portfolio overview
  - Submission status tracking
  - Target progress visualization

### Program Management APIs

#### Program CRUD Operations
- **Creation**: Validated program creation with auto-numbering
- **Retrieval**: Role-based program listing and details
- **Updates**: Change tracking with audit logging
- **Deletion**: Soft deletion with dependency checks

#### Program Validation
- **Number Uniqueness**: Real-time duplicate checking
- **Permission Validation**: Access control verification
- **Data Integrity**: Referential integrity maintenance

### Submission Workflow APIs

#### Draft Management
- **Auto-save**: Periodic draft saving during editing
- **Version Control**: Change tracking and history
- **Conflict Resolution**: Concurrent editing handling

#### Finalization Process
- **Validation**: Completeness and accuracy checks
- **State Transition**: Draft to final status change
- **Notification**: Stakeholder alert system
- **Audit Trail**: Complete action logging

### Reporting APIs

#### Report Generation
- **Data Aggregation**: Cross-program and cross-period analysis
- **Format Options**: Multiple output formats (JSON, PDF, Excel)
- **Custom Filtering**: Flexible data selection criteria
- **Template System**: Standardized report layouts

#### Report Management
- **Storage**: Persistent report saving
- **Access Control**: Permission-based report access
- **Sharing**: Controlled report distribution
- **Archival**: Long-term report retention

### User Management APIs

#### User Operations
- **Authentication**: Secure login and session management
- **Profile Management**: User information updates
- **Role Management**: Permission assignment and modification
- **Activity Tracking**: User action logging

#### Notification System
- **Event-driven**: Automatic notifications on key actions
- **Multi-channel**: Email and in-app notifications
- **Personalization**: Role-based notification filtering
- **Delivery Tracking**: Notification status monitoring

---

## Implementation Guidelines

### Best Practices

1. **Consistent Error Handling**: Standardized error response formats
2. **Input Validation**: Comprehensive server-side validation
3. **Security First**: Authentication and authorization on every endpoint
4. **Audit Logging**: Complete action tracking for compliance
5. **Transaction Safety**: Database transactions for complex operations
6. **Performance Optimization**: Efficient queries and caching strategies
7. **Documentation**: Inline code documentation and API specifications

### Development Standards

1. **Code Structure**: Modular, reusable components
2. **Naming Conventions**: Descriptive, consistent naming
3. **Error Handling**: Graceful failure with user-friendly messages  
4. **Testing**: Comprehensive endpoint testing
5. **Monitoring**: Application performance and error monitoring
6. **Version Control**: API versioning for breaking changes

This documentation provides a comprehensive overview of the PCDS 2030 Dashboard API architecture. It should be updated as new endpoints are added or existing ones are modified.