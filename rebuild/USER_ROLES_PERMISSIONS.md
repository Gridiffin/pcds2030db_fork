# PCDS 2030 Dashboard - User Roles & Permissions Documentation

## Overview

The PCDS 2030 Dashboard implements a hierarchical role-based access control (RBAC) system designed for multi-agency forestry sector reporting. The system supports three primary user roles with granular permissions at the program level.

## User Role Hierarchy

```
┌─────────────────────────────────────────────────────────────┐
│                     ADMIN USERS                            │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Ministry Level                         │   │
│  │  • Full system access                              │   │
│  │  • Cross-agency operations                         │   │
│  │  • Report generation                               │   │
│  │  • User management                                 │   │
│  │  • System configuration                           │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   AGENCY USERS                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                FOCAL USERS                          │   │
│  │  • Agency-level administration                     │   │
│  │  • Submission finalization                         │   │
│  │  • User assignment within agency                   │   │
│  │  • Override program restrictions                   │   │
│  └─────────────────────────────────────────────────────┘   │
│                              │                             │
│                              ▼                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              REGULAR USERS                          │   │
│  │  • Program creation and editing                    │   │
│  │  • Submission management                           │   │
│  │  • Target tracking                                 │   │
│  │  • File uploads                                    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## User Role Definitions

### 1. Admin Users (`role: 'admin'`)

**Purpose**: Ministry-level administrators with full system access

**Characteristics**:
- Not restricted to any specific agency
- Can access all agencies' data
- System-wide configuration rights
- Report generation capabilities

**Key Functions**:
```php
function is_admin() {
    return $_SESSION['role'] === 'admin';
}
```

**Permissions**:
- ✅ **User Management**: Create, edit, delete users across all agencies
- ✅ **Agency Management**: Manage agency settings and configurations
- ✅ **Program Oversight**: View and edit all programs across agencies
- ✅ **Submission Review**: Access all submissions regardless of status
- ✅ **Report Generation**: Create ministry-level PowerPoint reports
- ✅ **Period Management**: Create and manage reporting periods
- ✅ **Outcome Management**: Configure KPIs and outcomes for reporting
- ✅ **Audit Log Access**: View comprehensive system audit trails
- ✅ **System Configuration**: Modify system-wide settings and features

### 2. Focal Users (`role: 'focal'`)

**Purpose**: Agency-level administrators with submission finalization authority

**Characteristics**:
- Belong to a specific agency
- Elevated permissions within their agency
- Can override program-level restrictions
- Authority to finalize submissions for ministry review

**Key Functions**:
```php
function is_focal_user() {
    return get_user_role() === 'focal';
}

function is_agency() {
    return $_SESSION['role'] === 'agency' || $_SESSION['role'] === 'focal';
}
```

**Permissions**:
- ✅ **Submission Finalization**: Finalize submissions for ministry review
- ✅ **Agency Program Management**: Edit any program within their agency
- ✅ **User Assignment**: Assign users to programs within agency
- ✅ **Permission Override**: Bypass program editor restrictions
- ✅ **Agency Reporting**: Access agency-wide statistics and reports
- ✅ **File Management**: Upload/download attachments for agency programs
- ✅ **Notification Management**: Send notifications to agency users
- ❌ **Cross-Agency Access**: Cannot view other agencies' programs
- ❌ **System Configuration**: Cannot modify system-wide settings
- ❌ **User Creation**: Cannot create new user accounts

### 3. Regular Agency Users (`role: 'agency'`)

**Purpose**: Standard agency users with program management capabilities

**Characteristics**:
- Belong to a specific agency
- Program-level permissions based on assignments
- Cannot finalize submissions
- Basic CRUD operations within assigned scope

**Key Functions**:
```php
function is_agency() {
    return $_SESSION['role'] === 'agency' || $_SESSION['role'] === 'focal';
}

function get_agency_id() {
    return is_agency() ? ($_SESSION['agency_id'] ?? null) : null;
}
```

**Permissions**:
- ✅ **Program Creation**: Create new programs within their agency
- ✅ **Submission Management**: Create and edit submissions (draft mode)
- ✅ **Target Tracking**: Add and update program targets
- ✅ **File Operations**: Upload/download program attachments
- ✅ **Dashboard Access**: View agency-specific dashboard and statistics
- ✅ **Notification Viewing**: View personal and agency notifications
- ❌ **Submission Finalization**: Cannot submit final submissions
- ❌ **Cross-Agency Programs**: Cannot view other agencies' programs
- ❌ **User Management**: Cannot create or manage user accounts
- ❌ **Report Generation**: Cannot generate ministry reports

## Program-Level Permissions

### Permission Levels

#### 1. Owner (`permission_level: 'owner'`)
- **Full Control**: Complete access to program settings and data
- **Permission Management**: Can assign/remove other users
- **Deletion Rights**: Can delete the program
- **Override Authority**: Can override all restrictions

#### 2. Editor (`permission_level: 'editor'`)
- **Content Management**: Can modify program details and submissions
- **Target Management**: Can add/edit program targets
- **File Management**: Can upload/download attachments
- **Submission Creation**: Can create and edit submissions

#### 3. Viewer (`permission_level: 'viewer'`)
- **Read-Only Access**: Can view program information
- **Dashboard Visibility**: Program appears in dashboard
- **Report Access**: Can view program in reports
- **No Modifications**: Cannot edit any program data

### Program Permission Functions

```php
/**
 * Check if user's agency owns a program
 */
function is_program_owner($program_id, $agency_id = null) {
    // Checks if program belongs to user's agency
    // Used for agency-level ownership validation
}

/**
 * Check if user can edit a program
 */
function can_edit_program($program_id, $user_id = null) {
    // Focal users: Can edit any program in their agency
    // Regular users: Check program restrictions and assignments
}

/**
 * Check if user can view a program
 */
function can_view_program($program_id, $user_id = null) {
    // Agency users: Can view programs in their agency
    // Considers program-level viewer assignments
}

/**
 * Check if program has editor restrictions
 */
function program_has_editor_restrictions($program_id) {
    // Returns true if restrict_editors flag is set
    // Determines if assignment checks are needed
}
```

### Permission Matrix

| Action | Admin | Focal | Agency (Owner) | Agency (Editor) | Agency (Viewer) |
|--------|--------|--------|----------------|-----------------|-----------------|
| **Program Management** |
| Create Program | ✅ | ✅ | ✅ | ✅ | ❌ |
| Edit Program Details | ✅ | ✅ | ✅ | ✅* | ❌ |
| Delete Program | ✅ | ✅ | ✅ | ❌ | ❌ |
| View Program | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Submission Management** |
| Create Submission | ✅ | ✅ | ✅ | ✅* | ❌ |
| Edit Submission | ✅ | ✅ | ✅ | ✅* | ❌ |
| Finalize Submission | ✅ | ✅ | ❌ | ❌ | ❌ |
| Unsubmit Submission | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Target Management** |
| Add Targets | ✅ | ✅ | ✅ | ✅* | ❌ |
| Edit Targets | ✅ | ✅ | ✅ | ✅* | ❌ |
| Delete Targets | ✅ | ✅ | ✅ | ✅* | ❌ |
| **File Management** |
| Upload Attachments | ✅ | ✅ | ✅ | ✅* | ❌ |
| Download Attachments | ✅ | ✅ | ✅ | ✅ | ✅ |
| Delete Attachments | ✅ | ✅ | ✅ | ✅* | ❌ |
| **User Management** |
| Assign Program Users | ✅ | ✅ | ✅ | ❌ | ❌ |
| Modify Permissions | ✅ | ✅ | ✅ | ❌ | ❌ |
| **System Access** |
| Cross-Agency View | ✅ | ❌ | ❌ | ❌ | ❌ |
| Generate Reports | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage Periods | ✅ | ❌ | ❌ | ❌ | ❌ |

*Subject to program restriction settings

## Authentication Flow

### Session Management

```php
// Session variables stored upon login
$_SESSION['user_id'] = 123;
$_SESSION['username'] = 'john_doe';
$_SESSION['role'] = 'agency';
$_SESSION['agency_id'] = 2;
$_SESSION['fullname'] = 'John Doe';
```

### Authorization Checks

```php
// Basic authentication check
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Role-based checks
function require_admin() {
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }
}

function require_agency() {
    if (!is_agency()) {
        http_response_code(403);
        echo json_encode(['error' => 'Agency access required']);
        exit;
    }
}
```

### Database User Structure

```sql
-- Users table with role definitions
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `pw` varchar(255) NOT NULL,              -- bcrypt hashed
  `fullname` varchar(200) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `agency_id` int NOT NULL,                -- Foreign key to agency
  `role` enum('admin','agency','focal') NOT NULL,
  `is_active` tinyint DEFAULT '1',
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)
);

-- Program user assignments for granular permissions
CREATE TABLE `program_user_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `user_id` int NOT NULL,
  `permission_level` enum('owner','editor','viewer') NOT NULL DEFAULT 'viewer',
  `assigned_by` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`assignment_id`),
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);
```

## Security Implementation

### Password Security
```php
// Password hashing on registration/update
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Password verification on login
if (password_verify($input_password, $stored_hash)) {
    // Login successful
}
```

### Session Security
- **Session Regeneration**: ID regenerated on login
- **Session Timeout**: Automatic logout after inactivity
- **Session Validation**: User status checked on each request
- **Secure Cookies**: HttpOnly and Secure flags set

### Input Validation
```php
// Parameter sanitization
$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
$agency_id = isset($_SESSION['agency_id']) ? intval($_SESSION['agency_id']) : 0;

// SQL injection prevention
$stmt = $conn->prepare("SELECT * FROM programs WHERE program_id = ? AND agency_id = ?");
$stmt->bind_param("ii", $program_id, $agency_id);
```

## Migration Considerations for React/Vite

### JWT Token Structure
```typescript
interface JWTPayload {
  user_id: number;
  username: string;
  role: 'admin' | 'agency' | 'focal';
  agency_id: number;
  permissions: string[];
  exp: number; // Token expiration
}
```

### React Authorization Patterns
```typescript
// Context for user authentication
interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isAdmin: boolean;
  isAgency: boolean;
  isFocal: boolean;
  hasPermission: (permission: string) => boolean;
  canEditProgram: (programId: number) => boolean;
}

// HOC for role protection
export const withRoleProtection = (
  Component: React.ComponentType,
  requiredRole: UserRole
) => {
  return (props: any) => {
    const { user } = useAuth();
    
    if (!user || !hasRole(user, requiredRole)) {
      return <Unauthorized />;
    }
    
    return <Component {...props} />;
  };
};

// Hook for program permissions
export const useProgramPermissions = (programId: number) => {
  const { user } = useAuth();
  
  return useMemo(() => ({
    canView: canViewProgram(user, programId),
    canEdit: canEditProgram(user, programId),
    canDelete: canDeleteProgram(user, programId),
    canAssignUsers: canAssignUsers(user, programId)
  }), [user, programId]);
};
```

### API Authorization Middleware
```typescript
// Express.js middleware for route protection
export const requireRole = (role: UserRole) => {
  return (req: Request, res: Response, next: NextFunction) => {
    if (!req.user || !hasRole(req.user, role)) {
      return res.status(403).json({ error: 'Insufficient permissions' });
    }
    next();
  };
};

// Program-specific authorization
export const requireProgramAccess = (action: ProgramAction) => {
  return async (req: Request, res: Response, next: NextFunction) => {
    const programId = parseInt(req.params.programId);
    const canPerformAction = await checkProgramPermission(
      req.user,
      programId,
      action
    );
    
    if (!canPerformAction) {
      return res.status(403).json({ error: 'Program access denied' });
    }
    
    next();
  };
};
```

This comprehensive role and permissions system ensures secure, hierarchical access control while maintaining flexibility for program-level collaboration within agencies.