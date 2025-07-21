<?php
/**
 * Temporary test version of view_submissions to debug the blank page issue
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_attachments.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';

echo "TEST: Page loaded successfully<br>";

// Verify user is an agency
if (!is_agency()) {
    echo "TEST: User is not an agency, redirecting...<br>";
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

echo "TEST: User is an agency<br>";

// Get parameters from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

echo "TEST: program_id = $program_id, period_id = $period_id<br>";

// Validate required parameters
if (!$program_id || !$period_id) {
    echo "TEST: Missing parameters, redirecting...<br>";
    $_SESSION['message'] = 'Missing required parameters (program_id and period_id).';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

echo "TEST: Parameters validated<br>";

// Get program details
$program = get_program_details($program_id, true);
if (!$program) {
    echo "TEST: Program not found, redirecting...<br>";
    $_SESSION['message'] = 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

echo "TEST: Program found: " . htmlspecialchars($program['program_name']) . "<br>";

// Check if user has access to this program
if (!can_view_program($program_id)) {
    echo "TEST: User cannot view program, redirecting...<br>";
    $_SESSION['message'] = 'You do not have access to this program.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

echo "TEST: User has access to program<br>";

echo "<h1>Test Successful!</h1>";
echo "<p>Program: " . htmlspecialchars($program['program_name']) . "</p>";
echo "<p>Program ID: $program_id</p>";
echo "<p>Period ID: $period_id</p>";

// Test if we can access the rest of the data
try {
    // Get the specific submission for this program and period
    $submission_query = "SELECT ps.*, 
                                rp.year, rp.period_type, rp.period_number, rp.status as period_status,
                                CONCAT(rp.year, ' ', 
                                       CASE 
                                           WHEN rp.period_type = 'quarterly' THEN CONCAT('Q', rp.period_number)
                                           ELSE CONCAT(UPPER(LEFT(rp.period_type, 1)), SUBSTRING(rp.period_type, 2), ' ', rp.period_number)
                                       END
                                ) as period_display
                         FROM program_submissions ps
                         LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
                         WHERE ps.program_id = ? AND ps.period_id = ? AND ps.is_deleted = 0
                         ORDER BY ps.updated_at DESC
                         LIMIT 1";

    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();

    if ($submission) {
        echo "<p>Submission found: " . htmlspecialchars($submission['period_display']) . "</p>";
    } else {
        echo "<p>No submission found for this program and period</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='view_programs.php'>Back to Programs</a></p>";
?>
