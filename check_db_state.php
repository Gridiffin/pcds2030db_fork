<?php
require_once 'app/lib/database.php';
use App\lib\Database\DBCode;

echo "=== CURRENT PROGRAMS STATE ===\n";
$programs = DBCode::select('programs', ['program_id', 'program_name', 'description', 'extended_data', 'created_at'], [], 'ORDER BY created_at DESC LIMIT 5');
foreach ($programs as $program) {
    echo "ID: {$program['program_id']}, Name: {$program['program_name']}, Description: {$program['description']}, Extended: " . (empty($program['extended_data']) ? 'EMPTY' : 'HAS_DATA') . ", Created: {$program['created_at']}\n";
}

echo "\n=== CURRENT PROGRAM_SUBMISSIONS STATE ===\n";
$submissions = DBCode::select('program_submissions', ['submission_id', 'program_id', 'content_json', 'status', 'created_at'], [], 'ORDER BY created_at DESC LIMIT 5');
foreach ($submissions as $submission) {
    echo "Submission ID: {$submission['submission_id']}, Program ID: {$submission['program_id']}, Status: {$submission['status']}, Content: " . (empty($submission['content_json']) ? 'EMPTY' : 'HAS_DATA') . ", Created: {$submission['created_at']}\n";
}
?>
