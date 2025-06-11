# Programs Overview Filter Issues Investigation

## Problem
The filtering functionality in the programs overview page is not working as expected. Need to investigate and fix the issues.

## Issues Identified

### 1. AJAX Endpoint Missing Program Type Filter
- [ ] **Problem**: The AJAX endpoint `get_programs_list.php` doesn't handle the `program_type` filter
- [ ] **Location**: `app/views/admin/ajax/get_programs_list.php` line 23
- [ ] **Fix**: Add program type filter processing

### 2. SQL Query Issues in Backend
- [ ] **Problem**: Complex SQL query with potential issues in `get_admin_programs_list`
- [ ] **Location**: `app/lib/admins/statistics.php` lines 330-430
- [ ] **Issues**: 
  - Search filter has wrong parameter count (`"ss"` instead of `"s"`)
  - Status filter references `ps.status` but should reference `latest_sub.rating`
  - Complex subquery structure may cause issues

### 3. JavaScript Form Handling Issues
- [ ] **Problem**: Reset button selector is incorrect in JavaScript
- [ ] **Location**: `assets/js/admin/programs_list.js` line 295
- [ ] **Fix**: Update selector to match actual reset button

### 4. Missing Search Field
- [ ] **Problem**: JavaScript looks for search input but none exists in the form
- [ ] **Location**: `assets/js/admin/programs_list.js` line 330
- [ ] **Fix**: Either add search field or remove search handling

## Implementation Plan

### Step 1: Fix AJAX Endpoint
- Add program_type filter processing to `get_programs_list.php`
- Ensure all filters are properly passed to backend

### Step 2: Fix Backend SQL Query
- Correct the search filter parameter binding
- Fix status filter to use correct column reference
- Simplify query structure if needed

### Step 3: Fix JavaScript Issues
- Update reset button selector
- Remove or fix search input handling
- Ensure form submission works correctly

### Step 4: Add Missing Features
- Add search field to the form if needed
- Ensure all filter options work correctly

### Step 5: Testing
- Test all filter combinations
- Test reset functionality
- Test AJAX updates
- Test browser back/forward navigation

## Files to Modify
1. `app/views/admin/ajax/get_programs_list.php`
2. `app/lib/admins/statistics.php`
3. `assets/js/admin/programs_list.js`
4. `app/views/admin/programs/programs.php` (if search field needed)

## Testing Checklist
- [ ] Program type filter (Assigned/Agency-Created)
- [ ] Rating/Status filter
- [ ] Sector filter
- [ ] Agency filter
- [ ] Reset button functionality
- [ ] AJAX updates without page refresh
- [ ] Browser navigation (back/forward)
- [ ] Loading indicators
- [ ] Error handling
