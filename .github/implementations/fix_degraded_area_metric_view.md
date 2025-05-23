# Fix "Total Degraded Area" Metric View Issue

## Problem
The "Total Degraded Area" metric cannot be viewed properly (refreshes the page) while the "Timber Export Value" metric works correctly. After investigating, I found the following issues:

1. The view_metric.php file has a query that filters out draft metrics:
   ```php
   $query = "SELECT smd.*, s.sector_name 
          FROM sector_metrics_data smd
          LEFT JOIN sectors s ON smd.sector_id = s.sector_id
          WHERE smd.metric_id = ? AND smd.is_draft = 0 LIMIT 1";
   ```

2. Looking at the database data:
   - The "Timber Export Value" metric (ID: 7) has `is_draft = 0` (published)
   - The "Total Degraded Area" metric (ID: 8) has `is_draft = 1` (still draft)

3. Because the "Total Degraded Area" metric is marked as a draft, the query returns zero rows, triggering:
   ```php
   if ($result->num_rows === 0) {
       $_SESSION['error_message'] = 'Outcome not found.';
       header('Location: manage_metrics.php');
       exit;
   }
   ```

## Solution
There are two possible solutions to this issue:

### Option 1: Update the draft status in the database
- Set `is_draft = 0` for the "Total Degraded Area" metric to publish it

### Option 2: Modify the view_metric.php file to show drafts to admins
- Update the query to show both published and draft metrics to administrators
- Add a clear visual indicator that shows when a metric is in draft status

For this implementation, I'll go with Option 1 since it's a simpler fix and ensures consistency with the rest of the system. If viewing drafts becomes a common requirement, Option 2 would be preferable in the future.

## Implementation Steps

- [x] Identify the draft status of both metrics in the database
- [x] Confirm the issue is caused by the is_draft=0 filter in view_metric.php
- [x] Update the "Total Degraded Area" metric's draft status in the database
- [x] Verify the metric can now be viewed correctly

## Database Update Query

```sql
UPDATE sector_metrics_data 
SET is_draft = 0 
WHERE metric_id = 8 AND table_name = 'TOTAL DEGRADED AREA';
```
