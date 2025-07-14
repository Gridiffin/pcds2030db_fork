<?php
/**
 * Database Schema Update Script Runner
 * This script runs the SQL commands to update the old database schema to match the new one
 */

// Include database configuration
require_once __DIR__ . '/../app/config/config.php';

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "Connected to database successfully.\n";
    echo "Starting schema update...\n\n";

    // Read the SQL file
    $sqlFile = __DIR__ . '/update_old_db_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt) && !preg_match('/^(--|\/\*)/', $stmt); }
    );

    $pdo->beginTransaction();

    foreach ($statements as $i => $statement) {
        if (empty(trim($statement))) continue;
        
        echo "Executing statement " . ($i + 1) . "...\n";
        echo "SQL: " . substr($statement, 0, 100) . "...\n";
        
        try {
            $pdo->exec($statement);
            echo "✓ Success\n\n";
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n\n";
            throw $e;
        }
    }

    $pdo->commit();
    echo "Schema update completed successfully!\n";

    // Verify the changes
    echo "\nVerifying changes...\n";
    
    // Check users table structure
    $stmt = $pdo->query("DESCRIBE users");
    $usersColumns = $stmt->fetchAll();
    echo "Users table columns:\n";
    foreach ($usersColumns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\nPrograms table columns:\n";
    $stmt = $pdo->query("DESCRIBE programs");
    $programsColumns = $stmt->fetchAll();
    foreach ($programsColumns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?> 