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
$previous_submission = null;
if (isset($program['submissions']) && is_array($program['submissions'])) {
    // Sort submissions by period_id descending to find previous period easily
    usort($program['submissions'], function($a, $b) {
        return $b['period_id'] <=> $a['period_id'];
    });
    foreach ($program['submissions'] as $submission) {
        if (isset($submission['period_id']) && $submission['period_id'] == $period_id) {
            $current_submission = $submission;
            break;
        }
        // Track the most recent submission before the requested period
        if (isset($submission['period_id']) && $submission['period_id'] < $period_id) {
            if ($previous_submission === null || $submission['period_id'] > $previous_submission['period_id']) {
                $previous_submission = $submission;
            }
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

// Use current submission if exists, else fallback to previous submission if exists
$submission_to_use = $current_submission ?? $previous_submission;

if ($submission_to_use && isset($submission_to_use['content_json']) && is_string($submission_to_use['content_json'])) {
    $content = json_decode($submission_to_use['content_json'], true);
    if (isset($content['targets']) && is_array($content['targets'])) {
        $response['data']['targets'] = $content['targets'];
        $response['data']['rating'] = $content['rating'] ?? 'not-started';
        $response['data']['remarks'] = $content['remarks'] ?? '';
        $response['data']['brief_description'] = $content['brief_description'] ?? '';
    }
}

// Legacy fallback for old structure
if (!$submission_to_use && isset($program['brief_description'])) {
    $response['data']['brief_description'] = $program['brief_description'];
}

// Add submission_id if available
if ($submission_to_use && isset($submission_to_use['submission_id'])) {
    $response['data']['submission_id'] = $submission_to_use['submission_id'];
}

echo json_encode($response);
