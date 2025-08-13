<?php
/**
 * Debug script to test report data API response for timber export
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_names_helper.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php';

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

echo "<h2>Debug: Report Data API Response for Timber Export</h2>\n";

// Simulate report generation request
$period_id = 20; // Use a current period (Q2 2025 based on the PPTX files found)
$sector_id = 1;   // Forestry

// Get period details
$period_query = "SELECT period_id, period_type, period_number, year FROM reporting_periods WHERE period_id = ?";
$stmt = $conn->prepare($period_query);
$stmt->bind_param("i", $period_id);
$stmt->execute();
$period_result = $stmt->get_result();
$period_data = $period_result->fetch_assoc();

if (!$period_data) {
    echo "❌ Period ID {$period_id} not found. Available periods:<br>\n";
    $periods_query = "SELECT period_id, period_type, period_number, year FROM reporting_periods ORDER BY year DESC, period_number DESC LIMIT 10";
    $periods_result = $conn->query($periods_query);
    while ($period = $periods_result->fetch_assoc()) {
        echo "- Period ID: {$period['period_id']}, {$period['period_type']} {$period['period_number']} {$period['year']}<br>\n";
    }
    exit;
}

echo "✅ Using period: {$period_data['period_type']} {$period_data['period_number']} {$period_data['year']}<br>\n";

$current_year = $period_data['year'];
$previous_year = $current_year - 1;

echo "Years for chart: Current={$current_year}, Previous={$previous_year}<br>\n";

// Get outcomes data
$outcomes = get_all_outcomes();
$outcomes_by_code = [];
foreach ($outcomes as $outcome) {
    $outcomes_by_code[$outcome['code']] = $outcome;
}

// Get timber export data and filter it like the report does
$main_chart_data = $outcomes_by_code['timber_export']['data'] ?? [];
echo "<h3>Original timber export data structure:</h3>\n";
echo "Columns: " . json_encode($main_chart_data['columns'] ?? []) . "<br>\n";
echo "Row count: " . count($main_chart_data['rows'] ?? []) . "<br>\n";

// Apply the same filtering logic as report_data.php
if (!is_array($main_chart_data) || !isset($main_chart_data['columns']) || !isset($main_chart_data['rows'])) {
    echo "❌ Invalid data structure<br>\n";
    $main_chart_data = [
        'columns' => [],
        'rows' => []
    ];
} else {
    echo "✅ Valid data structure<br>\n";
}

// Filter columns and rows for current and previous year only
if (isset($main_chart_data['columns']) && isset($main_chart_data['rows'])) {
    $years_to_keep = [$previous_year, $current_year];
    $years_to_keep_str = array_map('strval', $years_to_keep);
    
    echo "<h3>Filtering for years: " . json_encode($years_to_keep_str) . "</h3>\n";
    
    // Filter columns
    $original_columns = $main_chart_data['columns'];
    $main_chart_data['columns'] = array_values(array_filter($main_chart_data['columns'], function($col) use ($years_to_keep_str) {
        return in_array($col, $years_to_keep_str);
    }));
    
    echo "Original columns: " . json_encode($original_columns) . "<br>\n";
    echo "Filtered columns: " . json_encode($main_chart_data['columns']) . "<br>\n";
    
    // Filter each row's values to only keep the selected years
    foreach ($main_chart_data['rows'] as &$row) {
        if (isset($row['values']) && is_array($row['values'])) {
            $row['values'] = array_intersect_key($row['values'], array_flip($main_chart_data['columns']));
        } else {
            // Check the raw row data for year values
            $filtered_row = [];
            foreach ($years_to_keep_str as $year) {
                if (isset($row[$year])) {
                    $filtered_row[$year] = $row[$year];
                }
            }
            echo "Row for {$row['month']}: " . json_encode($filtered_row) . "<br>\n";
        }
    }
    unset($row);
}

echo "<h3>Final filtered data:</h3>\n";
echo "Columns: " . json_encode($main_chart_data['columns']) . "<br>\n";
echo "Row count: " . count($main_chart_data['rows']) . "<br>\n";

// Check if there's any non-zero data
$has_data = false;
foreach ($main_chart_data['rows'] as $row) {
    foreach ($years_to_keep_str as $year) {
        if (isset($row[$year]) && $row[$year] > 0) {
            $has_data = true;
            echo "✅ Found non-zero data: {$row['month']} {$year} = {$row[$year]}<br>\n";
            break 2;
        }
    }
}

if (!$has_data) {
    echo "❌ No non-zero data found for years " . implode(', ', $years_to_keep_str) . "<br>\n";
    echo "This explains why 'No data available' is shown<br>\n";
}

echo "<br><strong>Debug complete!</strong><br>\n";
?>
