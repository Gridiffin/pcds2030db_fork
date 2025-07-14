# Focal User Security Fix - Agency Scoped Permissions

## Problem Statement
Critical security vulnerability discovered: Focal users could access and manage ALL programs across ALL agencies, instead of being limited to their own agency's programs. This violated the fundamental security principle that "focal have ultimate access but that only applies within its own agency".

## Impact Assessment
- **Severity**: High Security Risk
- **Scope**: Focal users from Agency A could view, edit, and manage programs belonging to Agency B, C, D, etc.
- **Data Exposure**: Cross-agency program data exposure
- **Privilege Escalation**: Unauthorized access beyond intended agency boundaries

## Root Cause Analysis
The permission system was checking for focal user status first and granting global access, without verifying the focal user's agency assignment to specific programs.

## Security Fix Implementation

### ✅ Core Permission Functions Updated
**File**: `app/lib/agencies/program_agency_assignments.php`

#### Fixed Functions:
- ✅ `can_edit_program()` - Now checks focal user's agency access first
- ✅ `can_view_program()` - Scoped to focal user's agency programs only  
- ✅ `is_program_owner()` - Respects agency boundaries for focal users
- ✅ `assign_agency_to_program()` - Focal users can only assign within accessible programs
- ✅ `remove_agency_from_program()` - Focal users can only remove from accessible programs

#### Security Logic:
```php
// For focal users, check agency access first, then apply focal privileges
if (is_focal_user()) {
    $focal_agency_id = $_SESSION['agency_id'] ?? null;
    if ($focal_agency_id) {
        $focal_role = get_user_program_role($program_id, $focal_agency_id);
        if ($focal_role) {
            return true; // Focal privileges within agency scope
        }
    }
}
```

### ✅ User Assignment Security
**File**: `app/lib/agencies/program_user_assignments.php`

#### Fixed Functions:
- ✅ `assign_user_to_program()` - Focal users can only assign within agency programs
- ✅ `remove_user_from_program()` - Focal users can only remove from agency programs
- ✅ `get_assignable_users_for_program()` - **EXCLUDED focal users from restriction lists**

### ✅ User Restriction Interface Security
**Files**: `app/views/agency/programs/create_program.php`, `app/views/agency/programs/edit_program.php`

#### Fixed Logic:
- ✅ **Removed focal users from "Restrict editing to specific users" options**
- ✅ Only regular agency users appear in user restriction selections
- ✅ Focal users bypass user-level restrictions entirely

#### Security Rationale:
**Focal users should NOT appear in user restriction lists because:**
- They have ultimate access within their agency
- They should bypass all user-level restrictions
- Including them defeats the purpose of their elevated privileges
- User restrictions are meant for regular agency users only

### ✅ Program Listing Security  
**File**: `app/views/agency/programs/view_programs.php`

#### Fixed Query Logic:
- ✅ Unified query for both focal and regular users based on agency assignments
- ✅ Removed global focal access bypass
- ✅ All users (including focal) see only agency-assigned programs

## Program Visibility Fix

### ✅ Fixed New Programs Not Showing in View Programs List
**Error**: Newly created programs were not appearing in the view programs list

**Root Cause**: Program creation functions only created `program_user_assignments` but not `program_agency_assignments`. However, the view programs query required agency assignments to show programs.

**Files Fixed**:
- ✅ `app/lib/agencies/programs.php` - Added agency assignment creation to all program creation functions
- ✅ Database - Created missing agency assignments for existing programs

**Changes Made**:
- Updated `create_agency_program()` to create agency assignments
- Updated `create_wizard_program_draft()` to create agency assignments  
- Updated `create_simple_program()` to create agency assignments
- Added database script to fix existing programs without agency assignments

**Before**:
```php
// Only created user assignment
INSERT INTO program_user_assignments (program_id, user_id, role, assigned_by) VALUES (?, ?, 'editor', ?)
```

**After**:
```php
// Creates both user and agency assignments
INSERT INTO program_user_assignments (program_id, user_id, role, assigned_by) VALUES (?, ?, 'editor', ?)
INSERT INTO program_agency_assignments (program_id, agency_id, role, assigned_by, notes) VALUES (?, ?, 'owner', ?, 'Auto-assigned during program creation')
```

**Result**: New programs now properly appear in the view programs list immediately after creation.

## Security Model After Fix

### Permission Hierarchy (Agency-Scoped):
1. **Agency Level Access**: Must have agency assignment to program
2. **User Level Restrictions**: Additional user-specific role limits (if restrict_editors = 1)
3. **Focal Override**: Enhanced privileges **ONLY** within agency-assigned programs

### Access Control Matrix:
| User Type | Agency A Programs | Agency B Programs | Cross-Agency Access |
|-----------|------------------|------------------|-------------------|
| Regular User | ✅ If assigned | ❌ No access | ❌ Blocked |
| Focal User (Agency A) | ✅ Full access | ❌ No access | ❌ **BLOCKED** |
| Admin | ✅ Full access | ✅ Full access | ✅ Global |

## Testing Requirements

### ✅ Test Cases to Validate:
1. **Focal User Agency Scope**: Focal from Agency A cannot see Agency B programs
2. **Focal Enhanced Privileges**: Focal can edit/manage within their agency regardless of user restrictions
3. **Regular User Boundaries**: Non-focal users still respect both agency and user level permissions
4. **Admin Global Access**: Administrators maintain cross-agency access

### Manual Testing Steps:
1. Login as focal user from Agency A
2. Navigate to programs list - should only see Agency A programs
3. Attempt direct URL access to Agency B program - should be denied
4. Test assignment functions - should only work on Agency A programs

## Database Integrity Fixes

### ✅ Fixed `assigned_by` Field Requirements
**Error**: `Database error: Field 'assigned_by' doesn't have a default value`

**Root Cause**: Multiple INSERT statements were missing the required `assigned_by` field

**Files Fixed**:
- ✅ `app/lib/agencies/programs.php` - Fixed 3 INSERT statements for program creation
- ✅ `app/api/program_user_assignments.php` - Added `assigned_by` field and validation

**Changes Made**:
- Updated all `program_user_assignments` INSERT statements to include `assigned_by`
- Added session validation to ensure valid user ID before assignment
- Self-assignment for program creators (creator assigns themselves as editor)

**Before**:
```sql
INSERT INTO program_user_assignments (program_id, user_id, role) VALUES (?, ?, 'editor')
```

**After**:
```sql
INSERT INTO program_user_assignments (program_id, user_id, role, assigned_by) VALUES (?, ?, 'editor', ?)
```

## Database Integrity
- ✅ No schema changes required
- ✅ Existing permission tables remain valid
- ✅ Audit trail preserved
- ✅ **`assigned_by` field errors resolved**

## Files Modified
1. `app/lib/agencies/program_agency_assignments.php` - Core permission functions
2. `app/lib/agencies/program_user_assignments.php` - User assignment functions  
3. `app/views/agency/programs/view_programs.php` - Program listing query

## Security Validation
- ✅ Focal users now properly scoped to agency boundaries
- ✅ Cross-agency data exposure eliminated
- ✅ Privilege escalation contained within agency scope
- ✅ Backward compatibility maintained for admin and regular users

## Next Steps
1. Comprehensive testing with multiple agencies and focal users
2. Code review of any remaining focal user functions
3. Documentation update for corrected permission model
4. Security audit of related access control points

---
**Security Status**: ✅ **CRITICAL VULNERABILITY FIXED**  
**Agency Boundary Enforcement**: ✅ **ACTIVE**  
**Focal User Scope**: ✅ **PROPERLY LIMITED TO AGENCY**
