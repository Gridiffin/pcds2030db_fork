# Admin Programs Conversion Implementation Plan

## Overview

Convert the agency programs view page to work for admin users, allowing them to view all programs across all agencies with enhanced administrative capabilities.

## Tasks

### 1. Update Authentication and Permission Checks

- [x] Change `is_agency()` check to `is_admin()` check
- [x] Remove agency-specific session validation
- [x] Update permission logic for admin cross-agency access

### 2. Modify Database Queries

- [x] Remove agency_id filtering to show all programs
- [x] Add agency information joins for display
- [x] Update program filtering to include agency context
- [x] Add agency filter dropdown for admin filtering

### 3. Update Page Header and UI Elements

- [x] Change page title from "Agency Programs" to "Admin Programs"
- [x] Update subtitle to reflect admin capabilities
- [x] Modify action buttons (remove "Create New Program", add admin-specific actions)
- [x] Update page variant to admin color scheme

### 4. Add Agency Information Display

- [x] Add agency column to all program tables
- [x] Update filter options to include agency selection
- [x] Display agency context in program information

### 5. Update JavaScript and Filtering

- [x] Change JavaScript file reference from agency to admin
- [x] Add agency filtering functionality
- [x] Update filter badges and counters
- [x] Modify table sorting to include agency column

### 6. Update Action Links and Permissions

- [x] Change program detail links to admin view pages
- [x] Update edit program links to admin edit pages
- [x] Modify delete program logic for admin permissions
- [x] Remove agency-specific restrictions

### 7. Update Scripts and Dependencies

- [x] Change script references from agency to admin
- [x] Update JavaScript initialization for admin context
- [x] Add admin-specific functionality

## Files to Modify

### Main File

- `c:\laragon\www\pcds2030_dashboard_fork\app\views\admin\programs\programs.php`

### JavaScript Files

- Create or update admin programs JavaScript file
- Update filtering and interaction logic

### CSS/Styling

- Ensure admin color scheme is applied
- Update any agency-specific styling

## Implementation Notes

- Admin users should see ALL programs from ALL agencies
- Add agency information prominently in the display
- Maintain the same card structure but add agency context
- Ensure proper permission checks for admin-specific actions
- Keep the filtering and pagination functionality
- Add agency-based filtering option

## Testing

- [ ] Verify admin authentication works
- [ ] Test cross-agency program visibility
- [ ] Confirm filtering works with agency information
- [ ] Test all action buttons work correctly
- [ ] Verify pagination and sorting functionality
- [ ] Check responsive design works properly
