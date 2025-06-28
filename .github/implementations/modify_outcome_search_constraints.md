# Implementation: Modify Outcome Search Constraints

## Overview
Update the backend report data API to use exact table name matching for "TIMBER EXPORT VALUE" and remove the submitted-only constraint to include all outcomes regardless of draft status.

## Changes Required

### ✅ 1. Update Timber Export Value Search Method
- **Current**: Uses LIKE patterns (`%Timber Export%` OR `%Export Value%`)
- **Target**: Exact match for `'TIMBER EXPORT VALUE'`
- **File**: `app/api/report_data.php` (line ~577)

### ✅ 2. Remove Draft Status Constraints
- **Current**: Only includes outcomes where `is_draft = 0` (submitted)
- **Target**: Include all outcomes regardless of `is_draft` value
- **Files**: 
  - `app/api/report_data.php` (multiple queries)
  - Any other backend files that filter by draft status

### ✅ 3. Update Documentation
- Update the investigation documentation to reflect the new search method
- Document the removal of draft status filtering

## Implementation Steps

### Step 1: ✅ Modify Timber Export Value Query
- [x] Change LIKE pattern to exact match in `app/api/report_data.php`
- [x] Test the query change

### Step 2: ✅ Remove Draft Status Filters
- [x] Remove `AND m.is_draft = 0` from Timber Export Value query
- [x] Remove `AND m.is_draft = 0` from Total Degraded Area query
- [x] Check for other queries that filter by draft status
- [x] Test all query changes

### Step 3: ✅ Validate Changes
- [x] Ensure syntax is correct in modified PHP file
- [ ] Update investigation documentation

## Technical Details

### Current Timber Export Query:
```sql
AND (m.table_name LIKE '%Timber Export%' OR m.table_name LIKE '%Export Value%')
AND m.is_draft = 0
```

### New Timber Export Query:
```sql
AND m.table_name = 'TIMBER EXPORT VALUE'
```

### Current Degraded Area Query:
```sql
AND m.table_name = 'TOTAL DEGRADED AREA'
AND m.is_draft = 0
```

### New Degraded Area Query:
```sql
AND m.table_name = 'TOTAL DEGRADED AREA'
```

### Step 3: ✅ Validate Changes
- [x] Ensure syntax is correct in modified PHP file
- [x] Update investigation documentation

---
**Status**: ✅ Complete
**Priority**: High
**Impact**: Backend API, Report Generation
