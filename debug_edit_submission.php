<?php
/**
 * Debug Edit Submission
 * Test what's happening with edit submission data
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/admin_edit_submission_data.php';

echo "<h2>Debug: Admin Edit Submission Data</h2>";

// Check session and authentication
echo "<h3>Session Check:</h3>";
echo "Session started: " . (session_id() ? 'Yes' : 'No') . "<br>";
echo "User logged in: " . (isset($_SESSION['user_id']) ? 'Yes (User ID: ' . $_SESSION['user_id'] . ')' : 'No') . "<br>";
echo "User role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
echo "Agency ID: " . ($_SESSION['agency_id'] ?? 'Not set') . "<br>";

// Test parameters
$program_id = $_GET['program_id'] ?? 1; // Default to first program
$period_id = $_GET['period_id'] ?? 1;   // Default to first period

echo "<h3>Request Parameters:</h3>";
echo "Program ID: " . htmlspecialchars($program_id) . "<br>";
echo "Period ID: " . htmlspecialchars($period_id) . "<br>";

// Test functions exist
echo "<h3>Function Availability:</h3>";
echo "get_admin_edit_submission_data: " . (function_exists('get_admin_edit_submission_data') ? 'Yes' : 'No') . "<br>";
echo "get_period_display_name: " . (function_exists('get_period_display_name') ? 'Yes' : 'No') . "<br>";
echo "format_file_size: " . (function_exists('format_file_size') ? 'Yes' : 'No') . "<br>";
echo "is_admin: " . (function_exists('is_admin') ? 'Yes' : 'No') . "<br>";

// Test database connection
echo "<h3>Database Connection:</h3>";
if (isset($conn) && $conn) {
    echo "Database connected: Yes<br>";
    
    // Test basic queries
    echo "<h4>Database Tests:</h4>";
    
    // Test agency table
    $agency_test = $conn->query("SELECT COUNT(*) as count FROM agency");
    if ($agency_test) {
        $agency_count = $agency_test->fetch_assoc();
        echo "Agency table: " . $agency_count['count'] . " records<br>";
    } else {
        echo "Agency table: Error - " . $conn->error . "<br>";
    }
    
    // Test programs table
    $programs_test = $conn->query("SELECT COUNT(*) as count FROM programs WHERE is_deleted = 0");
    if ($programs_test) {
        $programs_count = $programs_test->fetch_assoc();
        echo "Programs table: " . $programs_count['count'] . " records<br>";
    } else {
        echo "Programs table: Error - " . $conn->error . "<br>";
    }
    
    // Test reporting_periods table
    $periods_test = $conn->query("SELECT COUNT(*) as count FROM reporting_periods");
    if ($periods_test) {
        $periods_count = $periods_test->fetch_assoc();
        echo "Reporting periods table: " . $periods_count['count'] . " records<br>";
    } else {
        echo "Reporting periods table: Error - " . $conn->error . "<br>";
    }
    
} else {
    echo "Database connected: No<br>";
    if (isset($conn)) {
        echo "Connection error: " . $conn->connect_error . "<br>";
    }
}

// Test the admin edit submission data function
echo "<h3>Function Test:</h3>";
try {
    $edit_data = get_admin_edit_submission_data($program_id, $period_id);
    
    if ($edit_data) {
        echo "<p style='color: green;'>Data retrieved successfully!</p>";
        echo "<h4>Program Data:</h4>";
        echo "Program ID: " . htmlspecialchars($edit_data['program']['program_id'] ?? 'Not set') . "<br>";
        echo "Program Name: " . htmlspecialchars($edit_data['program']['program_name'] ?? 'Not set') . "<br>";
        echo "Agency Name: " . htmlspecialchars($edit_data['agency_info']['agency_name'] ?? 'Not set') . "<br>";
        echo "Period Display: " . htmlspecialchars($edit_data['period']['period_display'] ?? 'Not set') . "<br>";
        echo "Is New Submission: " . ($edit_data['is_new_submission'] ? 'Yes' : 'No') . "<br>";
        
        echo "<h4>Raw Data Sample:</h4>";
        echo "<pre>";
        print_r(array_keys($edit_data));
        echo "</pre>";
        
    } else {
        echo "<p style='color: red;'>No data returned - function returned null</p>";
        
        // Test raw program query
        echo "<h4>Raw Program Query Test:</h4>";
        $stmt = $conn->prepare("SELECT p.*, a.agency_name FROM programs p LEFT JOIN agency a ON p.agency_id = a.agency_id WHERE p.program_id = ? AND p.is_deleted = 0");
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($program = $result->fetch_assoc()) {
            echo "Raw program found: " . htmlspecialchars($program['program_name']) . "<br>";
            echo "Agency: " . htmlspecialchars($program['agency_name']) . "<br>";
        } else {
            echo "No program found with ID " . $program_id . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace:</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h3>Available Programs:</h3>";
$programs_query = $conn->query("SELECT program_id, program_name, agency_id FROM programs WHERE is_deleted = 0 LIMIT 5");
if ($programs_query) {
    while ($prog = $programs_query->fetch_assoc()) {
        echo "ID: " . $prog['program_id'] . " - " . htmlspecialchars($prog['program_name']) . " (Agency: " . $prog['agency_id'] . ")<br>";
    }
}

echo "<h3>Available Periods:</h3>";
$periods_query = $conn->query("SELECT period_id, period_type, period_number, year FROM reporting_periods LIMIT 5");
if ($periods_query) {
    while ($per = $periods_query->fetch_assoc()) {
        echo "ID: " . $per['period_id'] . " - " . $per['period_type'] . " " . $per['period_number'] . "/" . $per['year'] . "<br>";
    }
}
?>
