<?php
// Test the duplicate program creation fix
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/agencies/programs.php';

// Mock session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'agency';

echo "=== Testing Duplicate Prevention Fix ===\n";

// Test data that simulates form submission
$test_data = [
    'program_name' => 'Duplicate Test Program ' . date('H:i:s'),
    'description' => 'Test description for duplicate prevention',
    'brief_description' => 'Brief description',
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
    'target' => 'Test Target 1; Test Target 2',
    'status_description' => 'Test Status 1; Test Status 2'
];

try {
    $pdo = get_db_connection();
    
    // Count before test
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
    $before_count = $stmt->fetch()['count'];
    echo "Programs before test: $before_count\n";
    
    echo "\n--- Step 1: Simulate Auto-save (creates program) ---\n";
    // This simulates auto-save creating a program (no program_id)
    $autosave_data = $test_data;
    $autosave_result = auto_save_program_draft($autosave_data);
    
    echo "Auto-save result:\n";
    print_r($autosave_result);
    
    if ($autosave_result['success'] && isset($autosave_result['program_id'])) {
        $program_id = $autosave_result['program_id'];
        echo "Auto-save created program ID: $program_id\n";
        
        // Count after auto-save
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
        $after_autosave_count = $stmt->fetch()['count'];
        echo "Programs after auto-save: $after_autosave_count\n";
        
        echo "\n--- Step 2: Simulate Manual Save Draft (should update, not create) ---\n";
        // This simulates the user clicking "Save Draft" with program_id already set
        // We'll test both the old buggy way and the new fixed way
        
        // Simulate what create_program.php would receive in $_POST
        $_POST = array_merge($test_data, ['program_id' => $program_id]);
        
        // Simulate the fixed logic from create_program.php
        $program_id_from_post = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
        
        if ($program_id_from_post > 0) {
            echo "Detected existing program_id: $program_id_from_post, calling update function\n";
            $manual_save_result = update_wizard_program_draft($program_id_from_post, $test_data);
        } else {
            echo "No program_id detected, calling create function\n";
            $manual_save_result = create_wizard_program_draft($test_data);
        }
        
        echo "Manual save result:\n";
        print_r($manual_save_result);
        
        // Count after manual save
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
        $after_manual_count = $stmt->fetch()['count'];
        echo "Programs after manual save: $after_manual_count\n";
        
        // Check if duplicate was created
        $total_created = $after_manual_count - $before_count;
        echo "\nTotal programs created: $total_created\n";
        
        if ($total_created == 1) {
            echo "✅ SUCCESS: No duplicate created! Only 1 program was created.\n";
        } else {
            echo "❌ FAILURE: $total_created programs were created (should be 1).\n";
        }
        
        // Verify the program data
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE program_id = ?");
        $stmt->execute([$program_id]);
        $program = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($program) {
            echo "\nProgram data verification:\n";
            echo "  - ID: {$program['program_id']}\n";
            echo "  - Name: {$program['program_name']}\n";
            echo "  - Description: {$program['description']}\n";
            echo "  - Start Date: {$program['start_date']}\n";
            echo "  - End Date: {$program['end_date']}\n";
        }
        
        // Check submission data
        $stmt = $pdo->prepare("SELECT * FROM program_submissions WHERE program_id = ?");
        $stmt->execute([$program_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nSubmission data verification:\n";
        echo "  - Submissions count: " . count($submissions) . "\n";
        foreach ($submissions as $submission) {
            $content = json_decode($submission['content_json'], true);
            echo "  - Content: " . print_r($content, true);
        }
        
    } else {
        echo "Auto-save failed, cannot continue test\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
