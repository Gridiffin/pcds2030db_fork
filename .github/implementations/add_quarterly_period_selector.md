# Add Quarte### Phase 1: Backend Changes
- [x] Modify `period_selector_dashboard.php` to support both half-yearly and quarterly views
- [x] Add a toggle/switch to choose between "Half-Yearly" and "Quarterly" view
- [x] Update the dropdown generation logic to show individual quarters when quarterly mode is selected

### Phase 2: Frontend Changes  
- [x] Update `period_selector.js` to handle the new toggle functionality
- [x] Ensure AJAX calls work correctly with individual quarter period_ids
- [x] Add smooth transitions between view modes

### Phase 3: UI/UX Enhancements
- [x] Add appropriate styling for the view mode toggle
- [x] Ensure responsive design works with both view modes
- [x] Add loading states during view mode switchesector Option

## Overview
Currently, the period selector only shows half-yearly periods (H1: Q1+Q2, H2: Q3+Q4). We need to add quarterly filtering option so users can view data by individual quarters (Q1, Q2, Q3, Q4) as well.

## Current Implementation Analysis
- **File**: `/app/lib/period_selector_dashboard.php` - Groups periods by half-year
- **JavaScript**: `/assets/js/period_selector.js` - Handles AJAX updates
- **Data Sources**: Various `/app/ajax/*_data.php` files support `period_id` parameter

## Implementation Summary

### ✅ **Successfully Implemented:**

1. **View Mode Toggle**: Added radio button toggle between "Half-Yearly" and "Quarterly" views
2. **Dynamic Dropdown**: Period selector now shows different options based on selected view mode
3. **Quarterly Support**: Individual quarters (Q1, Q2, Q3, Q4) can now be selected
4. **Backward Compatibility**: Half-yearly view remains the default to maintain existing functionality
5. **URL State Management**: View mode and period selections are preserved in URL parameters
6. **AJAX Support**: Both view modes work with existing AJAX data loading

### 🔧 **Key Changes Made:**

#### `/app/lib/period_selector_dashboard.php`:
- Added `$view_mode` parameter handling from `$_GET['view_mode']`
- Created separate `$quarterly_options` array alongside existing `$half_year_options`
- Added view mode toggle UI with Bootstrap radio buttons
- Updated dropdown to show different options based on view mode
- Enhanced JavaScript to handle view mode changes

#### `/assets/js/period_selector.js`:
- Added `initViewModeToggle()` function
- Added `handleViewModeChange()` function
- Updated `updatePageContent()` to handle both single and comma-separated period IDs
- Maintains existing AJAX functionality

### 🎯 **How It Works:**

1. **Default Behavior**: Loads in half-yearly mode (maintains backward compatibility)
2. **Toggle Switch**: User can switch between "Half-Yearly" and "Quarterly" views
3. **Dynamic Dropdown**: Options update automatically based on selected view mode
4. **Period Selection**: Works with existing AJAX endpoints (no backend changes needed)
5. **URL Preservation**: View mode and period selections are maintained in browser URL

### 🔗 **Integration Points:**

- **Admin Dashboard**: `/admin/dashboard/dashboard.php`
- **Agency Dashboard**: `/agency/dashboard/dashboard.php` 
- **Programs Pages**: `/admin/programs/programs.php`, `/agency/sectors/view_all_sectors.php`
- **AJAX Endpoints**: All existing `*_data.php` files work unchanged

### 📱 **Responsive Design:**

- Radio button toggle adapts to mobile/tablet screens
- Dropdown maintains appropriate sizing on all devices
- Loading states work consistently across view modes

## Implementation Plan

### Phase 1: Backend Changes
- [ ] Modify `period_selector_dashboard.php` to support both half-yearly and quarterly views
- [ ] Add a toggle/switch to choose between "Half-Yearly" and "Quarterly" view
- [ ] Update the dropdown generation logic to show individual quarters when quarterly mode is selected

### Phase 2: Frontend Changes  
- [ ] Update `period_selector.js` to handle the new toggle functionality
- [ ] Ensure AJAX calls work correctly with individual quarter period_ids
- [ ] Add smooth transitions between view modes

### Phase 3: UI/UX Enhancements
- [ ] Add appropriate styling for the view mode toggle
- [ ] Ensure responsive design works with both view modes
- [ ] Add loading states during view mode switches

### Phase 4: Testing & Validation
- [ ] Test on admin dashboard
- [ ] Test on agency dashboard  
- [ ] Test on programs pages
- [ ] Verify AJAX data loading works correctly
- [ ] Test browser back/forward navigation

## Technical Details

### New Components:
1. **View Mode Toggle**: Radio buttons or toggle switch (Half-Yearly vs Quarterly)
2. **Dynamic Dropdown**: Changes options based on selected view mode
3. **Enhanced JavaScript**: Handles view mode changes and maintains state

### Data Flow:
1. User selects view mode (Half-Yearly/Quarterly)
2. Dropdown options update dynamically
3. Period selection triggers existing AJAX flow
4. Backend receives individual period_id (no changes needed to AJAX endpoints)

## Files to Modify:
- `/app/lib/period_selector_dashboard.php` - Main period selector component
- `/assets/js/period_selector.js` - Add view mode toggle handling
- CSS files (if needed for styling)

## Backward Compatibility:
- Existing half-yearly functionality remains intact
- Default view mode will be half-yearly to maintain current behavior
- All existing AJAX endpoints continue to work unchanged
