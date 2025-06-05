<?php
/**
 * Audit Log Maintenance Script
 * 
 * This script handles archiving old audit logs and database maintenance
 * to ensure optimal performance and compliance with data retention policies.
 * 
 * Usage: php audit_log_maintenance.php [--archive|--cleanup|--stats]
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

// Configuration
const ARCHIVE_AFTER_MONTHS = 24; // Archive logs older than 2 years
const DELETE_AFTER_MONTHS = 60;  // Delete logs older than 5 years (from archive)
const BATCH_SIZE = 1000;         // Process records in batches

/**
 * Display usage information
 */
function show_usage() {
    echo "Audit Log Maintenance Script\n";
    echo "Usage: php audit_log_maintenance.php [option]\n\n";
    echo "Options:\n";
    echo "  --archive    Archive old audit logs\n";
    echo "  --cleanup    Clean up very old archived logs\n";
    echo "  --stats      Show audit log statistics\n";
    echo "  --help       Show this help message\n\n";
}

/**
 * Get audit log statistics
 */
function get_audit_stats() {
    global $conn;
    
    echo "=== Audit Log Statistics ===\n\n";
    
    // Total logs
    $result = $conn->query("SELECT COUNT(*) as total FROM audit_logs");
    $total = $result->fetch_assoc()['total'];
    echo "Total audit logs: " . number_format($total) . "\n";
    
    // Date range
    $result = $conn->query("SELECT MIN(created_at) as oldest, MAX(created_at) as newest FROM audit_logs");
    $dates = $result->fetch_assoc();
    echo "Date range: {$dates['oldest']} to {$dates['newest']}\n";
    
    // Logs by status
    $result = $conn->query("SELECT status, COUNT(*) as count FROM audit_logs GROUP BY status");
    echo "\nLogs by status:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['status']}: " . number_format($row['count']) . "\n";
    }
    
    // Top actions
    $result = $conn->query("SELECT action, COUNT(*) as count FROM audit_logs GROUP BY action ORDER BY count DESC LIMIT 10");
    echo "\nTop 10 actions:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['action']}: " . number_format($row['count']) . "\n";
    }
    
    // Table size
    $result = $conn->query("
        SELECT 
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'DB Size in MB' 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() AND table_name = 'audit_logs'
    ");
    $size = $result->fetch_assoc()['DB Size in MB'];
    echo "\nTable size: {$size} MB\n";
    
    // Archive table stats (if exists)
    $result = $conn->query("SHOW TABLES LIKE 'audit_logs_archive'");
    if ($result->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as total FROM audit_logs_archive");
        $archived = $result->fetch_assoc()['total'];
        echo "Archived logs: " . number_format($archived) . "\n";
    }
    
    echo "\n";
}

/**
 * Create archive table if it doesn't exist
 */
function ensure_archive_table() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS audit_logs_archive (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT 0,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        status ENUM('success', 'failure') DEFAULT 'success',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_archived_created_at (created_at),
        INDEX idx_archived_user_id (user_id),
        INDEX idx_archived_action (action),
        INDEX idx_archived_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        die("Error creating archive table: " . $conn->error . "\n");
    }
}

/**
 * Archive old audit logs
 */
function archive_old_logs() {
    global $conn;
    
    echo "=== Archiving Old Audit Logs ===\n\n";
    
    // Calculate cutoff date
    $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . ARCHIVE_AFTER_MONTHS . ' months'));
    echo "Archiving logs older than: $cutoff_date\n";
    
    // Ensure archive table exists
    ensure_archive_table();
    
    // Count logs to archive
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM audit_logs WHERE created_at < ?");
    $stmt->bind_param('s', $cutoff_date);
    $stmt->execute();
    $total_to_archive = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
    
    if ($total_to_archive == 0) {
        echo "No logs found that need archiving.\n\n";
        return;
    }
    
    echo "Found " . number_format($total_to_archive) . " logs to archive\n";
    echo "Processing in batches of " . BATCH_SIZE . "...\n\n";
    
    $archived_count = 0;
    $batch_num = 1;
    
    // Begin transaction for consistency
    $conn->begin_transaction();
    
    try {
        do {
            echo "Processing batch $batch_num... ";
            
            // Get batch of logs to archive
            $stmt = $conn->prepare("
                SELECT id, user_id, action, details, ip_address, status, created_at 
                FROM audit_logs 
                WHERE created_at < ? 
                ORDER BY created_at 
                LIMIT ?
            ");
            $stmt->bind_param('si', $cutoff_date, BATCH_SIZE);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $batch_count = 0;
            $ids_to_delete = [];
            
            // Insert into archive table
            $archive_stmt = $conn->prepare("
                INSERT INTO audit_logs_archive (user_id, action, details, ip_address, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            while ($row = $result->fetch_assoc()) {
                $archive_stmt->bind_param('isssss', 
                    $row['user_id'], 
                    $row['action'], 
                    $row['details'], 
                    $row['ip_address'], 
                    $row['status'], 
                    $row['created_at']
                );
                
                if ($archive_stmt->execute()) {
                    $ids_to_delete[] = $row['id'];
                    $batch_count++;
                } else {
                    throw new Exception("Failed to archive log ID {$row['id']}: " . $archive_stmt->error);
                }
            }
            
            $stmt->close();
            $archive_stmt->close();
            
            // Delete from main table
            if (!empty($ids_to_delete)) {
                $placeholders = str_repeat('?,', count($ids_to_delete) - 1) . '?';
                $delete_stmt = $conn->prepare("DELETE FROM audit_logs WHERE id IN ($placeholders)");
                $delete_stmt->bind_param(str_repeat('i', count($ids_to_delete)), ...$ids_to_delete);
                
                if (!$delete_stmt->execute()) {
                    throw new Exception("Failed to delete archived logs: " . $delete_stmt->error);
                }
                $delete_stmt->close();
            }
            
            $archived_count += $batch_count;
            echo "archived $batch_count logs\n";
            
            $batch_num++;
            
        } while ($batch_count == BATCH_SIZE);
        
        $conn->commit();
        echo "\nArchiving completed successfully!\n";
        echo "Total logs archived: " . number_format($archived_count) . "\n\n";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "Transaction rolled back.\n\n";
    }
}

/**
 * Clean up very old archived logs
 */
function cleanup_old_archives() {
    global $conn;
    
    echo "=== Cleaning Up Old Archived Logs ===\n\n";
    
    // Check if archive table exists
    $result = $conn->query("SHOW TABLES LIKE 'audit_logs_archive'");
    if ($result->num_rows == 0) {
        echo "No archive table found. Nothing to clean up.\n\n";
        return;
    }
    
    // Calculate cutoff date for deletion
    $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . DELETE_AFTER_MONTHS . ' months'));
    echo "Deleting archived logs older than: $cutoff_date\n";
    
    // Count logs to delete
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM audit_logs_archive WHERE created_at < ?");
    $stmt->bind_param('s', $cutoff_date);
    $stmt->execute();
    $total_to_delete = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
    
    if ($total_to_delete == 0) {
        echo "No archived logs found that need deletion.\n\n";
        return;
    }
    
    echo "Found " . number_format($total_to_delete) . " archived logs to delete\n";
    
    // Confirm deletion (in production, you might want to skip this)
    echo "This will permanently delete old archived logs. Continue? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $confirm = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($confirm) !== 'y') {
        echo "Operation cancelled.\n\n";
        return;
    }
    
    // Delete old archived logs
    $stmt = $conn->prepare("DELETE FROM audit_logs_archive WHERE created_at < ?");
    $stmt->bind_param('s', $cutoff_date);
    
    if ($stmt->execute()) {
        $deleted_count = $stmt->affected_rows;
        echo "Successfully deleted " . number_format($deleted_count) . " old archived logs.\n\n";
    } else {
        echo "ERROR: Failed to delete old archived logs: " . $stmt->error . "\n\n";
    }
    
    $stmt->close();
}

/**
 * Optimize audit log tables
 */
function optimize_tables() {
    global $conn;
    
    echo "=== Optimizing Audit Log Tables ===\n\n";
    
    // Optimize main table
    echo "Optimizing audit_logs table... ";
    if ($conn->query("OPTIMIZE TABLE audit_logs")) {
        echo "✓\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
    
    // Optimize archive table if it exists
    $result = $conn->query("SHOW TABLES LIKE 'audit_logs_archive'");
    if ($result->num_rows > 0) {
        echo "Optimizing audit_logs_archive table... ";
        if ($conn->query("OPTIMIZE TABLE audit_logs_archive")) {
            echo "✓\n";
        } else {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
    
    echo "\n";
}

// Main execution
if ($argc < 2) {
    show_usage();
    exit(1);
}

$option = $argv[1];

switch ($option) {
    case '--archive':
        archive_old_logs();
        optimize_tables();
        break;
        
    case '--cleanup':
        cleanup_old_archives();
        optimize_tables();
        break;
        
    case '--stats':
        get_audit_stats();
        break;
        
    case '--help':
        show_usage();
        break;
        
    default:
        echo "Unknown option: $option\n\n";
        show_usage();
        exit(1);
}

$conn->close();
echo "Maintenance script completed.\n";
?>
