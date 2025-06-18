# Fix Rating Color Mapping in Slide Reports

## Problem Description
The rating color mapping in the slide report generation (`report-slide-styler.js`) was using a different logic than the program admin section. This caused inconsistencies in how rating colors were displayed between different parts of the application.

## Root Cause
The `report-slide-styler.js` file was using string matching logic with `includes()` checks for keywords like 'minor', 'caution', 'major', etc., while the program admin section uses the standardized `getRatingColorClass()` function from `rating_utils.js` with exact switch-case matching.

## Solution Steps

### ✅ Step 1: Analyze Current Logic
- [x] Found the rating color logic in `report-slide-styler.js` (lines 1342-1352)
- [x] Identified the standardized logic in `rating_utils.js` (`getRatingColorClass` function)
- [x] Confirmed the program admin uses the standardized logic
- [x] **DISCOVERED**: API in `report_data.php` was converting rating values to simplified colors ('green', 'yellow', 'red', 'grey')

### ✅ Step 2: Fix API Rating Color Mapping
- [x] Fixed incorrect mapping in `report_data.php` where `on-track-yearly` was grouped with green instead of yellow
- [x] Updated API to use correct rating-to-color mapping:
  - `target-achieved` and `completed` → 'green'
  - `on-track` and `on-track-yearly` → 'yellow' (FIXED: was incorrectly 'green')
  - `delayed` and `severe-delay` → 'red'
  - `not-started` → 'grey'
- [x] Added `rating_value` field to API response for debugging purposes

### ✅ Step 3: Update Slide Rating Color Logic
- [x] Updated slide styler to work with API's simplified color values ('green', 'yellow', 'red', 'grey')
- [x] Added debug logging to help troubleshoot rating color issues
- [x] Mapped API colors to appropriate theme colors:
  - 'green' → `themeColors.greenStatus`
  - 'yellow' → `themeColors.yellowStatus`
  - 'red' → `themeColors.redStatus`
  - 'grey' → `themeColors.greyStatus`

### ✅ Step 4: Documentation and Debugging
- [x] Added detailed comments explaining the API data format
- [x] Added console logging to help debug rating values
- [x] Updated this implementation documentation

## Files Modified
- `c:\laragon\www\pcds2030_dashboard\assets\js\report-modules\report-slide-styler.js`
  - Updated rating color mapping logic to work with API's simplified color values
  - Added debug logging for troubleshooting
  - Added documentation comments
- `c:\laragon\www\pcds2030_dashboard\app\api\report_data.php`
  - Fixed incorrect rating-to-color mapping (on-track-yearly now correctly maps to yellow)
  - Added `rating_value` field to API response for debugging
  - Updated comments to reflect the correct mapping

## Root Cause Analysis
The issue was actually a **data format mismatch**:
1. The slide styler was expecting actual rating values like 'target-achieved', 'on-track-yearly' 
2. But the API was converting these to simplified color names like 'green', 'yellow', 'red', 'grey'
3. Additionally, the API had an incorrect mapping where 'on-track-yearly' was grouped with 'green' instead of 'yellow'

## Testing Required
- [ ] Generate a slide report with programs of different ratings
- [ ] Verify that rating colors now match the program admin display:
  - 'on-track-yearly' should show as yellow (not green)
  - 'target-achieved' should show as green
  - 'delayed'/'severe-delay' should show as red
  - 'not-started' should show as gray

## Benefits
1. **Consistency**: Rating colors are now consistent across the entire application
2. **Maintainability**: Uses the same standardized logic, reducing code duplication
3. **Accuracy**: Eliminates incorrect color mappings (e.g., 'on-track-yearly' showing as green instead of yellow)

## Related Files
- `assets/js/utilities/rating_utils.js` - Contains the standardized `getRatingColorClass` function
- Program admin views that use rating colors for reference consistency
