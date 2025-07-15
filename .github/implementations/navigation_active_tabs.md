# Navigation Active Tab Improvements

## Problem
The navigation tabs were not properly highlighting as "active" when users were on program and submission related pages.

## Solution
Updated both agency and admin navigation to properly detect and highlight the "My Programs" tab when users are on any program or submission related pages.

## Tasks

### Agency Navigation Updates
- [x] Updated `app/views/layouts/agency_nav.php` to include all program and submission pages in the active tab detection
- [x] Added the following pages to the active tab condition:
  - `view_programs.php` - Main programs listing
  - `create_program.php` - Create new program
  - `edit_program.php` - Edit existing program
  - `program_details.php` - View program details
  - `view_submissions.php` - View program submissions
  - `add_submission.php` - Add new submission
  - `edit_submission.php` - Edit existing submission
  - `delete_program.php` - Delete program

### Admin Navigation Updates
- [x] Updated `app/views/layouts/admin_nav.php` to include all admin program pages in the active tab detection
- [x] Enhanced program page detection logic to include:
  - `programs.php` - All programs listing
  - `assign_programs.php` - Assign programs to agencies
  - `edit_program.php` - Edit program details
  - `edit_program_2.0.php` - Enhanced program editor
  - `view_program.php` - View program details
  - `delete_program.php` - Delete program
  - `bulk_assign_initiatives.php` - Bulk assign initiatives
  - `reopen_program.php` - Reopen closed program
  - `unsubmit.php` - Unsubmit program
  - `resubmit.php` - Resubmit program
  - `manage_programs.php` - Manage programs
- [x] Added "Bulk Assign Initiatives" to the Programs dropdown menu

## Technical Details

### Agency Navigation
- Uses `in_array()` function to check if current page is in the list of program/submission pages
- Maintains existing functionality while expanding coverage

### Admin Navigation
- Enhanced the existing URI-based detection with additional page-specific checks
- Added comprehensive list of all admin program-related pages
- Maintains dropdown functionality while ensuring proper active state

## Benefits
1. **Better UX**: Users can clearly see which section they're currently in
2. **Consistent Navigation**: All program and submission related pages now properly highlight the "My Programs" tab
3. **Improved Discoverability**: Users can easily navigate back to the main programs section
4. **Professional Appearance**: Navigation now behaves as expected in a modern web application

## Testing
- [ ] Test agency navigation on all program and submission pages
- [ ] Test admin navigation on all program management pages
- [ ] Verify that only one tab is active at a time
- [ ] Confirm dropdown functionality still works correctly 