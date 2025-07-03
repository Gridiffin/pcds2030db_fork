# Fix Duplicate Sort Arrows in Tables

## Problem
The sortable table headers in the view_programs page are showing duplicate sort arrows - one static arrow and one that changes based on user clicks.

## Root Cause
The HTML contains static sort icons in the table headers:
```html
<th class="sortable" data-sort="name">
    <i class="fas fa-project-diagram me-1"></i>Program Information 
    <i class="fas fa-sort ms-1"></i>  <!-- Static icon -->
</th>
```

The JavaScript table sorting utility is also trying to update these same icons, potentially creating duplicates or conflicts.

## Solution
1. **Option A**: Remove static sort icons from HTML and let JavaScript manage them completely
2. **Option B**: Update JavaScript to properly handle existing static icons
3. **Option C**: Add CSS to hide duplicate icons

## Tasks
- [x] Investigate the exact cause of duplication
- [x] Choose the best solution approach
- [x] Implement the fix
- [x] Add CSS improvements for sort arrows
- [ ] Test on both draft and finalized program tables
- [ ] Verify sort functionality still works correctly
- [ ] Update documentation

## Implementation
**Root Cause**: The JavaScript was targeting the first `<i>` element in each header with `h.querySelector('i')`, but each header has two icons:
1. Descriptive icon (e.g., `fas fa-project-diagram`)
2. Sort arrow icon (e.g., `fas fa-sort`)

**Solution**: Updated JavaScript to specifically target sort icons using `h.querySelector('i[class*="fa-sort"]')` instead of the first icon.

## Changes Made
1. **Updated `assets/js/utilities/table_sorting.js`**:
   - Changed icon selection from `h.querySelector('i')` to `h.querySelector('i[class*="fa-sort"]')`
   - Now correctly targets only the sort arrows, not descriptive icons

2. **Enhanced `assets/css/components/tables.css`**:
   - Added CSS rules for proper sort arrow styling
   - Added transitions and hover effects for sort arrows
   - Ensured proper spacing and positioning

## Files to Modify
- `app/views/agency/programs/view_programs.php` - Remove static sort icons
- `assets/js/utilities/table_sorting.js` - Ensure proper icon management
- Potentially CSS if additional styling is needed

## Expected Outcome
- Only one sort arrow per sortable column
- Sort arrows change correctly when clicked (up/down/neutral)
- No visual duplication or conflicts
