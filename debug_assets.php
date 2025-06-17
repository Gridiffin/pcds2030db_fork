<?php
/**
 * Debug Asset Paths - Troubleshooting Tool
 * 
 * This page helps diagnose asset loading issues by showing all relevant paths and URLs.
 * Delete this file after resolving the asset loading issues.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include configuration
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Asset Paths - PCDS 2030 Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .info-box {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .info-box h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 10px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin: 5px 0;
        }
        .test-result {
            padding: 10px;
            border-radius: 5px;
            margin: 5px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        .delete-notice {
            background: #ffe6e6;
            border: 2px solid #ff4444;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #cc0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Debug Asset Paths - PCDS 2030 Dashboard</h1>
        
        <div class="delete-notice">
            ⚠️ <strong>IMPORTANT:</strong> Delete this file (debug_assets.php) after resolving the asset loading issues for security purposes.
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>🌐 Server Information</h3>
                <div class="code">HTTP_HOST: <?php echo $_SERVER['HTTP_HOST'] ?? 'Not set'; ?></div>
                <div class="code">SERVER_NAME: <?php echo $_SERVER['SERVER_NAME'] ?? 'Not set'; ?></div>
                <div class="code">DOCUMENT_ROOT: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></div>
                <div class="code">SCRIPT_NAME: <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Not set'; ?></div>
                <div class="code">REQUEST_URI: <?php echo $_SERVER['REQUEST_URI'] ?? 'Not set'; ?></div>
            </div>

            <div class="info-box">
                <h3>📁 Path Configuration</h3>
                <div class="code">APP_URL: <?php echo APP_URL; ?></div>
                <div class="code">ROOT_PATH: <?php echo defined('ROOT_PATH') ? ROOT_PATH : 'Not defined'; ?></div>
                <div class="code">PROJECT_ROOT_PATH: <?php echo defined('PROJECT_ROOT_PATH') ? PROJECT_ROOT_PATH : 'Not defined'; ?></div>
                <div class="code">Current File: <?php echo __FILE__; ?></div>
            </div>
        </div>

        <h2>🔗 Asset URL Tests</h2>
        <table>
            <thead>
                <tr>
                    <th>Asset Type</th>
                    <th>File</th>
                    <th>Generated URL</th>
                    <th>File Exists</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $assets_to_test = [
                    ['css', 'main.css'],
                    ['css', 'simple-header.css'],
                    ['css', 'pages/login.css'],
                    ['js', 'main.js'],
                    ['js', 'url_helpers.js'],
                    ['js', 'login.js'],
                    ['images', 'logo.png'],
                    ['images', 'sarawak_crest.png'],
                    ['fonts/fontawesome', 'fa-solid-900.woff2']
                ];

                foreach ($assets_to_test as $asset) {
                    $type = $asset[0];
                    $file = $asset[1];
                    $url = asset_url($type, $file);
                    
                    // Check if file exists physically
                    $physical_path = PROJECT_ROOT_PATH . 'assets/' . $type . '/' . $file;
                    $file_exists = file_exists($physical_path);
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($type) . "</td>";
                    echo "<td>" . htmlspecialchars($file) . "</td>";
                    echo "<td><a href='" . htmlspecialchars($url) . "' target='_blank'>" . htmlspecialchars($url) . "</a></td>";
                    echo "<td>" . ($file_exists ? "✅ Yes" : "❌ No") . "</td>";
                    echo "<td>";
                    if ($file_exists) {
                        echo "<span class='test-result success'>File exists physically</span>";
                    } else {
                        echo "<span class='test-result error'>File missing: " . htmlspecialchars($physical_path) . "</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>🔍 Directory Structure Check</h2>
        <?php
        $directories_to_check = [
            'assets/',
            'assets/css/',
            'assets/js/',
            'assets/images/',
            'assets/fonts/',
            'app/',
            'app/config/',
            'app/lib/'
        ];

        echo "<table>";
        echo "<thead><tr><th>Directory</th><th>Status</th><th>Full Path</th></tr></thead>";
        echo "<tbody>";
        
        foreach ($directories_to_check as $dir) {
            $full_path = PROJECT_ROOT_PATH . $dir;
            $exists = is_dir($full_path);
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($dir) . "</td>";
            echo "<td>" . ($exists ? "<span class='test-result success'>✅ Exists</span>" : "<span class='test-result error'>❌ Missing</span>") . "</td>";
            echo "<td>" . htmlspecialchars($full_path) . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        ?>

        <h2>🧪 Live Asset Loading Test</h2>
        <div id="asset-test-results">
            <p>Testing asset loading via HTTP requests...</p>
        </div>

        <h2>📋 Recommendations</h2>
        <div class="info-box">
            <h3>If assets are not loading:</h3>
            <ol>
                <li><strong>Check APP_URL:</strong> Make sure the generated APP_URL matches your actual server setup</li>
                <li><strong>Verify file paths:</strong> Ensure all asset files exist in the correct directories</li>
                <li><strong>Check permissions:</strong> Make sure the web server can read the asset files</li>
                <li><strong>Clear browser cache:</strong> Force refresh your browser or clear cache</li>
                <li><strong>Check XAMPP configuration:</strong> Verify your virtual hosts or document root settings</li>
            </ol>
        </div>

        <script>
            // Test asset loading via JavaScript
            function testAssetLoading() {
                const assets = [
                    {type: 'css', file: 'main.css'},
                    {type: 'js', file: 'main.js'},
                    {type: 'images', file: 'logo.png'}
                ];
                
                const resultsDiv = document.getElementById('asset-test-results');
                resultsDiv.innerHTML = '<h4>HTTP Loading Test Results:</h4>';
                
                assets.forEach(asset => {
                    const url = '<?php echo APP_URL; ?>/assets/' + asset.type + '/' + asset.file;
                    
                    fetch(url, {method: 'HEAD'})
                        .then(response => {
                            const status = response.ok ? 'success' : 'error';
                            const statusText = response.ok ? '✅ Loads OK' : '❌ Failed to load';
                            resultsDiv.innerHTML += `<div class="test-result ${status}">${asset.type}/${asset.file}: ${statusText} (Status: ${response.status})</div>`;
                        })
                        .catch(error => {
                            resultsDiv.innerHTML += `<div class="test-result error">${asset.type}/${asset.file}: ❌ Network error</div>`;
                        });
                });
            }
            
            // Run the test when page loads
            document.addEventListener('DOMContentLoaded', testAssetLoading);
        </script>
    </div>
</body>
</html>
