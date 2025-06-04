<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "Available users:" . PHP_EOL;
$result = $conn->query('SELECT user_id, username, role, agency_name FROM users WHERE is_active = 1 ORDER BY role, username');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- Username: {$row['username']}, Role: {$row['role']}, Agency: " . ($row['agency_name'] ?? 'N/A') . " (ID: {$row['user_id']})" . PHP_EOL;
    }
} else {
    echo "Error: " . $conn->error . PHP_EOL;
}
?>
