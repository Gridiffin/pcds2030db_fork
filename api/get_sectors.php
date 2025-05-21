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
