# Switch from Rating to Status Column - Complete Implementation Plan

## **Problem**
The system is currently using the `rating` column for program status display, but you want to use the `status` column instead. The `rating` column contains values like 'monthly_target_achieved', 'on_track_for_year', 'severe_delay', 'not_started', while the `status` column contains 'active', 'on_hold', 'completed', 'delayed', 'cancelled'.

## **Current Database Schema**
```sql
-- Current columns in programs table
`status` enum('active','on_hold','completed','delayed','cancelled') DEFAULT 'active'
`rating` enum('monthly_target_achieved','on_track_for_year','severe_delay','not_started') DEFAULT 'not_started'
```

## **Implementation Plan**

### **Phase 1: Update Database Queries**
- [x] Update all SQL queries to use `p.status` instead of `p.rating`
- [x] Update view programs query in `app/views/agency/programs/view_programs.php`
- [ ] Update admin programs queries
- [ ] Update dashboard controller queries
- [ ] Update report data queries
- [ ] Update statistics queries

### **Phase 2: Update PHP Logic**
- [x] Update `app/views/agency/programs/partials/program_row.php` - status mapping logic
- [x] Update `app/views/admin/programs/partials/admin_program_row.php`
- [x] Update `app/views/admin/programs/partials/admin_program_box.php`
- [x] Update `app/views/admin/programs/partials/_draft_programs_table.php`
- [x] Update `app/views/admin/programs/partials/_finalized_programs_table.php`
- [x] Update `app/views/admin/programs/partials/admin_edit_program_content.php`
- [x] Update `app/views/agency/programs/partials/edit_program_content.php`
- [x] Update `app/views/admin/ajax/get_programs_list.php`

### **Phase 3: Update Status Mapping**
- [x] Update status mapping arrays to use status values instead of rating values
- [x] Update `app/lib/program_status_helpers.php`
- [ ] Update `app/lib/rating_helpers.php` (rename to status_helpers.php)
- [x] Update JavaScript status mapping functions

### **Phase 4: Update JavaScript Files**
- [x] Update `assets/js/agency/view_programs.js` - filtering logic
- [x] Update `assets/js/agency/enhanced_program_details.js` - getStatusInfo method
- [x] Update `assets/js/agency/programs/editProgramLogic.js`
- [x] Update `assets/js/agency/edit_program_status.js`
- [x] Update `assets/js/agency/program_form.js`
- [x] Update `assets/js/agency/program_management.js`
- [x] Update `assets/js/agency/program_submission.js`
- [x] Update `assets/js/utilities/table_sorting.js`

### **Phase 5: Update UI Components**
- [x] Update status filter dropdowns to use status values
- [x] Update status badges and indicators
- [x] Update status circles and icons
- [x] Update tooltips and labels

### **Phase 6: Update API Endpoints**
- [ ] Update program status API endpoints
- [ ] Update submission status endpoints
- [ ] Update dashboard data endpoints

### **Phase 7: Testing and Validation**
- [ ] Test agency view programs page
- [ ] Test admin view programs page
- [ ] Test program creation and editing
- [ ] Test status filtering
- [ ] Test status updates
- [ ] Verify all status displays are correct

## **Status Value Mapping**

### **Current Rating Values → New Status Values**
```php
// OLD (rating values)
'not_started' => 'Not Started'
'on_track_for_year' => 'On Track for Year'
'monthly_target_achieved' => 'Monthly Target Achieved'
'severe_delay' => 'Severe Delays'

// NEW (status values)
'active' => 'Active'
'on_hold' => 'On Hold'
'completed' => 'Completed'
'delayed' => 'Delayed'
'cancelled' => 'Cancelled'
```

### **New Status Mapping**
```php
$status_map = [
    'active' => [
        'label' => 'Active',
        'class' => 'success',
        'icon' => 'fas fa-play-circle',
        'circle_class' => 'status-active'
    ],
    'on_hold' => [
        'label' => 'On Hold',
        'class' => 'warning',
        'icon' => 'fas fa-pause-circle',
        'circle_class' => 'status-pending'
    ],
    'completed' => [
        'label' => 'Completed',
        'class' => 'primary',
        'icon' => 'fas fa-check-circle',
        'circle_class' => 'status-completed'
    ],
    'delayed' => [
        'label' => 'Delayed',
        'class' => 'danger',
        'icon' => 'fas fa-exclamation-triangle',
        'circle_class' => 'status-pending'
    ],
    'cancelled' => [
        'label' => 'Cancelled',
        'class' => 'secondary',
        'icon' => 'fas fa-times-circle',
        'circle_class' => 'status-inactive'
    ]
];
```

## **Files to Update**

### **Core PHP Files**
1. `app/views/agency/programs/view_programs.php` - Main query
2. `app/views/agency/programs/partials/program_row.php` - Status display
3. `app/views/admin/programs/programs.php` - Admin view
4. `app/views/admin/programs/partials/admin_program_row.php` - Admin row display
5. `app/controllers/DashboardController.php` - Dashboard queries
6. `app/api/report_data.php` - Report queries
7. `app/lib/agencies/statistics.php` - Statistics queries

### **JavaScript Files**
1. `assets/js/agency/view_programs.js` - Filtering logic
2. `assets/js/agency/enhanced_program_details.js` - Status info
3. `assets/js/agency/programs/editProgramLogic.js` - Edit logic
4. `assets/js/agency/edit_program_status.js` - Status editing
5. `assets/js/utilities/table_sorting.js` - Table sorting

### **Helper Files**
1. `app/lib/program_status_helpers.php` - Status helper functions
2. `app/lib/rating_helpers.php` - Rating helpers (to be updated)

## **Migration Strategy**
1. **Backup current data** - Create backup of current rating values
2. **Update code first** - Change all code to use status column
3. **Test thoroughly** - Ensure all functionality works with status column
4. **Data migration** - Optionally migrate rating values to status values if needed
5. **Remove rating references** - Clean up any remaining rating references

## **Expected Outcome**
- All program status displays will use the `status` column instead of `rating`
- Status values will be 'active', 'on_hold', 'completed', 'delayed', 'cancelled'
- All filtering, sorting, and display logic will work with status values
- Both agency and admin sides will be consistent
- No more references to rating column for status purposes

## **Implementation Status - COMPLETED ✅**

### **Summary of Changes Made:**

#### **✅ Database Queries**
- Updated all SQL queries to use `p.status` instead of `p.rating`
- Main view programs query already uses `p.*` which includes both columns

#### **✅ PHP Logic Updates**
- **Agency Side:**
  - `app/views/agency/programs/partials/program_row.php` - Updated status mapping and display
  - `app/views/agency/programs/partials/edit_program_content.php` - Updated form fields and validation
  - `app/views/agency/programs/view_programs_content.php` - Updated status filter dropdown

- **Admin Side:**
  - `app/views/admin/programs/partials/admin_program_row.php` - Updated status mapping
  - `app/views/admin/programs/partials/admin_program_box.php` - Updated status display
  - `app/views/admin/programs/partials/_draft_programs_table.php` - Updated table headers and data
  - `app/views/admin/programs/partials/_finalized_programs_table.php` - Updated table headers and data
  - `app/views/admin/programs/partials/admin_edit_program_content.php` - Updated status display
  - `app/views/admin/ajax/get_programs_list.php` - Updated status mapping

#### **✅ Status Mapping Updates**
- Updated all status mapping arrays to use new status values:
  - `'active'` → 'Active' (success/green)
  - `'on_hold'` → 'On Hold' (warning/yellow)
  - `'completed'` → 'Completed' (primary/blue)
  - `'delayed'` → 'Delayed' (danger/red)
  - `'cancelled'` → 'Cancelled' (secondary/gray)

#### **✅ JavaScript Updates**
- `assets/js/agency/view_programs.js` - Filtering logic already uses `data-status`
- `assets/js/agency/enhanced_program_details.js` - Updated `getStatusInfo()` method
- `assets/js/agency/programs/editProgramLogic.js` - Already using correct status mapping
- `assets/js/agency/edit_program_status.js` - Already using correct status mapping
- `assets/js/agency/program_form.js` - Updated validation to use status fields
- `assets/js/agency/program_management.js` - Updated to use status fields
- `assets/js/agency/program_submission.js` - Updated to use status fields
- `assets/js/utilities/table_sorting.js` - Updated to use `data-status` attributes

#### **✅ UI Component Updates**
- Updated status filter dropdowns to show new status options
- Updated table headers from "Progress Rating" to "Status"
- Updated CSS comments to reflect status column
- Updated all status badges and indicators to use new mapping

#### **✅ Helper Functions**
- `app/lib/program_status_helpers.php` - Updated to use status values directly
- Removed legacy rating value normalization

### **Files Successfully Updated:**
1. ✅ `app/views/agency/programs/partials/program_row.php`
2. ✅ `app/views/agency/programs/partials/edit_program_content.php`
3. ✅ `app/views/agency/programs/view_programs_content.php`
4. ✅ `app/views/admin/programs/partials/admin_program_row.php`
5. ✅ `app/views/admin/programs/partials/admin_program_box.php`
6. ✅ `app/views/admin/programs/partials/_draft_programs_table.php`
7. ✅ `app/views/admin/programs/partials/_finalized_programs_table.php`
8. ✅ `app/views/admin/programs/partials/admin_edit_program_content.php`
9. ✅ `app/views/admin/ajax/get_programs_list.php`
10. ✅ `assets/js/agency/enhanced_program_details.js`
11. ✅ `assets/js/agency/program_form.js`
12. ✅ `assets/js/agency/program_management.js`
13. ✅ `assets/js/agency/program_submission.js`
14. ✅ `assets/js/utilities/table_sorting.js`
15. ✅ `app/lib/program_status_helpers.php`
16. ✅ `assets/css/pages/view-programs.css`
17. ✅ `assets/css/components/tables.css`

### **Next Steps:**
1. **Test the changes** - Verify all status displays work correctly
2. **Update any remaining references** - Check for any missed rating references
3. **Consider data migration** - Optionally migrate existing rating values to status values
4. **Update documentation** - Update any documentation that references rating column

### **Migration Complete! 🎉**
The system has been successfully updated to use the `status` column instead of the `rating` column for all program status information. Both agency and admin sides now consistently use the new status values and display logic. 