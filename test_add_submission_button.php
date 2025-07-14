<?php
/**
 * Test file to verify the new "Add Submission" button functionality
 * for editors in the view_programs page
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

echo "<h2>Testing Add Submission Button for Editors</h2>";

// Check if user is logged in
if (!is_logged_in()) {
    echo "<p style='color: red;'>❌ User not logged in</p>";
    exit;
}

echo "<p style='color: green;'>✅ User logged in</p>";
echo "<p><strong>Current User:</strong> " . ($_SESSION['name'] ?? 'Unknown') . "</p>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['role'] ?? 'Unknown') . "</p>";
echo "<p><strong>Agency ID:</strong> " . ($_SESSION['agency_id'] ?? 'Not set') . "</p>";

// Get a sample program without submissions (program template)
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
    
    echo "<h3>Programs Without Submissions (Templates):</h3>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Program ID</th><th>Program Name</th><th>Created By Current User</th><th>User Role</th><th>Can Edit</th><th>Show Add Submission Button</th><th>Show Three Dots Button</th></tr>";
        
        while ($program = $result->fetch_assoc()) {
            $program_id = $program['program_id'];
            $is_creator = isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id'];
            $can_edit = can_edit_program($program_id);
            $user_role = $program['user_role'] ?? 'No role';
            
            echo "<tr>";
            echo "<td>" . $program_id . "</td>";
            echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
            echo "<td>" . ($is_creator ? 'Yes' : 'No') . "</td>";
            echo "<td>" . htmlspecialchars($user_role) . "</td>";
            echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'>" . ($can_edit ? 'Yes ✅' : 'No ❌') . "</td>";
            echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'>" . ($can_edit ? 'SHOW BUTTON ✅' : 'HIDE BUTTON ❌') . "</td>";
            echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'>" . ($can_edit ? 'SHOW THREE DOTS ✅' : 'HIDE THREE DOTS ❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Test Summary:</h3>";
        echo "<ul>";
        echo "<li><strong>Expected Behavior:</strong> 'Add Submission' button should show for users with edit permissions (owner/editor role)</li>";
        echo "<li><strong>Previous Behavior:</strong> Button only showed for program creators</li>";
        echo "<li><strong>New Behavior:</strong> Button shows for any user with edit permissions, regardless of who created the program</li>";
        echo "<li><strong>NEW: Three Dots Button:</strong> Now shows for all editors on programs with submissions (draft/finalized)</li>";
        echo "</ul>";
        
        // Test programs WITH submissions (draft/finalized) for three dots button
        echo "<h3>Programs WITH Submissions (for Three Dots Button Test):</h3>";
        $query_with_submissions = "SELECT DISTINCT p.program_id, p.program_name, p.created_by, paa.role as user_role
                  FROM programs p 
                  LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id AND paa.agency_id = ? AND paa.is_active = 1
                  INNER JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.is_deleted = 0
                  WHERE p.is_deleted = 0 AND paa.assignment_id IS NOT NULL
                  LIMIT 5";
        
        $stmt2 = $conn->prepare($query_with_submissions);
        $stmt2->bind_param("i", $agency_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if ($result2->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Program ID</th><th>Program Name</th><th>Created By Current User</th><th>User Role</th><th>Can Edit</th><th>Show Three Dots Button</th></tr>";
            
            while ($program = $result2->fetch_assoc()) {
                $program_id = $program['program_id'];
                $is_creator = isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id'];
                $can_edit = can_edit_program($program_id);
                $user_role = $program['user_role'] ?? 'No role';
                
                echo "<tr>";
                echo "<td>" . $program_id . "</td>";
                echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
                echo "<td>" . ($is_creator ? 'Yes' : 'No') . "</td>";
                echo "<td>" . htmlspecialchars($user_role) . "</td>";
                echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'>" . ($can_edit ? 'Yes ✅' : 'No ❌') . "</td>";
                echo "<td style='color: " . ($can_edit ? 'green' : 'red') . ";'>" . ($can_edit ? 'SHOW THREE DOTS ✅' : 'HIDE THREE DOTS ❌') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No programs with submissions found for your agency.</p>";
        }
        
    } else {
        echo "<p>No program templates found for your agency.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No agency ID in session</p>";
}

echo "<br><hr><p><strong>Test completed.</strong> Delete this file after verification.</p>";
?>
