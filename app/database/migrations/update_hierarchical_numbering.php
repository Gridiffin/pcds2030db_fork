<?php
/**
 * Migration: Update Existing Programs with Hierarchical Numbers
 * 
 * This script migrates existing programs to use hierarchical numbering
 * based on their assigned initiatives.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/db_connect.php';
require_once __DIR__ . '/../../lib/numbering_helpers.php';

// Check if running from command line
$is_cli = php_sapi_name() === 'cli';

function log_message($message, $is_cli = false) {
    $timestamp = date('Y-m-d H:i:s');
    $formatted = "[{$timestamp}] {$message}";
    
    if ($is_cli) {
        echo $formatted . "\n";
    } else {
        echo "<div style='font-family: monospace; margin: 2px 0;'>{$formatted}</div>";
    }
    
    // Also log to file
    error_log($formatted);
}

function migrate_hierarchical_numbering($dry_run = false) {
    global $conn, $is_cli;
    
    log_message("Starting hierarchical numbering migration (dry_run: " . ($dry_run ? 'true' : 'false') . ")", $is_cli);
    
    try {
        // Get all initiatives with programs
        $initiatives_query = "
            SELECT i.initiative_id, i.initiative_name, i.initiative_number,
                   COUNT(p.program_id) as program_count
            FROM initiatives i
            LEFT JOIN programs p ON i.initiative_id = p.initiative_id
            WHERE i.initiative_number IS NOT NULL
            GROUP BY i.initiative_id
            HAVING program_count > 0
            ORDER BY i.initiative_number
        ";
        
        $result = $conn->query($initiatives_query);
        $initiatives = [];
        
        while ($row = $result->fetch_assoc()) {
            $initiatives[] = $row;
        }
        
        log_message("Found " . count($initiatives) . " initiatives with programs", $is_cli);
        
        $total_updated = 0;
        $total_errors = 0;
        
        foreach ($initiatives as $initiative) {
            log_message("Processing Initiative: {$initiative['initiative_name']} (#{$initiative['initiative_number']})", $is_cli);
            
            // Get all programs for this initiative
            $programs_query = "
                SELECT program_id, program_name, program_number, created_at
                FROM programs 
                WHERE initiative_id = ? 
                ORDER BY created_at, program_id
            ";
            
            $stmt = $conn->prepare($programs_query);
            $stmt->bind_param("i", $initiative['initiative_id']);
            $stmt->execute();
            $programs_result = $stmt->get_result();
            
            $sequence = 1;
            $initiative_updates = 0;
            
            while ($program = $programs_result->fetch_assoc()) {
                $new_number = $initiative['initiative_number'] . '.' . $sequence;
                $old_number = $program['program_number'] ?: '(null)';
                
                log_message("  Program: {$program['program_name']} | {$old_number} -> {$new_number}", $is_cli);
                
                if (!$dry_run) {
                    $update_query = "UPDATE programs SET program_number = ? WHERE program_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("si", $new_number, $program['program_id']);
                    
                    if ($update_stmt->execute()) {
                        $initiative_updates++;
                        $total_updated++;
                    } else {
                        log_message("    ERROR: Failed to update program {$program['program_id']}", $is_cli);
                        $total_errors++;
                    }
                }
                
                $sequence++;
            }
            
            if (!$dry_run) {
                log_message("  Updated {$initiative_updates} programs for this initiative", $is_cli);
            }
        }
        
        // Handle programs not assigned to any initiative
        $orphaned_query = "
            SELECT program_id, program_name, program_number
            FROM programs 
            WHERE initiative_id IS NULL AND program_number IS NOT NULL
        ";
        
        $orphaned_result = $conn->query($orphaned_query);
        $orphaned_count = $orphaned_result->num_rows;
        
        if ($orphaned_count > 0) {
            log_message("Found {$orphaned_count} programs not assigned to initiatives with numbers", $is_cli);
            
            while ($program = $orphaned_result->fetch_assoc()) {
                log_message("  Orphaned: {$program['program_name']} (#{$program['program_number']})", $is_cli);
                
                if (!$dry_run) {
                    // Optionally clear program numbers for orphaned programs
                    // Uncomment the following lines if you want to clear them
                    /*
                    $clear_query = "UPDATE programs SET program_number = NULL WHERE program_id = ?";
                    $clear_stmt = $conn->prepare($clear_query);
                    $clear_stmt->bind_param("i", $program['program_id']);
                    $clear_stmt->execute();
                    */
                }
            }
        }
        
        log_message("Migration completed successfully!", $is_cli);
        log_message("Total programs updated: {$total_updated}", $is_cli);
        log_message("Total errors: {$total_errors}", $is_cli);
        
        return [
            'success' => true,
            'updated' => $total_updated,
            'errors' => $total_errors,
            'orphaned' => $orphaned_count
        ];
        
    } catch (Exception $e) {
        log_message("ERROR: Migration failed - " . $e->getMessage(), $is_cli);
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Check if this is being run directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    if ($is_cli) {
        // Command line interface
        $dry_run = in_array('--dry-run', $argv);
        
        echo "Hierarchical Program Numbering Migration\n";
        echo "=======================================\n\n";
        
        if ($dry_run) {
            echo "DRY RUN MODE - No changes will be made\n\n";
        }
        
        $result = migrate_hierarchical_numbering($dry_run);
        
        if ($result['success']) {
            echo "\nMigration completed successfully!\n";
            if (!$dry_run) {
                echo "Programs updated: {$result['updated']}\n";
                echo "Errors: {$result['errors']}\n";
            }
            exit(0);
        } else {
            echo "\nMigration failed: {$result['error']}\n";
            exit(1);
        }
    } else {
        // Web interface (for testing/admin use)
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Hierarchical Numbering Migration</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .button { padding: 10px 20px; margin: 10px; cursor: pointer; }
                .dry-run { background: #ffc107; }
                .execute { background: #dc3545; color: white; }
                .results { background: #f8f9fa; padding: 15px; margin: 10px 0; border: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <h1>Hierarchical Program Numbering Migration</h1>
            
            <?php if (!isset($_POST['action'])): ?>
                <p><strong>Warning:</strong> This will update program numbers based on initiative hierarchy.</p>
                <p>It's recommended to run a dry run first to see what changes will be made.</p>
                
                <form method="post">
                    <button type="submit" name="action" value="dry_run" class="button dry-run">
                        🔍 Run Dry Run (Preview Changes)
                    </button>
                    <button type="submit" name="action" value="execute" class="button execute" 
                            onclick="return confirm('Are you sure? This will modify program numbers in the database.')">
                        ⚡ Execute Migration
                    </button>
                </form>
            <?php else: ?>
                <div class="results">
                    <?php
                    $dry_run = $_POST['action'] === 'dry_run';
                    $result = migrate_hierarchical_numbering($dry_run);
                    ?>
                </div>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>">← Back to Migration Options</a>
            <?php endif; ?>
        </body>
        </html>
        <?php
    }
}
?>
