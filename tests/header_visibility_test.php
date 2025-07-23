<?php
/**
 * Header Visibility Test
 * 
 * This file tests the header visibility fix with different header configurations.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Set up test configurations
$testConfigs = [
    [
        'title' => 'Basic Header',
        'subtitle' => 'Testing basic header configuration',
        'variant' => 'green',
    ],
    [
        'title' => 'Header with Breadcrumbs',
        'subtitle' => 'Testing header with breadcrumbs',
        'variant' => 'blue',
        'breadcrumb' => [
            [
                'text' => 'Home',
                'url' => '#'
            ],
            [
                'text' => 'Test Page',
                'url' => null
            ]
        ]
    ],
    [
        'title' => 'Header with Actions',
        'subtitle' => 'Testing header with action buttons',
        'variant' => 'green',
        'actions' => [
            [
                'url' => '#',
                'text' => 'Test Action',
                'class' => 'btn-light'
            ]
        ]
    ],
    [
        'title' => 'Header with All Features',
        'subtitle' => 'Testing header with all features',
        'variant' => 'blue',
        'breadcrumb' => [
            [
                'text' => 'Home',
                'url' => '#'
            ],
            [
                'text' => 'Category',
                'url' => '#'
            ],
            [
                'text' => 'Test Page',
                'url' => null
            ]
        ],
        'actions' => [
            [
                'url' => '#',
                'text' => 'Action 1',
                'class' => 'btn-light'
            ],
            [
                'url' => '#',
                'text' => 'Action 2',
                'class' => 'btn-outline-light'
            ]
        ]
    ]
];

// Set up page variables
$pageTitle = 'Header Visibility Test';
$additionalStyles = [APP_URL . '/assets/css/layout/page_header.css'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Additional CSS Files -->
    <?php foreach ($additionalStyles as $style): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
    <?php endforeach; ?>
    
    <style>
        body {
            padding: 20px;
        }
        .test-container {
            margin-bottom: 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .test-info {
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        .test-header {
            margin-bottom: 0;
        }
        
        /* Custom styles for testing */
        .page-header--green {
            background-color: #28a745;
            border-bottom: 1px solid #218838;
        }
        
        .page-header--blue {
            background-color: #007bff;
            border-bottom: 1px solid #0069d9;
        }
        
        /* Navbar simulation */
        .navbar-simulation {
            background-color: #343a40;
            color: white;
            padding: 1rem;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Header Visibility Test</h1>
        <p>This page tests the header visibility fix with different configurations.</p>
        
        <?php foreach ($testConfigs as $index => $config): ?>
        <div class="test-container">
            <div class="test-info">
                <h3 class="test-header">Test Case <?php echo $index + 1; ?>: <?php echo htmlspecialchars($config['title']); ?></h3>
                <p>Testing configuration: <?php echo htmlspecialchars(json_encode($config)); ?></p>
            </div>
            
            <!-- Simulate navbar -->
            <div class="navbar-simulation">
                <div class="container">
                    <span>Navbar Simulation</span>
                </div>
            </div>
            
            <!-- Test header -->
            <?php
            $header_config = $config;
            include PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
            ?>
            
            <div class="container">
                <p>Content would go here...</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>