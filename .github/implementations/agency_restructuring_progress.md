# Agency Views Restructuring Progress

## Overall Task
Restructure the agency side views to match the admin side's organized directory structure, moving from flat structure to organized subdirectories.

## Completed Restructuring

### ✅ Dashboard (FULLY COMPLETE)
**Status**: Successfully completed and tested
**Directory**: `/app/views/agency/dashboard/`
**Files Moved**:
- `dashboard.php` → `dashboard/dashboard.php`
- AJAX file → `dashboard/ajax/agency_dashboard_data.php`
**Key Changes**:
- Updated PROJECT_ROOT_PATH for new depth
- Fixed all layout includes and navigation links
- Updated period selector JavaScript endpoint
- All functionality verified working

### ✅ Sectors (FULLY COMPLETE) 
**Status**: Successfully completed and tested
**Directory**: `/app/views/agency/sectors/`
**Files Moved**:
- `view_all_sectors.php` → `sectors/view_all_sectors.php`
- `ajax/sectors_data.php` → `sectors/ajax/sectors_data.php`
**Key Changes**:
- Updated PROJECT_ROOT_PATH for new depth
- Fixed AJAX file include paths
- Updated navigation and back URL references
- Fixed layout include paths (header, nav, footer)
- All functionality verified working

## Remaining Restructuring

### 🔄 Next Targets (In Priority Order)

1. **Programs View** (`view_programs.php`)
   - Move to `/app/views/agency/programs/view_programs.php`
   - Check for related AJAX files

2. **Program Management** 
   - `create_program.php` → `programs/create_program.php`
   - `edit_program.php` → `programs/edit_program.php`
   - `program_details.php` → `programs/program_details.php`

3. **Metrics/Outcomes Management**
   - Group metrics-related files into `metrics/` or `outcomes/`
   - Examples: `submit_metrics.php`, `view_metric.php`, etc.

4. **Reports and Notifications**
   - Group into `reports/` and `notifications/` directories

## Pattern Established

### Directory Structure Pattern
```
/app/views/agency/[component]/
├── [main_view].php
├── [create_view].php (if applicable)
├── [edit_view].php (if applicable)
└── ajax/
    └── [component]_data.php
```

### Update Checklist Per Component
- [ ] Create directory structure
- [ ] Move main files
- [ ] Move related AJAX files
- [ ] Update PROJECT_ROOT_PATH for new depth
- [ ] Fix layout include paths
- [ ] Update navigation references
- [ ] Update internal file references
- [ ] Update JavaScript endpoints
- [ ] Test functionality
- [ ] Validate syntax

## Current Status
- **2/7 major components restructured** 
- **Pattern well-established and proven**
- **Ready to continue with programs component**

## Tools and Techniques Used
- `PROJECT_ROOT_PATH` constant for reliable includes
- `__DIR__` relative paths for AJAX files
- Consistent navigation updates
- Thorough testing after each move
- File reference tracking and updates
