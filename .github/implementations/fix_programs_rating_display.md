# Fix Programs Rating Display Issue

## Problem
In the agency side programs view, all programs are showing as "Not Started" even though they have different ratings stored in their submissions. The rating data is stored in the `content_json` field of the `program_submissions` table but the current query is not extracting this information properly.

## Root Cause Analysis
1. The `programs` table does not have a direct `rating` column
2. Ratings are stored in the `content_json` field of `program_submissions` table as JSON data
3. The current query in `get_agency_programs()` function is trying to access `$program['rating']` which doesn't exist
4. The query joins with program_submissions but doesn't extract the rating from the JSON content

## Solution Steps

### Step 1: Update the Database Query
- [x] Analyze current query in `get_agency_programs()` function
- [x] Modify query to extract rating from `content_json` using MySQL JSON functions
- [x] Ensure the query gets the rating from the latest submission for each program

### Step 2: Update the PHP Logic
- [x] Update the `get_agency_programs()` function to properly extract rating information
- [x] Test the updated query with sample data
- [x] Ensure proper fallback to 'not-started' when no rating is found

### Step 3: Test the Fix
- [x] Verify that programs now show correct ratings in the view
- [x] Test with different program states (draft, finalized)
- [x] Ensure ratings are displayed correctly for both sections

### Step 4: Code Review and Cleanup
- [x] Review the updated code for any potential issues
- [x] Ensure proper error handling
- [x] Clean up any temporary debugging code

## Technical Details

### Current Query Issue
The current query joins with `program_submissions` but only selects basic fields:
```sql
SELECT p.*, 
       COALESCE(latest_sub.is_draft, 1) as is_draft,
       latest_sub.period_id,
       ...
```

### Required Fix
Need to extract rating from JSON using MySQL JSON_EXTRACT:
```sql
SELECT p.*, 
       COALESCE(latest_sub.is_draft, 1) as is_draft,
       latest_sub.period_id,
       JSON_UNQUOTE(JSON_EXTRACT(latest_sub.content_json, '$.rating')) as rating,
       ...
```

## Files to Modify
1. `app/views/agency/programs/view_programs.php` - Update the `get_agency_programs()` function

## Implementation Status
✅ **COMPLETED** - The issue has been successfully fixed!

## Summary of Changes Made
1. **Database Query Enhancement**: Updated the SQL query in `get_agency_programs()` function to extract the rating from the JSON content using `JSON_UNQUOTE(JSON_EXTRACT(latest_sub.content_json, '$.rating'))`.

2. **Proper Fallback**: Added `COALESCE()` to ensure programs without submissions default to 'not-started' rating.

3. **Testing**: Verified the query works correctly with the database and returns the expected ratings:
   - Forest Conservation Initiative: "not-started"
   - Forest Research & Development Initiative: "severe-delay" 
   - Reforestation and Restoration Project: "severe-delay"
   - Sustainable Timber Management Program: "on-track-yearly"
   - Wildlife Habitat Protection Scheme: "not-started"

The fix ensures that programs in the agency side now display their actual ratings instead of defaulting to "Not Started" for all programs.
