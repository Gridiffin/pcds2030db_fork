<?php
/**
 * Test script to verify the delete user path fix
 */

echo "<h2>Testing Delete User Path Fix</h2>\n";

// Current file location simulation
$current_location = "/app/views/admin/users/manage_users.php";
echo "Current file: $current_location<br>\n";

// Old relative path
$old_relative_path = "../../handlers/admin/process_user.php";
echo "Old relative path: $old_relative_path<br>\n";

// New relative path  
$new_relative_path = "../../../handlers/admin/process_user.php";
echo "New relative path: $new_relative_path<br>\n";

// Function to resolve relative paths
function resolve_relative_path($base, $relative) {
    $base_parts = explode('/', dirname($base));
    $relative_parts = explode('/', $relative);
    
    foreach ($relative_parts as $part) {
        if ($part === '..') {
            array_pop($base_parts);
        } elseif ($part !== '.' && $part !== '') {
            $base_parts[] = $part;
        }
    }
    
    return implode('/', $base_parts);
}

$old_resolved = resolve_relative_path($current_location, $old_relative_path);
$new_resolved = resolve_relative_path($current_location, $new_relative_path);

echo "<br><strong>Resolution Results:</strong><br>\n";
echo "Old path resolves to: $old_resolved<br>\n";
echo "New path resolves to: $new_resolved<br>\n";

// Check if files exist
$project_root = __DIR__;
$old_file_path = $project_root . $old_resolved;
$new_file_path = $project_root . $new_resolved;

echo "<br><strong>File Existence Check:</strong><br>\n";
echo "Old path exists: " . (file_exists($old_file_path) ? "YES" : "NO") . " ($old_file_path)<br>\n";
echo "New path exists: " . (file_exists($new_file_path) ? "YES" : "NO") . " ($new_file_path)<br>\n";

if (file_exists($new_file_path)) {
    echo "<br><span style='color: green;'><strong>✅ SUCCESS: The new path correctly points to the existing handler file!</strong></span><br>\n";
} else {
    echo "<br><span style='color: red;'><strong>❌ ERROR: The new path does not point to an existing file!</strong></span><br>\n";
}

?>
