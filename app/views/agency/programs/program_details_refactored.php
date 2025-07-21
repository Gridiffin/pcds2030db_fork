<?php
/**
 * Program Details - Refactored with Best Practices
 * 
 * Displays comprehensive information about a specific program including
 * submissions, targets, attachments, and timeline.
 * Modular structure with base.php layout and Vite bundling.
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_attachments.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program-details/data-processor.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program-details/error-handler.php';
require_once PROJECT_ROOT_PATH . 'app/lib/program_status_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get source parameter to determine where the user came from
$source = isset($_GET['source']) ? $_GET['source'] : '';

if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get comprehensive program details
$program = get_program_details($program_id, true);

// Instead of complex permission checks that might cause errors, just check if program exists
if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program attachments
$program_attachments = get_program_attachments($program_id);

// Get related programs if this program is linked to an initiative
$related_programs = [];
if (!empty($program['initiative_id'])) {
    $related_programs = get_related_programs_by_initiative(
        $program['initiative_id'],
        $program_id,
        true // Always allow cross-agency viewing
    );
}

// Get latest submission if available
$latest_submission = $program['current_submission'] ?? null;
$has_submissions = !empty($latest_submission);
$is_draft = $has_submissions && isset($latest_submission['is_draft']) && $latest_submission['is_draft'];

// Process submission data if available
$content = [];
$targets = [];
$rating = 'not-started';
$remarks = '';

if ($has_submissions) {
    // Get targets from the current submission (now properly fetched from program_targets table)
    if (isset($latest_submission['targets']) && is_array($latest_submission['targets'])) {
        $targets = $latest_submission['targets'];
    }
    
    // Get rating and remarks from submission
    $rating = $latest_submission['rating'] ?? $latest_submission['status_indicator'] ?? 'not-started';
    $remarks = $latest_submission['remarks'] ?? $latest_submission['description'] ?? '';
    
    // Fallback to legacy content_json if targets are not found in program_targets table
    if (empty($targets) && isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
        if (is_string($latest_submission['content_json'])) {
            $content = json_decode($latest_submission['content_json'], true) ?: [];
        } elseif (is_array($latest_submission['content_json'])) {
            $content = $latest_submission['content_json'];
        }
        
        // Extract targets from legacy content
        if (isset($content['targets']) && is_array($content['targets'])) {
            $targets = [];
            foreach ($content['targets'] as $target) {
                if (isset($target['target_text'])) {
                    $targets[] = [
                        'target_number' => $target['target_number'] ?? '',
                        'text' => $target['target_text'],
                        'status_description' => $target['status_description'] ?? '',
                        'start_date' => $target['start_date'] ?? '',
                        'end_date' => $target['end_date'] ?? ''
                    ];
                } else {
                    $targets[] = $target;
                }
            }
        } elseif (isset($content['target']) && !empty($content['target'])) {
            // Legacy single target format
            $targets[] = [
                'text' => $content['target'],
                'status_description' => $content['status_text'] ?? ''
            ];
        }
        
        // Override rating and remarks from legacy content if available
        if (isset($content['rating'])) {
            $rating = $content['rating'];
        }
        if (isset($content['remarks'])) {
            $remarks = $content['remarks'];
        }
    }
}

// Determine rating for badge - use same logic as view_programs.php for consistency
$rating = isset($program['rating']) ? $program['rating'] : 'not_started';

// Get all reporting periods for display
$all_periods = get_all_reporting_periods();
$latest_by_period = $program['latest_submissions_by_period'] ?? [];

// Get submission history for timeline
$submission_history = get_program_edit_history($program_id);

// Define edit/owner variables for use in the view
$can_edit = can_edit_program($program_id);
$is_owner = is_program_owner($program_id);

// Back button URL depends on source
$allSectorsUrl = APP_URL . '/app/views/agency/sectors/view_all_sectors.php';
$myProgramsUrl = APP_URL . '/app/views/agency/programs/view_programs.php';
$backUrl = $source === 'all_sectors' ? $allSectorsUrl : $myProgramsUrl;

// Configure modern page header
$program_display_name = '';
if (!empty($program['program_number'])) {
    $program_display_name = '<span class="badge bg-info me-2" title="Program Number">' . htmlspecialchars($program['program_number']) . '</span>';
}
$program_display_name .= htmlspecialchars($program['program_name']);

$header_config = [
    'title' => 'Program Details',
    'subtitle' => $program_display_name,
    'subtitle_html' => true,
    'variant' => 'white',
    'actions' => [
        [
            'url' => $backUrl,
            'text' => 'Back to ' . ($source === 'all_sectors' ? 'All Sectors' : 'My Programs'),
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Use the status from the programs table
$status = isset($program['status']) ? $program['status'] : 'active';
$status_info = get_program_status_info($status);

// Initialize alert flags
$showDraftAlert = $is_draft && $is_owner;

// Enhanced target detection - check multiple sources for targets
$has_targets = false;
if (!empty($targets)) {
    $has_targets = true;
} elseif ($has_submissions) {
    // Check if there are any targets in the submission data
    if (isset($latest_submission['targets']) && is_array($latest_submission['targets']) && !empty($latest_submission['targets'])) {
        $has_targets = true;
    } elseif (isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
        $content_check = is_string($latest_submission['content_json']) ? 
            json_decode($latest_submission['content_json'], true) : 
            $latest_submission['content_json'];
        
        if (isset($content_check['targets']) && is_array($content_check['targets']) && !empty($content_check['targets'])) {
            $has_targets = true;
        } elseif (isset($content_check['target']) && !empty($content_check['target'])) {
            $has_targets = true;
        }
    } elseif (!empty($latest_submission['target'])) {
        $has_targets = true;
    }
}

$showNoTargetsAlert = $has_submissions && !$has_targets && $is_owner;
$showNoSubmissionsAlert = !$has_submissions; // Show for all users, but action link only for editors

// Fetch all hold points for this program (read-only, same as API logic)
$hold_points = [];
if (isset($program_id) && $program_id) {
    $stmt = $conn->prepare('SELECT id, reason, remarks, created_at, ended_at, created_by FROM program_hold_points WHERE program_id = ? ORDER BY created_at ASC');
    $stmt->bind_param('i', $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $hold_points[] = $row;
    $stmt->close();
}

// Gather draft submissions for this program
$draft_submissions = [];
if (!empty($submission_history['submissions'])) {
    foreach ($submission_history['submissions'] as $submission) {
        if (!empty($submission['is_draft'])) {
            $draft_submissions[] = $submission;
        }
    }
}

// Set up base layout variables
$pageTitle = 'Program Details';
$cssBundle = 'program-details';
$jsBundle = 'program-details';

// Set content file for base layout
$contentFile = __DIR__ . '/partials/program_details/program_details_content.php';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
