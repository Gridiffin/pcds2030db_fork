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

---

## Additional Fix: Target Detection Issue

### Problem
The "No Targets" toast was incorrectly showing on program details pages even when programs had submissions with targets. This was because the system was still using legacy `content_json` field instead of the new `program_targets` table.

### Solution
- [x] Updated `get_program_details()` function in `app/lib/agencies/programs.php` to fetch targets from the `program_targets` table
- [x] Modified `program_details.php` to prioritize targets from the new table structure
- [x] Added fallback to legacy `content_json` for backward compatibility
- [x] Enhanced target detection logic to check multiple sources

### Technical Changes
1. **Database Query Enhancement**: Added proper JOIN with `program_targets` table in `get_program_details()`
2. **Target Processing**: Updated target extraction logic to use new table structure first
3. **Backward Compatibility**: Maintained support for legacy `content_json` format
4. **Detection Logic**: Improved `$has_targets` detection to check new structure first

### Benefits
- **Accurate Detection**: No more false "No Targets" alerts when targets exist
- **Modern Structure**: Uses current database schema instead of legacy fields
- **Backward Compatibility**: Still supports old data format for existing records
- **Better Performance**: Direct database queries instead of JSON parsing

---

## UI Improvement: Program Details Header Reorganization

### Problem
The program details header looked cluttered with multiple buttons and badges stacked vertically, making it difficult to scan and use.

### Solution
- [x] Moved "Add Submission" button from header to a dedicated "Quick Actions" section
- [x] Cleaned up header to only show status indicators (status badge and draft indicator)
- [x] Created a new "Quick Actions" card with organized action buttons
- [x] Added contextual descriptions for each action
- [x] Enhanced styling for better visual hierarchy

### Technical Changes
1. **Header Simplification**: Removed action buttons from header, kept only status indicators
2. **Quick Actions Section**: Created new card with organized action buttons
3. **Enhanced Styling**: Added CSS for quick actions card with hover effects and gradients
4. **Contextual Help**: Added descriptive text under each action button

### Benefits
- **Cleaner Header**: Status information is now clearly visible without clutter
- **Better Organization**: Actions are logically grouped in a dedicated section
- **Improved UX**: Users can easily find and understand available actions
- **Visual Hierarchy**: Clear separation between information display and actions
- **Responsive Design**: Better mobile experience with organized button layout

### New Quick Actions Include:
- **Add New Submission**: Primary action for creating progress reports
- **Edit Program Details**: Secondary action for modifying program information
- **View Submission History**: Available when submissions exist 