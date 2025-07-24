<?php
/**
 * Complete Test for Admin Programs CSS - Exact same setup as programs.php
 */

// Define the project root path correctly by navigating up from the current file's directory.
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL.
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries (commenting out admin check for testing)
/*
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}
*/

// Set up variables for base layout - EXACT same as programs.php
$pageTitle = 'Admin Programs';
$cssBundle = 'admin-programs';
$jsBundle = 'admin-programs';
$additionalScripts = [
    // Additional scripts specific to admin programs can be added here if needed
];

// Configure modern page header
$header_config = [
    'title' => $pageTitle,
    'subtitle' => 'View and manage programs across all agencies',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Bulk Assign Initiatives',
            'url' => 'bulk_assign_initiatives.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-link'
        ]
    ]
];

// Create simple content instead of using partials
$contentFile = null;

// Debug info
echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px; border-radius: 5px;'>";
echo "<h3>Debug Info:</h3>";
echo "<p><strong>PROJECT_ROOT_PATH:</strong> " . PROJECT_ROOT_PATH . "</p>";
echo "<p><strong>APP_URL:</strong> " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "</p>";
echo "<p><strong>CSS Bundle:</strong> " . $cssBundle . "</p>";
echo "<p><strong>Expected CSS URL:</strong> " . APP_URL . '/dist/css/' . $cssBundle . '.bundle.css' . "</p>";

$cssPath = PROJECT_ROOT_PATH . 'dist/css/' . $cssBundle . '.bundle.css';
echo "<p><strong>CSS File Exists:</strong> " . (file_exists($cssPath) ? '✅ Yes' : '❌ No') . "</p>";
if (file_exists($cssPath)) {
    echo "<p><strong>CSS File Size:</strong> " . round(filesize($cssPath) / 1024, 2) . " KB</p>";
}
echo "</div>";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - PCDS 2030 Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Dynamic CSS Bundle (Vite) - EXACT same as base.php -->
    <?php if ($cssBundle): ?>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/dist/css/<?php echo htmlspecialchars($cssBundle); ?>.bundle.css">
    <?php endif; ?>
</head>
<body>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        
        <!-- Test admin-specific CSS classes -->
        <div class="card mt-3">
            <div class="card-header">
                <h4>Testing Admin Programs CSS</h4>
                <div class="filter-badges">
                    <span class="badge bg-primary">Test Badge</span>
                    <span class="badge bg-secondary">Closeable Badge <i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="sortable">Program Name <i class="fas fa-sort"></i></th>
                            <th class="sortable">Agency <i class="fas fa-sort"></i></th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sample Program</td>
                            <td><span class="program-type-indicator"><i class="fas fa-leaf"></i> Sample Agency</span></td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <h5>What to Check:</h5>
            <ul>
                <li>Open Developer Tools → Network tab</li>
                <li>Look for <code><?php echo $cssBundle; ?>.bundle.css</code> request</li>
                <li>Check if badges have hover effects</li>
                <li>Check if sortable headers change on hover</li>
                <li>Verify program type indicator styling</li>
            </ul>
        </div>
    </div>

    <!-- Dynamic JS Bundle (Vite) -->
    <?php if ($jsBundle): ?>
    <script type="module" src="<?php echo APP_URL; ?>/dist/js/<?php echo htmlspecialchars($jsBundle); ?>.bundle.js"></script>
    <?php endif; ?>
</body>
</html>
