# Fix Outcome Editor Issues: Live Preview and Structure Filter Error

## Problem Description

### Issue 1: Missing Live Preview for New Columns
When users add a new column while editing an outcome, there's no immediate preview showing how the new column integrates with the existing table. Users should be able to:
- See the new column immediately in the table
- Enter data directly into the new column
- Preview the table structure changes in real-time

### Issue 2: JavaScript Error - structure.columns.filter is not a function
Error occurs in the chart initialization:
```
Uncaught TypeError: structure.columns.filter is not a function
```
**Root Cause**: The `structure.columns` might be null/undefined or not an array in some cases.

## Solution Steps

### Phase 1: Fix JavaScript Structure Error
- [x] Add proper null checks for structure.columns
- [x] Ensure structure.columns is always an array before using .filter()
- [x] Add fallback handling for malformed structure data
- [x] Improve error logging for debugging

### Phase 2: Implement Live Column Preview
- [ ] Update table structure designer to trigger table regeneration
- [ ] Implement real-time table column addition/removal
- [ ] Add visual feedback when columns are added/removed
- [ ] Ensure data inputs are properly created for new columns

### Phase 3: Enhance Table Regeneration
- [ ] Implement complete table rebuilding when structure changes
- [ ] Preserve existing data when adding new columns
- [ ] Add smooth transitions for UI changes
- [ ] Update totals and calculations automatically

### Phase 4: Testing and Polish
- [ ] Test column addition with various data types
- [ ] Test with both classic and flexible outcomes
- [ ] Verify data persistence when saving
- [ ] Ensure responsive design works with new columns

## Files to Modify:
1. `app/views/agency/outcomes/view_outcome.php` - Fix structure error and add live preview
2. `assets/js/table-structure-designer.js` - Enhance column management
3. `assets/css/table-structure-designer.css` - Add transition effects
