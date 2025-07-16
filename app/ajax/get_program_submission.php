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
require_once '../lib/admins/core.php';
require_once '../lib/admins/statistics.php';

header('Content-Type: application/json');

if (!is_agency() && !is_admin()) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

if (!$program_id || !$period_id) {
    echo json_encode(['success' => false, 'error' => 'Missing program_id or period_id.']);
    exit;
}

if (is_admin()) {
    // Admin users have access to all programs
    $program = get_admin_program_details($program_id);
} else {
    // Agency users have access based on their permissions
    $program = get_program_details($program_id, is_focal_user());
}

if (!$program) {
    echo json_encode(['success' => false, 'error' => 'Program not found.']);
    exit;
}

// Find the correct submission for the selected period
$current_submission = null;
if (isset($program['latest_submissions_by_period']) && is_array($program['latest_submissions_by_period'])) {
    // Use the new optimized array for latest submission by period
    if (isset($program['latest_submissions_by_period'][$period_id])) {
        $current_submission = $program['latest_submissions_by_period'][$period_id];
    }
} else if (isset($program['submissions']) && is_array($program['submissions'])) {
    // Fallback: Filter submissions for the selected period only
    $period_submissions = array_filter($program['submissions'], function($submission) use ($period_id) {
        return isset($submission['period_id']) && $submission['period_id'] == $period_id;
    });
    // If there are multiple submissions for this period, get the latest one
    if (!empty($period_submissions)) {
        // Sort by submission_id descending to get the latest submission for this period
        usort($period_submissions, function($a, $b) {
            return $b['submission_id'] <=> $a['submission_id'];
        });
        // Get the latest submission for this period
        $current_submission = reset($period_submissions);
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
        'period_id' => $period_id,
    ]
];

// Use only the current submission for the selected period if it exists
if ($current_submission && isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
    $content = json_decode($current_submission['content_json'], true);
    if (is_array($content)) {
        // Update with content from the current period submission
        if (isset($content['targets']) && is_array($content['targets'])) {
            $response['data']['targets'] = $content['targets'];
        }
        $response['data']['rating'] = $content['rating'] ?? 'not-started';
        $response['data']['remarks'] = $content['remarks'] ?? '';
        $response['data']['brief_description'] = $content['brief_description'] ?? '';
    }
} else {
    // No submission exists for this period - provide empty form
    // We'll keep the program name and number but reset everything else
    $response['data']['targets'] = [
        [
            'target_number' => '',
            'target_text' => '',
            'status_description' => '',
            'target_status' => 'not-started',
            'start_date' => null,
            'end_date' => null
        ]
    ];
}

// Include basic program info from the program table
if (isset($program['brief_description']) && empty($response['data']['brief_description'])) {
    $response['data']['brief_description'] = $program['brief_description'];
}

// Add submission_id if available, or set to null if creating a new submission for this period
if ($current_submission && isset($current_submission['submission_id'])) {
    $response['data']['submission_id'] = $current_submission['submission_id'];
} else {
    $response['data']['submission_id'] = null;
}

echo json_encode($response);
