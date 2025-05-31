<?php
// Simple test for duplicate program creation
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/agencies/programs.php';

// Mock session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'agency';

echo "=== Direct Function Test ===\n";

// Test data
$test_data = [
    'program_name' => 'Direct Test Program ' . date('H:i:s'),
    'description' => 'Test description',
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
    'target' => 'Test Target',
    'status_description' => 'Test Status'
];

echo "Test data prepared:\n";
print_r($test_data);

echo "\n--- Before function call ---\n";
$pdo = get_db_connection();
$stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
$before_count = $stmt->fetch()['count'];
echo "Programs count: $before_count\n";

try {
    echo "\n--- Calling create_wizard_program_draft ---\n";
    $result = create_wizard_program_draft($test_data);
    
    echo "Function result:\n";
    print_r($result);
    
    echo "\n--- After function call ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
    $after_count = $stmt->fetch()['count'];
    echo "Programs count: $after_count\n";
    echo "Difference: " . ($after_count - $before_count) . "\n";
    
    if (isset($result['program_id'])) {
        $program_id = $result['program_id'];
        echo "\n--- Checking created program ---\n";
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE program_id = ?");
        $stmt->execute([$program_id]);
        $program = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($program) {
            echo "Program found: " . $program['program_name'] . "\n";
            echo "Description: " . $program['description'] . "\n";
            echo "Extended data: " . ($program['extended_data'] ?? 'NULL') . "\n";
        }
        
        $stmt = $pdo->prepare("SELECT * FROM program_submissions WHERE program_id = ?");
        $stmt->execute([$program_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Submissions created: " . count($submissions) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
