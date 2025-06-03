<?php
// Test script to find programs with multiple targets
require_once 'app/config/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
      // First, let's see what tables exist
    echo "Available tables:\n";
    $stmt = $pdo->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "\n";
    
    // Check structure of program_submissions table
    echo "Structure of program_submissions table:\n";
    $stmt = $pdo->prepare("DESCRIBE program_submissions");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    echo "\n";
    
    // Query for programs with multiple targets (containing semicolons)
    $stmt = $pdo->prepare("SELECT * FROM program_submissions WHERE content_json LIKE '%;%' LIMIT 5");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Programs with potential multiple targets:\n";
    echo "Found " . count($results) . " programs\n\n";
      foreach ($results as $row) {
        echo "Record found:\n";
        foreach ($row as $key => $value) {
            if ($key === 'content_json') {
                echo "$key:\n";
                $content = json_decode($value, true);
                if (isset($content['target'])) {
                    echo "  Target: " . $content['target'] . "\n";
                    // Check if it contains semicolons
                    if (strpos($content['target'], ';') !== false) {
                        echo "  -> Contains multiple targets (semicolon found)\n";
                        $target_parts = array_map('trim', explode(';', $content['target']));
                        echo "  -> Split into " . count($target_parts) . " targets:\n";
                        foreach ($target_parts as $i => $part) {
                            echo "     " . ($i + 1) . ". " . $part . "\n";
                        }
                    }
                }
                if (isset($content['status_description'])) {
                    echo "  Status: " . $content['status_description'] . "\n";
                    if (strpos($content['status_description'], ';') !== false) {
                        echo "  -> Contains multiple status descriptions\n";
                    }
                }
            } else {
                echo "$key: $value\n";
            }
        }
        echo "---\n";
    }
      // Also check for any programs in the programs table
    echo "\nChecking programs table as well:\n";
    $stmt2 = $pdo->prepare("DESCRIBE programs");
    $stmt2->execute();
    $columns2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns2 as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    $stmt2 = $pdo->prepare("SELECT * FROM programs LIMIT 3");
    $stmt2->execute();
    $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results2 as $row) {
        echo "Programs table record:\n";
        foreach ($row as $key => $value) {
            echo "$key: $value\n";
        }
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
