<?php
/**
 * Debug the status check specifically
 */

// Include necessary files
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/admins/statistics.php';

// Get current period
$current_period = get_current_reporting_period();
$period_id = $current_period['period_id'] ?? null;

// Get programs list
$programs = get_admin_programs_list($period_id);

if (!empty($programs)) {
    $program = $programs[0];
    
    echo "Program status value: ";
    var_dump($program['status']);
    echo "\n";
    
    echo "isset(status): " . (isset($program['status']) ? 'true' : 'false') . "\n";
    echo "status !== null: " . ($program['status'] !== null ? 'true' : 'false') . "\n";
    echo "status === null: " . ($program['status'] === null ? 'true' : 'false') . "\n";
    echo "empty(status): " . (empty($program['status']) ? 'true' : 'false') . "\n";
    echo "status == '': " . ($program['status'] == '' ? 'true' : 'false') . "\n";
    echo "status === '': " . ($program['status'] === '' ? 'true' : 'false') . "\n";
    
    echo "\nFull condition: isset(status) && status !== null: " . (isset($program['status']) && $program['status'] !== null ? 'true' : 'false') . "\n";
}
?>