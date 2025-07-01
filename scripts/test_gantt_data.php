<?php
/**
 * Test Script for Gantt Data API
 * 
 * This script directly calls the gantt_data.php API and displays the raw output
 * to help debug issues with the Gantt chart.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// Include necessary files for session and authentication
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/core.php';

// Verify user is logged in and is an agency
if (!is_agency()) {
    echo "Error: Must be logged in as an agency to use this test script.";
    exit;
}

// Get initiative ID from query parameter
$initiative_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Build API URL
$api_url = PROJECT_ROOT_PATH . 'app/api/gantt_data.php';
if ($initiative_id) {
    $api_url .= "?initiative_id=$initiative_id";
}

// Set up cURL to call the API with the current session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, APP_URL . '/app/api/gantt_data.php' . ($initiative_id ? "?initiative_id=$initiative_id" : ""));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Get raw data for analysis
$raw_data = $result;
$parsed_data = json_decode($result, true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gantt Data API Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
        }
        .json-key { color: #881391; }
        .json-string { color: #1A5715; }
        .json-number { color: #1A31FF; }
        .json-boolean { color: #1A31FF; }
        .json-null { color: #888; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1>Gantt Data API Test</h1>
                <p class="lead">This page helps debug the Gantt chart data API.</p>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>API Request</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>URL:</strong> <?php echo APP_URL . '/app/api/gantt_data.php' . ($initiative_id ? "?initiative_id=$initiative_id" : ""); ?></p>
                        <p><strong>HTTP Status:</strong> <?php echo $http_code; ?></p>
                        
                        <form action="" method="get">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="id" value="<?php echo $initiative_id; ?>" placeholder="Initiative ID">
                                <button class="btn btn-primary" type="submit">Test</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Response Analysis</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($parsed_data): ?>
                            <ul class="list-group mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    API Request Success
                                    <span class="badge bg-<?php echo $parsed_data['success'] ? 'success' : 'danger'; ?>">
                                        <?php echo $parsed_data['success'] ? 'Success' : 'Failed'; ?>
                                    </span>
                                </li>
                                
                                <?php if (isset($parsed_data['data'])): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Tasks Count
                                        <span class="badge bg-primary">
                                            <?php echo isset($parsed_data['data']['data']) ? count($parsed_data['data']['data']) : '0'; ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Links Count
                                        <span class="badge bg-info">
                                            <?php echo isset($parsed_data['data']['links']) ? count($parsed_data['data']['links']) : '0'; ?>
                                        </span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            
                            <?php if (isset($parsed_data['data']['data']) && !empty($parsed_data['data']['data'])): ?>
                                <h5>First Task Structure</h5>
                                <pre><?php print_r($parsed_data['data']['data'][0]); ?></pre>
                            <?php endif; ?>
                            
                            <?php if (isset($parsed_data['error'])): ?>
                                <div class="alert alert-danger">
                                    <h5>Error:</h5>
                                    <p><?php echo $parsed_data['error']; ?></p>
                                    <?php if (isset($parsed_data['message'])): ?>
                                        <p><strong>Message:</strong> <?php echo $parsed_data['message']; ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($parsed_data['trace'])): ?>
                                        <pre><?php echo $parsed_data['trace']; ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                Failed to parse JSON response. Raw response:
                            </div>
                            <pre><?php echo htmlspecialchars($raw_data); ?></pre>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h4>Raw JSON Response</h4>
                    </div>
                    <div class="card-body">
                        <pre id="json-display"><?php echo htmlspecialchars($raw_data); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Format JSON with syntax highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const jsonDisplay = document.getElementById('json-display');
            try {
                const json = JSON.parse(jsonDisplay.textContent);
                jsonDisplay.innerHTML = syntaxHighlight(JSON.stringify(json, null, 2));
            } catch (e) {
                // Keep as-is if not valid JSON
            }
        });

        // JSON syntax highlighting function
        function syntaxHighlight(json) {
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }
    </script>
</body>
</html>
