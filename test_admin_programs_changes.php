<?php
/**
 * Test script to verify admin programs page changes
 * This script will test the database query and rating display
 */

// Include necessary files
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';

echo "Testing Admin Programs Page Changes\n";
echo "==================================\n\n";

// Test 1: Check if programs table has rating field
echo "1. Testing database structure...\n";
$query = "DESCRIBE programs";
$result = $conn->query($query);

$has_rating = false;
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'rating') {
        $has_rating = true;
        echo "   ✓ Rating field found: " . $row['Type'] . "\n";
        break;
    }
}

if (!$has_rating) {
    echo "   ✗ Rating field not found in programs table\n";
}

// Test 2: Check sample programs data
echo "\n2. Testing programs data with ratings...\n";
$query = "SELECT program_id, program_name, rating, agency_id FROM programs WHERE is_deleted = 0 LIMIT 5";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "   Sample programs:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['program_id']}, Name: {$row['program_name']}, Rating: {$row['rating']}\n";
    }
} else {
    echo "   ✗ No programs found\n";
}

// Test 3: Check updated query from programs.php
echo "\n3. Testing the updated admin query...\n";
$query = "SELECT DISTINCT p.*, 
                 i.initiative_name,
                 i.initiative_number,
                 i.initiative_id,
                 latest_sub.is_draft,
                 latest_sub.period_id,
                 latest_sub.submission_id as latest_submission_id,
                 latest_sub.submitted_at,
                 latest_sub.submitted_by,
                 rp.period_type,
                 rp.period_number,
                 rp.year as period_year,
                 a.agency_name,
                 su.fullname as submitted_by_name,
                 p.rating,
                 COALESCE(latest_sub.submitted_at, p.created_at) as updated_at
          FROM programs p 
          LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
          LEFT JOIN agency a ON p.agency_id = a.agency_id
          LEFT JOIN (
              SELECT ps1.*
              FROM program_submissions ps1
              INNER JOIN (
                  SELECT program_id, MAX(submission_id) as max_submission_id
                  FROM program_submissions
                  WHERE is_deleted = 0 AND is_draft = 0
                  GROUP BY program_id
              ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
              WHERE ps1.is_draft = 0
          ) latest_sub ON p.program_id = latest_sub.program_id
          LEFT JOIN reporting_periods rp ON latest_sub.period_id = rp.period_id
          LEFT JOIN users su ON latest_sub.submitted_by = su.user_id
          WHERE p.is_deleted = 0 
          AND latest_sub.submission_id IS NOT NULL
          ORDER BY a.agency_name, p.program_name
          LIMIT 3";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "   ✓ Query executed successfully. Sample results:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - Program: {$row['program_name']}, Agency: {$row['agency_name']}, Rating: {$row['rating']}\n";
    }
} else {
    echo "   ✗ Query returned no results\n";
}

echo "\n✓ Testing complete!\n";
?>
