<?php
/**
 * Debug Script for View Programs
 * Tests the database query and data retrieval for programs
 */

// Include necessary files using correct paths
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/session.php';
require_once __DIR__ . '/app/lib/functions.php';
require_once __DIR__ . '/app/lib/agencies/index.php';
require_once __DIR__ . '/app/lib/agencies/program_agency_assignments.php';

echo "<h2>Debug: View Programs Data Retrieval</h2>\n";

// Check if user is logged in
if (!is_logged_in()) {
    echo "<p style='color: red;'>❌ User is not logged in</p>\n";
    echo "<p><a href='login.php'>Login</a></p>\n";
    exit;
}

// Check if user is agency
if (!is_agency()) {
    echo "<p style='color: red;'>❌ User is not an agency user</p>\n";
    echo "<p>Current role: " . ($_SESSION['role'] ?? 'unknown') . "</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ User is logged in and is agency user</p>\n";

// Get agency_id
$agency_id = $_SESSION['agency_id'] ?? null;
echo "<p><strong>Agency ID:</strong> " . ($agency_id ?? 'NULL') . "</p>\n";

if ($agency_id === null) {
    echo "<p style='color: red;'>❌ agency_id is NULL in session</p>\n";
    exit;
}

// Test the query
echo "<h3>Testing Database Query</h3>\n";

$query = "SELECT DISTINCT p.*, 
                 i.initiative_name,
                 i.initiative_number,
                 i.initiative_id,
                 latest_sub.is_draft,
                 latest_sub.period_id,
                 latest_sub.submission_id as latest_submission_id,
                 latest_sub.submitted_at,
                 rp.period_type,
                 rp.period_number,
                 rp.year as period_year,
                 COALESCE(latest_sub.submitted_at, p.created_at) as updated_at,
                 paa.role as user_role
          FROM programs p 
          LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
          LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id AND paa.agency_id = ? AND paa.is_active = 1
          LEFT JOIN (
              SELECT ps1.*
              FROM program_submissions ps1
              INNER JOIN (
                  SELECT program_id, MAX(submission_id) as max_submission_id
                  FROM program_submissions
                  WHERE is_deleted = 0
                  GROUP BY program_id
              ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
          ) latest_sub ON p.program_id = latest_sub.program_id
          LEFT JOIN reporting_periods rp ON latest_sub.period_id = rp.period_id
          WHERE p.is_deleted = 0 AND paa.assignment_id IS NOT NULL
          ORDER BY p.program_name";

try {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo "<p style='color: red;'>❌ Failed to prepare statement: " . $conn->error . "</p>\n";
        exit;
    }
    
    $stmt->bind_param("i", $agency_id);
    $result = $stmt->execute();
    
    if (!$result) {
        echo "<p style='color: red;'>❌ Failed to execute query: " . $stmt->error . "</p>\n";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Query executed successfully</p>\n";
    
    $result = $stmt->get_result();
    $programs = [];
    $count = 0;
    
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
        $count++;
    }
    
    echo "<p><strong>Programs found:</strong> " . $count . "</p>\n";
    
    if ($count > 0) {
        echo "<h4>Program Details:</h4>\n";
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>ID</th><th>Name</th><th>Initiative</th><th>Role</th><th>Has Submission</th></tr>\n";
        foreach ($programs as $program) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($program['program_id']) . "</td>";
            echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
            echo "<td>" . htmlspecialchars($program['initiative_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($program['user_role'] ?? 'N/A') . "</td>";
            echo "<td>" . ($program['latest_submission_id'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Test if PHP errors are being displayed
    echo "<h3>PHP Error Reporting</h3>\n";
    echo "<p>Error reporting level: " . error_reporting() . "</p>\n";
    echo "<p>Display errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception occurred: " . $e->getMessage() . "</p>\n";
}

echo "<hr>\n";
echo "<p><a href='app/views/agency/programs/view_programs.php'>Try View Programs Page</a></p>\n";
echo "<p><a href='login.php'>Logout and Login Again</a></p>\n";
?>
