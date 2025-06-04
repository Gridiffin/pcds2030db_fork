<?php


if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/controllers/DashboardController.php';

if (!is_agency()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$program_id = $_POST['program_id'] ?? null;

if (!$program_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid program ID']);
    exit;
}

try {
    // Get current reporting period
    $current_period = get_current_reporting_period();
    
    if (!$current_period) {
        echo json_encode(['status' => 'error', 'message' => 'No active reporting period found']);
        exit;    }
    
    // First, validate that the program has content to submit
    $validation_query = "SELECT content_json FROM program_submissions 
                        WHERE program_id = ? AND period_id = ? 
                        ORDER BY submission_date DESC, submission_id DESC 
                        LIMIT 1";
    $validation_stmt = $conn->prepare($validation_query);
    $validation_stmt->bind_param("ii", $program_id, $period_id);
    $validation_stmt->execute();
    $validation_result = $validation_stmt->get_result();
    
    if ($validation_result->num_rows > 0) {
        $validation_row = $validation_result->fetch_assoc();
        $existing_content = $validation_row['content_json'];
        
        // Validate content exists and has required fields
        if (empty($existing_content) || $existing_content === 'null') {
            echo json_encode(['status' => 'error', 'message' => 'Cannot submit program without content. Please add targets and rating first.']);
            exit;
        }
        
        $content_data = json_decode($existing_content, true);
        if (!$content_data || (empty($content_data['targets']) && empty($content_data['target'])) || empty($content_data['rating'])) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot submit program without targets and rating. Please complete the program details first.']);
            exit;
        }
    }
    
    // Update the submission for this program in the current period
    $query = "UPDATE program_submissions 
              SET is_draft = 0, 
                  submission_date = NOW() 
              WHERE program_id = ? 
              AND period_id = ?";
              
    $stmt = $conn->prepare($query);
    $period_id = $current_period['period_id'];
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Program submitted successfully']);
    } else {
        // Check if submission exists
        $check_query = "SELECT submission_id FROM program_submissions WHERE program_id = ? AND period_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $program_id, $period_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
          if ($result->num_rows === 0) {
            // Create a new submission if one doesn't exist
            // Get the current user ID from the session
            $submitted_by = $_SESSION['user_id'];
              // First, get the content_json from the most recent submission/draft for this program
            $content_query = "SELECT content_json FROM program_submissions 
                             WHERE program_id = ? 
                             ORDER BY submission_date DESC, submission_id DESC 
                             LIMIT 1";
            $content_stmt = $conn->prepare($content_query);
            $content_stmt->bind_param("i", $program_id);
            $content_stmt->execute();
            $content_result = $content_stmt->get_result();
            
            $content_json = null;
            if ($content_result->num_rows > 0) {
                $content_row = $content_result->fetch_assoc();
                $content_json = $content_row['content_json'];
            }
            
            // Validate that we have meaningful content to submit
            if (empty($content_json) || $content_json === 'null') {
                echo json_encode(['status' => 'error', 'message' => 'Cannot submit program without content. Please add targets and rating first.']);
                exit;
            }
            
            // Additional validation: check if content has required fields
            $content_data = json_decode($content_json, true);
            if (!$content_data || (empty($content_data['targets']) && empty($content_data['target'])) || empty($content_data['rating'])) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot submit program without targets and rating. Please complete the program details first.']);
                exit;
            }
            
            $insert_query = "INSERT INTO program_submissions (program_id, period_id, is_draft, submission_date, submitted_by, content_json) 
                            VALUES (?, ?, 0, NOW(), ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iiis", $program_id, $period_id, $submitted_by, $content_json);
            $insert_stmt->execute();
            
            if ($insert_stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Program submitted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create submission']);
            }
        } else {
            echo json_encode(['status' => 'info', 'message' => 'Program was already submitted']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
