# Remove Sector Column and Fix Initiative Column - Admin View Programs

## Overview
Remove the sector column and sector filter from the admin "View Programs" page, and fix the initiative column to show both the correct initiative number and name.

## Changes Made

### ✅ Backend Changes

#### 1. Updated Database Query
- **File**: `app/lib/admins/statistics.php`
- **Function**: `get_admin_programs_list()`
- **Change**: Added `i.initiative_number` to the SELECT clause to include initiative numbers in the result set
- **Code**: 
  ```sql
  p.initiative_id, i.initiative_name, i.initiative_number,
  ```

### ✅ Frontend Changes

#### 2. Admin Programs Page Structure
- **File**: `app/views/admin/programs/programs.php`

##### Unsubmitted Programs Section:
- ✅ Removed sector filter dropdown from filters row
- ✅ Reorganized filter layout to use available space better
- ✅ Updated table header to remove "Sector" column
- ✅ Fixed colspan from 7 to 6 in "no programs found" message
- ✅ Updated initiative column to show both initiative number and name: "Number - Name"
- ✅ Removed sector column from table rows

##### Submitted Programs Section:
- ✅ Removed sector filter dropdown from filters row  
- ✅ Reorganized filter layout to use available space better
- ✅ Updated table header to remove "Sector" column
- ✅ Fixed colspan from 7 to 6 in "no programs found" message
- ✅ Updated initiative column to show both initiative number and name: "Number - Name"
- ✅ Removed sector column from table rows

#### 3. JavaScript Filtering Updates
- **File**: `assets/js/admin/programs_admin.js`
- ✅ Removed `unsubmittedSector` and `submittedSector` filter references
- ✅ Removed sector filters from event listeners arrays
- ✅ Removed `sectorValue` from filter values in `filterPrograms()` function
- ✅ Removed sector filtering logic from filter conditions
- ✅ Removed sector filter reset in `resetFilters()` function
- ✅ Removed sector filter badge generation in `updateFilterBadges()` function
- ✅ Removed sector filter from the filters object passed to `updateFilterBadges()`

## New Column Layout

### Before (7 columns):
1. Program Name
2. Initiative  
3. **Sector** ← Removed
4. Agency
5. Rating
6. Last Updated
7. Actions

### After (6 columns):
1. Program Name
2. Initiative (now shows "Number - Name")
3. Agency
4. Rating  
5. Last Updated
6. Actions

## Initiative Display Format

### Before:
- Only showed initiative name: `"Initiative Name"`

### After:
- Shows initiative number and name: `"INT001 - Initiative Name"`
- Falls back to just name if no number: `"Initiative Name"`

## Filter Layout Changes

### Before:
- Search (4 cols) | Rating (2 cols) | Type (2 cols) | Sector (2 cols) | Agency (2 cols)
- Initiative (3 cols) | Reset Button (9 cols)

### After:
- Search (4 cols) | Rating (2 cols) | Type (3 cols) | Agency (3 cols) | Initiative (2 cols)
- Reset Button (12 cols)

## Code Quality
- ✅ All PHP syntax validated (no errors)
- ✅ Consistent with existing truncation and hover functionality
- ✅ Maintains responsive design
- ✅ Preserves all existing functionality except sector filtering

## Files Modified
1. `app/lib/admins/statistics.php` - Added initiative_number to query
2. `app/views/admin/programs/programs.php` - Removed sector column/filters, enhanced initiative display
3. `assets/js/admin/programs_admin.js` - Removed sector filtering functionality

## Testing Recommendations
1. ✅ Verify PHP syntax is valid
2. Test that initiative column shows "Number - Name" format when both are available
3. Test that initiative column shows just name when number is not available
4. Verify all filters work correctly without sector filter
5. Confirm table responsive behavior with new 6-column layout
6. Test filter badges and reset functionality
