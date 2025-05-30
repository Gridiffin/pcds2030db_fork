<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/agencies/programs.php';

// Test data for creating a program draft
$test_data = [
    'program_name' => 'Test Program Draft',
    'targets' => [
        ['name' => 'Target 1', 'status' => 'In Progress'],
        ['name' => 'Target 2', 'status' => 'Completed']
    ]
];

$result = create_wizard_program_draft($test_data);

if ($result['success']) {
    echo "Program draft created successfully. Program ID: " . $result['program_id'] . "\n";
} else {
    echo "Error: " . $result['error'] . "\n";
}
?>
