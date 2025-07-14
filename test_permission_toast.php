<?php
/**
 * Test file to verify permission-based toast notifications
 * for programs with submissions
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

echo "<h2>Testing Permission-Based Toast Notifications</h2>";

// Check if user is logged in
if (!is_logged_in()) {
    echo "<p style='color: red;'>❌ User not logged in</p>";
    exit;
}

echo "<p style='color: green;'>✅ User logged in</p>";
echo "<p><strong>Current User:</strong> " . ($_SESSION['name'] ?? 'Unknown') . "</p>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['role'] ?? 'Unknown') . "</p>";
echo "<p><strong>Agency ID:</strong> " . ($_SESSION['agency_id'] ?? 'Not set') . "</p>";

// Get programs with submissions to test toast behavior
$agency_id = $_SESSION['agency_id'] ?? null;
if ($agency_id) {
    $query = "SELECT DISTINCT p.program_id, p.program_name, p.created_by, paa.role as user_role,
                     ps.is_submitted, ps.is_draft
              FROM programs p 
              LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id AND paa.agency_id = ? AND paa.is_active = 1
              INNER JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.is_deleted = 0
              WHERE p.is_deleted = 0 AND paa.assignment_id IS NOT NULL
              LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>Programs WITH Submissions (Toast Notification Test):</h3>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Program ID</th><th>Program Name</th><th>User Role</th><th>Can Edit</th><th>Is Draft</th><th>Toast Behavior</th></tr>";
        
        while ($program = $result->fetch_assoc()) {
            $program_id = $program['program_id'];
            $can_edit = can_edit_program($program_id);
            $user_role = $program['user_role'] ?? 'No role';
            $is_draft = $program['is_draft'] == 1;
            
            // Determine expected toast behavior
            $toast_behavior = '';
            if ($is_draft) {
                $toast_behavior = $can_edit ? 
                    '🟠 Draft Toast WITH "Edit & Submit" button' : 
                    '🟠 Draft Toast WITHOUT button (info only)';
            } else {
                $toast_behavior = 'No draft toast (program submitted)';
            }
            
            echo "<tr>";
            echo "<td>" . $program_id . "</td>";
            echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user_role) . "</td>";
            echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'>" . ($can_edit ? 'Yes ✅' : 'No ❌') . "</td>";
            echo "<td style='color: " . ($is_draft ? 'orange' : 'green') . ";'>" . ($is_draft ? 'Yes (Draft)' : 'No (Submitted)') . "</td>";
            echo "<td>" . $toast_behavior . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Expected Toast Behavior Summary:</h3>";
        echo "<ul>";
        echo "<li><strong>Editors (owner/editor/focal):</strong> See toast notifications WITH action buttons for actionable items</li>";
        echo "<li><strong>Viewers (viewer role):</strong> See toast notifications WITHOUT action buttons (informational only)</li>";
        echo "<li><strong>Draft Programs:</strong> Show 'Draft Submission' toast</li>";
        echo "<li><strong>Programs without targets:</strong> Show 'No Targets' toast (if applicable)</li>";
        echo "<li><strong>Security:</strong> Viewers never see action buttons they cannot use</li>";
        echo "</ul>";
        
        echo "<h3>Test Instructions:</h3>";
        echo "<ol>";
        echo "<li>Navigate to a program details page for a draft program</li>";
        echo "<li>If you're an editor: Should see orange toast with 'Edit & Submit' button</li>";
        echo "<li>If you're a viewer: Should see orange toast with message only, no button</li>";
        echo "<li>Click the program link above to test: <strong>";
        if ($result->num_rows > 0) {
            // Reset result to get first program
            $stmt->execute();
            $first_program = $stmt->get_result()->fetch_assoc();
            echo "<a href='" . APP_URL . "/app/views/agency/programs/program_details.php?id=" . $first_program['program_id'] . "' target='_blank'>";
            echo "Test Program: " . htmlspecialchars($first_program['program_name']);
            echo "</a>";
        }
        echo "</strong></li>";
        echo "</ol>";
        
    } else {
        echo "<p>No programs with submissions found for your agency.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No agency ID in session</p>";
}

echo "<br><hr><p><strong>Test completed.</strong> Delete this file after verification.</p>";
?>
