<?php
/**
 * Test Program Template Notification Fix
 * 
 * This test verifies that the program template notification respects edit permissions
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_agency_assignments.php';

echo "<h2>Testing Program Template Notification Fix</h2>";

// Check if user is logged in
if (!is_logged_in()) {
    echo "<p style='color: red;'>❌ User not logged in</p>";
    exit;
}

echo "<p style='color: green;'>✅ User logged in</p>";
echo "<p><strong>Current User:</strong> " . ($_SESSION['name'] ?? 'Unknown') . "</p>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['role'] ?? 'Unknown') . "</p>";
echo "<p><strong>Agency ID:</strong> " . ($_SESSION['agency_id'] ?? 'Not set') . "</p>";

// Get program templates (programs without submissions)
$agency_id = $_SESSION['agency_id'] ?? null;
if ($agency_id) {
    $query = "SELECT DISTINCT p.program_id, p.program_name, p.created_by, paa.role as user_role
              FROM programs p 
              LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id AND paa.agency_id = ? AND paa.is_active = 1
              LEFT JOIN (
                  SELECT DISTINCT program_id 
                  FROM program_submissions 
                  WHERE is_deleted = 0
              ) ps ON p.program_id = ps.program_id
              WHERE p.is_deleted = 0 AND paa.assignment_id IS NOT NULL AND ps.program_id IS NULL
              LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>Program Template Notification Test Results:</h3>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Program ID</th><th>Program Name</th><th>User Role</th><th>Can Edit</th><th>Expected Notification</th><th>Test Link</th></tr>";
        
        while ($program = $result->fetch_assoc()) {
            $program_id = $program['program_id'];
            $can_edit = can_edit_program($program_id);
            $user_role = $program['user_role'] ?? 'No role';
            
            // Determine expected notification based on edit permissions
            if ($can_edit) {
                $expected_notification = 'Template with action link: "Add your first progress report"';
                $notification_color = 'green';
            } else {
                $expected_notification = 'Template without action link: "No progress reports have been added yet"';
                $notification_color = 'blue';
            }
            
            echo "<tr>";
            echo "<td>" . $program_id . "</td>";
            echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user_role) . "</td>";
            echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'>" . ($can_edit ? 'Yes ✅' : 'No ❌') . "</td>";
            echo "<td style='color: " . $notification_color . ";'>" . $expected_notification . "</td>";
            echo "<td><a href='app/views/agency/programs/program_details.php?id=" . $program_id . "' target='_blank'>Test Program Details</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Test Instructions:</h3>";
        echo "<ul>";
        echo "<li><strong>FOR EDITORS (Can Edit = Yes):</strong> Click 'Test Program Details' link. You should see a notification with a clickable 'Add your first progress report' link.</li>";
        echo "<li><strong>FOR VIEWERS (Can Edit = No):</strong> Click 'Test Program Details' link. You should see a notification saying 'No progress reports have been added yet' WITHOUT a clickable link.</li>";
        echo "<li><strong>FIXED ISSUE:</strong> Previously, viewers would see the misleading action link they couldn't actually use.</li>";
        echo "</ul>";
        
        echo "<h3>Before vs After:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
        echo "<tr><th>User Type</th><th>BEFORE (Broken)</th><th>AFTER (Fixed)</th></tr>";
        echo "<tr>";
        echo "<td>Editor</td>";
        echo "<td>✅ Sees notification with action link</td>";
        echo "<td>✅ Sees notification with action link</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Viewer</td>";
        echo "<td>❌ Sees misleading action link they can't use</td>";
        echo "<td>✅ Sees informational notification without action link</td>";
        echo "</tr>";
        echo "</table>";
        
    } else {
        echo "<p>No program templates found for your agency to test with.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No agency ID in session</p>";
}

echo "<br><hr><p><strong>Test completed.</strong> Delete this file after verification.</p>";
?>
