<?php
/**
 * Debug test for view_submissions permission issue
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

echo "<h2>Debug: View Submissions Permission Issue</h2>";

// Check if user is logged in
if (!is_logged_in()) {
    echo "<p style='color: red;'>❌ User not logged in</p>";
    exit;
}

echo "<p style='color: green;'>✅ User logged in</p>";
echo "<p><strong>Current User:</strong> " . ($_SESSION['name'] ?? 'Unknown') . "</p>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['role'] ?? 'Unknown') . "</p>";
echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p><strong>Agency ID:</strong> " . ($_SESSION['agency_id'] ?? 'Not set') . "</p>";

// Get a program ID to test with
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if ($program_id > 0) {
    echo "<h3>Testing Program ID: $program_id</h3>";
    
    // Test each permission function individually
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Permission Function</th><th>Result</th><th>Details</th></tr>";
    
    // Test agency role
    $agency_role = get_user_program_role($program_id);
    echo "<tr>";
    echo "<td><code>get_user_program_role($program_id)</code></td>";
    echo "<td><strong>" . ($agency_role ?: 'false/null') . "</strong></td>";
    echo "<td>Agency-level role from program_agency_assignments table</td>";
    echo "</tr>";
    
    // Test view permission
    $can_view = can_view_program($program_id);
    echo "<tr>";
    echo "<td><code>can_view_program($program_id)</code></td>";
    echo "<td style='color: " . ($can_view ? 'green' : 'red') . ";'><strong>" . ($can_view ? 'TRUE' : 'FALSE') . "</strong></td>";
    echo "<td>Can user view this program?</td>";
    echo "</tr>";
    
    // Test edit permission
    $can_edit = can_edit_program($program_id);
    echo "<tr>";
    echo "<td><code>can_edit_program($program_id)</code></td>";
    echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'><strong>" . ($can_edit ? 'TRUE' : 'FALSE') . "</strong></td>";
    echo "<td>Can user edit this program?</td>";
    echo "</tr>";
    
    // Test owner permission
    $is_owner = is_program_owner($program_id);
    echo "<tr>";
    echo "<td><code>is_program_owner($program_id)</code></td>";
    echo "<td style='color: " . ($is_owner ? 'green' : 'red') . ";'><strong>" . ($is_owner ? 'TRUE' : 'FALSE') . "</strong></td>";
    echo "<td>Is user the program owner?</td>";
    echo "</tr>";
    
    echo "</table>";
    
    // Calculate effective role using the same logic as view_submissions.php
    if ($is_owner) {
        $effective_role = 'owner';
    } elseif ($can_edit) {
        $effective_role = 'editor';
    } elseif ($can_view) {
        $effective_role = 'viewer';
    } else {
        $effective_role = 'no access';
    }
    
    echo "<h3>Effective Role Calculation:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;'>";
    echo "<strong>Expected display in view_submissions.php:</strong><br>";
    echo "<strong>Access Level: " . ucfirst($effective_role) . "</strong><br>";
    echo "<small>You have $effective_role access to this program.</small>";
    echo "</div>";
    
    if ($effective_role === 'owner' && !$is_owner) {
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 10px;'>";
        echo "<strong>⚠️ WARNING:</strong> There's a logic error! The effective role is 'owner' but is_program_owner() returned FALSE.";
        echo "</div>";
    }
    
    echo "<h3>Test View Submissions Page:</h3>";
    echo "<p><a href='" . APP_URL . "/app/views/agency/programs/view_submissions.php?program_id=$program_id&period_id=1' target='_blank'>Test View Submissions for Program $program_id</a></p>";
    
} else {
    // Show available programs to test with
    $agency_id = $_SESSION['agency_id'] ?? null;
    if ($agency_id) {
        $query = "SELECT DISTINCT p.program_id, p.program_name
                  FROM programs p 
                  LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id AND paa.agency_id = ? AND paa.is_active = 1
                  WHERE p.is_deleted = 0 AND paa.assignment_id IS NOT NULL
                  LIMIT 5";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<h3>Available Programs to Test:</h3>";
        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($program = $result->fetch_assoc()) {
                echo "<li><a href='?program_id=" . $program['program_id'] . "'>";
                echo "Program " . $program['program_id'] . ": " . htmlspecialchars($program['program_name']);
                echo "</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No programs found for your agency.</p>";
        }
    }
}

echo "<br><hr><p><strong>Debug completed.</strong> Delete this file after verification.</p>";
?>
