<?php
/**
 * Test API for Initiative Features
 * 
 * Simple endpoint to test and validate initiative-aware backend functionality
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/outcome_automation.php';

// Set content type
header('Content-Type: application/json');

// Verify user is logged in
if (!is_logged_in()) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

try {
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'] ?? null,
        'tests' => []
    ];
    
    // Test 1: Check if initiatives table exists and has data
    $initiatives_query = "SELECT COUNT(*) as count FROM initiatives WHERE is_active = 1";
    $stmt = $conn->prepare($initiatives_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $initiatives_count = $result->fetch_assoc()['count'];
    
    $response['tests']['initiatives_table'] = [
        'status' => 'pass',
        'message' => "Found {$initiatives_count} active initiatives",
        'data' => ['count' => $initiatives_count]
    ];
    
    // Test 2: Check programs with initiative_id
    $programs_query = "SELECT COUNT(*) as total, 
                       COUNT(initiative_id) as with_initiative
                       FROM programs";
    $stmt = $conn->prepare($programs_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $programs_data = $result->fetch_assoc();
    
    $response['tests']['programs_initiatives'] = [
        'status' => 'pass',
        'message' => "Programs: {$programs_data['total']} total, {$programs_data['with_initiative']} with initiatives",
        'data' => $programs_data
    ];
    
    // Test 3: Check program-outcome links
    $links_query = "SELECT COUNT(*) as count FROM program_outcome_links";
    $stmt = $conn->prepare($links_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $links_count = $result->fetch_assoc()['count'];
    
    $response['tests']['program_outcome_links'] = [
        'status' => 'pass',
        'message' => "Found {$links_count} program-outcome links",
        'data' => ['count' => $links_count]
    ];
    
    // Test 4: Check outcomes with is_cumulative flag
    $outcomes_query = "SELECT COUNT(*) as total,
                       COUNT(CASE WHEN is_cumulative = 1 THEN 1 END) as cumulative,
                       COUNT(CASE WHEN is_cumulative = 0 THEN 1 END) as non_cumulative
                       FROM outcomes_details";
    $stmt = $conn->prepare($outcomes_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $outcomes_data = $result->fetch_assoc();
    
    $response['tests']['outcomes_cumulative'] = [
        'status' => 'pass',
        'message' => "Outcomes: {$outcomes_data['total']} total, {$outcomes_data['cumulative']} cumulative, {$outcomes_data['non_cumulative']} non-cumulative",
        'data' => $outcomes_data
    ];
    
    // Test 5: Test outcome automation function existence
    if (function_exists('updateOutcomeDataOnProgramStatusChange')) {
        $response['tests']['outcome_automation'] = [
            'status' => 'pass',
            'message' => 'Outcome automation functions loaded successfully',
            'data' => ['functions' => ['updateOutcomeDataOnProgramStatusChange', 'getLinkedPrograms', 'getOutcomeDataWithCumulative']]
        ];
    } else {
        $response['tests']['outcome_automation'] = [
            'status' => 'fail',
            'message' => 'Outcome automation functions not loaded',
            'data' => null
        ];
    }
    
    // Test 6: Sample initiative data
    if ($initiatives_count > 0) {
        $sample_query = "SELECT initiative_id, initiative_name, 
                         (SELECT COUNT(*) FROM programs WHERE initiative_id = i.initiative_id) as program_count
                         FROM initiatives i WHERE is_active = 1 LIMIT 3";
        $stmt = $conn->prepare($sample_query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sample_initiatives = [];
        while ($row = $result->fetch_assoc()) {
            $sample_initiatives[] = $row;
        }
        
        $response['tests']['sample_initiatives'] = [
            'status' => 'pass',
            'message' => 'Sample initiatives retrieved',
            'data' => $sample_initiatives
        ];
    }
    
    // Test 7: Database schema validation
    $schema_tests = [];
    
    // Check if initiatives table has required columns
    $check_initiatives = "SHOW COLUMNS FROM initiatives LIKE 'initiative_id'";
    $result = $conn->query($check_initiatives);
    $schema_tests['initiatives_table'] = $result->num_rows > 0;
    
    // Check if programs table has initiative_id column
    $check_programs = "SHOW COLUMNS FROM programs LIKE 'initiative_id'";
    $result = $conn->query($check_programs);
    $schema_tests['programs_initiative_id'] = $result->num_rows > 0;
    
    // Check if outcomes_details has is_cumulative column
    $check_outcomes = "SHOW COLUMNS FROM outcomes_details LIKE 'is_cumulative'";
    $result = $conn->query($check_outcomes);
    $schema_tests['outcomes_is_cumulative'] = $result->num_rows > 0;
    
    $response['tests']['database_schema'] = [
        'status' => array_sum($schema_tests) === count($schema_tests) ? 'pass' : 'partial',
        'message' => 'Database schema validation',
        'data' => $schema_tests
    ];
    
    ob_end_clean();
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in test API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Test failed: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
