<?php
/**
 * Test AJAX endpoint directly
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate being logged in as admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Set AJAX parameters
$_GET['ajax_table'] = '1';
$_GET['search'] = '';
$_GET['is_active'] = '';

echo "<h1>AJAX Endpoint Test</h1>";
echo "<p>Testing what the AJAX endpoint returns...</p>";

echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<h3>AJAX Response:</h3>";

// Capture the output
ob_start();
include 'app/views/admin/initiatives/manage_initiatives.php';
$output = ob_get_clean();

echo "<pre>" . htmlspecialchars($output) . "</pre>";
echo "</div>";
?>
