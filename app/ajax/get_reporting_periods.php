<?php
// AJAX endpoint to get latest reporting periods and submission statuses for a program
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/programs.php';
require_once '../lib/admins/core.php';

header('Content-Type: application/json');

if (!is_agency() && !is_admin()) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
if (!$program_id) {
    echo json_encode(['success' => false, 'error' => 'Missing program_id.']);
    exit;
}

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