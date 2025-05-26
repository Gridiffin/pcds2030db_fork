# Fix Chart View Functionality in View Outcomes Page

**Date:** May 26, 2025  
**Status:** 🔄 **IN PROGRESS**  

## Problem Analysis:
The chart view tab in the view_outcome.php page is not displaying any charts because:
1. Chart.js library is not being loaded (commented out in the code)
2. The outcomes-chart.js script is not being included
3. There's no JavaScript code to initialize the chart with the outcome data

## Solution Steps:

- [ ] Add Chart.js library to the additionalScripts array
- [ ] Include the outcomes-chart.js script in additionalScripts
- [ ] Add JavaScript code to pass the outcome data to the chart initialization function
- [ ] Implement the data formatting functions in outcomes-chart.js
- [ ] Test the chart functionality with real outcome data

## Implementation Details:

### 1. Update view_outcome.php to load required scripts:
```php
// Include JS specific to this page (for charts)
$additionalScripts = [
    APP_URL . '/assets/js/charts/Chart.min.js', // Chart.js library
    APP_URL . '/assets/js/charts/outcomes-chart.js' // Custom chart script
];
```

### 2. Add script to initialize the chart with outcome data:
```php
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pass outcome data to chart initialization function
    const outcomeData = <?= json_encode($outcome_metrics_data) ?>;
    const tableData = <?= json_encode($table_data) ?>;
    const monthNames = <?= json_encode($month_names) ?>;
    const tableName = <?= json_encode($table_name) ?>;
    
    // Initialize the chart if outcome data exists
    if (outcomeData && outcomeData.columns) {
        initOutcomesChart(outcomeData, tableData, monthNames, tableName);
    }
});
</script>
```

### 3. Ensure Chart.js library is available:
- Check if Chart.min.js exists in the assets/js/charts/ directory
- If not, download the latest version from the Chart.js CDN and save it

### 4. Complete the implementation of the chart data formatting functions in outcomes-chart.js