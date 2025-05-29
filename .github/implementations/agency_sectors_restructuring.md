# Agency Sectors Page Restructuring

## Task Description
Restructure the agency sectors page (`view_all_sectors.php`) to match the admin side's organized directory structure pattern, following the same approach used for the dashboard restructuring.

## Current State
- ✅ Dashboard restructuring completed successfully
- ✅ **Sectors restructuring completed successfully**

## Completed Changes

### Files Moved
- `d:\laragon\www\pcds2030_dashboard\app\views\agency\view_all_sectors.php` → `d:\laragon\www\pcds2030_dashboard\app\views\agency\sectors\view_all_sectors.php`
- `d:\laragon\www\pcds2030_dashboard\app\views\agency\ajax\sectors_data.php` → `d:\laragon\www\pcds2030_dashboard\app\views\agency\sectors\ajax\sectors_data.php`

### Files Updated with References
- `/app/views/layouts/agency_nav.php` - Updated sectors navigation link
- `/app/views/agency/program_details.php` - Updated back URL reference

### Path Updates
- **Sectors main file**: Updated `PROJECT_ROOT_PATH` for 4 levels deep directory structure
- **Sectors AJAX file**: Updated include paths for 5 levels deep directory structure using `__DIR__` relative paths
- **Layout includes**: Fixed relative paths from `../layouts/` to `../../layouts/` for new directory depth

### Testing Results
- ✅ Sectors page loads successfully at new location
- ✅ Navigation links work correctly
- ✅ All file references updated successfully
- ✅ Layout include paths fixed and working
- ✅ PHP syntax validation passed (pre-existing function warnings unrelated to restructuring)

## Plan

### Step 1: Analyze Current Sectors File Structure
- [x] Locate `view_all_sectors.php` file
- [x] Identify any related AJAX/API files for sectors functionality
- [x] Find all references to sectors file in navigation and other files

### Step 2: Create New Directory Structure
- [x] Create `/app/views/agency/sectors/` directory
- [x] Create `/app/views/agency/sectors/ajax/` directory (if needed)
- [x] Move `view_all_sectors.php` to `/app/views/agency/sectors/view_all_sectors.php`
- [x] Move any related AJAX files to the appropriate ajax subdirectory

### Step 3: Update File References
- [x] Update `PROJECT_ROOT_PATH` definition for new directory depth
- [x] Fix layout include paths (header.php, agency_nav.php, footer.php)
- [x] Update navigation links in `agency_nav.php`
- [x] Update any JavaScript file references
- [x] Update any redirect paths in other files

### Step 4: Validation
- [x] Check for PHP syntax errors
- [x] Test sectors page accessibility
- [x] Verify all navigation links work correctly
- [x] Test any AJAX functionality

## Directory Structure Target
```
/app/views/agency/sectors/
├── view_all_sectors.php (main sectors view)
└── ajax/
    └── [any sectors-related AJAX files]
```

## Files to Update
- Navigation files with sectors links
- Any redirect references
- JavaScript files referencing sectors endpoints
- Layout include paths in moved files

## Notes
- Follow the same pattern established in dashboard restructuring
- Maintain consistent `PROJECT_ROOT_PATH` usage
- Ensure all AJAX endpoints are properly relocated
- Test functionality after each major change
