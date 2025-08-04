# API Documentation

Comprehensive documentation for PCDS2030 Dashboard API endpoints, AJAX handlers, and integration patterns.

## 📋 Table of Contents
1. [API Overview](#api-overview)
2. [Authentication & Authorization](#authentication--authorization)
3. [Core API Endpoints](#core-api-endpoints)
4. [AJAX Handlers](#ajax-handlers)
5. [Data Structures](#data-structures)
6. [Error Handling](#error-handling)
7. [Frontend Integration](#frontend-integration)
8. [Security Features](#security-features)
9. [File Upload System](#file-upload-system)

## API Overview

The PCDS2030 Dashboard uses a hybrid API approach combining:
- **REST-like API endpoints** (`/app/api/`) for structured data operations
- **AJAX handlers** (`/app/ajax/`) for real-time frontend interactions
- **Session-based authentication** for security
- **JSON response format** for all API communications

### Base URLs
```
Production:  https://www.sarawakforestry.com/pcds2030/app/
Development: http://localhost/pcds2030_dashboard_fork/app/
```

### Common Response Format
```json
{
  "success": boolean,
  "message": string,
  "data": object|array,
  "error": string|null,
  "pagination": object|null
}
```

## Authentication & Authorization

### Authentication Endpoint

#### `POST /app/api/login.php`
User authentication and session establishment.

**Request Body:**
```json
{
  "username": "user@example.com",
  "password": "user_password"
}
```

**Response:**
```json
{
  "success": true,
  "role": "admin|agency|focal",
  "message": "Login successful",
  "redirect_url": "/app/views/admin/dashboard/dashboard.php"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Invalid credentials"
}
```

### Authorization Roles

| Role | Permissions | Description |
|------|-------------|-------------|
| `admin` | Full system access | System administrators |
| `agency` | Agency-specific data | Agency users |
| `focal` | Limited agency access | Focal point users |

### Session Management
```php
// Session variables set on login
$_SESSION['user_id']     // User ID
$_SESSION['role']        // User role
$_SESSION['agency_id']   // Agency ID (for agency users)
$_SESSION['username']    // Username
```

## API Endpoints (Actually Called from Views)

*This section documents only the API endpoints that are actually called from the view files to avoid confusion with unused legacy endpoints.*

### API Endpoints (Actually Used)

#### `GET /app/api/simple_gantt_data.php`
Retrieve Gantt chart data for program timelines.

**Authorization:** Logged-in users

**Query Parameters:**
- `period_id` (optional): Filter by reporting period
- `agency_id` (optional): Filter by agency

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "program_id": 1,
      "program_name": "Forest Conservation Initiative",
      "start_date": "2024-01-01",
      "end_date": "2024-12-31",
      "progress": 75.5,
      "status": "on_track"
    }
  ]
}
```

#### `GET /app/api/get_period_programs.php`
Retrieve programs for a specific reporting period.

**Authorization:** Admin or Agency required

**Query Parameters:**
- `period_id` (required): Reporting period identifier

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "program_id": 1,
      "program_name": "Forest Conservation Initiative", 
      "agency_name": "Forestry Department",
      "submission_status": "submitted",
      "last_updated": "2024-07-01T10:30:00Z"
    }
  ]
}
```

### Report Management

#### `POST /app/api/save_report.php`
Save generated report metadata.

**Authorization:** Admin only

**Request Body:**
```json
{
  "period_id": 2,
  "report_name": "Q2 2024 Forestry Report",
  "pptx_path": "/app/reports/pptx/Forestry_Q2-2024.pptx"
}
```

#### `DELETE /app/api/delete_report.php`
Delete a generated report.

**Authorization:** Admin only

**Request Body:**
```json
{
  "report_id": 123
}
```

## AJAX Handlers (Actually Called from Views)

*This section documents only the AJAX endpoints that are actually called from JavaScript in the view files.*

### Real-time Data Operations

#### `GET /app/ajax/get_program_submissions.php`
Retrieve program submissions for a specific program.

**Authorization:** Agency or Admin

**Query Parameters:**
- `program_id` (required): Program identifier
- `limit` (optional): Result limit

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "submission_id": 1,
      "program_id": 1,
      "period_id": 2,
      "is_draft": 0,
      "is_submitted": 1,
      "description": "Q2 2024 Progress Report"
    }
  ]
}
```

#### `GET /app/ajax/download_program_attachment.php`
Download program attachment files.

**Authorization:** Agency or Admin

**Query Parameters:**
- `attachment_id` (required): Attachment identifier

**Response:** File download or error JSON

#### `POST /app/ajax/simple_finalize.php`
Quick finalization of program submissions.

**Authorization:** Focal users only

**Request Body:**
```json
{
  "submission_id": 123
}
```

**Response:**
```json
{
  "success": true,
  "message": "Submission finalized successfully"
}
```

#### `GET /app/ajax/get_user_notifications.php`
Retrieve user notifications with pagination.

**Authorization:** Authenticated users

**Query Parameters:**
- `page` (default: 1): Page number
- `per_page` (default: 10): Items per page
- `filter` (default: 'all'): Filter type (all|unread|read|today)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "notification_id": 1,
      "message": "Your Q2 2024 submission has been approved",
      "type": "success",
      "read_status": 0,
      "created_at": "2024-07-01T10:30:00Z",
      "action_url": "/app/views/agency/programs/view_submission.php?id=123"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 3,
    "per_page": 10,
    "total_count": 25
  }
}
```

#### `POST /app/ajax/notifications.php`
Manage user notifications (mark as read, etc.).

**Authorization:** Authenticated users

**Request Body:**
```json
{
  "action": "mark_read|mark_all_read",
  "notification_id": 123
}
```

### Period Management

#### `POST /app/ajax/toggle_period_status.php`
Toggle reporting period status (open/closed).

**Authorization:** Admin only

**Request Body:**
```json
{
  "period_id": 2,
  "status": "open|closed"
}
```

#### `DELETE /app/ajax/delete_period.php`
Delete a reporting period.

**Authorization:** Admin only

**Request Body:**
```json
{
  "period_id": 2
}
```


## Data Structures (Actually Used in Database)

*These data structures reflect the actual database schema and JSON structures used in the system.*

### Program Structure (Database Table: programs)
```json
{
  "program_id": integer,
  "initiative_id": integer,
  "program_name": string,
  "program_number": string,
  "agency_id": integer,
  "status": "active|on_hold|completed|delayed|cancelled",
  "rating": "monthly_target_achieved|on_track_for_year|severe_delay|not_started",
  "program_description": string,
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD",
  "is_deleted": 0|1,
  "created_by": integer,
  "restrict_editors": 0|1
}
```

### Program Submission Structure (Database Table: program_submissions)
```json
{
  "submission_id": integer,
  "program_id": integer,
  "period_id": integer,
  "is_draft": 0|1,
  "is_submitted": 0|1,
  "description": string,
  "submitted_by": integer,
  "submitted_at": "YYYY-MM-DD HH:MM:SS",
  "is_deleted": 0|1
}
```

### Program Target Structure (Database Table: program_targets)
```json
{
  "target_id": integer,
  "target_number": string,
  "submission_id": integer,
  "target_description": string,
  "status_indicator": "not_started|in_progress|completed|delayed",
  "status_description": string,
  "remarks": string,
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD",
  "is_deleted": 0|1
}
```

### Submission Content JSON Structure (program_submissions.content_json)
```json
{
  "targets": [
    {
      "target_id": 1,
      "target_number": "1.1",
      "target_description": "Plant 1000 trees in protected areas",
      "status_indicator": "on_track",
      "achievement_percentage": 75.5,
      "remarks": "Good progress despite weather challenges"
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
```

### Notification Structure
```json
{
  "notification_id": integer,
  "user_id": integer,
  "title": string,
  "message": string,
  "type": "info|success|warning|error",
  "is_read": 0|1,
  "action_url": string|null,
  "created_at": "YYYY-MM-DD HH:MM:SS"
}
```

## Error Handling

### Standard Error Responses
```json
{
  "success": false,
  "error": "Error message",
  "error_code": "VALIDATION_ERROR|AUTH_ERROR|SERVER_ERROR",
  "details": {
    "field": "Field-specific error message"
  }
}
```

### HTTP Status Codes
- `200 OK`: Successful operation
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `500 Internal Server Error`: Server error

### Common Error Types

#### Authentication Errors
```json
{
  "success": false,
  "error": "Authentication required",
  "error_code": "AUTH_ERROR"
}
```

#### Validation Errors
```json
{
  "success": false,
  "error": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "details": {
    "program_id": "Program ID is required",
    "start_date": "Invalid date format"
  }
}
```

#### Permission Errors
```json
{
  "success": false,
  "error": "Insufficient permissions",
  "error_code": "PERMISSION_ERROR"
}
```

## Frontend Integration

### JavaScript Usage Patterns

#### Basic API Call
```javascript
async function apiCall(endpoint, options = {}) {
    const response = await fetch(`${window.APP_URL}/app/api/${endpoint}`, {
        method: options.method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            ...options.headers
        },
        body: options.body ? JSON.stringify(options.body) : null
    });
    
    return await response.json();
}
```

#### Authentication
```javascript
async function login(username, password) {
    const result = await apiCall('login.php', {
        method: 'POST',
        body: { username, password }
    });
    
    if (result.success) {
        window.location.href = result.redirect_url;
    } else {
        showError(result.error);
    }
}
```

#### File Upload with AJAX
```javascript
async function saveSubmission(formData) {
    const response = await fetch(`${window.APP_URL}/app/ajax/save_submission.php`, {
        method: 'POST',
        body: formData // FormData object with files
    });
    
    return await response.json();
}
```

#### Notification Management
```javascript
class NotificationManager {
    async getNotifications(page = 1, filter = 'all') {
        const params = new URLSearchParams({ page, filter });
        const response = await fetch(
            `${window.APP_URL}/app/ajax/get_user_notifications.php?${params}`
        );
        return await response.json();
    }
    
    async markAsRead(notificationId) {
        return await apiCall('notifications.php', {
            method: 'POST',
            body: { action: 'mark_read', notification_id: notificationId }
        });
    }
}
```

## Security Features

### Authentication Security
- **Session-based authentication**: Secure server-side session management
- **Session timeout**: Automatic logout after inactivity
- **Password security**: Hashed password storage
- **Login attempts**: Rate limiting for failed attempts

### Authorization Security
- **Role-based access control**: Multiple permission levels
- **Resource-level permissions**: User can only access their agency's data
- **Admin verification**: Double-check for administrative functions

### Data Security
- **SQL injection prevention**: Prepared statements throughout
- **XSS protection**: Input sanitization and output encoding
- **CSRF protection**: Session-based token validation
- **Input validation**: Server-side validation for all inputs

### Audit Security
- **Comprehensive logging**: All actions logged with user attribution
- **Change tracking**: Field-level change detection
- **IP tracking**: User IP addresses logged
- **Timestamp recording**: Precise action timing

## File Upload System

### Upload Endpoints
Multiple endpoints support file uploads:
- `/app/ajax/save_submission.php`: Submission attachments
- `/app/ajax/upload_program_attachment.php`: Program documents

### Upload Features
- **File type validation**: Allowed extensions and MIME types
- **Size limits**: Configurable file size restrictions
- **Unique naming**: Prevents filename conflicts
- **Storage organization**: Structured directory layout
- **Database tracking**: File metadata stored in database

### Upload Directory Structure
```
uploads/
├── programs/
│   └── attachments/
│       └── {submission_id}/
│           ├── document1.pdf
│           ├── image1.jpg
│           └── report1.docx
```

### File Upload JavaScript Example
```javascript
async function uploadFile(file, submissionId) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('submission_id', submissionId);
    
    const response = await fetch(`${window.APP_URL}/app/ajax/upload_program_attachment.php`, {
        method: 'POST',
        body: formData
    });
    
    return await response.json();
}
```

---

This API documentation provides comprehensive coverage of all endpoints, security features, and integration patterns for the PCDS2030 Dashboard system.