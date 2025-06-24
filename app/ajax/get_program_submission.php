<?php
/**
 * Get Program Submission for a Period (AJAX)
 * Returns program submission data for a given program_id and period_id.
 * Used for dynamic form population in update_program.php.
 */

require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/index.php';

header('Content-Type: application/json');

if (!is_agency()) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

if (!$program_id || !$period_id) {
    echo json_encode(['success' => false, 'error' => 'Missing program_id or period_id.']);
    exit;
}

$program = get_program_details($program_id, is_focal_user());
if (!$program) {
    echo json_encode(['success' => false, 'error' => 'Program not found.']);
    exit;
}

// Find the correct submission for the selected period
$current_submission = null;
if (isset($program['submissions']) && is_array($program['submissions'])) {
    foreach ($program['submissions'] as $submission) {
        if (isset($submission['period_id']) && $submission['period_id'] == $period_id) {
            $current_submission = $submission;
            break;
        }
    }
}

// Prepare response data
$response = [
    'success' => true,
    'data' => [
        'program_name' => $program['program_name'],
        'program_number' => $program['program_number'],
        'brief_description' => '',
        'targets' => [['target_text' => '', 'status_description' => '']],
        'rating' => 'not-started',
        'remarks' => '',
    ]
];

if ($current_submission && isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
    $content = json_decode($current_submission['content_json'], true);
    if (isset($content['targets']) && is_array($content['targets'])) {
        $response['data']['targets'] = $content['targets'];
        $response['data']['rating'] = $content['rating'] ?? 'not-started';
        $response['data']['remarks'] = $content['remarks'] ?? '';
        $response['data']['brief_description'] = $content['brief_description'] ?? '';
    }
}

// Legacy fallback for old structure
if (!$current_submission && isset($program['brief_description'])) {
    $response['data']['brief_description'] = $program['brief_description'];
}

// Add submission_id if available
if ($current_submission && isset($current_submission['submission_id'])) {
    $response['data']['submission_id'] = $current_submission['submission_id'];
}

echo json_encode($response);
