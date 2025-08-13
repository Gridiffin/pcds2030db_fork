<?php
/**
 * Test the timber export chart data flow end-to-end
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

echo "<h2>End-to-End Test: Timber Export Chart Data</h2>\n";

// Simulate what the report_data.php API does
$period_id = 20; // Q2 2025
$sector_id = 1;   // Forestry

// Get period details
$period_query = "SELECT period_id, period_type, period_number, year FROM reporting_periods WHERE period_id = ?";
$stmt = $conn->prepare($period_query);
$stmt->bind_param("i", $period_id);
$stmt->execute();
$period_result = $stmt->get_result();
$period_data = $period_result->fetch_assoc();

$current_year = $period_data['year'];
$previous_year = $current_year - 1;

echo "Testing for period: {$period_data['period_type']} {$period_data['period_number']} {$period_data['year']}<br>\n";
echo "Chart years: {$previous_year} and {$current_year}<br>\n";

// Get outcomes data like the API does
$outcomes = get_all_outcomes();
$outcomes_by_code = [];
foreach ($outcomes as $outcome) {
    $outcomes_by_code[$outcome['code']] = $outcome;
}

// Process timber export data like the API does
$main_chart_data = $outcomes_by_code['timber_export']['data'] ?? [];

// Apply filtering like the API does
if (isset($main_chart_data['columns']) && isset($main_chart_data['rows'])) {
    $years_to_keep = [$previous_year, $current_year];
    $years_to_keep_str = array_map('strval', $years_to_keep);
    
    // Filter columns
    $main_chart_data['columns'] = array_values(array_filter($main_chart_data['columns'], function($col) use ($years_to_keep_str) {
        return in_array($col, $years_to_keep_str);
    }));
    
    // The rows are already in the correct object format
}

// Create the final API response structure
$api_response = [
    'charts' => [
        'main_chart' => [
            'data' => $main_chart_data
        ]
    ]
];

echo "<h3>API Response Structure (what the JavaScript will receive):</h3>\n";
echo "<pre>" . json_encode($api_response, JSON_PRETTY_PRINT) . "</pre>\n";

// Test what the JavaScript chart function logic would do
$has_data = false;
$data_summary = [];

if (isset($main_chart_data['columns']) && isset($main_chart_data['rows'])) {
    foreach ($main_chart_data['rows'] as $row) {
        if (isset($row['month'])) {
            $month_data = [];
            foreach ($main_chart_data['columns'] as $year) {
                $value = isset($row[$year]) ? floatval($row[$year]) : 0;
                $month_data[$year] = $value;
                if ($value > 0) {
                    $has_data = true;
                }
            }
            $data_summary[$row['month']] = $month_data;
        }
    }
}

echo "<h3>Chart Data Summary:</h3>\n";
echo "Has any non-zero data: " . ($has_data ? "✅ YES" : "❌ NO") . "<br>\n";

if ($has_data) {
    echo "<strong>Sample data points with values:</strong><br>\n";
    $count = 0;
    foreach ($data_summary as $month => $year_data) {
        foreach ($year_data as $year => $value) {
            if ($value > 0 && $count < 5) {
                echo "- {$month} {$year}: " . number_format($value, 2) . "<br>\n";
                $count++;
            }
        }
        if ($count >= 5) break;
    }
} else {
    echo "❌ This would show 'No data available for Timber Export Value'<br>\n";
}

echo "<h3>Expected Behavior After Fix:</h3>\n";
echo "✅ Chart should display data for years: " . implode(', ', $main_chart_data['columns']) . "<br>\n";
echo "✅ Chart should show monthly data for " . count($main_chart_data['rows']) . " months<br>\n";
echo "✅ Chart should NOT show 'No data available' message<br>\n";

echo "<br><strong>Test complete!</strong><br>\n";
?>
