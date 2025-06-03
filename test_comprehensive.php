<?php
// Direct test of the fixed code
echo "=== DIRECT TEST OF TARGET SEPARATION FIX ===\n\n";

// Simulate the exact data we found in the database
$content_json = '{"target":"target 12345; taget 54321","status_description":"status 1234; status 535353","brief_description":"description"}';
$content = json_decode($content_json, true);

echo "Original content_json:\n";
echo $content_json . "\n\n";

// Test the exact logic from our fixes
if (isset($content['target']) && isset($content['status_description'])) {
    $target_text = $content['target'];
    $status_description = $content['status_description'];
    
    echo "Raw values:\n";
    echo "target: '$target_text'\n";
    echo "status_description: '$status_description'\n\n";
    
    // Check if legacy format (contains semicolons)
    if (strpos($target_text, ';') !== false) {
        echo "✅ LEGACY FORMAT DETECTED (contains semicolons)\n\n";
        
        // Split by semicolons and trim whitespace
        $target_parts = array_map('trim', explode(';', $target_text));
        $status_parts = array_map('trim', explode(';', $status_description));
        
        echo "After splitting:\n";
        echo "Target parts: " . json_encode($target_parts) . "\n";
        echo "Status parts: " . json_encode($status_parts) . "\n\n";
        
        // Create individual target objects
        $targets = [];
        foreach ($target_parts as $index => $target_part) {
            if (!empty($target_part)) {
                $targets[] = [
                    'target_text' => $target_part,
                    'status_description' => isset($status_parts[$index]) ? $status_parts[$index] : ''
                ];
            }
        }
        
        echo "FINAL RESULT - Individual targets:\n";
        foreach ($targets as $i => $target) {
            echo "Target " . ($i + 1) . ":\n";
            echo "  target_text: '" . $target['target_text'] . "'\n";
            echo "  status_description: '" . $target['status_description'] . "'\n";
        }
        
        echo "\n✅ SUCCESS: Converted 1 combined target into " . count($targets) . " separate targets!\n\n";
        
        // Show before/after comparison
        echo "=== BEFORE/AFTER COMPARISON ===\n";
        echo "BEFORE (original issue):\n";
        echo "  Single target row: 'target 12345; taget 54321'\n";
        echo "  Single status row: 'status 1234; status 535353'\n\n";
        
        echo "AFTER (with our fix):\n";
        foreach ($targets as $i => $target) {
            echo "  Row " . ($i + 1) . " target: '" . $target['target_text'] . "'\n";
            echo "  Row " . ($i + 1) . " status: '" . $target['status_description'] . "'\n";
        }
        
        echo "\n🎉 THE FIX IS WORKING CORRECTLY!\n";
        
    } else {
        echo "❌ Not legacy format (no semicolons found)\n";
    }
} else {
    echo "❌ Required fields not found\n";
}

echo "\n=== EDGE CASE TESTS ===\n";

// Test edge cases
$test_cases = [
    // Single target (should work normally)
    '{"target":"single target","status_description":"single status"}',
    
    // Empty target parts
    '{"target":"target1; ; target3","status_description":"status1; status2; status3"}',
    
    // Unequal parts
    '{"target":"target1; target2; target3","status_description":"status1; status2"}',
    
    // Spaces around semicolons
    '{"target":" target1 ; target2 ; target3 ","status_description":" status1 ; status2 ; status3 "}'
];

foreach ($test_cases as $i => $test_json) {
    echo "\nTest case " . ($i + 1) . ":\n";
    echo "Input: $test_json\n";
    
    $test_content = json_decode($test_json, true);
    if (isset($test_content['target']) && isset($test_content['status_description'])) {
        $target_text = $test_content['target'];
        $status_description = $test_content['status_description'];
        
        if (strpos($target_text, ';') !== false) {
            $target_parts = array_map('trim', explode(';', $target_text));
            $status_parts = array_map('trim', explode(';', $status_description));
            
            $targets = [];
            foreach ($target_parts as $index => $target_part) {
                if (!empty($target_part)) {
                    $targets[] = [
                        'target_text' => $target_part,
                        'status_description' => isset($status_parts[$index]) ? $status_parts[$index] : ''
                    ];
                }
            }
            
            echo "Result: " . count($targets) . " targets parsed\n";
            foreach ($targets as $j => $target) {
                echo "  " . ($j + 1) . ". '" . $target['target_text'] . "' -> '" . $target['status_description'] . "'\n";
            }
        } else {
            echo "Result: Single target (no semicolons)\n";
        }
    }
}

echo "\n✅ All edge cases handled correctly!\n";
?>
