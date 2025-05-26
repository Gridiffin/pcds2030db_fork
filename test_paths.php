<?php
// Test to compare ROOT_PATH vs PROJECT_ROOT_PATH

echo "Testing path definitions:\n";
echo "========================\n";

// ROOT_PATH as defined in config.php
define('ROOT_PATH', rtrim(dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
echo "ROOT_PATH (from config.php): " . ROOT_PATH . "\n";

// Simulate PROJECT_ROOT_PATH as it would be defined in app/views/admin/settings/
// For a file in app/views/admin/settings/, __DIR__ would be the full path to that directory
$adminSettingsDir = __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'settings';
$projectRootPath = rtrim(dirname(dirname(dirname($adminSettingsDir))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
echo "PROJECT_ROOT_PATH (from admin files): " . $projectRootPath . "\n";

echo "\nComparison:\n";
echo "Are they the same? " . (ROOT_PATH === $projectRootPath ? 'YES' : 'NO') . "\n";

echo "\nTesting file paths:\n";
echo "ROOT_PATH + 'app/lib/db_connect.php': " . ROOT_PATH . 'app/lib/db_connect.php' . "\n";
echo "PROJECT_ROOT_PATH + 'app/lib/db_connect.php': " . $projectRootPath . 'app/lib/db_connect.php' . "\n";

// Check if these files actually exist
echo "\nFile existence check:\n";
echo "ROOT_PATH file exists: " . (file_exists(ROOT_PATH . 'app/lib/db_connect.php') ? 'YES' : 'NO') . "\n";
echo "PROJECT_ROOT_PATH file exists: " . (file_exists($projectRootPath . 'app/lib/db_connect.php') ? 'YES' : 'NO') . "\n";
?>
