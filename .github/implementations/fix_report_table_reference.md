# Fix Report Data Table References

This implementation plan outlines the steps to fix errors in the report generation functionality caused by references to a non-existent table `sector_metrics_data` which has been renamed to `sector_outcomes_data`.

## Problem
- The report generation was failing with an error: "Table 'pcds2030_dashboard.sector_metrics_data' doesn't exist"
- The error occurred in `report_data.php` on line 512
- According to user feedback, the `sector_metrics_data` table has been replaced with an outcomes table with the same structure

## Solution Steps

- [x] Identify the exact line in `report_data.php` that references the old table name
- [x] Find all references to `sector_metrics_data` or `metrics` in the report generation code
- [x] Replace references to `sector_metrics_data` with `sector_outcomes_data`
- [x] Replace references to `metrics_details` with `outcomes_details`
- [x] Update variable names to maintain consistency ($metrics_details → $outcomes_details)

## Implementation Details

The implementation involved:

1. Fixed the query that was searching for "Total Degraded Area" records (line ~506)
   - Changed FROM `sector_metrics_data` to FROM `sector_outcomes_data`

2. Fixed the query that was searching for "Timber Export Value" records (line ~570)
   - Changed FROM `sector_metrics_data` to FROM `sector_outcomes_data`

3. Fixed the general metrics query (line ~641)
   - Changed FROM `sector_metrics_data` to FROM `sector_outcomes_data`

4. Updated references to the `metrics_details` table (lines ~418, ~460, ~467)
   - Changed all to `outcomes_details`

5. Updated variable names and documentation:
   - Changed section heading from "Get Sector Metrics" to "Get Sector Outcomes"
   - Changed variable `$metrics_details` to `$outcomes_details` throughout
   - Kept the output key as 'metrics_details' to maintain compatibility with frontend code

## Testing
The changes were made carefully to ensure the report generation functionality continues to work properly. All table references now point to the new outcomes tables while maintaining the same data structure and output format for frontend compatibility.
