# Fix JavaScript Error in Program Search/Filter

## Problem Statement
JavaScript error occurs when searching or filtering programs:
```
Uncaught ReferenceError: tableRows is not defined
applyFilters http://localhost/pcds2030_dashboard/assets/js/agency/view_programs.js:170
```

## Root Cause Analysis
The error occurs in the `applyFilters` function at line 170. This suggests that when I updated the search functionality to include program numbers, there may have been an issue with variable scoping or declaration.

## Implementation Plan

### ✅ Tasks
- [x] Examine the current `applyFilters` function in `view_programs.js`
- [x] Identify the scope issue with `tableRows` variable
- [x] Fix the variable declaration/scoping
- [x] Test the search and filter functionality
- [x] Ensure program number search still works correctly

### Root Cause Identified
The table filtering logic (lines 170-210) was orphaned outside the `applyFilters` function but was trying to reference variables (`tableId`, `searchText`, etc.) that were only defined inside the function scope.

### Fix Applied
Moved the orphaned table filtering code back inside the `applyFilters` function where it belongs, ensuring proper variable scoping.

### Files to Modify
- `assets/js/agency/view_programs.js` - Fix applyFilters function

### Expected Result
- Search and filter functionality works without JavaScript errors
- Program number search continues to work
- Both draft and finalized program tables can be filtered properly
