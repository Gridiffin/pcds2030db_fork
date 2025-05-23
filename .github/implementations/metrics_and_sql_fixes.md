# PCDS2030 Dashboard Metrics Function and SQL Error Fixes

## Issues to Fix

1. **Undefined function error**
   - [x] Fix `Call to undefined function get_agency_sector_metrics()` in submit_metrics.php (line 33)

2. **SQL Error**
   - [x] Fix SQL GROUP BY clause error incompatible with sql_mode=only_full_group_by

## Implementation Plan

### 1. Fix Undefined Function Error
- [x] Check if the metrics.php file is properly included in submit_metrics.php
- [x] Verify that the function is defined correctly in the metrics.php file
- [x] Include any missing dependencies

### 2. Fix SQL GROUP BY Error
- [x] Identify the query causing the GROUP BY error
- [x] Modify the query to include all non-aggregated columns in the GROUP BY clause
- [x] Test the modified query to ensure it works properly

## Implementation Details

### 1. Fix Undefined Function Error

The error was caused by the `metrics.php` file not being included in the `agencies/index.php` file. The file exists with the correct function, but it wasn't being loaded.

**Solution:**
Updated `app/lib/agencies/index.php` to include metrics.php:
```php
// Load all agency function files
require_once 'programs.php';
require_once 'statistics.php';
require_once 'metrics.php';  // <-- Added this line
```

### 2. Fix SQL GROUP BY Error

The error was in the `get_all_sectors_programs()` function in `statistics.php`. The query was using GROUP BY with only the program_id, but was selecting other columns that weren't part of the GROUP BY clause, which is not allowed in SQL mode `only_full_group_by`.

**Solution:**
Updated the GROUP BY clause to include all columns being selected:
```php
$query .= " GROUP BY p.program_id, p.program_name, p.description, p.start_date, p.end_date, 
            p.created_at, p.updated_at, p.sector_id, s.sector_name, u.agency_name";

// Add additional GROUP BY fields based on schema
if ($has_content_json) {
    $query .= ", ps.content_json";
} else {
    $query .= ", ps.target, ps.achievement, ps.status_date, ps.status_text";
}

$query .= ", ps.status, ps.is_draft ORDER BY (p.sector_id = ?) DESC, p.created_at DESC";
```

This ensures that all columns in the SELECT list are also included in the GROUP BY clause, which is required by MySQL's `only_full_group_by` SQL mode.
