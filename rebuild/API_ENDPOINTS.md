# PCDS 2030 Dashboard - API Endpoints Documentation

## Overview

The PCDS 2030 Dashboard uses a hybrid API architecture with both legacy AJAX endpoints and newer RESTful APIs. This documentation provides a comprehensive mapping of all endpoints for use in the React/Vite migration.

## API Architecture

### Two-Tier API Structure
- **`/app/ajax/`** - Legacy AJAX handlers (45+ endpoints)
- **`/app/api/`** - RESTful API endpoints (15+ endpoints)

### Authentication System
- **Method**: PHP session-based authentication
- **Session Variables**: `user_id`, `role`, `agency_id`
- **Authorization Functions**: `is_admin()`, `is_agency()`, `is_focal()`

### Standard Response Patterns

#### Success Response
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed successfully"
}
```

#### Error Response
```json
{
  "success": false,
  "error": "Error message",
  "details": "Additional error information"
}
```

### HTTP Status Codes
- `200` - Success
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (not logged in)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Endpoint Categories

### 1. Authentication

#### `POST /app/api/login.php`
**Purpose**: User authentication and session creation

**Request Body**:
```json
{
  "username": "string",
  "password": "string"
}
```

**Response**:
```json
{
  "success": true,
  "user": {
    "user_id": 1,
    "username": "admin",
    "fullname": "System Administrator",
    "role": "admin",
    "agency_id": 4
  }
}
```

**Authorization**: Public

---

### 2. Program Management

#### `GET /app/api/programs.php`
**Purpose**: List all programs with basic information

**Query Parameters**:
- `agency_id` (optional) - Filter by agency
- `status` (optional) - Filter by program status

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "program_id": 1,
      "program_name": "Forest Conservation Initiative",
      "program_number": "SFC-2025-001",
      "status": "active",
      "rating": "on_track_for_year",
      "agency_name": "SFC"
    }
  ]
}
```

**Authorization**: Admin, Agency

#### `POST /app/ajax/save_submission.php`
**Purpose**: Create or update program submissions

**Request Body** (Form Data):
```
program_id: 1
period_id: 5
description: "Q2 2025 Progress Report"
targets: [
  {
    "target_number": "SFC-2025-001.1",
    "target_description": "Plant 1000 trees",
    "target_value": 1000,
    "achievement_value": 750,
    "unit": "trees",
    "status": "in_progress"
  }
]
is_draft: 1
```

**Response**:
```json
{
  "success": true,
  "submission_id": 15,
  "message": "Submission saved as draft"
}
```

**Authorization**: Agency, Admin

#### `POST /app/ajax/finalize_submission.php`
**Purpose**: Finalize submission for ministry review

**Request Body**:
```json
{
  "submission_id": 15,
  "program_id": 1,
  "period_id": 5
}
```

**Response**:
```json
{
  "success": true,
  "message": "Submission finalized successfully"
}
```

**Authorization**: Focal, Admin

#### `GET /app/ajax/get_program_submissions.php`
**Purpose**: Retrieve submission history for a program

**Query Parameters**:
- `program_id` - Required program ID
- `period_id` (optional) - Filter by period

**Response**:
```json
{
  "success": true,
  "submissions": [
    {
      "submission_id": 15,
      "period_name": "Q2-2025",
      "is_draft": false,
      "is_submitted": true,
      "submitted_at": "2025-07-15 10:30:00",
      "targets_count": 3,
      "completed_targets": 1
    }
  ]
}
```

**Authorization**: Agency, Admin

---

### 3. File Management

#### `POST /app/ajax/upload_program_attachment.php`
**Purpose**: Upload files to programs

**Request Body** (Multipart Form):
```
program_id: 1
submission_id: 15
attachment_file: [FILE]
```

**Response**:
```json
{
  "success": true,
  "attachment_id": 42,
  "filename": "progress_report.pdf",
  "file_size": 1024000
}
```

**Authorization**: Agency, Admin

#### `GET /app/ajax/download_program_attachment.php`
**Purpose**: Download program attachments

**Query Parameters**:
- `attachment_id` - Required attachment ID

**Response**: File download or error JSON

**Authorization**: Agency, Admin

#### `DELETE /app/ajax/delete_program_attachment.php`
**Purpose**: Delete program attachments

**Request Body**:
```json
{
  "attachment_id": 42
}
```

**Response**:
```json
{
  "success": true,
  "message": "Attachment deleted successfully"
}
```

**Authorization**: Agency, Admin

---

### 4. Dashboard & Analytics

#### `GET /app/ajax/dashboard_data.php`
**Purpose**: Agency dashboard statistics and data

**Query Parameters**:
- `period_id` (optional) - Specific reporting period
- `include_assigned` (optional) - Include assigned programs only

**Response**:
```json
{
  "success": true,
  "data": {
    "stats": {
      "total_programs": 5,
      "draft_submissions": 2,
      "finalized_submissions": 3,
      "completion_rate": 85.5
    },
    "recent_activities": [
      {
        "action": "submission_updated",
        "program_name": "Forest Conservation",
        "timestamp": "2025-07-15 14:30:00"
      }
    ],
    "program_ratings": {
      "monthly_target_achieved": 2,
      "on_track_for_year": 2,
      "severe_delay": 1,
      "not_started": 0
    }
  }
}
```

**Authorization**: Agency

#### `GET /app/ajax/admin_dashboard_data.php`
**Purpose**: Admin dashboard with ministry-level statistics

**Query Parameters**:
- `period_id` (optional) - Specific reporting period
- `agency_id` (optional) - Filter by agency

**Response**:
```json
{
  "success": true,
  "data": {
    "agency_stats": [
      {
        "agency_name": "SFC",
        "total_programs": 8,
        "submitted_programs": 6,
        "submission_rate": 75.0
      }
    ],
    "overall_stats": {
      "total_agencies": 3,
      "total_programs": 25,
      "total_submissions": 18,
      "avg_completion_rate": 78.5
    },
    "rating_distribution": {
      "monthly_target_achieved": 5,
      "on_track_for_year": 8,
      "severe_delay": 4,
      "not_started": 1
    }
  }
}
```

**Authorization**: Admin

#### `GET /app/ajax/get_target_progress.php`
**Purpose**: Target progress and achievement data

**Query Parameters**:
- `submission_id` - Required submission ID
- `program_id` (optional) - Program filter

**Response**:
```json
{
  "success": true,
  "targets": [
    {
      "target_id": 25,
      "target_number": "SFC-2025-001.1",
      "target_description": "Plant 1000 trees",
      "target_value": 1000,
      "achievement_value": 750,
      "unit": "trees",
      "status": "in_progress",
      "completion_percentage": 75.0
    }
  ]
}
```

**Authorization**: Agency, Admin

---

### 5. Reporting System

#### `POST /app/api/generate_report.php`
**Purpose**: Generate PowerPoint reports for ministry

**Request Body**:
```json
{
  "period_id": 5,
  "report_type": "ministry_quarterly",
  "selected_programs": [1, 3, 5],
  "selected_targets": [10, 15, 20],
  "include_outcomes": true
}
```

**Response**:
```json
{
  "success": true,
  "report_id": 12,
  "file_path": "/app/reports/pptx/Ministry_Q2-2025_20250715.pptx",
  "download_url": "/download.php?type=report&id=12"
}
```

**Authorization**: Admin

#### `GET /app/api/report_data.php`
**Purpose**: Structured data for report generation

**Query Parameters**:
- `period_id` - Required reporting period
- `format` - Data format (json, pptx_data)

**Response**:
```json
{
  "success": true,
  "report_data": {
    "period_info": {
      "period_name": "Q2-2025",
      "start_date": "2025-04-01",
      "end_date": "2025-06-30"
    },
    "programs": [
      {
        "program_name": "Forest Conservation",
        "agency": "SFC",
        "rating": "on_track_for_year",
        "targets": [...]
      }
    ],
    "outcomes": [
      {
        "title": "Timber Export Value",
        "type": "graph",
        "data": {...}
      }
    ]
  }
}
```

**Authorization**: Admin

#### `GET /app/ajax/get_reports.php`
**Purpose**: List saved reports with metadata

**Query Parameters**:
- `period_id` (optional) - Filter by period
- `limit` (optional) - Pagination limit
- `offset` (optional) - Pagination offset

**Response**:
```json
{
  "success": true,
  "reports": [
    {
      "report_id": 12,
      "report_name": "Q2 2025 Ministry Report",
      "period_name": "Q2-2025",
      "generated_by": "admin",
      "generated_at": "2025-07-15 16:45:00",
      "file_size": "2.5 MB"
    }
  ]
}
```

**Authorization**: Admin

---

### 6. Administrative Functions

#### `POST /app/ajax/add_period.php`
**Purpose**: Create new reporting periods

**Request Body**:
```json
{
  "year": 2025,
  "period_type": "quarter",
  "period_number": 3,
  "start_date": "2025-07-01",
  "end_date": "2025-09-30"
}
```

**Response**:
```json
{
  "success": true,
  "period_id": 18,
  "message": "Reporting period created successfully"
}
```

**Authorization**: Admin

#### `PUT /app/ajax/update_period.php`
**Purpose**: Update existing reporting periods

**Request Body**:
```json
{
  "period_id": 18,
  "start_date": "2025-07-01",
  "end_date": "2025-09-30",
  "status": "open"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Period updated successfully"
}
```

**Authorization**: Admin

#### `GET /app/ajax/get_reporting_periods.php`
**Purpose**: List all reporting periods

**Query Parameters**:
- `status` (optional) - Filter by status (open/closed)
- `year` (optional) - Filter by year

**Response**:
```json
{
  "success": true,
  "periods": [
    {
      "period_id": 18,
      "period_name": "Q3-2025",
      "year": 2025,
      "period_type": "quarter",
      "period_number": 3,
      "start_date": "2025-07-01",
      "end_date": "2025-09-30",
      "status": "open"
    }
  ]
}
```

**Authorization**: All authenticated users

#### `GET /app/ajax/admin_user_tables.php`
**Purpose**: User management data for admin interface

**Query Parameters**:
- `action` - Required action type (list_users, get_user, etc.)
- `user_id` (optional) - Specific user ID

**Response**:
```json
{
  "success": true,
  "users": [
    {
      "user_id": 5,
      "username": "john_doe",
      "fullname": "John Doe",
      "email": "john@sfc.gov.my",
      "role": "agency",
      "agency_name": "SFC",
      "is_active": true,
      "last_login": "2025-07-15 09:30:00"
    }
  ]
}
```

**Authorization**: Admin

---

### 7. Outcomes & KPIs

#### `GET /app/api/get_outcomes.php`
**Purpose**: Retrieve all outcomes for reporting

**Query Parameters**:
- `type` (optional) - Filter by type (graph/kpi)
- `code` (optional) - Specific outcome code

**Response**:
```json
{
  "success": true,
  "outcomes": [
    {
      "id": 1,
      "code": "timber_export",
      "type": "graph",
      "title": "Timber Export Value (RM)",
      "description": "Monthly timber export values",
      "data": {
        "rows": [...],
        "columns": ["2022", "2023", "2024", "2025"]
      }
    }
  ]
}
```

**Authorization**: All authenticated users

#### `GET /app/api/outcomes/get_outcome.php`
**Purpose**: Get single outcome with detailed data

**Query Parameters**:
- `id` - Required outcome ID

**Response**:
```json
{
  "success": true,
  "outcome": {
    "id": 1,
    "code": "timber_export",
    "type": "graph",
    "title": "Timber Export Value (RM)",
    "data": {...},
    "updated_at": "2025-07-13 18:43:29"
  }
}
```

**Authorization**: All authenticated users

---

### 8. Notifications

#### `POST /app/ajax/notifications.php`
**Purpose**: Notification management actions

**Request Body**:
```json
{
  "action": "mark_read",
  "notification_id": 25
}
```

**Available Actions**:
- `get_notifications` - Get user notifications
- `mark_read` - Mark notification as read
- `mark_all_read` - Mark all notifications as read
- `clear_all` - Clear all notifications
- `delete_notification` - Delete specific notification

**Response**:
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

**Authorization**: All authenticated users

#### `GET /app/ajax/get_user_notifications.php`
**Purpose**: Get notifications for current user

**Query Parameters**:
- `limit` (optional) - Number of notifications to return
- `unread_only` (optional) - Only unread notifications

**Response**:
```json
{
  "success": true,
  "notifications": [
    {
      "notification_id": 25,
      "type": "submission_reminder",
      "title": "Submission Due Soon",
      "message": "Q3 submission due in 3 days",
      "is_read": false,
      "created_at": "2025-07-15 10:00:00"
    }
  ],
  "unread_count": 3
}
```

**Authorization**: All authenticated users

---

## Migration Guidelines for React/Vite

### Authentication Migration
```typescript
// Replace PHP sessions with JWT tokens
interface AuthResponse {
  token: string;
  user: {
    id: number;
    username: string;
    role: 'admin' | 'agency' | 'focal';
    agency_id: number;
  };
}

// Authorization hook for React
const useAuth = () => {
  const { user } = useContext(AuthContext);
  return {
    isAdmin: user?.role === 'admin',
    isAgency: ['agency', 'focal'].includes(user?.role),
    isFocal: user?.role === 'focal'
  };
};
```

### API Client Implementation
```typescript
// Unified API client for React
class ApiClient {
  private baseURL = '/api/v1';
  
  async get<T>(endpoint: string, params?: Record<string, any>): Promise<T> {
    const url = new URL(endpoint, this.baseURL);
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        url.searchParams.append(key, String(value));
      });
    }
    
    const response = await fetch(url.toString(), {
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Content-Type': 'application/json'
      }
    });
    
    return this.handleResponse<T>(response);
  }
  
  async post<T>(endpoint: string, data?: any): Promise<T> {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });
    
    return this.handleResponse<T>(response);
  }
}
```

### React Query Integration
```typescript
// Example query hooks for React Query
export const usePrograms = (filters?: ProgramFilters) => {
  return useQuery({
    queryKey: ['programs', filters],
    queryFn: () => apiClient.get<Program[]>('/programs', filters)
  });
};

export const useSaveSubmission = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data: SubmissionData) => 
      apiClient.post<SaveResponse>('/submissions', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['submissions'] });
    }
  });
};
```

This comprehensive API documentation provides the foundation for migrating to a React/Vite frontend while maintaining full functionality and proper error handling patterns.