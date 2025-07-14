<?php
/**
 * Migration Script: Create Program Agency Assignments Table
 * 
 * Run this script to create the program_agency_assignments table
 * and migrate existing program ownership data.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/lib/db_connect.php';

echo "Starting Program Agency Assignments Migration...\n";

// Read and execute the SQL migration file
$sql_file = __DIR__ . '/create_program_agency_assignments.sql';
if (!file_exists($sql_file)) {
    die("Error: Migration SQL file not found at $sql_file\n");
}

$sql = file_get_contents($sql_file);
$statements = explode(';', $sql);

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    try {
        if ($conn->query($statement)) {
            $success_count++;
            echo "✓ Executed SQL statement successfully\n";
        } else {
            $error_count++;
            echo "✗ Error executing statement: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
}

echo "\nMigration completed!\n";
echo "✓ Successful statements: $success_count\n";
if ($error_count > 0) {
    echo "✗ Failed statements: $error_count\n";
} else {
    echo "🎉 All statements executed successfully!\n";
}

// Verify the migration
$check_table = $conn->query("SHOW TABLES LIKE 'program_agency_assignments'");
if ($check_table && $check_table->num_rows > 0) {
    echo "\n✓ Table 'program_agency_assignments' created successfully\n";
    
    // Check if data was migrated
    $count_assignments = $conn->query("SELECT COUNT(*) as count FROM program_agency_assignments");
    if ($count_assignments) {
        $count = $count_assignments->fetch_assoc()['count'];
        echo "✓ Migrated $count existing program ownership records\n";
    }
} else {
    echo "\n✗ Warning: Table 'program_agency_assignments' not found after migration\n";
}

echo "\nNext Steps:\n";
echo "1. Test the new permission system with existing programs\n";
echo "2. Create admin interface for managing agency assignments\n";
echo "3. Update other program views to use new permission system\n";
echo "4. Consider backing up the database before running in production\n";
?>
