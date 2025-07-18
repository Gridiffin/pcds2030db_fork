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
    // Try to find a submission for the current period first
    $validation_query = "SELECT content_json FROM program_submissions 
                        WHERE program_id = ? AND period_id = ? AND is_deleted = 0
                        ORDER BY submission_date DESC, submission_id DESC 
                        LIMIT 1";
    $validation_stmt = $conn->prepare($validation_query);
    $validation_stmt->bind_param("ii", $program_id, $period_id);
    $validation_stmt->execute();
    $validation_result = $validation_stmt->get_result();
    
    $existing_content = null;
    $has_current_period_submission = false;
    
    if ($validation_result->num_rows > 0) {
        $validation_row = $validation_result->fetch_assoc();
        $existing_content = $validation_row['content_json'];
        $has_current_period_submission = true;
    } else {
        // No submission for current period, check if program has any submissions from other periods
        $any_submission_query = "SELECT content_json FROM program_submissions 
                               WHERE program_id = ? AND is_deleted = 0
                               ORDER BY submission_date DESC, submission_id DESC 
                               LIMIT 1";
        $any_submission_stmt = $conn->prepare($any_submission_query);
        $any_submission_stmt->bind_param("i", $program_id);
        $any_submission_stmt->execute();
        $any_submission_result = $any_submission_stmt->get_result();
        
        if ($any_submission_result->num_rows > 0) {
            $any_submission_row = $any_submission_result->fetch_assoc();
            $existing_content = $any_submission_row['content_json'];
        } else {
            // No submissions at all, check if program exists and has basic data
            $program_check_query = "SELECT program_name FROM programs WHERE program_id = ?";
            $program_check_stmt = $conn->prepare($program_check_query);
            $program_check_stmt->bind_param("i", $program_id);
            $program_check_stmt->execute();
            $program_check_result = $program_check_stmt->get_result();
            
            if ($program_check_result->num_rows === 0) {
                // Program doesn't exist
                log_audit_action(
                    'program_submit_invalid_program',
                    "Program submission failed - program does not exist (Program ID: {$program_id})",
                    'failure',
                    $_SESSION['user_id'] ?? null
                );
                echo json_encode(['status' => 'error', 'message' => 'Invalid program ID.']);
                exit;
            }
            
            // Program exists but has no submissions - this is OK for new programs
            // We'll allow submission but will need to create content from form data or use defaults
            $existing_content = null;
        }
    }
    
    // Validate content if we have it
    if (!empty($existing_content) && $existing_content !== 'null') {
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
    } else {
        // No existing content, program might be new - this is acceptable
        // The submission will be created with minimal content or default values
        log_audit_action(
            'program_submit_new_program',
            "Program submission for new program without prior content (Program ID: {$program_id}, Period ID: {$period_id})",
            'info',
            $_SESSION['user_id'] ?? null
        );
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
                                   WHERE program_id = ? AND period_id = ? AND is_draft = 0 AND is_deleted = 0
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
        $check_query = "SELECT submission_id FROM program_submissions WHERE program_id = ? AND period_id = ? AND is_deleted = 0";
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
                             WHERE program_id = ? AND is_deleted = 0
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
            } else {
                // No existing content found, create minimal content for new program
                // Get basic program info from the programs table
                $program_info_query = "SELECT program_name, program_number FROM programs WHERE program_id = ?";
                $program_info_stmt = $conn->prepare($program_info_query);
                $program_info_stmt->bind_param("i", $program_id);
                $program_info_stmt->execute();
                $program_info_result = $program_info_stmt->get_result();
                
                if ($program_info_result->num_rows > 0) {
                    $program_info = $program_info_result->fetch_assoc();
                    
                    // Create minimal content for submission
                    $minimal_content = [
                        'rating' => 'not-started',
                        'targets' => [
                            [
                                'target_text' => 'Initial submission',
                                'status_description' => 'Program submitted for the first time',
                                'target_status' => 'not-started'
                            ]
                        ],
                        'remarks' => 'Initial program submission',
                        'program_name' => $program_info['program_name'],
                        'program_number' => $program_info['program_number'] ?? ''
                    ];
                    $content_json = json_encode($minimal_content);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Program not found.']);
                    exit;
                }
            }
            
            // Validate that we have meaningful content to submit
            if (empty($content_json) || $content_json === 'null') {
                echo json_encode(['status' => 'error', 'message' => 'Cannot submit program without content. Please add targets and rating first.']);
                exit;
            }
            
            // Validate content structure (but be more lenient for new programs)
            $content_data = json_decode($content_json, true);
            if (!$content_data) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid program content format.']);
                exit;
            }
            
            // Ensure minimal required fields exist
            if (empty($content_data['rating'])) {
                $content_data['rating'] = 'not-started';
            }
            if (empty($content_data['targets']) && empty($content_data['target'])) {
                $content_data['targets'] = [
                    [
                        'target_text' => 'Initial submission',
                        'status_description' => 'Program submitted for the first time',
                        'target_status' => 'not-started'
                    ]
                ];
            }
            
            // Update content_json with any defaults we added
            $content_json = json_encode($content_data);
            
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
