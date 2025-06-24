<?php
/**
 * Simple test of the initiative-aware SQL query logic
 * Tests the SQL query without admin authentication requirements
 */

require_once '../config/config.php';
require_once '../lib/db_connect.php';

// Test with period_id = 2 (which has 59 non-draft submissions)
$period_id = 2;

echo "<h2>Testing Initiative-Aware SQL Query for Period {$period_id}</h2>\n";

try {
    // Use the same query structure as the updated get_period_programs.php
    $programs_query = "SELECT DISTINCT p.program_id, p.program_name, p.program_number, p.initiative_id,
                      i.initiative_name, i.initiative_number, 
                      s.sector_id, s.sector_name, u.agency_name, u.user_id as owner_agency_id
                      FROM programs p
                      LEFT JOIN (
                          SELECT ps1.program_id
                          FROM program_submissions ps1
                          INNER JOIN (
                              SELECT program_id, MAX(submission_date) as latest_date, MAX(submission_id) as latest_submission_id
                              FROM program_submissions
                              WHERE period_id = ? AND is_draft = 0
                              GROUP BY program_id
                          ) ps2 ON ps1.program_id = ps2.program_id 
                               AND ps1.submission_date = ps2.latest_date 
                               AND ps1.submission_id = ps2.latest_submission_id
                          WHERE ps1.period_id = ? AND ps1.is_draft = 0
                      ) ps ON p.program_id = ps.program_id
                      LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
                      LEFT JOIN sectors s ON p.sector_id = s.sector_id
                      LEFT JOIN users u ON p.owner_agency_id = u.user_id
                      WHERE ps.program_id IS NOT NULL
                      ORDER BY i.initiative_name, s.sector_name, u.agency_name, p.program_name";

    $stmt = $conn->prepare($programs_query);
    $stmt->bind_param("ii", $period_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>Query Results:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Program ID</th><th>Program Name</th><th>Program Number</th>";
    echo "<th>Initiative ID</th><th>Initiative Name</th><th>Initiative Number</th>";
    echo "<th>Sector</th><th>Agency</th>";
    echo "</tr>\n";
    
    $count = 0;
    while ($program = $result->fetch_assoc()) {
        $count++;
        echo "<tr>";
        echo "<td>{$program['program_id']}</td>";
        echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
        echo "<td>" . ($program['program_number'] ?? 'N/A') . "</td>";
        echo "<td>" . ($program['initiative_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($program['initiative_name'] ?? 'No Initiative') . "</td>";
        echo "<td>" . ($program['initiative_number'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($program['sector_name']) . "</td>";
        echo "<td>" . htmlspecialchars($program['agency_name']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<p><strong>Total programs found:</strong> {$count}</p>\n";
    
    // Test filtering by initiative
    echo "<h3>Testing Initiative Filter (initiative_id = 1):</h3>\n";
    $initiative_query = $programs_query . " AND p.initiative_id = ?";
    $stmt2 = $conn->prepare($initiative_query);
    $stmt2->bind_param("iii", $period_id, $period_id, 1);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    $init_count = 0;
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Program ID</th><th>Program Name</th><th>Initiative Name</th>";
    echo "</tr>\n";
    
    while ($program = $result2->fetch_assoc()) {
        $init_count++;
        echo "<tr>";
        echo "<td>{$program['program_id']}</td>";
        echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
        echo "<td>" . htmlspecialchars($program['initiative_name']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo "<p><strong>Programs with initiative_id = 1:</strong> {$init_count}</p>\n";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<p><strong>SQL Query Test Completed!</strong></p>\n";
?>
