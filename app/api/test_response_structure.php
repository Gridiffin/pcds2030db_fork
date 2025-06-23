<?php
/**
 * Test the complete response structure of initiative-aware programs
 */

require_once '../config/config.php';
require_once '../lib/db_connect.php';

// Test with period_id = 2
$period_id = 2;

echo "<h2>Testing Complete Response Structure for Period {$period_id}</h2>\n";

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
    
    // Build the response structure like in the actual API
    $programs = [];
    while ($program = $result->fetch_assoc()) {
        if (!isset($programs[$program['sector_id']])) {
            $programs[$program['sector_id']] = [
                'sector_name' => $program['sector_name'],
                'programs' => []
            ];
        }
        
        $programs[$program['sector_id']]['programs'][] = [
            'program_id' => $program['program_id'],
            'program_name' => $program['program_name'],
            'program_number' => $program['program_number'],
            'initiative_id' => $program['initiative_id'],
            'initiative_name' => $program['initiative_name'],
            'initiative_number' => $program['initiative_number'],
            'agency_name' => $program['agency_name'],
            'owner_agency_id' => $program['owner_agency_id']
        ];
    }
    
    // Display the structured response
    echo "<h3>Structured Response (JSON):</h3>\n";
    echo "<pre style='background-color: #f8f8f8; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>\n";
    echo json_encode(['success' => true, 'programs' => $programs], JSON_PRETTY_PRINT);
    echo "</pre>\n";
    
    // Show summary
    echo "<h3>Response Summary:</h3>\n";
    echo "<ul>\n";
    foreach ($programs as $sector_id => $sector_data) {
        $program_count = count($sector_data['programs']);
        $with_initiatives = 0;
        foreach ($sector_data['programs'] as $prog) {
            if ($prog['initiative_id'] !== null) $with_initiatives++;
        }
        echo "<li><strong>{$sector_data['sector_name']}</strong>: {$program_count} programs ({$with_initiatives} linked to initiatives)</li>\n";
    }
    echo "</ul>\n";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<p><strong>Response Structure Test Completed!</strong></p>\n";
?>
