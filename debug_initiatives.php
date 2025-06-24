<?php
/**
 * Debug file to check initiative numbers
 * Temporary file to verify initiative data is correct
 */

require_once 'config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';

echo "<h2>Initiative Number Debug</h2>";

// Get all initiatives
$initiatives = get_initiatives_for_select(true);

echo "<h3>Active Initiatives with Numbers:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Name</th><th>Number</th><th>Rendered Option Text</th></tr>";

foreach ($initiatives as $initiative) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($initiative['initiative_id']) . "</td>";
    echo "<td>" . htmlspecialchars($initiative['initiative_name']) . "</td>";
    echo "<td>" . htmlspecialchars($initiative['initiative_number'] ?? 'NULL') . "</td>";
    
    // Show how it would appear in the select option
    $option_text = htmlspecialchars($initiative['initiative_name']);
    if ($initiative['initiative_number']) {
        $option_text .= " (" . htmlspecialchars($initiative['initiative_number']) . ")";
    }
    echo "<td>" . $option_text . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test the JavaScript extraction logic in PHP
echo "<h3>JavaScript Extraction Test (PHP simulation):</h3>";
foreach ($initiatives as $initiative) {
    $option_text = htmlspecialchars($initiative['initiative_name']);
    if ($initiative['initiative_number']) {
        $option_text .= " (" . htmlspecialchars($initiative['initiative_number']) . ")";
    }
    
    // Simulate JavaScript regex: /\(([^)]+)\)$/
    preg_match('/\(([^)]+)\)$/', $option_text, $matches);
    $extracted = isset($matches[1]) ? $matches[1] : 'NULL';
    
    echo "<p><strong>Initiative:</strong> " . htmlspecialchars($initiative['initiative_name']) . " <br>";
    echo "<strong>Option Text:</strong> " . $option_text . " <br>";
    echo "<strong>Extracted Number:</strong> " . $extracted . "</p>";
    echo "<hr>";
}

// Check database directly
echo "<h3>Direct Database Check:</h3>";
$result = $conn->query("SELECT initiative_id, initiative_name, initiative_number FROM initiatives WHERE is_active = 1 ORDER BY initiative_name");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Name</th><th>Number (Raw)</th><th>Number Type</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['initiative_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['initiative_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['initiative_number'] ?? 'NULL') . "</td>";
    echo "<td>" . gettype($row['initiative_number']) . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
