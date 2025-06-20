<?php
// Simple test to verify update_program.php syntax
echo "<h1>Testing update_program.php syntax</h1>";

$file_path = 'app/views/agency/programs/update_program.php';

// Check if file exists
if (file_exists($file_path)) {
    echo "✅ File exists: $file_path<br>";
    
    // Check PHP syntax
    $output = shell_exec("php -l \"$file_path\" 2>&1");
    echo "<h2>PHP Syntax Check:</h2>";
    echo "<pre>$output</pre>";
    
    // Check file size
    $size = filesize($file_path);
    echo "<h2>File Info:</h2>";
    echo "File size: " . number_format($size) . " bytes<br>";
    
} else {
    echo "❌ File not found: $file_path<br>";
}

echo "<br><a href='app/views/agency/programs/update_program.php?id=224'>Test update program page →</a>";
?>
