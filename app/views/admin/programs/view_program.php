<?php
/**
 * Admin View Program
 * 
 * Detailed view of a specific program for administrators.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/agencies/program_attachments.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';
require_once ROOT_PATH . 'app/lib/agencies/programs.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate program_id
if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get program details with submissions history
$program = get_admin_program_details($program_id); // Using the admin function from statistics.php

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
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
        true // Allow cross-agency viewing for admin
    );
}

// Extract targets from current submission - check both program_targets table and content_json
$targets = [];

if (isset($program['current_submission']) && !empty($program['current_submission'])) {
    $current_submission = $program['current_submission'];
    
    // First, try to get targets from program_targets table (modern approach)
    if (!empty($current_submission['submission_id'])) {
        $submission_id = $current_submission['submission_id'];
        $targets_query = "SELECT target_id, target_number, target_description, status_indicator, 
                                 status_description, remarks, start_date, end_date 
                          FROM program_targets 
                          WHERE submission_id = ? AND is_deleted = 0 
                          ORDER BY target_id";
        
        $targets_stmt = $conn->prepare($targets_query);
        $targets_stmt->bind_param("i", $submission_id);
        $targets_stmt->execute();
        $targets_result = $targets_stmt->get_result();
        
        while ($target_row = $targets_result->fetch_assoc()) {
            $targets[] = [
                'text' => htmlspecialchars($target_row['target_description'] ?? ''),
                'status_description' => htmlspecialchars($target_row['status_description'] ?? ''),
                'target_number' => htmlspecialchars($target_row['target_number'] ?? ''),
                'start_date' => $target_row['start_date'] ?? '',
                'end_date' => $target_row['end_date'] ?? '',
                'target_id' => $target_row['target_id'] ?? '',
                'status_indicator' => $target_row['status_indicator'] ?? ''
            ];
        }
        $targets_stmt->close();
    }
    
    // If no targets found in program_targets table, try content_json (legacy approach)
    if (empty($targets) && isset($current_submission['content_json']) && !empty($current_submission['content_json'])) {
        $content = json_decode($current_submission['content_json'], true);
        
        if ($content && is_array($content)) {
            // Handle new target structure (array of targets)
            if (isset($content['targets']) && is_array($content['targets'])) {
                foreach ($content['targets'] as $target) {
                    if (isset($target['target_text']) && !empty($target['target_text'])) {
                        $targets[] = [
                            'text' => htmlspecialchars($target['target_text']),
                            'status_description' => htmlspecialchars($target['status_description'] ?? ''),
                            'target_number' => htmlspecialchars($target['target_number'] ?? ''),
                            'start_date' => $target['start_date'] ?? '',
                            'end_date' => $target['end_date'] ?? ''
                        ];
                    }
                }
            } 
            // Handle legacy target structure (single target string)
            elseif (isset($content['target']) && !empty($content['target'])) {
                $target_text = $content['target'];
                $status_description = $content['status_text'] ?? '';
                
                // Check if target contains multiple targets separated by semicolon
                if (strpos($target_text, ';') !== false) {
                    $target_parts = array_map('trim', explode(';', $target_text));
                    $status_parts = array_map('trim', explode(';', $status_description));
                    
                    foreach ($target_parts as $index => $target_part) {
                        if (!empty($target_part)) {
                            $targets[] = [
                                'text' => htmlspecialchars($target_part),
                                'status_description' => htmlspecialchars(isset($status_parts[$index]) ? $status_parts[$index] : ''),
                                'target_number' => '',
                                'start_date' => '',
                                'end_date' => ''
                            ];
                        }
                    }
                } else {
                    // Single target
                    $targets[] = [
                        'text' => htmlspecialchars($target_text),
                        'status_description' => htmlspecialchars($status_description),
                        'target_number' => '',
                        'start_date' => '',
                        'end_date' => ''
                    ];
                }
            }
        }
    }
}

// Remove all content_json and per-submission rating logic
// Refactor to use only programs.rating for all rating display and logic
$rating = $program['rating'] ?? 'not-started';
$remarks = $program['remarks'] ?? '';

// Set page title
$pageTitle = 'Program Details: ' . $program['program_name'];

// Remove inline CSS - now handled by external CSS file
// Responsive table styling moved to admin-performance-table.css

// Define rating mapping for display
$rating_map = [
    'on-track' => ['label' => 'On Track', 'class' => 'warning', 'icon' => 'fas fa-chart-line'],
    'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
    'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
    'delayed' => ['label' => 'Delayed', 'class' => 'danger', 'icon' => 'fas fa-exclamation-circle'],
    'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle'],
    'completed' => ['label' => 'Completed', 'class' => 'primary', 'icon' => 'fas fa-flag-checkered'],
    'not-started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start']
];

// Convert rating for display
$rating_value = isset($rating) ? convert_legacy_rating($rating) : 'not-started';
if (!isset($rating_map[$rating_value])) {
    $rating_value = 'not-started';
}

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/utilities/program_details_table.js',
    APP_URL . '/assets/js/utilities/program_details_responsive.js'
];

// Additional CSS
$additionalCSS = [
    APP_URL . '/assets/css/admin/programs.css',
    APP_URL . '/assets/css/components/period-performance.css'
];

// Configure the modern page header
$header_config = [
    'title' => 'Program Details',
    'subtitle' => (!empty($program['program_number']) ? '#' . htmlspecialchars($program['program_number']) . ' - ' : '') . htmlspecialchars($program['program_name']),
    'variant' => 'white',
    'actions' => [
        [
            'text' => 'Back to Programs',
            'url' => APP_URL . '/app/views/admin/programs/programs.php',
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/view_program_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
?>

