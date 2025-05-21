<?php
/**
 * File Access Logger for PCDS2030 Dashboard
 * 
 * This script adds logging functionality to specified PHP files to track which files
 * are actually being accessed during normal application usage.
 * 
 * Usage:
 * 1. Adjust the $filesToInstrument array below to specify which files to monitor
 * 2. Run this script from the command line: php add_file_logging.php
 * 3. Use the application normally for 1-2 weeks
 * 4. Check the access_log.txt file to see which files were accessed
 */

// Configuration
$projectRoot = __DIR__;
$logFile = $projectRoot . '/file_access_log.txt';
$loggingCode = <<<'EOD'

// BEGIN LOGGING CODE - FOR CLEANUP ANALYSIS - TEMPORARY
if (!function_exists('log_file_access')) {
    function log_file_access() {
        $logFile = dirname(__DIR__, 1) . '/file_access_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $file = str_replace('\\', '/', __FILE__);
        $file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        
        $logMessage = "$timestamp | $file | $ip | $method | $uri\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return true;
    }
}
log_file_access();
// END LOGGING CODE

EOD;

// Files to instrument with logging
$filesToInstrument = [
    // Test/Debug files
    'check_db.php',
    
    // AJAX endpoints with no JS references
    'ajax/add_quarter_column.php',
    'ajax/add_reporting_period_column.php',
    
    // API endpoints with no JS references
    'api/check_metric.php',
    'api/check_outcome.php',
    'api/get_metric_data.php',
    'api/get_outcome_data.php',
    'api/get_periods.php',
    'api/get_recent_reports.php',
    'api/get_sectors.php',
    'api/save_metric_json.php',
    
    // Helper files to verify usage
    'includes/history_helpers.php',
    'includes/status_helpers.php',
];

// Initialize log file
file_put_contents($logFile, "# File Access Log - Created " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, "# Format: timestamp | file path | IP | method | URI\n", FILE_APPEND);
file_put_contents($logFile, "# ------------------------------------------------------\n", FILE_APPEND);

// Add logging code to each file
$processedCount = 0;
$errors = [];

foreach ($filesToInstrument as $relativeFilePath) {
    $filePath = $projectRoot . '/' . $relativeFilePath;
    
    if (!file_exists($filePath)) {
        $errors[] = "File not found: $relativeFilePath";
        continue;
    }
    
    // Get current file content
    $content = file_get_contents($filePath);
    
    // Check if logging code is already added
    if (strpos($content, 'BEGIN LOGGING CODE') !== false) {
        echo "Logging already added to $relativeFilePath\n";
        continue;
    }
    
    // Create backup
    file_put_contents($filePath . '.bak', $content);
    
    // Look for PHP opening tag
    $phpPos = strpos($content, '<?php');
    
    if ($phpPos !== false) {
        // Add code after <?php
        $newContent = substr($content, 0, $phpPos + 5) . "\n" . $loggingCode . substr($content, $phpPos + 5);
        
        // Write modified content
        if (file_put_contents($filePath, $newContent)) {
            $processedCount++;
            echo "Added logging to $relativeFilePath\n";
        } else {
            $errors[] = "Failed to write to $relativeFilePath";
        }
    } else {
        $errors[] = "No PHP opening tag found in $relativeFilePath";
    }
}

// Summary
echo "\n=== Summary ===\n";
echo "Added logging to $processedCount files.\n";

if (!empty($errors)) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nInstructions:\n";
echo "1. Use the application normally for 1-2 weeks\n";
echo "2. Check the access_log.txt file to see which files were accessed\n";
echo "3. Remove the logging code from files or restore the backups (.bak files)\n";

?>
