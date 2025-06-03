<?php
// Test script to check if our fix is working
echo "Starting test script...\n";

require_once 'app/config/config.php';
echo "Config loaded\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Get the program submission with multiple targets
    $submission_id = 134;
    $program_id = 161;
    
    echo "Testing update program page logic for submission ID: $submission_id, Program ID: $program_id\n\n";
    
    // Simulate the logic from update_program.php
    $stmt = $pdo->prepare("
        SELECT ps.*, p.program_name 
        FROM program_submissions ps
        JOIN programs p ON ps.program_id = p.program_id
        WHERE ps.submission_id = ? AND ps.program_id = ?
    ");
    $stmt->execute([$submission_id, $program_id]);
    $submission = $stmt->fetch();
    
    if ($submission) {
        echo "Found submission:\n";
        echo "Program Name: " . $submission['program_name'] . "\n";
        echo "Is Draft: " . ($submission['is_draft'] ? 'Yes' : 'No') . "\n\n";
        
        // Parse the content JSON
        $content = json_decode($submission['content_json'], true);
        
        echo "Raw content_json:\n";
        echo $submission['content_json'] . "\n\n";
        
        // Test our legacy target parsing logic
        if (isset($content['target']) && isset($content['status_description'])) {
            echo "=== TESTING LEGACY TARGET PARSING ===\n";
            
            $target_text = $content['target'];
            $status_description = $content['status_description'];
            
            echo "Raw target: $target_text\n";
            echo "Raw status: $status_description\n\n";
            
            // Check if legacy format (semicolon separated)
            if (strpos($target_text, ';') !== false) {
                echo "✓ Legacy format detected (contains semicolons)\n";
                
                $target_parts = array_map('trim', explode(';', $target_text));
                $status_parts = array_map('trim', explode(';', $status_description));
                
                echo "Split into " . count($target_parts) . " target parts:\n";
                
                $targets = [];
                foreach ($target_parts as $index => $target_part) {
                    if (!empty($target_part)) {
                        $target_obj = [
                            'target_text' => $target_part,
                            'status_description' => isset($status_parts[$index]) ? $status_parts[$index] : ''
                        ];
                        $targets[] = $target_obj;
                        
                        echo "Target " . ($index + 1) . ":\n";
                        echo "  Text: " . $target_part . "\n";
                        echo "  Status: " . (isset($status_parts[$index]) ? $status_parts[$index] : '(none)') . "\n";
                    }
                }
                
                echo "\n✅ RESULT: Successfully parsed " . count($targets) . " separate targets!\n";
                
                // Show how this would appear in the form
                echo "\n=== HOW THIS APPEARS IN THE UPDATE FORM ===\n";
                foreach ($targets as $i => $target) {
                    echo "Target Row " . ($i + 1) . ":\n";
                    echo "  Target Input: '" . htmlspecialchars($target['target_text']) . "'\n";
                    echo "  Status Input: '" . htmlspecialchars($target['status_description']) . "'\n";
                    echo "\n";
                }
                
            } else {
                echo "❌ Not legacy format (no semicolons found)\n";
            }
        } else {
            echo "❌ No target/status_description found in content\n";
        }
        
    } else {
        echo "❌ No submission found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
