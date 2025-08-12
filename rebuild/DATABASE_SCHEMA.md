# PCDS 2030 Dashboard - Database Schema Documentation

## Overview

The PCDS 2030 Dashboard uses a MySQL database with a normalized schema designed for forestry sector reporting and program management. The database supports multi-agency operations, quarterly reporting periods, and comprehensive audit logging.

## Database Information
- **Engine**: MySQL 8.0.30 (InnoDB)
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_0900_ai_ci / utf8mb4_general_ci
- **Foreign Key Constraints**: Enabled with CASCADE options

## Core Entity Relationship Diagram

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Agency    │    │ Initiatives │    │   Users     │
│             │    │             │    │             │
│ agency_id   │◄──┐│ initiative_ │◄──┐│ user_id     │
│ agency_name │   ││ id          │   ││ username    │
│             │   ││ initiative_ │   ││ role        │
└─────────────┘   ││ name        │   ││ agency_id   │
       │          ││             │   │└─────────────┘
       │          │└─────────────┘   │       │
       │          │                  │       │
       ▼          ▼                  ▼       │
┌─────────────┐    ┌─────────────┐    ┌─────▼───────┐
│  Programs   │    │   Reports   │    │Notification │
│             │    │             │    │             │
│ program_id  │    │ report_id   │    │notification │
│ initiative_ │    │ period_id   │    │_id          │
│ id          │    │ data        │    │ user_id     │
│ agency_id   │    │             │    │             │
│ created_by  │    └─────────────┘    └─────────────┘
│ program_    │           │
│ number      │           │
└─────┬───────┘           │
      │                   │
      ▼                   ▼
┌─────────────┐    ┌─────────────┐
│ Program     │    │ Reporting   │
│ Submissions │    │ Periods     │
│             │    │             │
│submission_id│    │ period_id   │
│ program_id  │    │ year        │
│ period_id   │◄───┤ period_type │
│ is_draft    │    │ period_     │
│ is_submitted│    │ number      │
└─────┬───────┘    │ status      │
      │            └─────────────┘
      ▼
┌─────────────┐
│ Program     │
│ Targets     │
│             │
│ target_id   │
│submission_id│
│target_number│
│ description │
│ achievement │
│ status      │
└─────────────┘
```

## Table Definitions

### 1. Core Entity Tables

#### `agency`
**Purpose**: Represents the three main agencies under the Ministry of Natural Resources and Urban Development.

```sql
CREATE TABLE `agency` (
  `agency_id` int NOT NULL AUTO_INCREMENT,
  `agency_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`agency_id`)
);
```

**Sample Data**:
- STIDC (Sarawak Timber Industry Development Corporation)
- SFC (Sarawak Forestry Corporation)
- FDS (Forest Department Sarawak)

**Business Rules**:
- Each agency manages its own programs and users
- Agency-level permissions are enforced throughout the system

#### `users`
**Purpose**: User accounts with role-based access control for the system.

```sql
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `pw` varchar(255) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `agency_id` int NOT NULL,
  `role` enum('admin','agency','focal') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint DEFAULT '1',
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)
);
```

**Role Definitions**:
- **admin**: Ministry-level users with full system access
- **focal**: Agency-level administrators who can finalize submissions
- **agency**: Regular agency users with program management access

**Business Rules**:
- Users belong to exactly one agency (except admins)
- Role determines system-wide permissions
- Password hashing using PHP's `password_hash()`

#### `initiatives`
**Purpose**: High-level strategic goals that group related programs.

```sql
CREATE TABLE `initiatives` (
  `initiative_id` int NOT NULL AUTO_INCREMENT,
  `initiative_name` varchar(255) NOT NULL,
  `initiative_number` varchar(20) DEFAULT NULL,
  `initiative_description` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`initiative_id`)
);
```

**Business Rules**:
- Initiatives are system-wide (not agency-specific)
- Programs can be linked to initiatives
- Numbering system for tracking and reporting

### 2. Program Management Tables

#### `programs`
**Purpose**: Core business entity representing agency initiatives and projects.

```sql
CREATE TABLE `programs` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `initiative_id` int DEFAULT NULL,
  `program_name` varchar(255) NOT NULL,
  `status` enum('active','on_hold','completed','delayed','cancelled') DEFAULT 'active',
  `program_number` varchar(50) DEFAULT NULL,
  `rating` enum('monthly_target_achieved','on_track_for_year','severe_delay','not_started') DEFAULT 'not_started',
  `program_description` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `agency_id` int NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `restrict_editors` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`program_id`),
  FOREIGN KEY (`initiative_id`) REFERENCES `initiatives` (`initiative_id`),
  FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
);
```

**Key Fields**:
- **status**: Operational status of the program
- **rating**: Performance rating for ministry reporting
- **program_number**: Unique identifier following agency numbering conventions
- **restrict_editors**: Controls who can edit the program within the agency

**Business Rules**:
- Programs belong to exactly one agency
- Program numbers follow format: `AGENCY_CODE-YYYY-###`
- Ratings are used for ministry-level reporting and dashboards
- Soft delete using `is_deleted` flag

#### `program_submissions`
**Purpose**: Quarterly progress reports for programs within specific reporting periods.

```sql
CREATE TABLE `program_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `period_id` int NOT NULL,
  `is_draft` tinyint(1) DEFAULT '1',
  `is_submitted` tinyint(1) DEFAULT '0',
  `description` text,
  `submitted_by` int DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`submission_id`),
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`) ON DELETE CASCADE,
  FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`)
);
```

**Workflow States**:
1. **Draft** (`is_draft=1, is_submitted=0`): Being worked on by agency
2. **Submitted** (`is_draft=0, is_submitted=1`): Finalized by focal user
3. **Unsubmitted** (`is_draft=1, is_submitted=0`): Returned to draft state

**Business Rules**:
- One submission per program per reporting period
- Only focal users can finalize submissions
- Submitted submissions are visible to ministry admins

#### `program_targets`
**Purpose**: Measurable goals and achievements within program submissions.

```sql
CREATE TABLE `program_targets` (
  `target_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `target_number` varchar(50) DEFAULT NULL,
  `target_description` text,
  `target_value` decimal(15,2) DEFAULT NULL,
  `achievement_value` decimal(15,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','delayed') DEFAULT 'not_started',
  `remarks` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`target_id`),
  FOREIGN KEY (`submission_id`) REFERENCES `program_submissions` (`submission_id`) ON DELETE CASCADE
);
```

**Target Number Format**: `PROGRAM_NUMBER.TARGET_INDEX` (e.g., `SFC-2025-001.1`)

**Business Rules**:
- Targets can carry over between quarters if not completed
- Achievement tracking with quantitative values
- Status tracking for progress monitoring

### 3. Reporting and Period Management

#### `reporting_periods`
**Purpose**: Defines quarterly, half-yearly, or yearly reporting timeframes.

```sql
CREATE TABLE `reporting_periods` (
  `period_id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `period_type` enum('quarter','half','yearly') NOT NULL DEFAULT 'quarter',
  `period_number` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`period_id`),
  CONSTRAINT `chk_valid_period_numbers` CHECK (
    ((`period_type` = 'quarter') and (`period_number` between 1 and 4)) or
    ((`period_type` = 'half') and (`period_number` between 1 and 2)) or
    ((`period_type` = 'yearly') and (`period_number` = 1))
  )
);
```

**Period Types**:
- **quarter**: Q1, Q2, Q3, Q4 (standard quarterly reporting)
- **half**: H1, H2 (semi-annual reporting)
- **yearly**: Annual reporting

**Business Rules**:
- Only one period can be "open" at a time per type
- Submissions can only be created for open periods
- Period closure finalizes all submissions for that timeframe

#### `outcomes`
**Purpose**: Ministry-level metrics and KPIs for reporting and dashboards.

```sql
CREATE TABLE `outcomes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `type` enum('graph','kpi') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `data` json NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
);
```

**Data Structure Examples**:
```json
{
  "rows": [
    {"month": "January", "2022": 408531176.77, "2023": 263569916.63, "2024": 276004972.69},
    {"month": "February", "2022": 239761718.38, "2023": 226356164.3, "2024": 191530929.47}
  ],
  "columns": ["2022", "2023", "2024", "2025", "2026"]
}
```

**Outcome Types**:
- **graph**: Time-series data for chart visualization
- **kpi**: Key performance indicators with target/achievement values

### 4. Supporting Tables

#### `program_attachments`
**Purpose**: File uploads associated with programs.

```sql
CREATE TABLE `program_attachments` (
  `attachment_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_size` int NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `uploaded_by` int NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`attachment_id`),
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
);
```

#### `program_user_assignments`
**Purpose**: Granular permissions for program access within agencies.

```sql
CREATE TABLE `program_user_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `user_id` int NOT NULL,
  `permission_level` enum('owner','editor','viewer') NOT NULL DEFAULT 'viewer',
  `assigned_by` int NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`assignment_id`),
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`)
);
```

**Permission Levels**:
- **owner**: Full control including permission management
- **editor**: Can modify program content and submissions
- **viewer**: Read-only access to program information

#### `notifications`
**Purpose**: System notifications for users.

```sql
CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);
```

### 5. Audit and Logging Tables

#### `audit_logs`
**Purpose**: Comprehensive audit trail for all system actions.

```sql
CREATE TABLE `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int DEFAULT NULL,
  `record_snapshot` json DEFAULT NULL,
  `changes_summary` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
);
```

#### `audit_field_changes`
**Purpose**: Detailed field-level change tracking.

```sql
CREATE TABLE `audit_field_changes` (
  `change_id` int NOT NULL AUTO_INCREMENT,
  `audit_log_id` int NOT NULL,
  `target_id` int DEFAULT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_type` varchar(50) DEFAULT 'text',
  `old_value` text,
  `new_value` text,
  `target_snapshot` json DEFAULT NULL,
  `change_type` enum('added','modified','removed') NOT NULL DEFAULT 'modified',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`change_id`),
  FOREIGN KEY (`audit_log_id`) REFERENCES `audit_logs` (`id`) ON DELETE CASCADE
);
```

#### `reports`
**Purpose**: Generated PowerPoint report metadata and storage.

```sql
CREATE TABLE `reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `report_name` varchar(255) NOT NULL,
  `period_id` int NOT NULL,
  `report_data` longtext NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `generated_by` int NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`report_id`),
  FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
);
```

## Key Relationships and Constraints

### Primary Relationships
1. **Users → Agency**: Each user belongs to one agency
2. **Programs → Agency**: Programs are agency-owned
3. **Programs → Initiatives**: Programs can be linked to initiatives
4. **Submissions → Programs + Periods**: One submission per program per period
5. **Targets → Submissions**: Multiple targets per submission

### Foreign Key Constraints
- **CASCADE DELETE**: Submissions and targets are deleted when parent is deleted
- **RESTRICT DELETE**: Users and agencies cannot be deleted if referenced

### Business Logic Constraints
- **Period Validation**: Check constraints ensure valid period numbers
- **Unique Constraints**: Program numbers must be unique within agency
- **Status Workflows**: Enum values enforce valid state transitions

## Data Migration Considerations for React/Vite

### JSON Data Structures
- **Outcomes**: Already using JSON for flexible data storage
- **Audit Snapshots**: JSON snapshots for complete record history
- **Report Data**: JSON storage for PowerPoint generation data

### PHP API Patterns for Alpine.js Integration

**PHP API Response Structures**:
```php
<?php
// API response format for Alpine.js consumption

// User with Role Information
function formatUserForAPI($user) {
    return [
        'user_id' => (int) $user['user_id'],
        'username' => $user['username'],
        'fullname' => $user['fullname'],
        'email' => $user['email'],
        'agency' => [
            'agency_id' => (int) $user['agency_id'],
            'agency_name' => $user['agency_name']
        ],
        'role' => $user['role'],
        'is_active' => (bool) $user['is_active'],
        'created_at' => $user['created_at'],
        'updated_at' => $user['updated_at']
    ];
}

// Program with Nested Relationships  
function formatProgramForAPI($program, $includeSubmissions = false) {
    $formatted = [
        'program_id' => (int) $program['program_id'],
        'program_name' => $program['program_name'],
        'program_number' => $program['program_number'],
        'status' => $program['status'],
        'rating' => $program['rating'],
        'program_description' => $program['program_description'],
        'start_date' => $program['start_date'],
        'end_date' => $program['end_date'],
        'agency' => [
            'agency_id' => (int) $program['agency_id'],
            'agency_name' => $program['agency_name']
        ],
        'created_by' => [
            'user_id' => (int) $program['created_by'],
            'fullname' => $program['creator_name'] ?? 'Unknown'
        ],
        'created_at' => $program['created_at'],
        'updated_at' => $program['updated_at']
    ];
    
    if ($program['initiative_id']) {
        $formatted['initiative'] = [
            'initiative_id' => (int) $program['initiative_id'],
            'initiative_name' => $program['initiative_name']
        ];
    }
    
    if ($includeSubmissions) {
        $formatted['submissions'] = formatSubmissionsForAPI($program['submissions'] ?? []);
    }
    
    return $formatted;
}

// Submission with Targets
function formatSubmissionForAPI($submission) {
    return [
        'submission_id' => (int) $submission['submission_id'],
        'program_id' => (int) $submission['program_id'],
        'period' => [
            'period_id' => (int) $submission['period_id'],
            'year' => (int) $submission['year'],
            'period_type' => $submission['period_type'],
            'period_number' => (int) $submission['period_number'],
            'period_name' => $submission['period_name'] ?? formatPeriodName($submission)
        ],
        'is_draft' => (bool) $submission['is_draft'],
        'is_submitted' => (bool) $submission['is_submitted'],
        'description' => $submission['description'],
        'targets' => formatTargetsForAPI($submission['targets'] ?? []),
        'submitted_by' => $submission['submitted_by'] ? [
            'user_id' => (int) $submission['submitted_by'],
            'fullname' => $submission['submitter_name'] ?? 'Unknown'
        ] : null,
        'submitted_at' => $submission['submitted_at'],
        'updated_at' => $submission['updated_at']
    ];
}

// API Endpoint Examples
function handleProgramsAPI() {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    switch ($method) {
        case 'GET':
            if (preg_match('/\/api\/programs\/(\d+)$/', $path, $matches)) {
                // Get single program
                $programId = (int) $matches[1];
                $program = getProgramWithDetails($programId);
                
                if (!$program) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Program not found']);
                    return;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => formatProgramForAPI($program, true)
                ]);
            } else {
                // List programs with pagination and filtering
                $filters = [
                    'search' => $_GET['search'] ?? '',
                    'status' => $_GET['status'] ?? '',
                    'agency_id' => $_GET['agency_id'] ?? '',
                    'rating' => $_GET['rating'] ?? '',
                    'page' => max(1, (int) ($_GET['page'] ?? 1)),
                    'per_page' => min(100, max(10, (int) ($_GET['per_page'] ?? 20))),
                    'sort_field' => $_GET['sort_field'] ?? 'updated_at',
                    'sort_direction' => $_GET['sort_direction'] ?? 'desc'
                ];
                
                $result = getProgramsList($filters);
                
                echo json_encode([
                    'success' => true,
                    'data' => array_map('formatProgramForAPI', $result['programs']),
                    'pagination' => [
                        'currentPage' => $filters['page'],
                        'perPage' => $filters['per_page'],
                        'total' => $result['total'],
                        'totalPages' => ceil($result['total'] / $filters['per_page']),
                        'from' => ($filters['page'] - 1) * $filters['per_page'] + 1,
                        'to' => min($result['total'], $filters['page'] * $filters['per_page'])
                    ]
                ]);
            }
            break;
            
        case 'POST':
            // Create new program
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $validator = new ProgramValidator();
            $errors = $validator->validate($input);
            
            if (!empty($errors)) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]);
                return;
            }
            
            // Create program
            $programId = createProgram($input);
            $program = getProgramWithDetails($programId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Program created successfully',
                'data' => formatProgramForAPI($program)
            ]);
            break;
            
        case 'PUT':
            // Update program
            if (preg_match('/\/api\/programs\/(\d+)$/', $path, $matches)) {
                $programId = (int) $matches[1];
                $input = json_decode(file_get_contents('php://input'), true);
                
                // Check permissions and update
                if (!canUpdateProgram($programId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Access denied']);
                    return;
                }
                
                $updated = updateProgram($programId, $input);
                
                if ($updated) {
                    $program = getProgramWithDetails($programId);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Program updated successfully',
                        'data' => formatProgramForAPI($program)
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to update program']);
                }
            }
            break;
            
        case 'DELETE':
            // Soft delete program
            if (preg_match('/\/api\/programs\/(\d+)$/', $path, $matches)) {
                $programId = (int) $matches[1];
                
                if (!canDeleteProgram($programId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Access denied']);
                    return;
                }
                
                $deleted = softDeleteProgram($programId);
                
                if ($deleted) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Program deleted successfully'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to delete program']);
                }
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}
?>
```

### Indexing Strategy for PHP + Alpine.js
```sql
-- Performance indexes for PHP API queries with Alpine.js frontend
CREATE INDEX idx_programs_agency_status ON programs(agency_id, status);
CREATE INDEX idx_programs_rating ON programs(rating);
CREATE INDEX idx_programs_search ON programs(program_name, program_number);
CREATE INDEX idx_submissions_period_status ON program_submissions(period_id, is_submitted);
CREATE INDEX idx_submissions_program_period ON program_submissions(program_id, period_id);
CREATE INDEX idx_targets_submission_status ON program_targets(submission_id, status);
CREATE INDEX idx_users_agency_role ON users(agency_id, role);
CREATE INDEX idx_audit_user_date ON audit_logs(user_id, created_at);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);

-- Full-text search indexes for Alpine.js search functionality
ALTER TABLE programs ADD FULLTEXT(program_name, program_description);
ALTER TABLE program_targets ADD FULLTEXT(target_description);
```

## Database Optimization for PHP + Alpine.js

### API Response Optimization
```php
<?php
// Optimized queries for Alpine.js data consumption

// Dashboard statistics with single query
function getDashboardStats($agencyId = null) {
    $sql = "
        SELECT 
            COUNT(*) as total_programs,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_programs,
            COUNT(CASE WHEN rating = 'monthly_target_achieved' THEN 1 END) as on_target,
            COUNT(CASE WHEN rating = 'severe_delay' THEN 1 END) as delayed,
            AVG(CASE WHEN rating = 'monthly_target_achieved' THEN 4 
                     WHEN rating = 'on_track_for_year' THEN 3
                     WHEN rating = 'severe_delay' THEN 1
                     ELSE 2 END) as avg_rating
        FROM programs p 
        WHERE is_deleted = 0
    ";
    
    if ($agencyId) {
        $sql .= " AND agency_id = ?";
        return Database::fetch($sql, [$agencyId]);
    }
    
    return Database::fetch($sql);
}

// Efficient program listing with pagination
function getProgramsList($filters) {
    $conditions = ['p.is_deleted = 0'];
    $params = [];
    
    // Search functionality for Alpine.js
    if (!empty($filters['search'])) {
        $conditions[] = "(p.program_name LIKE ? OR p.program_number LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Filters for Alpine.js components
    if (!empty($filters['status'])) {
        $conditions[] = "p.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['agency_id'])) {
        $conditions[] = "p.agency_id = ?";
        $params[] = $filters['agency_id'];
    }
    
    if (!empty($filters['rating'])) {
        $conditions[] = "p.rating = ?";
        $params[] = $filters['rating'];
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    
    // Count total for pagination
    $countSql = "
        SELECT COUNT(*) as total 
        FROM programs p
        JOIN agency a ON p.agency_id = a.agency_id
        $whereClause
    ";
    
    $total = Database::fetch($countSql, $params)['total'];
    
    // Get paginated results
    $offset = ($filters['page'] - 1) * $filters['per_page'];
    $orderBy = $filters['sort_field'] . ' ' . strtoupper($filters['sort_direction']);
    
    $dataSql = "
        SELECT 
            p.*,
            a.agency_name,
            u.fullname as creator_name,
            i.initiative_name,
            COUNT(ps.submission_id) as submission_count
        FROM programs p
        JOIN agency a ON p.agency_id = a.agency_id
        LEFT JOIN users u ON p.created_by = u.user_id
        LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
        LEFT JOIN program_submissions ps ON p.program_id = ps.program_id
        $whereClause
        GROUP BY p.program_id
        ORDER BY $orderBy
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $filters['per_page'];
    $params[] = $offset;
    
    return [
        'programs' => Database::fetchAll($dataSql, $params),
        'total' => $total
    ];
}
?>
```

### Performance Improvements for PHP
1. **Query Optimization**: Use JOINs instead of multiple queries for Alpine.js data
2. **Prepared Statements**: All queries use prepared statements for security and performance
3. **Pagination**: Efficient LIMIT/OFFSET patterns for large datasets
4. **Connection Management**: Proper PDO connection handling
5. **Caching**: Simple PHP file-based caching for static data

### Alpine.js Frontend Benefits
- **Real-time Updates**: Fetch API calls update data without page reloads
- **Optimistic UI**: Show changes immediately, sync with server
- **Efficient Filtering**: Client-side filtering reduces server requests
- **Search Debouncing**: Limit API calls during typing

This approach provides a modern, responsive frontend experience while maintaining the simplicity and reliability of PHP backend development, perfect for a single developer managing the entire system.