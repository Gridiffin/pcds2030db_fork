<?php
/**
 * Test script for initiative-aware get_period_programs.php
 */

require_once '../config/config.php';
require_once '../lib/db_connect.php';

echo "<h2>Testing Initiative-Aware Program API</h2>\n";

// First, let's check what periods exist
$periods_query = "SELECT period_id, year, quarter FROM reporting_periods ORDER BY year DESC, quarter DESC LIMIT 5";
$periods_result = $conn->query($periods_query);

echo "<h3>Available Reporting Periods:</h3>\n";
echo "<table border='1'>\n";
echo "<tr><th>Period ID</th><th>Year</th><th>Quarter</th></tr>\n";
while ($period = $periods_result->fetch_assoc()) {
    echo "<tr><td>{$period['period_id']}</td><td>{$period['year']}</td><td>{$period['quarter']}</td></tr>\n";
}
echo "</table>\n";

// Test the program query directly
echo "<h3>Programs with Initiative Information:</h3>\n";
$test_query = "SELECT DISTINCT p.program_id, p.program_name, p.program_number, p.initiative_id,
              i.initiative_name, i.initiative_number, 
              s.sector_id, s.sector_name, u.agency_name, u.user_id as owner_agency_id
              FROM programs p
              LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
              LEFT JOIN sectors s ON p.sector_id = s.sector_id
              LEFT JOIN users u ON p.owner_agency_id = u.user_id
              ORDER BY i.initiative_name, s.sector_name, u.agency_name, p.program_name
              LIMIT 10";

$result = $conn->query($test_query);
echo "<table border='1'>\n";
echo "<tr><th>Program ID</th><th>Program Name</th><th>Initiative ID</th><th>Initiative Name</th><th>Sector</th><th>Agency</th></tr>\n";
while ($program = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$program['program_id']}</td>";
    echo "<td>{$program['program_name']}</td>";
    echo "<td>" . ($program['initiative_id'] ?? 'NULL') . "</td>";
    echo "<td>" . ($program['initiative_name'] ?? 'No Initiative') . "</td>";
    echo "<td>{$program['sector_name']}</td>";
    echo "<td>{$program['agency_name']}</td>";
    echo "</tr>\n";
}
echo "</table>\n";

// Check program submissions for testing
echo "<h3>Non-Draft Program Submissions by Period:</h3>\n";
$submissions_query = "SELECT ps.period_id, rp.year, rp.quarter, COUNT(*) as submission_count
                     FROM program_submissions ps
                     JOIN reporting_periods rp ON ps.period_id = rp.period_id
                     WHERE ps.is_draft = 0
                     GROUP BY ps.period_id, rp.year, rp.quarter
                     ORDER BY rp.year DESC, rp.quarter DESC";

$submissions_result = $conn->query($submissions_query);
echo "<table border='1'>\n";
echo "<tr><th>Period ID</th><th>Year</th><th>Quarter</th><th>Non-Draft Submissions</th></tr>\n";
while ($sub = $submissions_result->fetch_assoc()) {
    echo "<tr><td>{$sub['period_id']}</td><td>{$sub['year']}</td><td>{$sub['quarter']}</td><td>{$sub['submission_count']}</td></tr>\n";
}
echo "</table>\n";

echo "<p><strong>Test completed!</strong> You can now test the actual API endpoint.</p>\n";
?>
