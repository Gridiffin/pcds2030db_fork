# Report Data API Migration Fix

## Overview
Fix the `app/api/report_data.php` file to work with the new custom outcome data structure format after the classic-to-flexible migration.

## Problem
The report data API is still using the old data JSON format pattern:
- Old: `$data['data'][$row][$column]` and `$data['columns']`
- New: `$data[$row][$column_index]` with separate `row_config` and `column_config`

## Files to Fix
- [x] `app/api/report_data.php` - Main report data processing
- [ ] Check if there are any other report-related files

## Changes Made

### 1. Updated Database Queries
**Added row_config and column_config to queries:**
- Degraded Area query: Added `m.row_config, m.column_config`
- Timber Export query: Added `m.row_config, m.column_config`

### 2. Added Format Detection and Compatibility Layer
**New structure detection:**
```php
// Check if we have the new custom structure or fallback to old format
if (!empty($row_config) && !empty($column_config)) {
    // New custom structure format
    $year_columns = array_map(function($col) { return $col['label']; }, $column_config['columns']);
    $monthly_data_rows = $data_json_degraded; // Direct access to month data
} elseif (isset($data_json_degraded['data']) && isset($data_json_degraded['columns'])) {
    // Legacy format fallback
```

### 3. Updated Data Access Patterns
**New format (custom structure):**
- Column access: `$year_column_index = array_search($year, $year_columns)`
- Data access: `$month_values[$year_column_index]`

**Legacy format (backward compatibility):**
- Column access: `in_array($year, $year_columns)`
- Data access: `$month_values[$year]`

### 4. Updated Units Extraction
**New format:** Extract units from `column_config['columns'][]['unit']`
**Legacy format:** Extract units from `data_json['units']`

### 5. Updated Both Degraded Area and Timber Export Processing
- [x] Degraded Area data processing
- [x] Timber Export data processing
- [x] Units extraction for both
- [x] Backward compatibility maintained

---
