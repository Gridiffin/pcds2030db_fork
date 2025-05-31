<?php
require_once 'app/lib/db_connect.php';

echo "<h2>Current Database State - Duplicate Check</h2>";

try {
    $pdo = get_db_connection();
    
    // Check for duplicate program names
    echo "<h3>1. Checking for Duplicate Program Names</h3>";
    $stmt = $pdo->query("
        SELECT program_name, COUNT(*) as count, 
               GROUP_CONCAT(program_id ORDER BY created_at) as program_ids,
               GROUP_CONCAT(created_at ORDER BY created_at) as created_dates
        FROM programs 
        GROUP BY program_name 
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "<div style='color: red; font-weight: bold;'>Found " . count($duplicates) . " duplicate program names:</div>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Program Name</th><th>Count</th><th>Program IDs</th><th>Created Dates</th></tr>";
        
        foreach ($duplicates as $dup) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($dup['program_name']) . "</td>";
            echo "<td style='padding: 8px; text-align: center;'>" . $dup['count'] . "</td>";
            echo "<td style='padding: 8px;'>" . $dup['program_ids'] . "</td>";
            echo "<td style='padding: 8px; font-size: 12px;'>" . $dup['created_dates'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show details for each duplicate
        echo "<h4>Duplicate Details:</h4>";
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup['program_ids']);
            echo "<strong>Program: " . htmlspecialchars($dup['program_name']) . "</strong><br>";
            
            foreach ($ids as $id) {
                $stmt = $pdo->prepare("SELECT * FROM programs WHERE program_id = ?");
                $stmt->execute([trim($id)]);
                $program = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($program) {
                    echo "  ID {$program['program_id']}: Created {$program['created_at']}, Owner: {$program['owner_agency_id']}<br>";
                }
            }
            echo "<br>";
        }
        
    } else {
        echo "<div style='color: green; font-weight: bold;'>✅ No duplicate program names found!</div>";
    }
    
    // Show recent programs
    echo "<h3>2. Recent Programs (Last 10)</h3>";
    $stmt = $pdo->query("
        SELECT program_id, program_name, description, owner_agency_id, created_at
        FROM programs 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    
    $recent_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Owner</th><th>Created</th></tr>";
    
    foreach ($recent_programs as $program) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $program['program_id'] . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($program['program_name']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars(substr($program['description'], 0, 50)) . "...</td>";
        echo "<td style='padding: 8px;'>" . $program['owner_agency_id'] . "</td>";
        echo "<td style='padding: 8px;'>" . $program['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show total counts
    echo "<h3>3. Database Counts</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
    $programs_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM program_submissions");
    $submissions_count = $stmt->fetch()['count'];
    
    echo "<p><strong>Total Programs:</strong> $programs_count</p>";
    echo "<p><strong>Total Submissions:</strong> $submissions_count</p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>ERROR: " . $e->getMessage() . "</div>";
}
?>
