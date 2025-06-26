# Fix: Custom Row Configuration Not Showing

## Problem
When users select "Custom" structure type in the table designer, the Row Configuration section was not appearing, making it impossible to define custom rows.

## Root Cause
- The row designer visibility logic was implemented but not properly initialized
- Event handling for structure type changes needed improvement
- Missing preset row population for different structure types

## Solution Implemented

### 1. Enhanced Event Handling
- Improved structure type change detection
- Added proper event bubbling for radio button clicks
- Added console logging for debugging visibility changes

### 2. Preset Row Population
- Added `populatePresetRows()` method to automatically populate rows for:
  - **Monthly**: 12 months (January - December)
  - **Quarterly**: 4 quarters (Q1 - Q4)
  - **Yearly**: 5 years (current year ± 2)
  - **Custom**: Empty array for user definition

### 3. Improved Initialization
- Updated `init()` method to call preset population and visibility updates
- Ensured proper order of operations during initialization

### 4. Enhanced Styling
- Added distinct styling for row designer section (light blue theme)
- Improved visual feedback for row items and form elements
- Added proper empty state messaging

## Files Modified
- `assets/js/table-structure-designer.js` - Core functionality fixes
- `assets/css/table-structure-designer.css` - Visual styling improvements

## How It Works Now

1. **Monthly/Quarterly/Yearly**: Rows are automatically populated with appropriate time periods
2. **Custom**: Row designer section appears, allowing users to:
   - Add custom row labels
   - Choose row types (Data Entry, Calculated, Separator)
   - Reorder and remove rows
   - See live preview of their custom structure

## Testing
- [x] Select "Custom" structure type - Row Configuration section appears
- [x] Add custom rows with different types
- [x] Switch between structure types - appropriate rows populate
- [x] Row designer hides/shows correctly based on selection
- [x] Visual styling is consistent and accessible
