# Reduce Recent Program Updates to 5 Items

## Problem
The recent program updates section in the agency dashboard currently shows all recent updates, which can make the section too long and affect the page layout. Need to limit it to show only the 5 most recent updates.

## Solution Steps

### ✅ Step 1: Analyze current recent updates implementation
- Located the issue in `DashboardController.php` in the `getRecentUpdates()` method
- The method uses `LIMIT 10` in the SQL query
- The query already properly orders results by most recent first: `ORDER BY COALESCE(ps.submission_date, p.updated_at, p.created_at) DESC`

### ✅ Step 2: Update the data fetching logic
- Modified the LIMIT clause from 10 to 5 in the `getRecentUpdates()` method
- This ensures only the 5 most recent updates are returned

### ⬜ Step 3: Test the changes
- Verify only 5 items are shown in the recent updates section
- Check that the most recent updates appear
- Ensure layout and styling remain consistent

## Files to be Modified

1. **DashboardController.php** - Change LIMIT 10 to LIMIT 5 in getRecentUpdates method

## Implementation Details

The query in the `getRecentUpdates()` method currently fetches:
```sql
ORDER BY COALESCE(ps.submission_date, p.updated_at, p.created_at) DESC
LIMIT 10
```

This will be changed to:
```sql
ORDER BY COALESCE(ps.submission_date, p.updated_at, p.created_at) DESC
LIMIT 5
```

## Expected Result
The recent program updates section in the agency dashboard will display only the 5 most recent updates instead of up to 10, improving the page layout and focusing attention on the most current activities.
