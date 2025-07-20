<?php
/**
 * Test the get_public_reports.php AJAX endpoint directly
 */

// Start session to simulate logged-in user
session_start();

// Set a test agency session (replace with actual agency ID)
$_SESSION['agency_id'] = 1;
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'agency';

// Call the AJAX endpoint and capture the response
$url = 'http://localhost/pcds2030_dashboard_fork/app/ajax/get_public_reports.php';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response:\n";
echo $response;
?>
