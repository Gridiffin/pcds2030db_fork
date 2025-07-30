<?php
/**
 * Test Notifications Filters
 * 
 * This file tests the enhanced notification filtering functionality
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/notifications.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<h1>Test Notifications Filters</h1>';
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
    <title>Test Notifications Filters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-filter me-2"></i>
                    Test Notifications Filters
                </h1>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Tests</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100 mb-2" onclick="testFilter('all')">
                                    <i class="fas fa-list me-2"></i>All
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-warning w-100 mb-2" onclick="testFilter('unread')">
                                    <i class="fas fa-envelope me-2"></i>Unread
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-success w-100 mb-2" onclick="testFilter('read')">
                                    <i class="fas fa-envelope-open me-2"></i>Read
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-info w-100 mb-2" onclick="testFilter('today')">
                                    <i class="fas fa-calendar-day me-2"></i>Today
                                </button>
                            </div>
                        </div>
                        
                        <div id="results" class="mt-3">
                            <div class="alert alert-info">
                                Click a filter button above to test the notifications endpoint.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Test Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="testResults"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Detect base URL (same logic as in the fixed AJAX module)
        function detectBaseUrl() {
            const currentPath = window.location.pathname;
            const currentOrigin = window.location.origin;
            
            // Check if we're accessing a file directly (like /app/views/agency/users/all_notifications.php)
            if (currentPath.includes('/app/views/')) {
                const pathParts = currentPath.split('/');
                const appIndex = pathParts.indexOf('app');
                if (appIndex > 0) {
                    const projectPath = pathParts.slice(0, appIndex).join('/');
                    return currentOrigin + projectPath;
                }
            }
            
            // Check if we're in a subdirectory (like /pcds2030_dashboard_fork/)
            if (currentPath.includes('/pcds2030_dashboard_fork')) {
                return currentOrigin + '/pcds2030_dashboard_fork';
            }
            
            return currentOrigin;
        }
        
        const baseUrl = detectBaseUrl();
        const testResults = [];
        
        async function testFilter(filter) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `<div class="alert alert-info">Testing ${filter} filter...</div>`;
            
            try {
                const url = baseUrl + `/app/ajax/get_user_notifications.php?page=1&per_page=10&filter=${filter}`;
                console.log(`Testing ${filter} filter URL:`, url);
                
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
                console.log(`${filter} filter response:`, data);
                
                if (data.success) {
                    resultsDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h6>✓ ${filter.charAt(0).toUpperCase() + filter.slice(1)} Filter Test Passed!</h6>
                            <p>Successfully fetched ${data.notifications?.length || 0} notifications</p>
                            <p>Total count: ${data.pagination?.total_count || 0}</p>
                            <p>Unread count: ${data.stats?.unread_count || 0}</p>
                        </div>
                    `;
                    
                    // Store test result
                    testResults.push({
                        filter: filter,
                        status: 'PASSED',
                        count: data.notifications?.length || 0,
                        total: data.pagination?.total_count || 0,
                        unread: data.stats?.unread_count || 0
                    });
                } else {
                    resultsDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <h6>⚠ ${filter.charAt(0).toUpperCase() + filter.slice(1)} Filter Test Completed with Warning</h6>
                            <p>${data.message || 'Unknown error'}</p>
                        </div>
                    `;
                    
                    testResults.push({
                        filter: filter,
                        status: 'WARNING',
                        message: data.message || 'Unknown error'
                    });
                }
                
                updateTestResults();
                
            } catch (error) {
                console.error(`${filter} filter test failed:`, error);
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>✗ ${filter.charAt(0).toUpperCase() + filter.slice(1)} Filter Test Failed</h6>
                        <p>Error: ${error.message}</p>
                    </div>
                `;
                
                testResults.push({
                    filter: filter,
                    status: 'FAILED',
                    error: error.message
                });
                
                updateTestResults();
            }
        }
        
        function updateTestResults() {
            const resultsDiv = document.getElementById('testResults');
            
            if (testResults.length === 0) {
                resultsDiv.innerHTML = '<p class="text-muted">No tests run yet.</p>';
                return;
            }
            
            let html = '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>Filter</th><th>Status</th><th>Count</th><th>Total</th><th>Unread</th><th>Details</th></tr></thead><tbody>';
            
            testResults.forEach(result => {
                const statusClass = result.status === 'PASSED' ? 'success' : 
                                  result.status === 'WARNING' ? 'warning' : 'danger';
                
                html += `<tr class="table-${statusClass}">`;
                html += `<td><strong>${result.filter}</strong></td>`;
                html += `<td><span class="badge bg-${statusClass}">${result.status}</span></td>`;
                html += `<td>${result.count || '-'}</td>`;
                html += `<td>${result.total || '-'}</td>`;
                html += `<td>${result.unread || '-'}</td>`;
                html += `<td>${result.message || result.error || '-'}</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            
            // Add summary
            const passed = testResults.filter(r => r.status === 'PASSED').length;
            const total = testResults.length;
            
            html += `<div class="alert alert-info mt-3">
                <strong>Summary:</strong> ${passed}/${total} filters passed
            </div>`;
            
            resultsDiv.innerHTML = html;
        }
        
        // Test all filters on page load
        window.addEventListener('load', function() {
            setTimeout(() => {
                testFilter('all');
            }, 1000);
        });
    </script>
</body>
</html>