<?php
echo "Testing target separation logic:\n";

$test_data = "target 12345; taget 54321";
echo "Test data: $test_data\n";

if (strpos($test_data, ';') !== false) {
    echo "Contains semicolon - splitting...\n";
    $parts = explode(';', $test_data);
    echo "Parts found: " . count($parts) . "\n";
    foreach ($parts as $i => $part) {
        echo "Part " . ($i+1) . ": '" . trim($part) . "'\n";
    }
} else {
    echo "No semicolon found\n";
}
?>
