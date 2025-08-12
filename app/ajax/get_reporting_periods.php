<?php
// Standardize root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR));
}
$__ROOT = rtrim(PROJECT_ROOT_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

// Core includes
require_once $__ROOT . 'app/config/config.php';
// Load minimal deps first (avoid DB until after validation)
require_once $__ROOT . 'app/lib/session.php';
require_once $__ROOT . 'app/lib/admins/core.php';
require_once $__ROOT . 'app/lib/agencies/core.php';
require_once $__ROOT . 'app/helpers/ajax_helpers.php';

// JSON header
ajax_set_json_header();

// Method gating (GET only)
if (!ajax_method_allowed(['GET'])) {
    http_response_code(405);
    ajax_send_error('Method not allowed. Use GET.', 405);
    return;
}

// Role gating (agency, focal, or admin)
if (!is_agency() && !is_admin()) {
    http_response_code(403);
    ajax_send_error('Access denied.', 403);
    return;
}

// Validate required params
$missing = ajax_missing_params(['program_id'], $_GET);
if (!empty($missing)) {
    http_response_code(400);
    ajax_send_error('Missing required parameter(s): ' . implode(', ', $missing), 400);
    return;
}

$program_id = (int)($_GET['program_id'] ?? 0);

// Build response
// Now include DB and feature libs
require_once $__ROOT . 'app/lib/db_connect.php';
require_once $__ROOT . 'app/lib/functions.php';
require_once $__ROOT . 'app/lib/agencies/programs.php';

$periods = [];
$reporting_periods = get_reporting_periods_for_submissions(true);

// Get all submissions for this program
$existing_submissions_query = "SELECT ps.period_id, ps.is_draft, ps.is_submitted, ps.submission_id
                              FROM program_submissions ps
                              WHERE ps.program_id = ? AND ps.is_deleted = 0";
$stmt = $conn->prepare($existing_submissions_query);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$existing_submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$submissions_by_period = [];
foreach ($existing_submissions as $submission) {
    $submissions_by_period[$submission['period_id']] = $submission;
}

foreach ($reporting_periods as $period) {
    $has_submission = isset($submissions_by_period[$period['period_id']]);
    $submission = $has_submission ? $submissions_by_period[$period['period_id']] : null;
    $periods[] = [
        'period_id' => $period['period_id'],
        'display_name' => $period['display_name'],
        'status' => $period['status'],
        'has_submission' => $has_submission,
        'is_draft' => $has_submission ? !!$submission['is_draft'] : null,
        'submission_id' => $has_submission ? $submission['submission_id'] : null
    ];
}

echo json_encode(['success' => true, 'periods' => $periods]);
return;