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
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';
require_once PROJECT_ROOT_PATH . 'app/lib/outcome_automation.php';

if (!is_agency()) {
    // Log unauthorized access attempt
    log_audit_action(
        'program_submit_unauthorized',
        "Unauthorized program submission attempt for program ID: {$program_id}",
        'failure'
    );
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$program_id = $_POST['program_id'] ?? null;

if (!$program_id) {
    // Log invalid program ID attempt
    log_audit_action(
        'program_submit_invalid_id',
        'Program submission attempted with invalid/missing program ID',
        'failure',
        $_SESSION['user_id'] ?? null
    );
    echo json_encode(['status' => 'error', 'message' => 'Invalid program ID']);
    exit;
}

try {
    // Get current reporting period
    $current_period = get_current_reporting_period();
      if (!$current_period) {
        // Log no active period error
        log_audit_action(
            'program_submit_no_period',
            "Program submission failed - no active reporting period found (Program ID: {$program_id})",
            'failure',
            $_SESSION['user_id'] ?? null
        );
        echo json_encode(['status' => 'error', 'message' => 'No active reporting period found']);
        exit;    
    }
    $period_id = $current_period['period_id']; // Moved this line here

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
            // Log content validation failure
            log_audit_action(
                'program_submit_no_content',
                "Program submission failed - no content available (Program ID: {$program_id}, Period ID: {$period_id})",
                'failure',
                $_SESSION['user_id'] ?? null
            );
            echo json_encode(['status' => 'error', 'message' => 'Cannot submit program without content. Please add targets and rating first.']);
            exit;
        }
        $content_data = json_decode($existing_content, true);
        if (!$content_data || (empty($content_data['targets']) && empty($content_data['target'])) || empty($content_data['rating'])) {
            // Log validation failure for incomplete content
            log_audit_action(
                'program_submit_incomplete_content',
                "Program submission failed - incomplete content (missing targets/rating) (Program ID: {$program_id}, Period ID: {$period_id})",
                'failure',
                $_SESSION['user_id'] ?? null
            );
            echo json_encode(['status' => 'error', 'message' => 'Cannot submit program without targets and rating. Please complete the program details first.']);
            exit;
        }
    } else { // Added else to handle case where no validation result is found
        // Log content validation failure - no prior submission found
        log_audit_action(
            'program_submit_no_prior_submission',
            "Program submission failed - no prior submission or draft found to validate content (Program ID: {$program_id}, Period ID: {$period_id})",
            'failure',
            $_SESSION['user_id'] ?? null
        );
        echo json_encode(['status' => 'error', 'message' => 'Cannot submit program. No prior submission or draft found to validate content.']);
        exit;
    }
    
    // CASCADING SUBMISSION LOGIC: Finalize ALL other drafts for this program (any period)
    $cascade_query = "UPDATE program_submissions 
                      SET is_draft = 0, 
                          submission_date = NOW() 
                      WHERE program_id = ? 
                      AND is_draft = 1 
                      AND period_id != ?";
    
    $cascade_stmt = $conn->prepare($cascade_query);
    $cascade_stmt->bind_param("ii", $program_id, $period_id);
    $cascade_result = $cascade_stmt->execute();
    
    if ($cascade_result && $cascade_stmt->affected_rows > 0) {
        // Log cascading finalization
        log_audit_action(
            'program_cascade_finalized',
            "Cascading finalization: {$cascade_stmt->affected_rows} other period drafts finalized for Program ID: {$program_id} when submitting Period ID: {$period_id}",
            'success',
            $_SESSION['user_id'] ?? null
        );
    }
    
    // Update the submission for this program in the current period
    $query = "UPDATE program_submissions 
              SET is_draft = 0, 
                  submission_date = NOW() 
              WHERE program_id = ? 
              AND period_id = ?";
              
    $stmt = $conn->prepare($query);
    // $period_id = $current_period['period_id']; // This line was moved up
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();      if ($stmt->affected_rows > 0) {
        // Get the submitted content to check status
        $submitted_content_query = "SELECT content_json FROM program_submissions 
                                   WHERE program_id = ? AND period_id = ? AND is_draft = 0 
                                   ORDER BY submission_date DESC LIMIT 1";
        $content_stmt = $conn->prepare($submitted_content_query);
        $content_stmt->bind_param("ii", $program_id, $period_id);
        $content_stmt->execute();
        $content_result = $content_stmt->get_result();
        
        if ($content_result->num_rows > 0) {
            $content_row = $content_result->fetch_assoc();
            $content_data = json_decode($content_row['content_json'], true);
            $program_status = $content_data['rating'] ?? 'not-started';
            
            // Trigger outcome automation if program is linked to outcomes
            updateOutcomeDataOnProgramStatusChange($program_id, $program_status, $period_id, $_SESSION['user_id'] ?? 1);
        }
        
        // Log successful program submission
        log_audit_action(
            'program_submitted',
            "Program successfully submitted (Program ID: {$program_id}, Period ID: {$period_id})",
            'success',
            $_SESSION['user_id'] ?? null
        );
        echo json_encode(['status' => 'success', 'message' => 'Program submitted successfully']);
    }else {
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
            
            // CASCADING SUBMISSION LOGIC: Finalize ALL other drafts for this program (any period)
            $cascade_query = "UPDATE program_submissions 
                              SET is_draft = 0, 
                                  submission_date = NOW() 
                              WHERE program_id = ? 
                              AND is_draft = 1 
                              AND period_id != ?";
            
            $cascade_stmt = $conn->prepare($cascade_query);
            $cascade_stmt->bind_param("ii", $program_id, $period_id);
            $cascade_result = $cascade_stmt->execute();
            
            if ($cascade_result && $cascade_stmt->affected_rows > 0) {
                // Log cascading finalization
                log_audit_action(
                    'program_cascade_finalized',
                    "Cascading finalization: {$cascade_stmt->affected_rows} other period drafts finalized for Program ID: {$program_id} when submitting Period ID: {$period_id}",
                    'success',
                    $_SESSION['user_id'] ?? null
                );
            }
            
            $insert_query = "INSERT INTO program_submissions (program_id, period_id, is_draft, submission_date, submitted_by, content_json) 
                            VALUES (?, ?, 0, NOW(), ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iiis", $program_id, $period_id, $submitted_by, $content_json);
            $insert_stmt->execute();            if ($insert_stmt->affected_rows > 0) {
                // Check program status for outcome automation
                $content_data = json_decode($content_json, true);
                $program_status = $content_data['rating'] ?? 'not-started';
                
                // Trigger outcome automation if program is linked to outcomes
                updateOutcomeDataOnProgramStatusChange($program_id, $program_status, $period_id, $_SESSION['user_id'] ?? 1);
                
                // Log successful program submission (new record)
                log_audit_action(
                    'program_submitted',
                    "Program successfully submitted (new submission) (Program ID: {$program_id}, Period ID: {$period_id})",
                    'success',
                    $_SESSION['user_id'] ?? null
                );
                echo json_encode(['status' => 'success', 'message' => 'Program submitted successfully']);
            }else {
                // Log failed submission creation
                log_audit_action(
                    'program_submit_failed',
                    "Failed to create program submission (Program ID: {$program_id}, Period ID: {$period_id})\"",
                    'failure',
                    $_SESSION['user_id'] ?? null
                );
                echo json_encode(['status' => 'error', 'message' => 'Failed to create submission']);
            }
        } else {
            // This case means a submission record exists but was not updated by the UPDATE query,
            // which implies it was already submitted (is_draft = 0).
            log_audit_action(
                'program_submit_already_submitted',
                "Program submission attempt for already submitted program (Program ID: {$program_id}, Period ID: {$period_id})",
                'info', // Changed to info as it's not an error
                $_SESSION['user_id'] ?? null
            );
            echo json_encode(['status' => 'info', 'message' => 'Program was already submitted']);
        }
    }
} catch (Exception $e) {
    // Log exception during program submission
    log_audit_action(
        'program_submit_exception',
        "Exception during program submission (Program ID: {$program_id}): " . $e->getMessage(),
        'failure',
        $_SESSION['user_id'] ?? null
    );
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
