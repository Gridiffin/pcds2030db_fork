<?php
/**
 * Test file to check admin access and database connectivity
 */

// Include necessary files
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/admin_functions.php';

echo "<h1>Admin System Test</h1>";

// Check database connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check for admin users
    $query = "SELECT username, role FROM users WHERE role = 'admin'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Admin users found:</p>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>Username: " . $row['username'] . " (Role: " . $row['role'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠ No admin users found</p>";
        
        // Create a test admin user
        $test_username = 'admin';
        $test_password = 'admin123';
        $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (username, password, role, is_active, created_at) VALUES (?, ?, 'admin', 1, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $test_username, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Test admin user created!</p>";
            echo "<p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create test admin user: " . $stmt->error . "</p>";
        }
    }
    
    // Check reporting periods
    $periods_query = "SELECT period_id, year, quarter, status FROM reporting_periods ORDER BY year DESC, quarter DESC LIMIT 5";
    $periods_result = $conn->query($periods_query);
    
    if ($periods_result && $periods_result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Reporting periods found:</p>";
        echo "<ul>";
        while ($row = $periods_result->fetch_assoc()) {
            echo "<li>Period ID: " . $row['period_id'] . " - Q" . $row['quarter'] . " " . $row['year'] . " (Status: " . $row['status'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠ No reporting periods found</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Check current session
if (is_logged_in()) {
    echo "<p style='color: green;'>✓ User is logged in</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Username: " . $_SESSION['username'] . "</p>";
    echo "<p>Role: " . $_SESSION['role'] . "</p>";
    
    if (is_admin()) {
        echo "<p style='color: green;'>✓ User has admin privileges</p>";
        echo "<p><a href='app/views/admin/settings/reporting_periods.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Access Reporting Periods</a></p>";
    } else {
        echo "<p style='color: orange;'>⚠ User does not have admin privileges</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ No user is logged in</p>";
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login</a></p>";
}

echo "<hr>";
echo "<p><strong>Links for testing:</strong></p>";
echo "<ul>";
echo "<li><a href='login.php'>Login Page</a></li>";
echo "<li><a href='app/views/admin/dashboard/dashboard.php'>Admin Dashboard</a></li>";
echo "<li><a href='app/views/admin/settings/reporting_periods.php'>Reporting Periods</a></li>";
echo "</ul>";
?>
