<?php
/**
 * Debug Save Draft Functionality
 * 
 * Test the save_submission.php AJAX endpoint directly
 */

// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/session.php';
require_once __DIR__ . '/app/lib/functions.php';

echo "=== SAVE DRAFT DEBUG ===<br>";

// Set up a test session (logged in user)
$_SESSION['user_id'] = 12;
$_SESSION['role'] = 'focal';
$_SESSION['agency_id'] = 5;

echo "Session set: user_id=" . $_SESSION['user_id'] . ", role=" . $_SESSION['role'] . "<br>";

// Test if authentication functions work
echo "is_agency(): " . (function_exists('is_agency') ? (is_agency() ? 'TRUE' : 'FALSE') : 'function not found') . "<br>";
echo "is_logged_in(): " . (function_exists('is_logged_in') ? (is_logged_in() ? 'TRUE' : 'FALSE') : 'function not found') . "<br>";

if ($_POST) {
    echo "<br>=== PROCESSING SAVE REQUEST ===<br>";
    
    // Simulate the save_submission.php request
    $program_id = intval($_POST['program_id'] ?? 0);
    $period_id = intval($_POST['period_id'] ?? 0);
    $submission_id = intval($_POST['submission_id'] ?? 0);
    
    echo "Program ID: $program_id<br>";
    echo "Period ID: $period_id<br>";
    echo "Submission ID: $submission_id<br>";
    
    if ($program_id && $period_id) {
        echo "<br>Calling save_submission.php via include...<br>";
        
        // Capture output from save_submission.php
        ob_start();
        
        // Set up POST data for the included script
        $_POST['program_id'] = $program_id;
        $_POST['period_id'] = $period_id;
        $_POST['submission_id'] = $submission_id;
        $_POST['submission_data'] = json_encode(['test' => 'data']);
        $_POST['is_draft'] = '1';
        
        try {
            // Change directory context to match AJAX call
            $old_cwd = getcwd();
            chdir(__DIR__ . '/app/ajax');
            
            include './save_submission.php';
            
            chdir($old_cwd);
            
            $output = ob_get_contents();
            ob_end_clean();
            
            echo "Output from save_submission.php:<br>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
            
        } catch (Exception $e) {
            ob_end_clean();
            echo "ERROR: " . $e->getMessage() . "<br>";
        } catch (Error $e) {
            ob_end_clean();
            echo "FATAL ERROR: " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "❌ Program ID and Period ID required<br>";
    }
}
?>

<h3>Test Save Draft</h3>
<form method="post">
    <label>Program ID: <input type="number" name="program_id" value="1" required></label><br><br>
    <label>Period ID: <input type="number" name="period_id" value="1" required></label><br><br>
    <label>Submission ID (optional): <input type="number" name="submission_id" value="" placeholder="Leave empty for new submission"></label><br><br>
    <button type="submit">Test Save Draft</button>
</form>

<p><strong>Instructions:</strong></p>
<ol>
    <li>Upload this file to your server</li>
    <li>Use valid Program ID and Period ID from your database</li>
    <li>Click "Test Save Draft" to see what happens</li>
    <li>This will show you the exact error or output from save_submission.php</li>
</ol>