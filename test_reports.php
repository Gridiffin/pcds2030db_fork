<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "<h2>Database Connection Test</h2>";

// Test database connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection: SUCCESS</p>";
    
    // Check if reports table exists and has data
    $result = $conn->query('SELECT COUNT(*) as count FROM reports');
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Reports table exists with " . $row['count'] . " records</p>";
    }
    
    // Test the getRecentReports function query
    $query = "SELECT r.report_id, r.report_name, r.description, r.pptx_path, 
                     r.generated_at, r.is_public,
                     rp.quarter, rp.year,
                     u.username, u.first_name, u.last_name
              FROM reports r
              INNER JOIN reporting_periods rp ON r.period_id = rp.period_id
              INNER JOIN users u ON r.generated_by = u.user_id
              ORDER BY r.generated_at DESC
              LIMIT 10";
    
    $result = $conn->query($query);
    if ($result) {
        echo "<p style='color: green;'>✓ getRecentReports query: SUCCESS - Found " . $result->num_rows . " reports</p>";
        
        if ($result->num_rows > 0) {
            echo "<h3>Recent Reports:</h3><ul>";
            while ($row = $result->fetch_assoc()) {
                $quarter = (int)$row['quarter'];
                $year = $row['year'];
                $period_display = ($quarter >= 1 && $quarter <= 4) ? "Q{$quarter} {$year}" : 
                                  ($quarter == 5 ? "Half Yearly 1 {$year}" : "Half Yearly 2 {$year}");
                echo "<li><strong>" . htmlspecialchars($row['report_name']) . "</strong> (" . $period_display . ") - Generated on " . date('M j, Y', strtotime($row['generated_at'])) . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>✗ getRecentReports query: ERROR - " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection: FAILED</p>";
    if (mysqli_connect_error()) {
        echo "<p>Error: " . mysqli_connect_error() . "</p>";
    }
}
?>
