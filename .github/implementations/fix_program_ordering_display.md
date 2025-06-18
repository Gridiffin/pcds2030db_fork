# Fix Program Ordering Counter Display Issue

## Problem Description
In the admin generate reports page, when clicking/selecting programs, the order counter numbers are not visible. Users cannot see the ordering numbers which are essential for arranging programs in the desired sequence for report generation.

## Investigation Steps

### 1. Check HTML Structure ✅
- [x] Examine the program checkbox HTML structure
- [x] Verify order input elements are properly created
- [x] Check CSS classes and styling for order inputs

### 2. Check JavaScript Functionality ✅
- [x] Verify toggleOrderInput function is working
- [x] Check if order numbers are being assigned correctly
- [x] Examine updateOrderNumbers function
- [x] Test if event listeners are properly attached

### 3. Check CSS Styling ✅
- [x] Verify order input visibility styles
- [x] Check for conflicting CSS rules
- [x] Ensure proper positioning and display properties

## Issues Found

### Primary Issue: Missing CSS Import ❌
- `report-generator.css` file exists with proper styles but is NOT imported in `main.css`
- This means all the order input styling is not being applied
- Order inputs are likely invisible or improperly styled

### Secondary Issue: Event Listener Timing ❌
- Event listeners are attached to checkboxes, but programs are loaded dynamically
- Need to ensure event listeners are reattached after programs are loaded
- toggleOrderInput function may not be called for dynamically loaded content

### Styling Issues ❌
- Order inputs have `display: none` by default
- They should show when checkbox is checked
- But without proper CSS, the display logic might not work correctly

### 4. Fix Implementation Issues ✅
- [x] Add missing CSS import for report-generator.css in main.css
- [x] Add initializeSelectButtons() call after programs are rendered
- [x] Add CSS styling for program-order-badge (currently missing)
- [x] Set default display: none for order inputs
- [x] Enhanced toggleOrderInput to update badge display
- [x] Added updateOrderBadges function
- [x] Added event listeners for order input changes
- [x] Ensure mobile compatibility with touch-friendly styling

## Implementation Completed ✅

### Fixed Issues:
1. **Missing CSS Import**: Added `report-generator.css` to main.css imports
2. **Missing Badge Styling**: Added comprehensive CSS for `.program-order-badge`
3. **Event Listener Timing**: Added `initializeSelectButtons()` call after HTML rendering
4. **Default Visibility**: Set order inputs to `display: none` by default
5. **Badge Updates**: Enhanced functions to update badge numbers dynamically
6. **Input Event Handlers**: Added listeners for manual order number changes

### How It Works Now:
1. Programs load with invisible order inputs and visible "#" badges
2. When a program is selected, the order input becomes visible and badge shows the number
3. Badge updates in real-time as users change order numbers
4. All styling is properly loaded and applied
5. Mobile-friendly touch targets and responsive design

## Files to Investigate
1. `assets/js/report-generator.js` - Main ordering logic
2. `app/views/admin/reports/generate_reports.php` - HTML structure
3. `assets/css/pages/admin.css` - Styling for order elements
4. `app/api/get_period_programs.php` - Data structure
