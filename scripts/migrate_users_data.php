<?php
/**
 * User Data Migration Script
 * 
 * This script migrates user data from the old database structure to the new structure.
 * 
 * Old structure: users (user_id, username, password, agency_name, role, sector_id, agency_group_id, created_at, updated_at, is_active)
 * New structure: users (user_id, username, pw, fullname, email, agency_id, role, created_at, updated_at, is_active)
 * 
 * Migration mapping:
 * - user_id -> user_id (same)
 * - username -> username (same)
 * - password -> pw (same)
 * - agency_name -> fullname (use agency_name as fullname)
 * - email -> generate email from username
 * - agency_group_id -> agency_id (map to new agency table)
 * - role -> role (same)
 * - created_at -> created_at (same)
 * - updated_at -> updated_at (same)
 * - is_active -> is_active (same)
 */

// Include new DB config
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

// Include old DB config (create this file if it doesn't exist)
require_once __DIR__ . '/../app/config/old_db_config.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== User Data Migration Script ===\n";
echo "Starting migration at: " . date('Y-m-d H:i:s') . "\n\n";

// Connect to old database
$old_conn = new mysqli(OLD_DB_HOST, OLD_DB_USER, OLD_DB_PASS, OLD_DB_NAME);
if ($old_conn->connect_errno) {
    die("Failed to connect to old database: " . $old_conn->connect_error . "\n");
}

try {
    // Get all users from the old structure
    $old_users_query = "SELECT user_id, username, password, agency_name, role, created_at, updated_at, is_active FROM users ORDER BY user_id";
    $old_users_result = $old_conn->query($old_users_query);
    
    if (!$old_users_result) {
        throw new Exception("Failed to query old users table: " . $old_conn->error);
    }
    
    $old_users = [];
    while ($row = $old_users_result->fetch_assoc()) {
        $old_users[] = $row;
    }
    
    echo "Found " . count($old_users) . " users in old database.\n\n";
    
    // Begin transaction on new DB
    $conn->begin_transaction();
    
    $migrated_count = 0;
    $skipped_count = 0;
    $error_count = 0;
    
    foreach ($old_users as $old_user) {
        try {
            // Check if user already exists in new structure
            $check_query = "SELECT user_id FROM users WHERE username = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("s", $old_user['username']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                echo "Skipping user '{$old_user['username']}' - already exists in new structure.\n";
                $skipped_count++;
                continue;
            }
            
            // Prepare data for new structure
            $user_id = $old_user['user_id'];
            $username = $old_user['username'];
            $password = $old_user['password']; // This is already hashed
            $fullname = $old_user['agency_name'] ?: $old_user['username']; // Use agency_name as fullname, fallback to username
            $email = $old_user['username'] . '@pcds2030.gov.my'; // Generate email from username
            $agency_id = 1; // Default to STIDC for all migrated users
            $role = $old_user['role'];
            $created_at = $old_user['created_at'];
            $updated_at = $old_user['updated_at'];
            $is_active = $old_user['is_active'];
            
            // Insert into new structure
            $insert_query = "INSERT INTO users (user_id, username, pw, fullname, email, agency_id, role, created_at, updated_at, is_active) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("issssisssi", 
                $user_id, $username, $password, $fullname, $email, $agency_id, $role, $created_at, $updated_at, $is_active
            );
            
            if ($insert_stmt->execute()) {
                echo "✓ Migrated user: {$username} (ID: {$user_id}) -> {$fullname} ({$email})\n";
                $migrated_count++;
            } else {
                throw new Exception("Failed to insert user {$username}: " . $insert_stmt->error);
            }
            
        } catch (Exception $e) {
            echo "✗ Error migrating user '{$old_user['username']}': " . $e->getMessage() . "\n";
            $error_count++;
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "\n=== Migration Summary ===\n";
    echo "Total users found: " . count($old_users) . "\n";
    echo "Successfully migrated: {$migrated_count}\n";
    echo "Skipped (already exists): {$skipped_count}\n";
    echo "Errors: {$error_count}\n";
    echo "Migration completed at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    // Rollback on error
    if ($conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    echo "Migration aborted at: " . date('Y-m-d H:i:s') . "\n";
    exit(1);
}

// Close connections
$conn->close();
$old_conn->close();

echo "\nMigration script completed.\n";
?> 