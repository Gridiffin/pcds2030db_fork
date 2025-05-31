<?php
require_once 'app/lib/database.php';
use App\lib\Database\DBCode;

echo "=== TESTING CREATE PROGRAM FLOW ===\n";

// Test 1: Check recent programs
echo "\n1. Current state of programs table (last 5):\n";
$programs = DBCode::select('programs', ['program_id', 'program_name', 'description', 'extended_data', 'created_at'], [], 'ORDER BY created_at DESC LIMIT 5');
foreach ($programs as $program) {
    echo "ID: {$program['program_id']}, Name: {$program['program_name']}, Description: " . (empty($program['description']) ? 'EMPTY' : 'HAS_DATA') . ", Extended: " . (empty($program['extended_data']) ? 'EMPTY' : 'HAS_DATA') . ", Created: {$program['created_at']}\n";
}

echo "\n2. Current state of program_submissions table (last 5):\n";
$submissions = DBCode::select('program_submissions', ['submission_id', 'program_id', 'content_json', 'status', 'created_at'], [], 'ORDER BY created_at DESC LIMIT 5');
foreach ($submissions as $submission) {
    echo "Submission ID: {$submission['submission_id']}, Program ID: {$submission['program_id']}, Status: {$submission['status']}, Content: " . (empty($submission['content_json']) ? 'EMPTY' : 'HAS_DATA') . ", Created: {$submission['created_at']}\n";
}

// Test 2: Identify duplicates
echo "\n3. Looking for duplicate program names:\n";
$duplicate_check = DBCode::query("SELECT program_name, COUNT(*) as count FROM programs GROUP BY program_name HAVING COUNT(*) > 1");
if ($duplicate_check) {
    foreach ($duplicate_check as $dup) {
        echo "Duplicate name: '{$dup['program_name']}' appears {$dup['count']} times\n";
        
        // Show the specific duplicate records
        $duplicates = DBCode::select('programs', ['program_id', 'program_name', 'created_at'], ['program_name' => $dup['program_name']], 'ORDER BY created_at DESC');
        foreach ($duplicates as $dup_record) {
            echo "  - ID: {$dup_record['program_id']}, Created: {$dup_record['created_at']}\n";
        }
    }
} else {
    echo "No duplicates found\n";
}
?>
