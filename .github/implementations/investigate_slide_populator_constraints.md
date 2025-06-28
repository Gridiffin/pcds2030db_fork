# Investigation: Slide Populator Constraints Analysis

## Overview
This investigation explores the constraints in the slide/report generation system, specifically how the backend searches for outcomes and what data the frontend expects for chart generation.

## Analysis Findings

### ✅ Backend Search Constraints (app/api/report_data.php)

The backend actively searches for exactly **TWO** specific outcome types using hardcoded table name queries:

#### 1. Timber Export Value
- **Search Method**: Exact table name match
- **SQL Query**:
  ```sql
  AND m.table_name = 'TIMBER EXPORT VALUE'
  ```
- **Line**: 577 in report_data.php
- **Usage**: Powers the main_chart data structure

#### 2. Total Degraded Area  
- **Search Method**: Exact table name match
- **SQL Query**:
  ```sql
  AND m.table_name = 'TOTAL DEGRADED AREA'
  ```
- **Line**: 510 in report_data.php
- **Usage**: Powers the degraded_area_chart data structure

### ✅ Data Structure Requirements

The backend constructs specific data structures that the frontend expects:

#### Main Chart (Timber Export)
```php
$main_chart_data = [
    'labels' => $monthly_labels,
    'data' . $previous_year => $timber_export_data[$previous_year],
    'data' . $current_year => $timber_export_data[$current_year],
    'total' . $previous_year => array_sum($timber_export_data[$previous_year]),
    'total' . $current_year => array_sum($timber_export_data[$current_year])
];
```

#### Degraded Area Chart
```php
$degraded_area_chart_data_prepared = [
    'labels' => $monthly_labels,
    'years' => $degraded_area_years,
    'units' => $degraded_area_units ?: 'Ha'
];
// Plus dynamic data arrays for each year
```

### ✅ Frontend Chart Generation (assets/js/report-modules/report-slide-styler.js)

The frontend `addTimberExportChart` function expects:
- `data.charts.main_chart.data` structure
- Dynamic properties: `data2024`, `data2023`, etc.
- Monthly data arrays with exactly 12 values
- Automatic year-based property calculation

### ✅ System Limitations Confirmed

1. **Only TWO outcome types** are supported for chart generation:
   - Timber Export Value (exact matching: 'TIMBER EXPORT VALUE')
   - Total Degraded Area (exact matching: 'TOTAL DEGRADED AREA')

2. **Hardcoded table name dependencies**:
   - Backend searches rely on exact table name matches
   - Frontend expects specific data structure keys

3. **No dynamic outcome discovery**:
   - System doesn't automatically detect available outcome types
   - No configuration for adding new chart-capable outcomes

4. **All outcome statuses included**:
   - System now includes both draft and submitted outcomes
   - No filtering by submission status

## Recommendations for Improvement

### 1. Make Chart Generation Configurable
- Create a configuration table mapping outcome types to chart configurations
- Remove hardcoded table name dependencies
- Allow dynamic discovery of chart-capable outcomes

### 2. Standardize Outcome Type Identification
- Use outcome type IDs instead of table name string matching
- Implement consistent naming conventions for outcome types

### 3. Expand Chart Support
- Allow multiple outcome types per sector
- Support different chart types beyond line charts
- Enable custom chart configurations per outcome type

## Implementation Priority
- **High**: Document current limitations for users
- **Medium**: Create configuration system for chart mappings
- **Low**: Implement dynamic outcome discovery system

---
**Status**: ✅ Investigation Complete
**Next Steps**: Consider implementing configurability improvements based on user needs
