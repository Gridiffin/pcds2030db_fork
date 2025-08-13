<?php
/**
 * Test script to verify program validation fix
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/numbering_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_validation.php';

echo "<h2>Testing Program Validation with Initiative</h2>\n";

// Test 1: Function exists
if (function_exists('validate_program_data')) {
    echo "✅ validate_program_data() function exists<br>\n";
} else {
    echo "❌ validate_program_data() function does not exist<br>\n";
    exit;
}

// Test 2: Test validation with initiative (the problematic case)
$test_data = [
    'program_name' => 'Test Program',
    'program_description' => 'Test Description',
    'program_number' => '1.1',
    'initiative_id' => 1, // Test with initiative ID 1 if it exists
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31'
];

echo "<br>Testing validation with initiative_id = 1:<br>\n";

try {
    $validation_result = validate_program_data($test_data);
    echo "✅ Program validation completed without fatal error<br>\n";
    echo "Validation result: " . ($validation_result['success'] ? 'SUCCESS' : 'VALIDATION ERRORS') . "<br>\n";
    
    if (!$validation_result['success'] && !empty($validation_result['errors'])) {
        echo "Validation errors found (this is expected):<br>\n";
        foreach ($validation_result['errors'] as $field => $error) {
            echo "- {$field}: {$error}<br>\n";
        }
    }
    
} catch (Error $e) {
    echo "❌ Fatal error still occurring: " . $e->getMessage() . "<br>\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "<br>\n";
}

// Test 3: Test validation without initiative (should work fine)
$test_data_no_initiative = [
    'program_name' => 'Test Program Without Initiative',
    'program_description' => 'Test Description',
    'program_number' => '2.1',
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31'
];

echo "<br>Testing validation without initiative:<br>\n";

try {
    $validation_result = validate_program_data($test_data_no_initiative);
    echo "✅ Program validation without initiative completed successfully<br>\n";
    echo "Validation result: " . ($validation_result['success'] ? 'SUCCESS' : 'VALIDATION ERRORS') . "<br>\n";
    
} catch (Error $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "<br>\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>\n";
}

echo "<br><strong>Testing complete!</strong><br>\n";
?>
