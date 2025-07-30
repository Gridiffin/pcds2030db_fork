<?php
/**
 * Quick Test for Notifications AJAX Endpoint
 * 
 * This file tests the notifications AJAX functionality to verify the 404 fix
 * Usage: Access via browser to test notifications loading
 */

// Define PROJECT_ROOT_PATH
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<h1>Test Notifications AJAX</h1>';
    echo '<p>Please log in first to test notifications.</p>';
    echo '<p><a href="login.php">Go to Login</a></p>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Notifications AJAX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-bell me-2"></i>
                    Test Notifications AJAX
                </h1>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Notifications Test</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <button id="testAjax" class="btn btn-primary">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Test AJAX Request
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button id="testDirect" class="btn btn-secondary">
                                    <i class="fas fa-link me-2"></i>
                                    Test Direct URL
                                </button>
                            </div>
                        </div>
                        
                        <div id="results" class="mt-3">
                            <div class="alert alert-info">
                                Click a button above to test the notifications endpoint.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Debug Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Current URL:</h6>
                                <code id="currentUrl"></code>
                            </div>
                            <div class="col-md-6">
                                <h6>Detected Base URL:</h6>
                                <code id="baseUrl"></code>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>User ID:</h6>
                                <code><?php echo $_SESSION['user_id'] ?? 'Not set'; ?></code>
                            </div>
                            <div class="col-md-6">
                                <h6>Session Status:</h6>
                                <code><?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Debug information
        document.getElementById('currentUrl').textContent = window.location.href;
        
        // Detect base URL (same logic as in the fixed AJAX module)
        function detectBaseUrl() {
            const currentPath = window.location.pathname;
            const currentOrigin = window.location.origin;
            
            if (currentPath.includes('/pcds2030_dashboard_fork')) {
                return currentOrigin + '/pcds2030_dashboard_fork';
            }
            
            if (currentPath === '/' || currentPath === '') {
                return currentOrigin;
            }
            
            const pathSegments = currentPath.split('/').filter(segment => segment.length > 0);
            if (pathSegments.length > 0) {
                return currentOrigin + '/' + pathSegments[0];
            }
            
            return currentOrigin;
        }
        
        const baseUrl = detectBaseUrl();
        document.getElementById('baseUrl').textContent = baseUrl;
        
        // Test AJAX request
        document.getElementById('testAjax').addEventListener('click', async function() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<div class="alert alert-info">Testing AJAX request...</div>';
            
            try {
                const url = baseUrl + '/app/ajax/get_user_notifications.php?page=1&per_page=10&filter=all';
                console.log('Testing URL:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    resultsDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h6>✓ AJAX Test Passed!</h6>
                            <p>Successfully fetched ${data.notifications?.length || 0} notifications</p>
                            <small>Response time: ${response.headers.get('X-Response-Time') || 'N/A'}</small>
                        </div>
                        <div class="mt-3">
                            <h6>Response Data:</h6>
                            <pre class="bg-light p-3 rounded"><code>${JSON.stringify(data, null, 2)}</code></pre>
                        </div>
                    `;
                } else {
                    resultsDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <h6>⚠ AJAX Test Completed with Warning</h6>
                            <p>${data.message || 'Unknown error'}</p>
                        </div>
                    `;
                }
                
            } catch (error) {
                console.error('AJAX test failed:', error);
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>✗ AJAX Test Failed</h6>
                        <p>Error: ${error.message}</p>
                        <small>Check browser console for more details</small>
                    </div>
                `;
            }
        });
        
        // Test direct URL
        document.getElementById('testDirect').addEventListener('click', function() {
            const url = baseUrl + '/app/ajax/get_user_notifications.php?page=1&per_page=10&filter=all';
            window.open(url, '_blank');
            
            document.getElementById('results').innerHTML = `
                <div class="alert alert-info">
                    <h6>Direct URL Test</h6>
                    <p>Opened URL in new tab: <code>${url}</code></p>
                    <p>Check the new tab to see if the endpoint responds correctly.</p>
                </div>
            `;
        });
    </script>
</body>
</html>