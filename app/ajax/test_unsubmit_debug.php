<?php
/**
 * Debug script to test unsubmit functionality and check database state
 */
require_once dirname(__DIR__) . '/lib/db_connect.php';
require_once dirname(__DIR__) . '/lib/session.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$submission_id = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : 0;

if ($submission_id <= 0) {
    echo json_encode(['error' => 'Invalid submission ID']);
    exit;
}

// Get current submission data
$stmt = $conn->prepare('SELECT * FROM program_submissions WHERE submission_id = ? AND is_deleted = 0');
$stmt->bind_param('i', $submission_id);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();

if (!$submission) {
    echo json_encode(['error' => 'Submission not found']);
    exit;
}

// Get all submissions for this program to see the full picture
$stmt = $conn->prepare('SELECT submission_id, program_id, is_draft, is_submitted, created_at, updated_at FROM program_submissions WHERE program_id = ? AND is_deleted = 0 ORDER BY submission_id DESC');
$stmt->bind_param('i', $submission['program_id']);
$stmt->execute();
$result = $stmt->get_result();
$all_submissions = [];
while ($row = $result->fetch_assoc()) {
    $all_submissions[] = $row;
}

echo json_encode([
    'current_submission' => $submission,
    'all_submissions_for_program' => $all_submissions,
    'latest_submission_id' => max(array_column($all_submissions, 'submission_id')),
    'debug_info' => [
        'user_id' => $_SESSION['user_id'] ?? 'unknown',
        'username' => $_SESSION['username'] ?? 'unknown',
        'agency_id' => $_SESSION['agency_id'] ?? 'unknown'
    ]
]);
?> 