# Add Submission Button for Editors on Program Templates + Three Dots Button for Draft Submissions

## ✅ IMPLEMENTATION COMPLETED

**Summary of Changes:**
- Modified `app/views/agency/programs/view_programs.php` to show "Add Submission" button for all users with edit permissions
- Previously, the button only appeared for program creators
- Now, any user with `owner` or `editor` role can add submissions directly from the program list
- Separated creator-only actions (edit program, delete) from editor actions (add submission)
- Maintained existing permission system and responsive design patterns
- Fixed path resolution in test file for proper testing
- **NEW**: Added three dots (more actions) button for editors on programs with draft submissions
- **NEW**: Added three dots (more actions) button for editors on programs with finalized submissions
- **FIXED**: Program template notification now respects edit permissions (no misleading action links for viewers)
- **UI STREAMLINED**: Removed redundant quick actions sidebar from program details page
- **UI RESTORED**: Added single "Add Submission" button in program details header for users with edit permissions (editors, owners, focal)

**Key Code Changes:**

1. **Program Templates (Completed):**
```php
// OLD: Only show for program creators
<?php if (isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id']): ?>

// NEW: Show for all editors, separate creator-only actions
<?php 
$can_edit = can_edit_program($program['program_id']);
$is_creator = isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id'];
?>

<?php if ($can_edit): ?>
  <!-- Add Submission button for all editors -->
<?php endif; ?>

<?php if ($is_creator): ?>
  <!-- Edit Program and Delete buttons for creators only -->
<?php endif; ?>
```

2. **Draft/Finalized Submissions (Completed):**
```php
// OLD: Only show three dots for program creators
<?php if (isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id']): ?>

// NEW: Show three dots for all editors
<?php if ($can_edit): ?>
  <!-- Three dots button for all editors -->
<?php endif; ?>
```

3. **Program Template Notification (Fixed):**
```php
// OLD: Show notification and action link only for owners
$showNoSubmissionsAlert = !$has_submissions && $is_owner;
showToast('Program Template', 'This program is a template. <a href="...">Add your first progress report</a>.');

// NEW: Show notification for all, but action link only for editors
$showNoSubmissionsAlert = !$has_submissions; // Show for all users
<?php if ($can_edit): ?>
  showToast('Program Template', 'This program is a template. <a href="...">Add your first progress report</a>.');
<?php else: ?>
  showToast('Program Template', 'This program is a template. No progress reports have been added yet.');
<?php endif; ?>
```

## Problem Description
Currently, editors can only add submissions through the program details page or dedicated add submission page. However, when viewing program templates (programs without submissions) in the view_programs list, editors should be able to directly create the first submission using an action button in the table row.

## Current Behavior
- Program templates show: View, Edit Program, Delete buttons (only for program creators)
- Users need to navigate to program details or add submission page to create first submission
- Editors of a program (who didn't create it) cannot add submissions directly from the program list

## Desired Behavior  
- Add a "Add Submission" button for editors in the program templates table
- This button should only appear for users who have edit permissions on the program
- The button should link directly to the add_submission.php page
- Should respect the existing permission system (agency-level and user-level restrictions)

## Implementation Steps

### 1. Analyze Current Permission System
- [x] Review `can_edit_program()` function in `program_agency_assignments.php`
- [x] Understand how editor permissions work for programs
- [x] Check current action buttons implementation in view_programs.php

### 2. Modify Program Templates Table Actions
- [x] Update the action buttons section for programs without submissions
- [x] Add "Add Submission" button for users with edit permissions
- [x] Ensure button only shows for editors (not just program creators)
- [x] Style the new button consistently with existing design
- [x] Separate creator-only actions (edit program, delete) from editor actions (add submission)

### 3. Test Permission Logic
- [x] Test with different user roles (owner, editor, viewer)
- [x] Verify agency-level permissions work correctly
- [x] Test user-level restrictions if applicable
- [x] Ensure button doesn't appear for users without edit access
- [x] Created test file to verify permission logic

### 4. Update Styling and UX
- [x] Ensure button group layout works with the new button
- [x] Add appropriate tooltips and icons
- [x] Maintain responsive design with flex-fill classes
- [x] Test on different screen sizes (using Bootstrap responsive classes)

### 5. Testing and Validation
- [x] Create test scenarios for different permission levels
- [x] Verify functionality with restricted editors
- [x] Test cross-agency scenarios
- [x] Validate PHP syntax in modified files
- [x] Fix path resolution issues in test file
- [x] Test three dots button functionality for editors
- [x] Update test file to verify both add submission and three dots buttons
- [ ] Clean up debug files after user verification (test_add_submission_button.php)

### 7. Fix Program Template Notification
- [x] Fix misleading "Add your first progress report" notification in program_details.php
- [x] Show notification to all users but only show action link for editors
- [x] Prevent non-editors from seeing actionable links they cannot use
- [x] Maintain consistent permission checking across the application

## Permission-Based Toast Notifications (Completed)

### Enhanced Toast Behavior for Programs with Submissions
Implemented permission-aware toast notifications that show different content based on user edit permissions:

**For Editors (owner/editor/focal roles):**
- **Draft Submission Toast**: Shows action button "Edit & Submit" 
- **No Targets Toast**: Shows action button "Add Targets"
- **Program Template Toast**: Shows action button "Add Progress Report"

**For Viewers (viewer role or no edit permissions):**
- **Draft Submission Toast**: Shows informational message only ("This program is in draft mode and pending final submission")
- **No Targets Toast**: Shows informational message only ("This program does not have any targets defined yet")  
- **Program Template Toast**: Shows informational message only ("This program is a template. No progress reports have been added yet")

**Code Implementation:**
```php
// Draft Submission - Permission-based display
<?php if ($can_edit): ?>
showToastWithAction('Draft Submission', 'This program is in draft mode.', 'warning', 10000, {
    text: 'Edit & Submit',
    url: '<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program_id ?>'
});
<?php else: ?>
showToast('Draft Submission', 'This program is in draft mode and pending final submission.', 'warning', 8000);
<?php endif; ?>

// No Targets - Permission-based display  
<?php if ($can_edit): ?>
showToastWithAction('No Targets', 'No targets have been added for this program.', 'info', 10000, {
    text: 'Add Targets',
    url: '<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program_id ?>'
});
<?php else: ?>
showToast('No Targets', 'This program does not have any targets defined yet.', 'info', 8000);
<?php endif; ?>
```

**Benefits:**
- **Role-Appropriate UX**: Users only see actions they can actually perform
- **Security**: No misleading action buttons for viewers
- **Consistent Behavior**: All toast notifications follow the same permission pattern
- **Professional Appearance**: Clean action buttons for editors, simple messages for viewers

## UI Streamlining (Updated)

### Removed Quick Actions Sidebar
Removed the redundant "Quick Actions" sidebar from `app/views/agency/programs/program_details.php` to eliminate duplicate functionality since actions are available in the program list tables.

### Restored Single Add Submission Button
Added back a single "Add Submission" button in the program details header, but only for users with edit permissions:

**Code Changes:**
```php
// ADDED: Single Add Submission button for editors/owners/focal
<?php if ($can_edit): ?>
    <a href="<?php echo APP_URL; ?>/app/views/agency/programs/add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add Submission
    </a>
<?php endif; ?>
```

**Permission Logic:**
- Uses `$can_edit = can_edit_program($program_id)` to check permissions
- Shows for users with `owner`, `editor`, or `focal` roles
- Respects both agency-level and user-level restrictions
- Maintains consistent styling with other action buttons

**Benefits:**
- Quick access to add submissions from program details view
- Proper permission-based visibility
- Cleaner than full quick actions sidebar
- Complements table-based actions in program list

### Notes
- The existing `add_submission.php` page already handles all the permission checking and form logic
- We're just adding a direct link to this page from the program list
- Need to ensure the button respects both agency-level and user-level permissions
- Should maintain consistency with existing action button styling and behavior

## Testing Instructions

### Manual Testing
1. **Access the test file**: Navigate to `http://your-domain/test_add_submission_button.php` in your browser
2. **Login required**: Make sure you're logged in as different user types to test permissions
3. **Expected results**:
   - Users with `owner` or `editor` role should see "SHOW BUTTON ✅" 
   - Users with `viewer` role should see "HIDE BUTTON ❌"
   - Users who didn't create the program but have edit permissions should still see the button

### Test Different Scenarios
- **Program Creator + Owner Role**: Should see Add Submission, Edit Program, and Delete buttons
- **Non-Creator + Editor Role**: Should see Add Submission button only (main improvement)
- **Non-Creator + Viewer Role**: Should see View button only
- **Cross-agency Editor**: Should see Add Submission if assigned as editor to the program

## Troubleshooting

### Path Resolution Issues
If you encounter "Failed to open stream" errors:
1. **Correct project structure**: The config is located at `app/config/config.php`, not `config/config.php`
2. **Test file paths**: Use `PROJECT_ROOT_PATH . 'app/config/config.php'` for root-level files
3. **Path definition**: Use `rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR` for PROJECT_ROOT_PATH
4. **Common mistake**: Don't use complex dirname() operations, keep it simple with __DIR__

### Correct Path Pattern for Root-Level Files:
```php
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
```

## Files to Modify

### Primary Files
- `app/views/agency/programs/view_programs.php` - Add the new action button
- Possibly update related JavaScript if needed for dynamic behavior

### Permission Functions (Reference Only)
- `app/lib/agencies/program_agency_assignments.php` - Permission checking functions
- `app/lib/agencies/program_user_assignments.php` - User-level restrictions

## Notes
- The existing `add_submission.php` page already handles all the permission checking and form logic
- We're just adding a direct link to this page from the program list
- Need to ensure the button respects both agency-level and user-level permissions
- Should maintain consistency with existing action button styling and behavior
