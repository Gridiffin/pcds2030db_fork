# Admin Submissions Conversion Implementation Plan

## Overview

Convert the agency submissions pages (view_submissions.php, edit_submission.php, add_submission.php) to work for admin users, allowing them to manage submissions across all agencies with enhanced administrative capabilities.

## Tasks

### 1. View Submissions Page (`view_submissions.php`)

- [x] Change `is_agency()` check to `is_admin()` check
- [x] Update database queries to use admin functions (cross-agency access)
- [x] Add agency information display in submission list
- [x] Update page title and navigation for admin context
- [x] Modify action buttons for admin permissions
- [x] Update JavaScript references to admin scripts

### 2. Edit Submission Page (`edit_submission.php`)

- [x] Change `is_agency()` check to `is_admin()` check
- [x] Update submission retrieval to use admin functions
- [x] Add agency context display in form header
- [x] Update permission checks for admin cross-agency editing
- [x] Modify save/update functions for admin context
- [x] Update navigation and redirect links

### 3. Add Submission Page (`add_submission.php`)

- [x] Change `is_agency()` check to `is_admin()` check
- [x] Update program retrieval to include agency context
- [x] Add agency information display
- [x] Update form submission handling for admin context
- [x] Modify validation and save functions
- [x] Update navigation and redirect links

### 4. Common Updates Across All Files

- [x] Update authentication from `is_agency()` to `is_admin()`
- [x] Change database function calls to admin versions
- [x] Add agency context throughout the UI
- [x] Update page titles and subtitles
- [x] Modify navigation breadcrumbs
- [x] Update script references from agency to admin
- [x] Add admin-specific styling/colors

## Files to Convert

### Source Files (Agency)

- `app/views/agency/programs/view_submissions.php`
- `app/views/agency/programs/edit_submission.php`
- `app/views/agency/programs/add_submission.php`

### Target Files (Admin)

- `app/views/admin/programs/view_submissions.php`
- `app/views/admin/programs/edit_submission.php`
- `app/views/admin/programs/add_submission.php`

## Implementation Strategy

1. **Start with view_submissions.php** - Most complex, establishes patterns
2. **Convert edit_submission.php** - Apply established patterns
3. **Convert add_submission.php** - Complete the trilogy

## Key Conversion Patterns

### Authentication Changes

```php
// FROM (Agency)
if (!is_agency()) {
    redirect_to_login();
}

// TO (Admin)
if (!is_admin()) {
    redirect_to_login();
}
```

### Database Function Updates

```php
// FROM (Agency)
$submissions = get_program_submissions($program_id, $_SESSION['agency_id']);

// TO (Admin)
$submissions = get_admin_program_submissions($program_id); // Cross-agency
```

### UI Updates

```php
// FROM (Agency)
$page_title = "View Submissions";

// TO (Admin)
$page_title = "View Submissions (Admin)";
$page_subtitle = "Manage submissions across all agencies";
```

## Issues Resolved

### Database Function Access Issue

- **Problem**: Agency functions like `get_program_details()` had `is_agency()` checks that blocked admin users
- **Solution**: Created `get_admin_program_details()` function in `app/lib/admins/program_management.php`
- **Files Updated**: All three admin submission files now use admin-specific functions

### Path Resolution Issues

- **Problem**: Incorrect PROJECT_ROOT_PATH calculation and include paths
- **Solution**: Fixed PROJECT_ROOT_PATH to use 3 `dirname()` calls and corrected include paths
- **Files Fixed**: Fixed path issues in all submission files

## Testing Checklist

- [ ] Admin authentication works correctly
- [ ] Cross-agency submission access functions properly
- [ ] Agency context is displayed appropriately
- [ ] All action buttons work with admin permissions
- [ ] Navigation and redirects work correctly
- [ ] Form submissions save with proper admin context
- [ ] Error handling works for admin users
- [ ] Responsive design maintained

## Notes

- Admin users should see submissions from all agencies
- Agency information should be prominently displayed
- Maintain existing functionality while adding admin capabilities
- Keep consistent with admin programs page design patterns
- Ensure proper audit logging for admin actions
