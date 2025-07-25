<?php
require_once 'app/config/config.php';
require_once 'app/helpers/vite-helpers.php';

// Simulate the view_programs.php request
$_SERVER['REQUEST_URI'] = '/pcds2030_dashboard_fork/app/views/agency/programs/view_programs.php';
$_SERVER['SCRIPT_NAME'] = '/pcds2030_dashboard_fork/app/views/agency/programs/view_programs.php';
$_GET['debug_vite'] = true; // Enable debug mode

echo "<h1>Debug View Programs Vite Assets</h1>";
echo "<h2>Simulated Request</h2>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "</pre>";

echo "<h2>Bundle Detection</h2>";
$bundle_name = detect_bundle_name();
echo "<p>Detected Bundle: <strong>$bundle_name</strong></p>";

echo "<h2>Assets HTML Output</h2>";
$assets = vite_assets();
echo "<pre>" . htmlspecialchars($assets) . "</pre>";

echo "<h2>Test Direct Bundle Loading</h2>";
$direct_assets = vite_assets('agency-view-programs');
echo "<pre>" . htmlspecialchars($direct_assets) . "</pre>";
?>