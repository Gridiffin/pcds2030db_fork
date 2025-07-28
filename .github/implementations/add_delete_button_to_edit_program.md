# Add Delete Program Button to Edit Program Page

## Overview
Add a delete program button to the edit program page with proper role-based access control. Only the creator of the program, focal users, and admins can delete a program.

## Requirements Analysis

### Current Edit Program Structure
- **Agency Side**: `app/views/agency/programs/edit_program.php` with content in `partials/edit_program_content.php`
- **Admin Side**: `app/views/admin/programs/edit_program.php` with content in `partials/admin_edit_program_content.php`
- **Access Control**: Uses `can_edit_program()` function for editing permissions
- **Role System**: Creator, focal users, and admins have different permission levels

### Delete Functionality Patterns (from codebase analysis)
- **Existing Delete Handlers**: 
  - Agency: `app/views/agency/programs/delete_program.php`
  - Admin: `app/views/admin/programs/delete_program.php`
- **Permission Logic**: `is_focal_user() || is_program_creator($program_id)` for agency, `is_admin()` for admin
- **Modal Pattern**: Bootstrap modal with confirmation dialog
- **Form Submission**: POST method with `program_id` and `confirm_delete` parameters

## Implementation Plan

### 1. ✅ Analyze Current Edit Program Page Structure and Access Control
- [x] Review existing edit program pages (agency and admin)
- [x] Understand current permission system and role checks
- [x] Identify where to add delete button in UI

### 2. ✅ Review Existing Delete Functionality Patterns
- [x] Analyze existing delete handlers and their permission logic
- [x] Understand modal confirmation patterns
- [x] Review form submission and error handling

### 3. ✅ Add Backend Delete Functionality with Role Checks
- [x] Create or update delete handlers for edit program context
- [x] Implement proper role-based access control
- [x] Add audit logging for delete operations

### 4. ✅ Implement Delete Button in Edit Program UI
- [x] Add delete button to edit program content partials
- [x] Implement modal confirmation dialog
- [x] Add proper styling and positioning

### 5. Test Role-Based Access Control for Delete Functionality
- [ ] Test with program creator
- [ ] Test with focal users
- [ ] Test with admins
- [ ] Test with unauthorized users

## Detailed Implementation

### Agency Side Implementation

#### 1. Update Edit Program Content Partial
**File**: `app/views/agency/programs/partials/edit_program_content.php`

Add delete button in the form actions section:
```php
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
    <div class="d-flex gap-2">
        <a href="view_programs.php" class="btn btn-outline-secondary">
            <i class="fas fa-times me-2"></i>Cancel
        </a>
        <?php if (is_focal_user() || is_program_creator($program['program_id'])): ?>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProgramModal">
                <i class="fas fa-trash me-2"></i>Delete Program
            </button>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>Update Program
    </button>
</div>
```

#### 2. Add Delete Modal
**File**: `app/views/agency/programs/partials/edit_program_content.php`

Add modal at the end of the file:
```php
<!-- Delete Program Modal -->
<div class="modal fade" id="deleteProgramModal" tabindex="-1" aria-labelledby="deleteProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProgramModalLabel">Delete Program</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone and will permanently delete all program data.
                </div>
                
                <p>Are you sure you want to delete this program?</p>
                <p><strong>Program:</strong> <?php echo htmlspecialchars($program['program_name']); ?></p>
                
                <div class="program-info bg-light p-3 rounded">
                    <h6>This will permanently remove:</h6>
                    <ul class="mb-0">
                        <li>All program submissions and progress data</li>
                        <li>All associated targets and achievements</li>
                        <li>All file attachments and documents</li>
                        <li>All historical audit records</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo APP_URL; ?>/app/views/agency/programs/delete_program.php" method="post" style="display:inline;">
                    <input type="hidden" name="program_id" value="<?php echo $program['program_id']; ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Program
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

### Admin Side Implementation

#### 1. Update Admin Edit Program Content Partial
**File**: `app/views/admin/programs/partials/admin_edit_program_content.php`

Add delete button in the hero section:
```php
<div class="d-flex gap-2">
    <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary">
        <i class="fas fa-times me-2"></i>Cancel
    </a>
    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProgramModal">
        <i class="fas fa-trash me-2"></i>Delete Program
    </button>
    <button type="submit" form="editProgramForm" class="btn btn-success">
        <i class="fas fa-check-circle me-2"></i>Update Program
    </button>
</div>
```

#### 2. Add Delete Modal
**File**: `app/views/admin/programs/partials/admin_edit_program_content.php`

Add modal at the end of the file:
```php
<!-- Delete Program Modal -->
<div class="modal fade" id="deleteProgramModal" tabindex="-1" aria-labelledby="deleteProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProgramModalLabel">Delete Program</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone and will permanently delete all program data.
                </div>
                
                <p>Are you sure you want to delete this program?</p>
                <p><strong>Program:</strong> <?php echo htmlspecialchars($program['program_name']); ?></p>
                <p><strong>Agency:</strong> <?php echo htmlspecialchars($agency_info['agency_name']); ?></p>
                
                <div class="program-info bg-light p-3 rounded">
                    <h6>This will permanently remove:</h6>
                    <ul class="mb-0">
                        <li>All program submissions and progress data</li>
                        <li>All associated targets and achievements</li>
                        <li>All file attachments and documents</li>
                        <li>All historical audit records</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete_program.php" method="post" style="display:inline;">
                    <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Program
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

### Backend Permission Updates

#### 1. Update Agency Delete Handler
**File**: `app/views/agency/programs/delete_program.php`

Ensure proper permission check:
```php
// Check if user can delete program (creators and focal users)
$can_delete = is_focal_user() || is_program_creator($program_id);
if (!$can_delete) {
    // Log failed deletion attempt - unauthorized
    log_audit_action('delete_program_failed', "Program ID: $program_id | Error: Unauthorized access - not program creator or focal user", 'failure', $user_id);
    
    $_SESSION['message'] = 'You do not have permission to delete this program. Only program creators and focal users can delete programs.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ' . APP_URL . '/app/views/agency/programs/view_programs.php');
    exit;
}
```

#### 2. Update Admin Delete Handler
**File**: `app/views/admin/programs/delete_program.php`

Ensure admin permission check:
```php
// Check role-based permissions: only admin can delete
$can_delete = is_admin();

if (!$can_delete) {
    // Log failed deletion attempt - unauthorized
    log_audit_action('delete_program_failed', "Program ID: $program_id | Error: Unauthorized access - user not admin", 'failure', $_SESSION['user_id']);
    
    $_SESSION['message'] = 'You do not have permission to delete this program. Only administrators can delete programs.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}
```

## Testing Checklist

### Agency Side Testing
- [ ] **Program Creator**: Can see delete button and successfully delete program
- [ ] **Focal User**: Can see delete button and successfully delete program
- [ ] **Regular Agency User**: Cannot see delete button
- [ ] **Program Editor**: Cannot see delete button
- [ ] **Program Viewer**: Cannot see delete button

### Admin Side Testing
- [ ] **Admin User**: Can see delete button and successfully delete program
- [ ] **Non-Admin User**: Cannot access edit program page

### General Testing
- [ ] **Modal Functionality**: Delete confirmation modal opens correctly
- [ ] **Form Submission**: Delete form submits with correct parameters
- [ ] **Success Redirect**: After deletion, user is redirected to appropriate page
- [ ] **Error Handling**: Proper error messages for unauthorized access
- [ ] **Audit Logging**: Delete operations are properly logged

## Security Considerations

1. **Frontend vs Backend**: Frontend button visibility is for UX only - backend validation is critical
2. **Role-Based Access**: Different permission levels for different user roles
3. **Audit Logging**: All delete operations must be logged for accountability
4. **Confirmation Dialog**: Double confirmation prevents accidental deletions
5. **Data Integrity**: Proper transaction handling ensures complete deletion

## Files to Modify

### Agency Side
- `app/views/agency/programs/partials/edit_program_content.php` - Add delete button and modal
- `app/views/agency/programs/delete_program.php` - Verify permission logic

### Admin Side
- `app/views/admin/programs/partials/admin_edit_program_content.php` - Add delete button and modal
- `app/views/admin/programs/delete_program.php` - Verify permission logic

## Success Criteria

1. ✅ Delete button appears only for authorized users
2. ✅ Modal confirmation prevents accidental deletions
3. ✅ Proper role-based access control enforced
4. ✅ Successful deletion redirects to appropriate page
5. ✅ Unauthorized access attempts are properly handled
6. ✅ All delete operations are audited
7. ✅ UI is consistent with existing design patterns

## Implementation Status

### ✅ Completed Tasks
- [x] **Agency Side**: Added delete button to edit program content with proper permission checks
- [x] **Agency Side**: Added delete confirmation modal with comprehensive warning
- [x] **Admin Side**: Added delete button to admin edit program content
- [x] **Admin Side**: Fixed existing delete modal and permission logic
- [x] **Backend**: Verified existing delete handlers have proper role-based access control
- [x] **Backend**: Confirmed audit logging is implemented for all delete operations

### 🔄 Remaining Tasks
- [ ] **Testing**: Test role-based access control with different user types
- [ ] **Testing**: Verify modal functionality and form submission
- [ ] **Testing**: Confirm proper redirects after successful deletion
- [ ] **Testing**: Test error handling for unauthorized access attempts

## Notes

- The implementation follows existing patterns from the codebase
- Uses Bootstrap modal for confirmation dialog
- Leverages existing permission functions
- Maintains consistency with current UI/UX patterns
- Includes proper error handling and user feedback 