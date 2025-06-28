# Fix Edit Outcomes Data Retrieval Issue

## Problem Description
The "Edit" button in the Important Outcomes section was working (correctly navigating to `edit_outcomes.php`) but was not displaying any data from the database, returning empty forms instead.

## Root Cause Analysis
The issue was in `app/views/agency/outcomes/edit_outcomes.php` line 36. The SQL query was specifically filtering for draft outcomes only:

```sql
SELECT table_name, data_json FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? AND is_draft = 1 LIMIT 1
```

However, important outcomes in the Important Outcomes section can be either:
- Draft outcomes (`is_draft = 1`) 
- Submitted outcomes (`is_draft = 0`)

Since the query only looked for `is_draft = 1`, it would return empty results when trying to edit submitted important outcomes.

## Solution Applied

### 1. Updated SQL Query
- **File**: `app/views/agency/outcomes/edit_outcomes.php`
- **Change**: Removed the `AND is_draft = 1` filter from the data retrieval query
- **New Query**: 
```sql
SELECT table_name, data_json, is_draft FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? LIMIT 1
```

### 2. Enhanced Data Handling
- Added `is_draft` field to the SELECT statement to track the current status
- Added `$is_outcome_draft` variable to store the current draft status of the outcome being edited
- This allows the form to work with both draft and submitted outcomes

### 3. Improved User Experience
- Updated page subtitle to show "(Draft)" or "(Submitted)" status
- Added a status badge in the page header to clearly indicate whether editing a draft or submitted outcome
- Users now have clear visual feedback about what type of outcome they're editing

## Files Modified
- ✅ `app/views/agency/outcomes/edit_outcomes.php`
  - Fixed SQL query to retrieve both draft and submitted outcomes
  - Added visual indicators for outcome status
  - Enhanced user experience with clear status information

## Testing Verification
- [x] Check syntax errors - No errors found
- [x] Verify query logic - Now retrieves data regardless of draft status
- [x] Confirm user experience improvements - Status indicators added

## Impact
- ✅ Edit buttons in Important Outcomes section now work correctly for both draft and submitted outcomes
- ✅ Form will populate with existing data when editing important outcomes
- ✅ Users get clear visual feedback about outcome status
- ✅ Maintains backward compatibility with existing draft editing functionality

## Related Issues Fixed
This fix resolves the issue where clicking "Edit" on important outcomes (especially submitted ones) would show an empty form instead of the existing outcome data.
