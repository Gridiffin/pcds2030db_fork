<?php
/**
 * Debug Notifications Path
 * 
 * This file helps debug the base path detection issue
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Notifications Path</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Debug Notifications Path</h1>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Current Environment</h5>
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
                                <h6>Expected AJAX URL:</h6>
                                <code id="ajaxUrl"></code>
                            </div>
                            <div class="col-md-6">
                                <h6>APP_URL from PHP:</h6>
                                <code><?php echo APP_URL ?? 'Not defined'; ?></code>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Test AJAX Request</h5>
                    </div>
                    <div class="card-body">
                        <button id="testAjax" class="btn btn-primary">
                            Test AJAX Request
                        </button>
                        <div id="results" class="mt-3"></div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Direct File Test</h5>
                    </div>
                    <div class="card-body">
                        <p>Test if the file exists directly:</p>
                        <a href="app/ajax/get_user_notifications.php" target="_blank" class="btn btn-secondary">
                            Test Direct File Access
                        </a>
                        <div class="mt-3">
                            <p><strong>File exists check:</strong></p>
                            <code><?php echo file_exists('app/ajax/get_user_notifications.php') ? 'File exists' : 'File does not exist'; ?></code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Debug information
        document.getElementById('currentUrl').textContent = window.location.href;
        
        // Detect base URL (same logic as in the AJAX module)
        function detectBaseUrl() {
            const currentPath = window.location.pathname;
            const currentOrigin = window.location.origin;
            
            console.log('Current path:', currentPath);
            console.log('Current origin:', currentOrigin);
            
            if (currentPath.includes('/pcds2030_dashboard_fork')) {
                console.log('Detected pcds2030_dashboard_fork in path');
                return currentOrigin + '/pcds2030_dashboard_fork';
            }
            
            if (currentPath === '/' || currentPath === '') {
                console.log('Detected root directory');
                return currentOrigin;
            }
            
            const pathSegments = currentPath.split('/').filter(segment => segment.length > 0);
            console.log('Path segments:', pathSegments);
            if (pathSegments.length > 0) {
                console.log('Using first path segment:', pathSegments[0]);
                return currentOrigin + '/' + pathSegments[0];
            }
            
            console.log('Using fallback origin');
            return currentOrigin;
        }
        
        const baseUrl = detectBaseUrl();
        document.getElementById('baseUrl').textContent = baseUrl;
        
        const ajaxUrl = baseUrl + '/app/ajax/get_user_notifications.php';
        document.getElementById('ajaxUrl').textContent = ajaxUrl;
        
        // Test AJAX request
        document.getElementById('testAjax').addEventListener('click', async function() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<div class="alert alert-info">Testing AJAX request...</div>';
            
            try {
                const url = ajaxUrl + '?page=1&per_page=10&filter=all';
                console.log('Testing URL:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data:', data);
                
                resultsDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h6>✓ AJAX Test Passed!</h6>
                        <p>Successfully fetched ${data.notifications?.length || 0} notifications</p>
                    </div>
                `;
                
            } catch (error) {
                console.error('AJAX test failed:', error);
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>✗ AJAX Test Failed</h6>
                        <p>Error: ${error.message}</p>
                        <p>URL tested: ${ajaxUrl}</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>