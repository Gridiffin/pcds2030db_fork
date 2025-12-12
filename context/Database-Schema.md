# Database Schema Documentation

## Overview

The PCDS2030 (Palawan Council for Sustainable Development 2030) database is designed to manage strategic planning, program management, reporting, and auditing for multiple government agencies. The database supports a hierarchical structure of initiatives, programs, submissions, and targets with comprehensive audit trails.

**Database Engine:** MySQL 8.0  
**Character Set:** utf8mb4  
**Collation:** utf8mb4_0900_ai_ci / utf8mb4_general_ci

## Table Structure Overview

The database consists of 17 tables organized into the following functional groups:

1. **Core Entity Tables**: `agency`, `users`, `initiatives`, `programs`
2. **Reporting & Submission Tables**: `reporting_periods`, `program_submissions`, `program_targets`
3. **Management Tables**: `program_attachments`, `program_hold_points`, `program_status_history`, `program_user_assignments`
4. **Audit & Logging Tables**: `audit_logs`, `audit_field_changes`, `audit_logs_with_changes`
5. **System Tables**: `notifications`, `reports`, `outcomes`

## Detailed Table Specifications

### 1. Agency Management

#### `agency`
**Purpose:** Manages government agencies participating in PCDS2030
```sql
CREATE TABLE agency (
  agency_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  agency_name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Key Points:**
- Central agency registry with auto-updating timestamps
- Referenced by users and programs for organization structure
- Contains agencies like STIDC, SFC, FDS

#### `users`
**Purpose:** User authentication and role management
```sql
CREATE TABLE users (
  user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  pw VARCHAR(255) NOT NULL,
  fullname VARCHAR(200) DEFAULT NULL,
  email VARCHAR(255) NOT NULL,
  agency_id INT NOT NULL,
  role ENUM('admin','agency','focal') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_active TINYINT DEFAULT 1,
  
  KEY agency_id (agency_id),
  KEY email (email),
  KEY username (username),
  CONSTRAINT users_ibfk_1 FOREIGN KEY (agency_id) REFERENCES agency (agency_id)
);
```

**Role Hierarchy:**
- `admin`: System administrators with full access
- `agency`: Agency-level users with department access
- `focal`: Focal persons with specific program responsibilities

### 2. Strategic Planning Structure

#### `initiatives`
**Purpose:** High-level strategic initiatives that group related programs
```sql
CREATE TABLE initiatives (
  initiative_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  initiative_name VARCHAR(255) NOT NULL,
  initiative_number VARCHAR(20) DEFAULT NULL,
  initiative_description TEXT,
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  is_active TINYINT NOT NULL DEFAULT 1,
  created_by INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  KEY idx_created_by (created_by),
  KEY idx_initiative_number (initiative_number),
  KEY idx_is_active (is_active)
);
```

**Business Logic:**
- Initiatives provide strategic grouping for programs
- Initiative numbers follow a standardized format (e.g., "31", "PCDS-CI-001")
- Soft deletion via `is_active` flag

#### `programs`
**Purpose:** Specific programs under strategic initiatives
```sql
CREATE TABLE programs (
  program_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  initiative_id INT DEFAULT NULL,
  program_name VARCHAR(255) NOT NULL,
  status ENUM('active','on_hold','completed','delayed','cancelled') DEFAULT 'active',
  program_number VARCHAR(50) DEFAULT NULL,
  rating ENUM('monthly_target_achieved','on_track_for_year','severe_delay','not_started') DEFAULT 'not_started',
  program_description TEXT,
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  agency_id INT NOT NULL,
  is_deleted TINYINT(1) DEFAULT 0,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  restrict_editors TINYINT(1) DEFAULT 0 COMMENT 'Whether to restrict editing to specific users within the agency',
  
  KEY initiative_id (initiative_id),
  KEY agency_id (agency_id),
  KEY created_by (created_by),
  KEY idx_program_number (program_number),
  KEY idx_restrict_editors (restrict_editors),
  
  CONSTRAINT programs_ibfk_1 FOREIGN KEY (initiative_id) REFERENCES initiatives (initiative_id),
  CONSTRAINT programs_ibfk_2 FOREIGN KEY (agency_id) REFERENCES agency (agency_id),
  CONSTRAINT programs_ibfk_3 FOREIGN KEY (created_by) REFERENCES users (user_id)
);
```

**Status Management:**
- `status`: Operational status of the program
- `rating`: Performance assessment against targets
- `restrict_editors`: Controls who can edit program details

### 3. Reporting System

#### `reporting_periods`
**Purpose:** Defines reporting cycles and timeframes
```sql
CREATE TABLE reporting_periods (
  period_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  year INT NOT NULL,
  period_type ENUM('quarter','half','yearly') NOT NULL DEFAULT 'quarter',
  period_number INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('open','closed') DEFAULT 'open',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  KEY period_type_year_idx (period_type, year),
  KEY year_period_unique (year, period_type, period_number),
  
  CONSTRAINT chk_valid_period_numbers CHECK (
    ((period_type = 'quarter' AND period_number BETWEEN 1 AND 4) OR
     (period_type = 'half' AND period_number BETWEEN 1 AND 2) OR
     (period_type = 'yearly' AND period_number = 1))
  )
);
```

**Validation Rules:**
- Quarter periods: 1-4
- Half-year periods: 1-2  
- Yearly periods: 1 only
- Enforced via CHECK constraint

#### `program_submissions`
**Purpose:** Program reporting submissions for specific periods
```sql
CREATE TABLE program_submissions (
  submission_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  program_id INT NOT NULL,
  period_id INT NOT NULL,
  is_draft TINYINT(1) DEFAULT 1,
  is_submitted TINYINT(1) DEFAULT 0,
  description TEXT,
  submitted_by INT DEFAULT NULL,
  submitted_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_deleted TINYINT(1) DEFAULT 0,
  
  KEY program_id (program_id),
  KEY period_id (period_id),
  KEY submitted_by (submitted_by),
  
  CONSTRAINT program_submissions_ibfk_1 FOREIGN KEY (program_id) REFERENCES programs (program_id) ON DELETE CASCADE,
  CONSTRAINT program_submissions_ibfk_2 FOREIGN KEY (period_id) REFERENCES reporting_periods (period_id) ON DELETE CASCADE,
  CONSTRAINT program_submissions_ibfk_3 FOREIGN KEY (submitted_by) REFERENCES users (user_id)
);
```

**Workflow States:**
- `is_draft=1, is_submitted=0`: Draft state (editable)
- `is_draft=0, is_submitted=1`: Finalized submission (read-only)

#### `program_targets`
**Purpose:** Specific measurable targets within program submissions
```sql
CREATE TABLE program_targets (
  target_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  target_number VARCHAR(50) DEFAULT NULL,
  submission_id INT NOT NULL,
  target_description TEXT,
  status_indicator ENUM('not_started','in_progress','completed','delayed') DEFAULT 'not_started',
  status_description TEXT,
  remarks TEXT,
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  is_deleted TINYINT(1) DEFAULT 0,
  
  KEY submission_id (submission_id),
  KEY idx_target_number (target_number),
  
  CONSTRAINT program_targets_ibfk_1 FOREIGN KEY (submission_id) REFERENCES program_submissions (submission_id) ON DELETE CASCADE
);
```

**Target Numbering:**
- Hierarchical format: Initiative.Program.Target (e.g., "31.1.1", "31.A.2")
- Supports both numeric and alphanumeric schemes

### 4. Program Management Features

#### `program_attachments`
**Purpose:** File attachments for program submissions
```sql
CREATE TABLE program_attachments (
  attachment_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  submission_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_size INT DEFAULT NULL,
  file_type VARCHAR(100) DEFAULT NULL,
  uploaded_by INT NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_deleted TINYINT(1) DEFAULT 0,
  
  KEY submission_id (submission_id),
  KEY uploaded_by (uploaded_by),
  
  CONSTRAINT program_attachments_ibfk_1 FOREIGN KEY (submission_id) REFERENCES program_submissions (submission_id) ON DELETE CASCADE,
  CONSTRAINT program_attachments_ibfk_2 FOREIGN KEY (uploaded_by) REFERENCES users (user_id)
);
```

#### `program_hold_points`
**Purpose:** Records when programs are put on hold and reasons
```sql
CREATE TABLE program_hold_points (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  program_id INT NOT NULL,
  reason TEXT NOT NULL,
  remarks TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP DEFAULT NULL,
  created_by INT NOT NULL,
  
  KEY program_id (program_id),
  KEY created_by (created_by),
  
  CONSTRAINT program_hold_points_ibfk_1 FOREIGN KEY (program_id) REFERENCES programs (program_id),
  CONSTRAINT program_hold_points_ibfk_2 FOREIGN KEY (created_by) REFERENCES users (user_id)
);
```

#### `program_status_history`
**Purpose:** Audit trail for program status changes
```sql
CREATE TABLE program_status_history (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  program_id INT NOT NULL,
  status ENUM('active','on_hold','completed','delayed','cancelled') NOT NULL,
  changed_by INT NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  remarks TEXT,
  
  KEY program_id (program_id),
  KEY changed_by (changed_by),
  
  CONSTRAINT program_status_history_ibfk_1 FOREIGN KEY (program_id) REFERENCES programs (program_id),
  CONSTRAINT program_status_history_ibfk_2 FOREIGN KEY (changed_by) REFERENCES users (user_id)
);
```

#### `program_user_assignments`
**Purpose:** Fine-grained user permissions for programs
```sql
CREATE TABLE program_user_assignments (
  assignment_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  program_id INT NOT NULL,
  user_id INT NOT NULL,
  role ENUM('editor','viewer') NOT NULL DEFAULT 'viewer',
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  assigned_by INT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_active TINYINT(1) DEFAULT 1,
  notes TEXT,
  
  KEY program_id (program_id),
  KEY user_id (user_id),
  KEY idx_program_user_role (program_id, user_id, role),
  KEY fk_user_assigned_by (assigned_by),
  
  CONSTRAINT fk_user_assigned_by FOREIGN KEY (assigned_by) REFERENCES users (user_id),
  CONSTRAINT program_user_assignments_ibfk_1 FOREIGN KEY (program_id) REFERENCES programs (program_id) ON DELETE CASCADE,
  CONSTRAINT program_user_assignments_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);
```

### 5. Comprehensive Audit System

#### `audit_logs`
**Purpose:** Primary audit log for all system actions
```sql
CREATE TABLE audit_logs (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  action VARCHAR(128) NOT NULL,
  details TEXT,
  ip_address VARCHAR(45) DEFAULT NULL,
  status VARCHAR(16) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  KEY action (action),
  KEY created_at (created_at),
  KEY user_id (user_id),
  KEY idx_entity_operation (action, created_at)
);
```

#### `audit_field_changes`
**Purpose:** Detailed field-level change tracking
```sql
CREATE TABLE audit_field_changes (
  change_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  audit_log_id INT NOT NULL,
  target_id INT DEFAULT NULL,
  field_name VARCHAR(100) NOT NULL,
  field_type VARCHAR(50) DEFAULT 'text' COMMENT 'text, number, date, boolean, json, etc.',
  old_value TEXT,
  new_value TEXT,
  target_snapshot JSON DEFAULT NULL,
  change_type ENUM('added','modified','removed') NOT NULL DEFAULT 'modified',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  KEY idx_audit_log_id (audit_log_id),
  KEY idx_field_name (field_name),
  KEY idx_created_at (created_at),
  KEY idx_target_id (target_id),
  
  CONSTRAINT fk_audit_field_changes_log FOREIGN KEY (audit_log_id) REFERENCES audit_logs (id) ON DELETE CASCADE
);
```

**Change Tracking Features:**
- Field-level granularity with old/new value comparison
- Support for different data types (text, number, date, boolean, JSON)
- Snapshot capability for complex objects
- Three change types: added, modified, removed

#### `audit_logs_with_changes`
**Purpose:** Materialized view combining audit logs with field changes summary
```sql
CREATE TABLE audit_logs_with_changes (
  id INT NOT NULL,
  user_id INT NOT NULL,
  action VARCHAR(128) NOT NULL,
  details TEXT,
  ip_address VARCHAR(45),
  status VARCHAR(16) NOT NULL,
  created_at DATETIME,
  username VARCHAR(100),
  fullname VARCHAR(200),
  agency_name VARCHAR(255),
  field_changes_count BIGINT NOT NULL,
  field_changes_summary TEXT
) ENGINE=MyISAM;
```

### 6. System Features

#### `notifications`
**Purpose:** User notification system
```sql
CREATE TABLE notifications (
  notification_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(50) NOT NULL DEFAULT 'update',
  read_status TINYINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  action_url VARCHAR(255) DEFAULT NULL,
  
  KEY user_id (user_id),
  
  CONSTRAINT notifications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id)
);
```

#### `reports`
**Purpose:** Generated reports storage and metadata
```sql
CREATE TABLE reports (
  report_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  period_id INT NOT NULL,
  report_name VARCHAR(255) NOT NULL,
  description TEXT,
  pdf_path VARCHAR(255) NOT NULL,
  pptx_path VARCHAR(255) NOT NULL,
  generated_by INT NOT NULL,
  generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_public TINYINT NOT NULL DEFAULT 0,
  
  KEY generated_by (generated_by),
  KEY period_id (period_id),
  
  CONSTRAINT reports_ibfk_1 FOREIGN KEY (period_id) REFERENCES reporting_periods (period_id),
  CONSTRAINT reports_ibfk_2 FOREIGN KEY (generated_by) REFERENCES users (user_id)
);
```

#### `outcomes`
**Purpose:** KPI and outcome data storage with flexible JSON structure
```sql
CREATE TABLE outcomes (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  type ENUM('graph','kpi') NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  data JSON NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Data Structure:**
- **Graph Type**: Time-series data with rows/columns structure
- **KPI Type**: Array of metric objects with values and descriptions
- JSON flexibility allows for complex data relationships

## Entity Relationships

### Primary Relationships

1. **Agency → Users** (1:N)
   - Each agency has multiple users
   - Users belong to exactly one agency

2. **Users → Programs** (1:N via created_by)
   - Users can create multiple programs
   - Programs have a single creator

3. **Initiatives → Programs** (1:N)
   - Initiatives can contain multiple programs
   - Programs optionally belong to one initiative

4. **Programs → Submissions** (1:N)
   - Programs have multiple submissions across reporting periods
   - Submissions belong to exactly one program

5. **Submissions → Targets** (1:N)
   - Submissions contain multiple measurable targets
   - Targets belong to exactly one submission

6. **Reporting Periods → Submissions** (1:N)
   - Each period can have multiple submissions
   - Submissions are for exactly one period

### Audit Relationships

1. **Audit Logs → Field Changes** (1:N)
   - Each audit log can have multiple field changes
   - Field changes reference exactly one audit log

2. **Users → All Audited Actions** (1:N)
   - All system actions are tied to the performing user
   - Comprehensive user activity tracking

### Permission Relationships

1. **Programs → User Assignments** (1:N)
   - Programs can have multiple assigned users
   - Assignments define role-based access (editor/viewer)

2. **Users → Assignments** (1:N)
   - Users can be assigned to multiple programs
   - Different roles per program assignment

## Data Integrity Features

### Referential Integrity
- All foreign keys properly defined with appropriate ON DELETE actions
- CASCADE deletes for dependent data (submissions, targets, attachments)
- RESTRICT deletes for core entities to prevent data loss

### Data Validation
- ENUM constraints for status fields and roles
- CHECK constraints for reporting period validation
- NOT NULL constraints for critical fields
- UNIQUE constraints where appropriate

### Audit Trail Completeness
- Comprehensive field-level change tracking
- IP address and timestamp logging
- User attribution for all modifications
- JSON snapshot capability for complex changes

### Soft Deletion
- `is_deleted` flags for programs, targets, and attachments
- `is_active` flags for users and initiatives
- Preserves data integrity while hiding inactive records

## Performance Considerations

### Index Strategy
- Primary keys on all tables (auto-increment)
- Foreign key indexes for join optimization
- Composite indexes for common query patterns
- Specialized indexes for search fields (program_number, initiative_number)

### Query Optimization
- Materialized view for audit log aggregation
- Efficient period-based reporting queries
- User permission checking optimization
- Status filtering indexes

## Business Logic Reflected in Schema

### Hierarchical Program Structure
```
Agency
├── Users (roles: admin, agency, focal)
├── Initiatives
│   └── Programs
│       └── Submissions (per reporting period)
│           └── Targets (measurable objectives)
```

### Workflow Management
1. **Draft → Submitted**: Programs move through submission states
2. **Status Tracking**: Comprehensive status history for programs
3. **Hold Management**: Temporary program suspension with reasons
4. **Permission Control**: Fine-grained access control per program

### Reporting Cycle Integration
- Flexible period definitions (quarterly, half-yearly, yearly)
- Period-based submission tracking
- Report generation tied to specific periods
- Historical data preservation across reporting cycles

### Audit and Compliance
- Complete change history at field level
- User attribution for all actions
- IP address tracking for security
- JSON data snapshots for complex objects

## Data Types and Storage

### Text Storage
- `TEXT` for long descriptions and remarks
- `VARCHAR` with appropriate lengths for names and codes
- `JSON` for flexible data structures (outcomes)

### Temporal Data
- `TIMESTAMP` with automatic updates for tracking
- `DATE` for business dates (start/end dates)
- `DATETIME` for precise audit timestamps

### Status Management
- `ENUM` types for controlled vocabularies
- `TINYINT(1)` for boolean flags
- Consistent status naming across tables

### Numeric Data
- `INT AUTO_INCREMENT` for primary keys
- `INT` for foreign keys and counts
- Appropriate field sizing for expected data volumes

This database schema represents a comprehensive solution for strategic planning, program management, and performance monitoring with robust audit capabilities and flexible reporting structures.