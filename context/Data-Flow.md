# PCDS 2030 Dashboard - Data Flow Analysis

## Executive Summary

This document provides a comprehensive analysis of the data flow and business logic patterns in the PCDS 2030 Dashboard system. The analysis is based on examination of the application's core business logic libraries, controllers, AJAX endpoints, and workflow implementations.

## Table of Contents

1. [Core Business Processes](#core-business-processes)
2. [Data Flow Patterns](#data-flow-patterns)
3. [Business Rules and Validation](#business-rules-and-validation)
4. [Integration Workflows](#integration-workflows)
5. [State Management](#state-management)
6. [Security and Access Control](#security-and-access-control)
7. [Audit and Compliance](#audit-and-compliance)

## Core Business Processes

### 1. Program Lifecycle Management

The system manages a complete program lifecycle with the following key stages:

#### Program Creation
- **Entry Point**: `app/lib/agencies/programs.php::create_agency_program()` or `create_simple_program()`
- **Flow**:
  1. User input validation (required fields, format validation)
  2. Business rule validation (duplicate program numbers, initiative validation)
  3. Database transaction begins
  4. Program record creation in `programs` table
  5. User assignment creation in `program_user_assignments` table
  6. Optional initial submission creation (depending on creation type)
  7. Notification dispatch to relevant stakeholders
  8. Audit logging
  9. Transaction commit

#### Program Updates
- **Entry Point**: `app/ajax/save_submission.php` for submissions, various program edit functions
- **Flow**:
  1. Access control validation
  2. Data preparation and validation
  3. State comparison (before/after) for audit tracking
  4. Database updates with field-level change tracking
  5. Notification dispatch based on scope of changes
  6. Comprehensive audit logging

#### Program Status Transitions
Key status states: `not_started`, `in_progress`, `completed`, `delayed`, `on_hold`
- Managed through rating system in `app/lib/rating_helpers.php`
- Status changes trigger notifications and audit events

### 2. Reporting Period Management

The system implements a sophisticated reporting period system:

#### Automatic Period Management
- **Function**: `app/lib/functions.php::auto_manage_reporting_periods()`
- **Features**:
  - Automatic creation of quarterly and half-yearly periods
  - Smart period status management based on current date
  - Respect for admin manual overrides
  - Forward-looking period creation (Q1 of next year in Q4)

#### Period Types
- **Quarter**: Q1-Q4 (3-month periods)
- **Half-yearly**: H1-H2 (6-month periods)  
- **Yearly**: Annual reporting periods
- **Display Format**: Standardized as "Q1-2025", "H1-2025", etc.

### 3. Submission Workflow

#### Draft → Final Submission Process
- **Creation**: Always starts as draft (`is_draft = 1`)
- **Auto-save**: Frequent background saves during editing
- **Validation**: Business rule validation before finalization
- **Finalization**: Status change to `is_draft = 0, is_submitted = 1`
- **Audit Trail**: Complete field-level change tracking

#### Target Management
- **Structure**: Hierarchical numbering (e.g., "1.2.3.1")
- **Fields**: Description, status, dates, remarks
- **Validation**: Format validation, duplicate prevention
- **Change Tracking**: Individual target field changes audited

### 4. Initiative Management

#### Initiative-Program Relationships
- **Hierarchical Structure**: Initiatives contain multiple programs
- **Numbering System**: Programs inherit numbering from initiatives
- **Cascade Updates**: Initiative number changes propagate to programs
- **Access Control**: Initiative-based permission inheritance

## Data Flow Patterns

### 1. Request/Response Cycles

#### AJAX Data Processing
The system heavily uses AJAX for dynamic interactions:

**Standard AJAX Pattern**:
```
Frontend (JS) → AJAX Request → PHP Endpoint → Business Logic → Database → Response → Frontend Update
```

**Key AJAX Endpoints**:
- `app/ajax/save_submission.php` - Complex submission handling
- `app/ajax/get_reporting_periods.php` - Period data retrieval
- `app/ajax/admin_user_tables.php` - User management operations

#### Error Handling Pattern
- Consistent JSON response format: `{success: boolean, message: string, error?: string}`
- Database transaction rollback on errors
- Comprehensive error logging
- User-friendly error messages

### 2. Database Transaction Patterns

#### Complex Operations Pattern
```php
$conn->begin_transaction();
try {
    // Multiple related database operations
    // Business logic validation
    // Audit logging preparation
    $conn->commit();
    // Post-transaction notifications
} catch (Exception $e) {
    $conn->rollback();
    // Error handling
}
```

#### Audit Integration Pattern
All significant data changes follow:
1. Capture current state
2. Perform changes within transaction
3. Log audit entries with field-level changes
4. Send notifications after successful commit

### 3. File Upload and Attachment Handling

#### Secure Upload Process
- **Location**: `uploads/programs/attachments/{submission_id}/`
- **Security**: Filename sanitization, type validation, size limits
- **Organization**: Per-submission folder structure
- **Cleanup**: Automatic file deletion on attachment removal

## Business Rules and Validation

### 1. Program Validation Rules

#### Program Number Validation
- **Format Requirements**: Hierarchical format when linked to initiatives
- **Uniqueness**: Per-initiative uniqueness checking
- **Auto-generation**: Automatic numbering when initiative is selected

#### Date Validation
- **Format**: Strict YYYY-MM-DD validation
- **Business Rules**: Start date must precede end date
- **Null Handling**: Explicit null value support for optional dates

### 2. User Access Validation

#### Role-Based Permissions
- **Admin**: Full system access across all agencies
- **Agency**: Limited to own agency's data
- **Focal**: Enhanced permissions within agency
- **Editor**: Assigned program editing rights

#### Program-Specific Permissions
- **Assignment-Based**: User-program assignments in `program_user_assignments`
- **Role Inheritance**: Agency-level permissions cascade to programs
- **Restriction Support**: Programs can be restricted to assigned editors only

### 3. Status Transition Rules

#### Valid Status Transitions
Defined in `app/lib/rating_helpers.php`:
- Initial: `not_started`
- Progressive: `not_started` → `in_progress` → `completed`
- Alternative paths: Any status → `delayed` or `on_hold`
- Recovery: `delayed`/`on_hold` → `in_progress`

## Integration Workflows

### 1. Cross-Module Data Dependencies

#### Program-Initiative Integration
- **Dependency**: Programs reference initiatives via `initiative_id`
- **Cascade Effects**: Initiative changes affect related programs
- **Numbering Inheritance**: Programs inherit initiative numbering schemes

#### User-Agency Relationships
- **Core Relationship**: Users belong to agencies via `agency_id`
- **Permission Inheritance**: Agency membership determines program access
- **Cross-Agency Access**: Admin users have cross-agency permissions

### 2. Notification System Workflows

#### Event-Driven Notifications
Located in `app/lib/notifications_core.php`:
- **Program Events**: Creation, editing, deletion
- **Submission Events**: Creation, editing, finalization, deletion  
- **Assignment Events**: User-program assignments
- **System Events**: System-wide announcements

#### Notification Routing Logic
```php
// Determine recipients based on program restrictions and user roles
if ($program['restrict_editors']) {
    // Notify only assigned users
    $recipients = get_assigned_users($program_id);
} else {
    // Notify all agency users
    $recipients = get_agency_users($agency_id);
}
// Always notify admins and focal users
$recipients = array_merge($recipients, get_admin_users(), get_focal_users($agency_id));
```

### 3. Report Generation Processes

#### Data Aggregation Flow
- **Source**: Multiple tables (programs, submissions, targets, outcomes)
- **Aggregation**: Statistical calculations at various levels
- **Export Formats**: PDF, Excel, CSV support
- **Real-time Generation**: On-demand report generation

## State Management

### 1. Session Handling

#### Session Security
- **Validation**: Active session verification on each request
- **Role Enforcement**: Role-based access control throughout
- **Timeout Handling**: Automatic session expiration
- **Data Consistency**: Agency ID explicitly set in session

#### Session Data Structure
```php
$_SESSION = [
    'user_id' => int,
    'username' => string,
    'role' => string,  // 'admin', 'agency', 'focal'
    'agency_id' => int,
    'is_active' => boolean,
    // Other user fields...
];
```

### 2. Data Persistence Patterns

#### Entity Lifecycle Management
- **Create**: Transaction-protected creation with validation
- **Read**: Optimized queries with appropriate JOINs
- **Update**: Field-level change tracking with audit
- **Delete**: Soft deletes where appropriate, hard deletes for sensitive data

#### Caching Strategies
- **Reporting Periods**: Cached period data for performance
- **User Permissions**: Session-based permission caching
- **Initiative Data**: Cached for program number generation

### 3. Transaction Management

#### Critical Operations
All business-critical operations use database transactions:
- Program creation/editing
- Submission finalization
- User assignments
- Bulk operations

#### Rollback Scenarios
- Validation failures
- Database constraint violations
- File operation failures
- External API failures

## Security and Access Control

### 1. Authentication Flow

#### Login Process
Located in `app/lib/functions.php::validate_login()`:
1. Username existence verification
2. Account status validation (is_active check)
3. Password verification using PHP password_verify()
4. Session establishment with complete user data
5. Audit log entry creation
6. Redirect to appropriate dashboard

#### Security Features
- Password hashing using PHP's password_hash()
- Account deactivation support
- Failed login attempt logging
- IP address tracking for audit

### 2. Authorization Patterns

#### Role-Based Access Control (RBAC)
```php
// Common authorization pattern
if (!is_admin() && !is_agency()) {
    http_response_code(403);
    exit('Access denied');
}

// Program-specific authorization
if (!can_edit_program($program_id, $user_id)) {
    return ['error' => 'Access denied'];
}
```

#### Data Isolation
- **Agency Separation**: Strict agency-based data isolation
- **Cross-Agency Admin Access**: Admins can access all agency data
- **Assignment-Based Access**: Program-specific user assignments

## Audit and Compliance

### 1. Comprehensive Audit System

#### Audit Log Structure
Located in `app/lib/audit_log.php`:
- **Basic Logging**: User, action, timestamp, IP address
- **Field-Level Changes**: Detailed before/after change tracking
- **Contextual Information**: Related entity IDs and metadata

#### Audit Events
- **Authentication**: Login/logout attempts
- **Data Operations**: Create, update, delete operations
- **Administrative Actions**: User management, system configuration
- **Export Operations**: Data export and report generation

### 2. Field-Level Change Tracking

#### Change Detection
```php
// Example from save_submission.php
foreach ($fields_to_track as $field) {
    $old_value = $existing_data[$field] ?? null;
    $new_value = $new_data[$field] ?? null;
    if ($old_value !== $new_value) {
        log_field_change($audit_log_id, $field, $old_value, $new_value, 'modified');
    }
}
```

#### Audit Data Retention
- **Retention Policy**: Configurable retention periods
- **Cleanup Procedures**: Automated old audit log cleanup
- **Export Capability**: Audit log export for compliance

### 3. Compliance Features

#### Data Integrity
- **Referential Integrity**: Foreign key constraints
- **Business Rule Validation**: Multi-layer validation
- **Transaction Atomicity**: All-or-nothing operation guarantee

#### Reporting Requirements
- **Audit Reports**: Comprehensive audit trail reporting
- **Data Export**: Multiple format support for compliance
- **User Activity Tracking**: Complete user action history

## Technical Implementation Notes

### Database Architecture
- **Primary Keys**: Consistent integer auto-increment IDs
- **Foreign Keys**: Properly defined relationships with cascade rules
- **Indexes**: Performance-optimized query indexes
- **Constraints**: Business rule enforcement at database level

### Error Handling Strategy
- **Graceful Degradation**: System continues operating despite non-critical errors
- **User Feedback**: Clear, actionable error messages
- **Developer Debugging**: Comprehensive error logging
- **Recovery Procedures**: Transaction rollback and cleanup

### Performance Considerations
- **Query Optimization**: Efficient JOINs and indexing
- **Pagination**: Large result set handling
- **Caching**: Strategic data caching for frequently accessed information
- **Background Processing**: Long-running operations handled asynchronously

## Conclusion

The PCDS 2030 Dashboard implements a robust, well-structured data flow architecture that effectively manages complex business processes while maintaining security, audit compliance, and performance. The system's modular design and consistent patterns facilitate maintenance and future enhancements while preserving the integrity of critical business data.

Key strengths include:
- Comprehensive audit trail system
- Robust transaction management
- Flexible notification system
- Strong security implementation
- Clear separation of concerns

Areas for potential improvement during restructuring:
- Further modularization of business logic
- Enhanced caching mechanisms
- API standardization
- Performance optimization for large datasets
- Expanded integration capabilities