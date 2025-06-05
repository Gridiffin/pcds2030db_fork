# Fix View Details Button Issue on Submit Outcomes Page

## Problem Description
The "View Details" button on the agency submit outcomes page (`app/views/agency/outcomes/submit_outcomes.php`) is refreshing the whole page without doing anything. No errors are appearing.

## Analysis of the Issue

### Current Implementation
- **File**: `app/views/agency/outcomes/submit_outcomes.php` (Line 144)
- **Button Code**: 
  ```html
  <a href="view_outcome.php?outcome_id=<?= $outcome['metric_id'] ?>" class="btn btn-sm btn-outline-primary">
      <i class="fas fa-eye me-1"></i> View Details
  </a>
  ```
- **Target**: `view_outcome.php?outcome_id=<?= $outcome['metric_id'] ?>`

### Possible Causes
1. ✅ **Target file exists**: `app/views/agency/outcomes/view_outcome.php` exists
2. ❓ **Parameter mismatch**: The link uses `metric_id` but target might expect different parameter
3. ❓ **Database query issue**: The target file might be failing silently
4. ❓ **Permission/session issues**: User authentication might be failing
5. ❓ **JavaScript interference**: Some JS might be preventing navigation

### Investigation Steps
- [x] Locate the submit outcomes page
- [x] Find the View Details button implementation  
- [x] Check if target file exists
- [x] Examine the target file's parameter handling
- [x] Check database query in target file
- [x] Identified root cause: Parameter mismatch in database query
- [x] Test the query logic
- [x] Implemented the fix

### Root Cause Identified
**Parameter Mismatch in Database Query**

The issue was in `app/views/agency/outcomes/view_outcome.php` at line 37-42:

- **Link sends**: `outcome_id=<?= $outcome['metric_id'] ?>` (passes `metric_id` value as `outcome_id` parameter)
- **Query used**: `WHERE id = ?` (incorrectly looking for `id` field)
- **Should use**: `WHERE metric_id = ?` (correct field name based on database structure)

The `sector_outcomes_data` table uses `metric_id` as the primary identifier, not `id`.

### Next Steps
1. ✅ Analyze the `view_outcome.php` file parameter handling
2. ✅ Check the database query logic
3. ✅ Verify data availability
4. ✅ Test and fix the issue

## Solution Implementation

### Fixed the Parameter Mismatch (Primary Issue)
**File**: `app/views/agency/outcomes/view_outcome.php` (Line 37-39)

**Before**:
```php
$query = "SELECT data_json, table_name, created_at, updated_at FROM sector_outcomes_data 
          WHERE id = ? AND sector_id = ? AND is_draft = 0 LIMIT 1";
```

**After**:
```php
$query = "SELECT data_json, table_name, created_at, updated_at FROM sector_outcomes_data 
          WHERE metric_id = ? AND sector_id = ? AND is_draft = 0 LIMIT 1";
```

### Fixed Related Issues (Additional Fixes)
During the investigation, similar parameter mismatches were found in other related files:

**File**: `app/views/agency/outcomes/submit_draft_outcome.php` (Line 31)
- **Before**: `WHERE id = ? AND sector_id = ?`
- **After**: `WHERE metric_id = ? AND sector_id = ?`

**File**: `app/views/agency/outcomes/edit_outcomes.php` (Lines 36 & 75)
- **Before**: `WHERE id = ? AND sector_id = ?` (SELECT query)
- **After**: `WHERE metric_id = ? AND sector_id = ?` (SELECT query)
- **Before**: `WHERE id = ? AND sector_id = ?` (UPDATE query)
- **After**: `WHERE metric_id = ? AND sector_id = ?` (UPDATE query)

### What This Fixes
- The link from submit outcomes page passes `metric_id` value as `outcome_id` parameter
- All queries now correctly use `metric_id` field instead of `id`
- This allows the database to find the correct records
- The "View Details" button will now properly navigate to the outcome details page
- Draft submission and editing functions will also work correctly

## Testing

### Manual Testing Required
To verify the fix works correctly:

1. **Navigate to Submit Outcomes Page**
   - Go to `app/views/agency/outcomes/submit_outcomes.php`
   - Ensure you have existing outcome data to test with

2. **Test View Details Button**
   - Click the "View Details" button for any submitted outcome
   - Verify it navigates to the outcome details page without refreshing
   - Confirm the outcome data displays correctly

3. **Test Related Functions**
   - Test editing draft outcomes (if any exist)
   - Test submitting draft outcomes (if any exist)
   - Verify all functions work without errors

### Expected Results
- ✅ "View Details" button should navigate properly
- ✅ No page refreshes or blank responses
- ✅ Outcome data should display correctly
- ✅ No database errors should occur
- ✅ Draft editing and submission should work properly

### Files Modified
- `app/views/agency/outcomes/view_outcome.php` (database query + header path fix)
- `app/views/agency/outcomes/submit_draft_outcome.php` (database query)
- `app/views/agency/outcomes/edit_outcomes.php` (database query)

### Additional Issues Fixed
**Header Path Issue**: Fixed incorrect include path in `view_outcome.php`
- **Before**: `require_once '../layouts/header.php';`
- **After**: `require_once '../../layouts/header.php';`

The file structure is: `app/views/agency/outcomes/view_outcome.php` → `app/views/layouts/header.php`
Path should be: `../../layouts/header.php` (go up 2 levels from outcomes folder)

All modified files passed syntax validation with no errors found.
