<?php
/**
 * Simple Program Details Test
 * Tests if program details page loads without JavaScript
 */

// Include necessary files using correct paths
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/session.php';
require_once __DIR__ . '/app/lib/functions.php';
require_once __DIR__ . '/app/lib/agencies/index.php';
require_once __DIR__ . '/app/lib/agencies/programs.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$program_id) {
    echo "<h2>Error: No program ID provided</h2>";
    echo "<p><a href='app/views/agency/programs/view_programs.php'>Back to Programs</a></p>";
    exit;
}

// Get basic program info
$stmt = $conn->prepare("SELECT p.*, i.initiative_name 
                       FROM programs p 
                       LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id 
                       WHERE p.program_id = ? AND p.is_deleted = 0");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();

if (!$program) {
    echo "<h2>Error: Program not found</h2>";
    echo "<p><a href='app/views/agency/programs/view_programs.php'>Back to Programs</a></p>";
    exit;
}

// Test permission functions
$agency_id = $_SESSION['agency_id'] ?? null;

echo "<h2>Simple Program Details Test</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0;'>";
echo "<h3>Debug Information:</h3>";
echo "<strong>User Info:</strong><br>";
echo "- User ID: " . ($_SESSION['user_id'] ?? 'N/A') . "<br>";
echo "- Agency ID: " . ($agency_id ?? 'N/A') . "<br>";
echo "- Role: " . ($_SESSION['role'] ?? 'N/A') . "<br><br>";

echo "<strong>Program Info:</strong><br>";
echo "- Program ID: " . $program['program_id'] . "<br>";
echo "- Program Name: " . htmlspecialchars($program['program_name']) . "<br>";
echo "- Initiative: " . htmlspecialchars($program['initiative_name'] ?? 'N/A') . "<br>";
echo "- Agency ID: " . $program['agency_id'] . "<br><br>";

// Test permission functions safely
try {
    // Include the program_agency_assignments functions
    require_once __DIR__ . '/app/lib/agencies/program_agency_assignments.php';
    
    echo "<strong>Permission Tests:</strong><br>";
    
    // Test get_user_program_role
    $user_role = get_user_program_role($program_id, $agency_id);
    echo "- User Role for this program: " . ($user_role ?: 'None') . "<br>";
    
    // Test is_program_owner
    $is_owner = is_program_owner($program_id, $agency_id);
    echo "- Is Owner: " . ($is_owner ? 'Yes' : 'No') . "<br>";
    
    // Test can_edit_program if function exists
    if (function_exists('can_edit_program')) {
        $can_edit = can_edit_program($program_id);
        echo "- Can Edit: " . ($can_edit ? 'Yes' : 'No') . "<br>";
    } else {
        echo "- Can Edit: Function not found<br>";
    }
    
    // Test can_view_program if function exists
    if (function_exists('can_view_program')) {
        $can_view = can_view_program($program_id);
        echo "- Can View: " . ($can_view ? 'Yes' : 'No') . "<br>";
    } else {
        echo "- Can View: Function not found<br>";
    }
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>Error testing permissions:</strong><br>";
    echo htmlspecialchars($e->getMessage()) . "<br>";
}

echo "</div>";

if ($program) {
    echo "<h3>Program Details:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>Program Name</td><td>" . htmlspecialchars($program['program_name']) . "</td></tr>";
    echo "<tr><td>Program Number</td><td>" . htmlspecialchars($program['program_number'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>Description</td><td>" . htmlspecialchars($program['program_description'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>Initiative</td><td>" . htmlspecialchars($program['initiative_name'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>Rating</td><td>" . htmlspecialchars($program['rating'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>Start Date</td><td>" . htmlspecialchars($program['start_date'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>End Date</td><td>" . htmlspecialchars($program['end_date'] ?? 'N/A') . "</td></tr>";
    echo "</table>";
}

echo "<hr>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='app/views/agency/programs/view_programs.php' class='btn btn-secondary'>Back to Programs List</a> ";
echo "<a href='app/views/agency/programs/program_details.php?id=" . $program_id . "' class='btn btn-primary'>Try Full Program Details</a>";
echo "</div>";

// Add simple JavaScript test
echo "<script>";
echo "console.log('Simple program details page loaded successfully');";
echo "console.log('Program ID: " . $program_id . "');";
echo "console.log('Agency ID: " . ($agency_id ?? 'null') . "');";
echo "console.log('JavaScript is working - no infinite loops detected');";
echo "</script>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Program Details Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .btn { padding: 8px 16px; margin: 5px; text-decoration: none; color: white; border-radius: 4px; }
        .btn-primary { background-color: #007bff; }
        .btn-secondary { background-color: #6c757d; }
    </style>
</head>
<body>
<!-- Content already echoed above -->
</body>
</html>
