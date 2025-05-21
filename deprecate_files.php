<?php
/**
 * File Deprecation Tool for PCDS2030 Dashboard
 * 
 * This script helps safely move potentially unused files to a 'deprecated' folder
 * rather than deleting them outright. This allows for safe testing while still being
 * able to restore files if needed.
 * 
 * Usage:
 * 1. Review and adjust the $filesToDeprecate array below
 * 2. Run this script from the command line: php deprecate_files.php
 * 3. Test the application thoroughly after moving files
 */

// Configuration
$projectRoot = __DIR__;
$deprecatedFolder = $projectRoot . '/deprecated';
$logFile = $projectRoot . '/deprecated_files_log.txt';

// Create deprecated folder if it doesn't exist
if (!file_exists($deprecatedFolder)) {
    mkdir($deprecatedFolder, 0755, true);
}

// Initialize log file
file_put_contents($logFile, "# Files moved to deprecated folder - " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, "# Format: timestamp | original path | new path | status\n", FILE_APPEND);
file_put_contents($logFile, "# ------------------------------------------------------\n", FILE_APPEND);

// Files to deprecate - UPDATE THIS LIST after verification period
$filesToDeprecate = [
    // Test/Debug files (examples - verify with logging first)
    'check_db.php',
    'test_program_filter.html',
    
    // AJAX endpoints with no JS references - VERIFY WITH LOGGING FIRST!
    'ajax/add_quarter_column.php',
    'ajax/add_reporting_period_column.php',
    
    // API endpoints with no JS references - VERIFY WITH LOGGING FIRST!
    'api/check_metric.php',
    'api/check_outcome.php',
    'api/get_metric_data.php',
    'api/get_outcome_data.php',
    'api/get_periods.php',
    'api/get_recent_reports.php',
    'api/get_sectors.php',
    'api/save_metric_json.php',
];

// Move files to deprecated folder
$processedCount = 0;
$errors = [];

foreach ($filesToDeprecate as $relativeFilePath) {
    $sourcePath = $projectRoot . '/' . $relativeFilePath;
    
    if (!file_exists($sourcePath)) {
        $errors[] = "File not found: $relativeFilePath";
        
        // Log the error
        $logEntry = date('Y-m-d H:i:s') . " | $relativeFilePath | NONE | ERROR: File not found\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        continue;
    }
    
    // Ensure target directory exists
    $targetDir = $deprecatedFolder . '/' . dirname($relativeFilePath);
    if (!file_exists($targetDir) && dirname($relativeFilePath) !== '.') {
        mkdir($targetDir, 0755, true);
    }
    
    // Set target path
    $targetPath = $deprecatedFolder . '/' . $relativeFilePath;
    
    // Move the file
    if (copy($sourcePath, $targetPath)) {
        // Add a .deprecated note to the original file instead of deleting
        $content = file_get_contents($sourcePath);
        $deprecationNotice = <<<'EOD'
<?php
/**
 * @deprecated This file has been deprecated on <?= date('Y-m-d') ?>.
 * A copy of this file exists in the /deprecated folder.
 * This stub exists to catch any unexpected usage of this file.
 * If you see this message, please inform the development team.
 */
 
// Log any access to this deprecated file
$logFile = __DIR__ . '/deprecated_access_log.txt';
$timestamp = date('Y-m-d H:i:s');
$file = __FILE__;
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
$method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        
$logMessage = "$timestamp | $file | $ip | $method | $uri\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Die with error message
die('This file has been deprecated. Please contact the development team if you\'re seeing this message.');

EOD;

        // Replace original with stub
        file_put_contents($sourcePath, $deprecationNotice);
        
        $processedCount++;
        echo "Moved $relativeFilePath to deprecated folder\n";
        
        // Log the action
        $logEntry = date('Y-m-d H:i:s') . " | $relativeFilePath | $targetPath | SUCCESS\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    } else {
        $errors[] = "Failed to move $relativeFilePath";
        
        // Log the error
        $logEntry = date('Y-m-d H:i:s') . " | $relativeFilePath | $targetPath | ERROR: Failed to move\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

// Summary
echo "\n=== Summary ===\n";
echo "Moved $processedCount files to deprecated folder.\n";

if (!empty($errors)) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nInstructions:\n";
echo "1. Test the application thoroughly to ensure nothing broke\n";
echo "2. Check the deprecated_access_log.txt file to see if any deprecated files were accessed\n";
echo "3. After 2-4 weeks with no issues, the stubs can be removed and the deprecated folder can be archived\n";

?>
