<?php
/**
 * Test file to verify the fixed permission display in view_submissions
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

echo "<h2>Testing View Submissions Permission Display Fix</h2>";

// Check if user is logged in
if (!is_logged_in()) {
    echo "<p style='color: red;'>❌ User not logged in</p>";
    exit;
}

echo "<p style='color: green;'>✅ User logged in</p>";
echo "<p><strong>Current User:</strong> " . ($_SESSION['name'] ?? 'Unknown') . "</p>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['role'] ?? 'Unknown') . "</p>";
echo "<p><strong>Agency ID:</strong> " . ($_SESSION['agency_id'] ?? 'Not set') . "</p>";

// Get programs with submissions to test permission display
$agency_id = $_SESSION['agency_id'] ?? null;
if ($agency_id) {
    $query = "SELECT DISTINCT p.program_id, p.program_name
              FROM programs p 
              LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id AND paa.agency_id = ? AND paa.is_active = 1
              INNER JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.is_deleted = 0
              WHERE p.is_deleted = 0 AND paa.assignment_id IS NOT NULL
              LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>Programs with Submissions - Permission Display Test:</h3>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Program ID</th><th>Program Name</th><th>Agency Role</th><th>Effective User Role</th><th>Can View</th><th>Can Edit</th><th>Is Owner</th><th>View Submissions Link</th></tr>";
        
        while ($program = $result->fetch_assoc()) {
            $program_id = $program['program_id'];
            
            // Get agency-level role (what was causing the bug)
            $agency_role = get_user_program_role($program_id);
            
            // Get actual permissions
            $can_view = can_view_program($program_id);
            $can_edit = can_edit_program($program_id);
            $is_owner = is_program_owner($program_id);
            
            // Determine effective user role (the fix)
            if ($is_owner) {
                $effective_role = 'owner';
            } elseif ($can_edit) {
                $effective_role = 'editor';
            } elseif ($can_view) {
                $effective_role = 'viewer';
            } else {
                $effective_role = 'no access';
            }
            
            echo "<tr>";
            echo "<td>" . $program_id . "</td>";
            echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
            echo "<td><strong>" . ($agency_role ?: 'None') . "</strong></td>";
            echo "<td style='color: " . ($effective_role !== $agency_role ? 'orange' : 'green') . ";'><strong>" . ucfirst($effective_role) . "</strong></td>";
            echo "<td>" . ($can_view ? '✅' : '❌') . "</td>";
            echo "<td>" . ($can_edit ? '✅' : '❌') . "</td>";
            echo "<td>" . ($is_owner ? '✅' : '❌') . "</td>";
            echo "<td><a href='" . APP_URL . "/app/views/agency/programs/view_submissions.php?program_id=" . $program_id . "' target='_blank'>Test View</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Fix Summary:</h3>";
        echo "<ul>";
        echo "<li><strong>Problem:</strong> 'Access Level' notification was showing agency-level role instead of effective user role</li>";
        echo "<li><strong>Root Cause:</strong> Using <code>get_user_program_role()</code> which returns agency assignment, not considering user-level restrictions</li>";
        echo "<li><strong>Solution:</strong> Calculate effective role based on actual permission functions (<code>is_owner</code>, <code>can_edit</code>, <code>can_view</code>)</li>";
        echo "<li><strong>Result:</strong> Access level notification now shows the correct role that matches user's actual permissions</li>";
        echo "</ul>";
        
        echo "<h3>Test Instructions:</h3>";
        echo "<ol>";
        echo "<li>Click any 'Test View' link above</li>";
        echo "<li>Check the 'Access Level' notification</li>";
        echo "<li>It should now show '" . ucfirst($effective_role) . "' instead of '" . ($agency_role ?: 'None') . "'</li>";
        echo "<li>The notification should match your actual permissions (buttons/actions available)</li>";
        echo "</ol>";
        
    } else {
        echo "<p>No programs with submissions found for your agency.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No agency ID in session</p>";
}

echo "<br><hr><p><strong>Test completed.</strong> Delete this file after verification.</p>";
?>
