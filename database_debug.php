<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "<h3>Database Investigation</h3>";

// Check programs with submissions
$query = "
    SELECT p.program_id, p.program_name, 
           COUNT(ps.submission_id) as submission_count,
           GROUP_CONCAT(CONCAT(ps.submission_id, '(', rp.name, ')') SEPARATOR ', ') as submissions
    FROM programs p 
    LEFT JOIN program_submissions ps ON p.program_id = ps.program_id 
    LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
    GROUP BY p.program_id 
    HAVING submission_count > 1
    ORDER BY submission_count DESC
";

$result = $conn->query($query);

echo "<h4>Programs with Multiple Submissions (should show history):</h4>";
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Program ID</th><th>Program Name</th><th>Submission Count</th><th>Submissions</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['program_id']}</td>";
        echo "<td>{$row['program_name']}</td>";
        echo "<td>{$row['submission_count']}</td>";
        echo "<td>{$row['submissions']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No programs found with multiple submissions!</p>";
}

// Check individual submissions for program 168
echo "<h4>Detailed Submissions for Program 168:</h4>";
$query2 = "
    SELECT ps.submission_id, ps.period_id, ps.is_draft, ps.submission_date,
           rp.name as period_name, ps.content_json
    FROM program_submissions ps
    LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id  
    WHERE ps.program_id = 168
    ORDER BY ps.submission_date DESC
";

$result2 = $conn->query($query2);
if ($result2 && $result2->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Submission ID</th><th>Period</th><th>Is Draft</th><th>Date</th><th>Has Content</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['submission_id']}</td>";
        echo "<td>{$row['period_name']}</td>";
        echo "<td>" . ($row['is_draft'] ? 'Yes' : 'No') . "</td>";
        echo "<td>{$row['submission_date']}</td>";
        echo "<td>" . (!empty($row['content_json']) ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No submissions found for program 168</p>";
}

// Check the get_program_edit_history function directly
echo "<h4>Direct Function Test:</h4>";
require_once 'app/lib/agencies/programs.php';
$program_history = get_program_edit_history(168);

echo "<p>Function returned: " . (is_array($program_history) ? 'Array' : gettype($program_history)) . "</p>";
if (isset($program_history['submissions'])) {
    echo "<p>Submissions count: " . count($program_history['submissions']) . "</p>";
    echo "<p>Show history condition: " . (count($program_history['submissions']) > 1 ? 'TRUE' : 'FALSE') . "</p>";
} else {
    echo "<p>❌ No submissions key found</p>";
}
?>
