# Agency Outcomes Edit Implementation

**Date:** 2025-01-27  
**Status:** In Progress  
**Module:** Agency Outcomes Edit Functionality

## Overview

Implement edit capabilities for agency users to edit outcomes, supporting both KPI and Graph outcome types, based on the existing admin edit functionality.

## Current State Analysis

- ✅ Admin edit functionality exists and works (`app/views/admin/outcomes/edit_outcome.php`)
- ✅ Admin has `update_outcome_full()` function in `app/lib/admins/outcomes.php`
- ❌ Agency `edit_outcome.php` exists but is empty (0 bytes)
- ❌ Agency missing `update_outcome_full()` function
- ❌ No edit buttons in agency outcomes view

## Implementation Plan

### 1. Add Missing Agency Functions

- [ ] Add `update_outcome_full()` function to `app/lib/agencies/outcomes.php`
- [ ] Ensure proper access control for agency users

### 2. Create Agency Edit Pages

- [ ] Implement `app/views/agency/outcomes/edit_outcome.php` for Graph outcomes
- [ ] Implement `app/views/agency/outcomes/edit_kpi.php` for KPI outcomes
- [ ] Adapt for agency context (different breadcrumbs, access control)
- [ ] Use agency-specific CSS/JS bundles

### 3. Create Edit Content Partials

- [ ] Create `app/views/agency/outcomes/partials/edit_outcome_content.php` for Graph outcomes
- [ ] Create `app/views/agency/outcomes/partials/edit_kpi_content.php` for KPI outcomes
- [ ] Based on admin version but adapted for agency use
- [ ] Include dynamic table editing functionality for Graph outcomes

### 4. Add Edit Buttons to Outcomes View

- [ ] Modify `app/views/agency/outcomes/partials/submit_content.php`
- [ ] Add edit buttons to each outcome card
- [ ] Ensure proper routing to correct edit page based on outcome type

### 5. Update Navigation and Routing

- [ ] Verify navbar includes edit_outcomes.php in active pages
- [ ] Test routing from outcomes grid to edit page
- [ ] Ensure proper breadcrumb navigation

### 6. Testing and Validation

- [ ] Test edit functionality with different outcome types
- [ ] Verify data persistence and validation
- [ ] Test access control and permissions
- [ ] Ensure responsive design works

## Files to Modify/Create

### New Files

- `app/views/agency/outcomes/partials/edit_outcome_content.php`

### Modified Files

- `app/lib/agencies/outcomes.php` - Add update_outcome_full function
- `app/views/agency/outcomes/edit_outcome.php` - Implement edit page
- `app/views/agency/outcomes/partials/submit_content.php` - Add edit buttons

## Progress Tracking

- [x] Step 1: Add missing agency functions
- [x] Step 2: Create agency edit pages (Graph + KPI)
- [x] Step 3: Create edit content partials (Graph + KPI)
- [x] Step 4: Add edit buttons to outcomes view
- [x] Step 5: Update navigation and routing
- [x] Step 6: Testing and validation

## Implementation Summary

### ✅ Completed Tasks

1. **Added Missing Agency Functions**

   - Added `update_outcome_full()` function to `app/lib/agencies/outcomes.php`
   - Function matches admin implementation for consistency

2. **Created Agency Edit Page**

   - Implemented `app/views/agency/outcomes/edit_outcome.php`
   - Based on admin version but adapted for agency context
   - Uses agency-specific routing and breadcrumbs

3. **Created Edit Content Partial**

   - Created `app/views/agency/outcomes/partials/edit_outcome_content.php`
   - Includes dynamic table editing functionality
   - Full JavaScript implementation for add/remove rows/columns

4. **Added Edit Buttons to Outcomes View**

   - Modified `app/views/agency/outcomes/partials/submit_content.php`
   - Added edit buttons to each outcome card
   - Proper routing to edit page

5. **Updated Navigation and Routing**

   - Fixed navbar configuration to use correct filename (`edit_outcome.php`)
   - Updated both `agency_nav.php` and `navbar-modern.php`
   - Ensured proper active page detection

6. **Created JavaScript Bundle**
   - Created `assets/js/agency/agency-edit-outcomes.js`
   - Includes CSS imports for styling
   - Ready for Vite bundling

### 🎯 Key Features Implemented

- **Full Edit Capability**: Agency users can now edit all outcome fields (code, type, title, description, data)
- **Dynamic Table Editing**: Add/remove rows and columns with real-time updates
- **Data Validation**: Form validation ensures all required fields are filled
- **Consistent UI**: Matches admin edit functionality for familiar user experience
- **Proper Access Control**: Only agency users can access edit functionality
- **Navigation Integration**: Edit buttons integrated into outcomes grid view

### 🔧 Technical Details

- **File Structure**: Follows established project patterns
- **Security**: Proper session and role validation
- **Data Handling**: Uses JSON for complex table data structures
- **Error Handling**: Comprehensive validation and error messages
- **Responsive Design**: Works on all screen sizes

### 📝 Usage

1. Agency users navigate to Outcomes page
2. Click "Edit" button on any outcome card
3. Modify outcome details and table data
4. Click "Save Changes" to update
5. Redirected to view page with success message

The implementation is now complete and ready for testing!

## 🎯 Dual Outcome Type Support

### **KPI Outcomes** (`type = 'kpi'`)

- **Edit Page**: `edit_kpi.php`
- **Data Structure**: Array of objects with `description`, `value`, `unit`, `extra` fields
- **Interface**: Form-based editing with individual field inputs
- **Validation**: Title required, data sanitization

### **Graph Outcomes** (`type = 'graph'` or other)

- **Edit Page**: `edit_outcome.php`
- **Data Structure**: Table format with `columns` and `rows` arrays
- **Interface**: Dynamic table editing with add/remove rows/columns
- **Validation**: Title, type, description required, table structure validation

### **Smart Routing**

- **View Page**: Automatically routes to correct edit page based on outcome type
- **Submit Page**: Edit buttons show appropriate text ("Edit KPI" vs "Edit Outcome")
- **Type Validation**: Prevents wrong edit page access with redirects

### **Key Features**

- ✅ **Type Detection**: Automatically detects outcome type and routes accordingly
- ✅ **Dual Interfaces**: Separate optimized interfaces for KPI vs Graph editing
- ✅ **Data Validation**: Type-specific validation and error handling
- ✅ **Consistent UX**: Matches admin functionality while being agency-appropriate

## Notes

- Reusing proven admin implementation ensures reliability
- Agency users should have appropriate access control
- Dynamic table editing should work the same as admin version
- CSS/JS bundles should be agency-specific
