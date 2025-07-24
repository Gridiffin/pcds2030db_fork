<?php
/**
 * Simple test for admin programs CSS loading
 */

// Define the project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include config
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Test the exact same setup as programs.php
$pageTitle = 'Test Admin Programs';
$cssBundle = 'admin-programs';
$jsBundle = 'admin-programs';
$additionalScripts = [];

$header_config = [
    'title' => $pageTitle,
    'subtitle' => 'Testing CSS loading',
    'variant' => 'green'
];

// Simple content for testing
$contentFile = null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Programs CSS</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Test: Direct CSS Bundle -->
    <?php if ($cssBundle): ?>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/dist/css/<?php echo htmlspecialchars($cssBundle); ?>.bundle.css">
    <?php endif; ?>
    
    <style>
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Admin Programs CSS Test</h1>
        
        <div class="debug-info">
            <h3>Debug Information</h3>
            <p><strong>APP_URL:</strong> <?php echo APP_URL; ?></p>
            <p><strong>CSS Bundle:</strong> <?php echo $cssBundle; ?></p>
            <p><strong>Expected CSS URL:</strong> <?php echo APP_URL . '/dist/css/' . $cssBundle . '.bundle.css'; ?></p>
            <p><strong>CSS File Exists:</strong> 
                <?php 
                $cssPath = PROJECT_ROOT_PATH . 'dist/css/' . $cssBundle . '.bundle.css';
                if (file_exists($cssPath)) {
                    $size = round(filesize($cssPath) / 1024, 2);
                    echo "<span style='color: green;'>✅ Yes ($size KB)</span>";
                } else {
                    echo "<span style='color: red;'>❌ No</span>";
                }
                ?>
            </p>
        </div>
        
        <!-- Test admin-specific styles -->
        <div class="card">
            <div class="card-header">
                <h4>Test Admin Programs Styles</h4>
                <div class="filter-badges">
                    <span class="badge bg-primary">Test Badge</span>
                    <span class="badge bg-secondary">Another Badge <i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr class="sortable">
                            <th>Program Name <i class="fas fa-sort"></i></th>
                            <th>Agency <i class="fas fa-sort"></i></th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Test Program</td>
                            <td><span class="program-type-indicator"><i class="fas fa-leaf"></i> Test Agency</span></td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-3">
            <h5>Network Tab Check:</h5>
            <p>Open browser developer tools → Network tab → Refresh page</p>
            <p>Look for: <code><?php echo $cssBundle; ?>.bundle.css</code></p>
            <p>Status should be: <span class="badge bg-success">200 OK</span></p>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</body>
</html>
