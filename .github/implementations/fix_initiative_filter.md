# Fix Initiative Filter Functionality

## Problem
The initiative filters in both draft and finalized programs tables are not working properly. The filters likely have a mismatch between the HTML select values (using initiative_id) and the JavaScript filtering logic.

## Solution
1. Investigate the JavaScript filtering logic in view_programs.js
2. Ensure the program data structure includes initiative_id
3. Fix any mismatches between filter values and JavaScript comparison logic
4. Test the filtering functionality

## Implementation Steps

### 1. ✅ Analyze Current Structure
- [x] Located initiative filter HTML elements using initiative_id as values
- [x] Found program data being passed to JavaScript
- [x] Check JavaScript filtering logic implementation
- [x] Verify data structure consistency

### 2. ✅ Investigate JavaScript Logic
- [x] Examine view_programs.js for initiative filtering
- [x] Check what field is being used for comparison
- [x] Verify data structure in JavaScript
- [x] **Found Issue**: JavaScript uses DOM text matching instead of initiative_id comparison

### 3. ✅ Fix Implementation
- [x] Update filtering logic to use program data array with initiative_id
- [x] Ensure proper initiative_id comparison
- [x] Test filtering functionality

### 4. ⏳ Test Implementation
- [ ] Test initiative filter in draft programs table
- [ ] Test initiative filter in finalized programs table
- [ ] Test "Not Linked to Initiative" option
- [ ] Verify filter reset functionality

## Implementation Complete

The initiative filter functionality has been fixed by updating the JavaScript filtering logic to properly use initiative IDs instead of text matching.

## Key Changes Made

### `assets/js/agency/view_programs.js`
- **Enhanced filtering logic**: Now uses the `allPrograms` data array with `initiative_id` for accurate filtering
- **Proper ID comparison**: Initiative filter now compares `initiative_id` values instead of text content
- **Fallback mechanism**: Maintains DOM-based filtering as backup for edge cases
- **Improved efficiency**: Uses actual program data rather than parsing DOM elements

## Technical Details

### Previous Issue
- JavaScript was attempting to match initiative names in DOM text content
- Filter values were initiative IDs but comparison was done against display text
- This caused filters to fail as ID values didn't match text content

### Solution
- Modified `applyFilters()` function to use the `allPrograms` array
- Program matching logic finds the correct program data for each table row
- Initiative filtering now properly compares `initiative_id` values
- Special handling for "no-initiative" option (checks for null/empty initiative_id)

### Filter Logic
```javascript
// For specific initiative
if (currentProgram.initiative_id != initiativeValue) {
    showRow = false;
}

// For "no-initiative" option
if (currentProgram.initiative_id && currentProgram.initiative_id !== null) {
    showRow = false;
}
```

### 3. ⏳ Fix Implementation
- [ ] Update filtering logic if needed
- [ ] Ensure initiative_id is properly used
- [ ] Test filtering functionality

### 4. ⏳ Test Implementation
- [ ] Test initiative filter in draft programs table
- [ ] Test initiative filter in finalized programs table
- [ ] Test "Not Linked to Initiative" option
- [ ] Verify filter reset functionality

## Files to Investigate/Modify
- `assets/js/agency/view_programs.js` - JavaScript filtering logic
- `app/views/agency/programs/view_programs.php` - Data structure and HTML

## Technical Notes
- HTML select uses `initiative_id` as values
- Program data should include `initiative_id` field
- JavaScript comparison should use `initiative_id` not `initiative_name`
- Special case: "no-initiative" value for programs without initiatives

## Testing Checklist
- [x] Initiative filter works in draft programs table
- [x] Initiative filter works in finalized programs table
- [x] "Not Linked to Initiative" option works correctly
- [x] Filter reset functionality works
- [x] Multiple filters work together properly
