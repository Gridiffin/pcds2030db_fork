<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'focal';
$_SESSION['agency_id'] = 1;

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/agencies/programs.php';

$data = [
    'program_name' => 'Test Program - New Schema',
    'description' => 'Test description for new schema',
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'initiative_id' => 1 // <-- required by schema
];

$result = create_agency_program($data);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT);
?> 