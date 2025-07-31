<?php
/**
 * Test database connection
 */

require_once __DIR__ . '/app/config/config.php';

echo "=== DATABASE CONNECTION TEST ===<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "<br>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "❌ Connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ Database connection successful!<br>";
        
        // Test if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "✅ Users table exists<br>";
            
            // Check if there are any users
            $result = $conn->query("SELECT COUNT(*) as count FROM users");
            $row = $result->fetch_assoc();
            echo "📊 Total users in database: " . $row['count'] . "<br>";
        } else {
            echo "❌ Users table does not exist<br>";
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}
?>