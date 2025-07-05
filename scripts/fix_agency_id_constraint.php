<?php
/**
 * Fix agency_id NOT NULL constraint for admin users
 * This script allows agency_id to be NULL so admin users can be created without agency assignment
 */

// Include database connection
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

echo "Starting agency_id constraint fix...\n";

try {
    // Step 1: Drop the existing foreign key constraint
    echo "Step 1: Dropping foreign key constraint...\n";
    $sql = "ALTER TABLE `users` DROP FOREIGN KEY `users_ibfk_1`";
    if ($conn->query($sql)) {
        echo "✓ Foreign key constraint dropped successfully\n";
    } else {
        echo "✗ Error dropping foreign key: " . $conn->error . "\n";
        // Continue anyway as the constraint might not exist
    }

    // Step 2: Modify the agency_id column to allow NULL values
    echo "Step 2: Modifying agency_id column to allow NULL...\n";
    $sql = "ALTER TABLE `users` MODIFY COLUMN `agency_id` int NULL";
    if ($conn->query($sql)) {
        echo "✓ agency_id column modified successfully\n";
    } else {
        echo "✗ Error modifying column: " . $conn->error . "\n";
        exit(1);
    }

    // Step 3: Re-add the foreign key constraint with NULL allowed
    echo "Step 3: Re-adding foreign key constraint...\n";
    $sql = "ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`) ON DELETE SET NULL ON UPDATE CASCADE";
    if ($conn->query($sql)) {
        echo "✓ Foreign key constraint re-added successfully\n";
    } else {
        echo "✗ Error adding foreign key: " . $conn->error . "\n";
        exit(1);
    }

    // Step 4: Verify the changes
    echo "Step 4: Verifying changes...\n";
    $sql = "SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_id'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "✓ agency_id column details:\n";
        echo "  - Column: " . $row['COLUMN_NAME'] . "\n";
        echo "  - Nullable: " . $row['IS_NULLABLE'] . "\n";
        echo "  - Default: " . ($row['COLUMN_DEFAULT'] ?? 'NULL') . "\n";
        
        if ($row['IS_NULLABLE'] === 'YES') {
            echo "✓ SUCCESS: agency_id column now allows NULL values\n";
        } else {
            echo "✗ ERROR: agency_id column still does not allow NULL values\n";
            exit(1);
        }
    } else {
        echo "✗ ERROR: Could not verify column changes\n";
        exit(1);
    }

    echo "\n🎉 Database schema updated successfully!\n";
    echo "Admin users can now be created without agency assignment.\n";

} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?> 