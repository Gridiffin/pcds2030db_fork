<?php
echo "=== Simple Database Test ===\n";

try {
    require_once 'app/lib/db_connect.php';
    $pdo = get_db_connection();
    
    echo "Database connection: SUCCESS\n";
    
    // Check if programs table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'programs'");
    if ($stmt->rowCount() > 0) {
        echo "Programs table: EXISTS\n";
        
        // Get programs count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
        $count = $stmt->fetch()['count'];
        echo "Programs count: $count\n";
        
        // Get last 3 programs
        $stmt = $pdo->query("SELECT program_id, program_name, created_at FROM programs ORDER BY created_at DESC LIMIT 3");
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Last 3 programs:\n";
        foreach ($programs as $program) {
            echo "  ID: {$program['program_id']}, Name: {$program['program_name']}, Created: {$program['created_at']}\n";
        }
        
    } else {
        echo "Programs table: NOT FOUND\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
