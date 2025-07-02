<?php
/**
 * Database connectivity test for dhtmlxGantt
 */

require_once 'app/config/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Database Connectivity Test</h2>
    
    <?php
    echo "<h3>Configuration Check:</h3>";
    echo "<p>DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "</p>";
    echo "<p>DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "</p>";
    echo "<p>DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "</p>";
    
    try {
        // Test PDO connection
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        echo "<div class='success'>✅ Database connection successful</div>";
        
        // Test initiatives table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM initiatives");
        $result = $stmt->fetch();
        echo "<div class='success'>✅ Initiatives table accessible: " . $result['count'] . " records</div>";
        
        // Test programs table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
        $result = $stmt->fetch();
        echo "<div class='success'>✅ Programs table accessible: " . $result['count'] . " records</div>";
        
        // Test program_submissions table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM program_submissions");
        $result = $stmt->fetch();
        echo "<div class='success'>✅ Program_submissions table accessible: " . $result['count'] . " records</div>";
        
        // Test specific initiative
        $stmt = $pdo->prepare("SELECT * FROM initiatives WHERE initiative_id = ?");
        $stmt->execute([1]);
        $initiative = $stmt->fetch();
        
        if ($initiative) {
            echo "<div class='success'>✅ Test initiative (ID=1) found: " . htmlspecialchars($initiative['initiative_name']) . "</div>";
            echo "<pre>" . htmlspecialchars(json_encode($initiative, JSON_PRETTY_PRINT)) . "</pre>";
        } else {
            echo "<div class='warning'>⚠️ Test initiative (ID=1) not found</div>";
            
            // Show available initiatives
            $stmt = $pdo->query("SELECT initiative_id, initiative_name FROM initiatives LIMIT 5");
            $available = $stmt->fetchAll();
            if ($available) {
                echo "<p><strong>Available initiatives:</strong></p>";
                foreach ($available as $init) {
                    echo "<p>ID: " . $init['initiative_id'] . " - " . htmlspecialchars($init['initiative_name']) . "</p>";
                }
            }
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
</body>
</html>
