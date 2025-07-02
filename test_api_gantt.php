<?php
/**
 * Simple test to verify the API returns proper data for dhtmlxGantt
 */

// Include necessary files
require_once 'app/config/config.php';

// Test API endpoint
$initiative_id = 1; // Test with initiative ID 1
$api_url = "app/api/simple_gantt_data.php?initiative_id=" . $initiative_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test for dhtmlxGantt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <h2>API Test for dhtmlxGantt</h2>
    <p><strong>Testing URL:</strong> <code><?php echo htmlspecialchars($api_url); ?></code></p>

    <?php
    // Make a request to the API
    $full_url = "http://localhost/" . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__), '/') . "/" . $api_url;
    echo "<p><strong>Full URL:</strong> <code>" . htmlspecialchars($full_url) . "</code></p>";

    // Use file_get_contents to test the API
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Content-Type: application/json\r\n",
            'timeout' => 10
        ]
    ]);

    $response = file_get_contents($full_url, false, $context);

    if ($response === false) {
        echo "<div class='error'>❌ <strong>Error:</strong> Could not fetch data from API</div>";
        
        // Try direct file inclusion as fallback
        echo "<h3>Trying Direct File Inclusion:</h3>";
        try {
            $_GET['initiative_id'] = $initiative_id;
            ob_start();
            include 'app/api/simple_gantt_data.php';
            $direct_response = ob_get_clean();
            
            if (!empty($direct_response)) {
                echo "<div class='success'>✅ <strong>Direct inclusion successful</strong></div>";
                echo "<pre>" . htmlspecialchars($direct_response) . "</pre>";
            } else {
                echo "<div class='error'>❌ Direct inclusion failed</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Direct inclusion error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='success'>✅ <strong>Success:</strong> API responded</div>";
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<div class='error'>❌ <strong>JSON Error:</strong> " . json_last_error_msg() . "</div>";
            echo "<h3>Raw Response:</h3>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        } else {
            echo "<h3>Parsed Data:</h3>";
            echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
            
            // Validate data structure for dhtmlxGantt
            if (isset($data['success']) && $data['success']) {
                echo "<h3>Data Validation for dhtmlxGantt:</h3>";
                
                if (isset($data['data']['initiative'])) {
                    echo "<div class='success'>✅ Initiative data found</div>";
                } else {
                    echo "<div class='error'>❌ Initiative data missing</div>";
                }
                
                if (isset($data['data']['programs']) && is_array($data['data']['programs'])) {
                    echo "<div class='success'>✅ Programs array found (" . count($data['data']['programs']) . " programs)</div>";
                    
                    foreach ($data['data']['programs'] as $index => $program) {
                        echo "<div>&nbsp;&nbsp;Program $index: " . htmlspecialchars($program['name'] ?? 'No name') . "</div>";
                        if (isset($program['targets']) && is_array($program['targets'])) {
                            echo "<div class='success'>&nbsp;&nbsp;&nbsp;&nbsp;✅ Targets: " . count($program['targets']) . " found</div>";
                        } else {
                            echo "<div class='error'>&nbsp;&nbsp;&nbsp;&nbsp;❌ No targets found</div>";
                        }
                    }
                } else {
                    echo "<div class='error'>❌ Programs data missing or invalid</div>";
                }
            } else {
                echo "<div class='error'>❌ <strong>API Error:</strong> " . htmlspecialchars($data['message'] ?? 'Unknown error') . "</div>";
            }
        }
    }
    ?>
</body>
</html>
