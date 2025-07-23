<?php
/**
 * Page Header Responsive Test
 * 
 * This file tests the page header component's responsive behavior
 * to ensure it meets the requirements specified in the design document.
 * 
 * Requirements being tested:
 * - 3.1: Responsive behavior on different screen sizes
 * - 3.2: Consistent styling
 * - 3.3: Adequate spacing and visual hierarchy
 * - 3.4: Sufficient contrast for readability
 */

// Ensure PROJECT_ROOT_PATH is defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';

// Define viewport sizes for testing
$viewports = [
    'Mobile' => '320px',
    'Small Mobile' => '375px',
    'Large Mobile' => '425px',
    'Tablet' => '768px',
    'Laptop' => '1024px',
    'Desktop' => '1440px'
];

// Define test configurations
$testConfigs = [
    [
        'name' => 'Standard Header',
        'config' => [
            'title' => 'Responsive Test',
            'subtitle' => 'Testing responsive behavior',
            'breadcrumb' => [
                [
                    'text' => 'Home',
                    'url' => '/index.php'
                ],
                [
                    'text' => 'Responsive Test',
                    'url' => null
                ]
            ]
        ]
    ],
    [
        'name' => 'Long Title and Subtitle',
        'config' => [
            'title' => 'This is a very long page title that should wrap on smaller screens to test how the component handles long text content',
            'subtitle' => 'This is also a very long subtitle that contains a lot of text to see how it wraps and maintains readability on various screen sizes from mobile to desktop',
            'breadcrumb' => [
                [
                    'text' => 'Home',
                    'url' => '/index.php'
                ],
                [
                    'text' => 'Category with a long name',
                    'url' => '/index.php?page=category'
                ],
                [
                    'text' => 'Subcategory',
                    'url' => '/index.php?page=subcategory'
                ],
                [
                    'text' => 'Current Page with Long Title',
                    'url' => null
                ]
            ]
        ]
    ],
    [
        'name' => 'Dark Theme',
        'config' => [
            'title' => 'Dark Theme Test',
            'subtitle' => 'Testing contrast in dark theme',
            'theme' => 'dark',
            'breadcrumb' => [
                [
                    'text' => 'Home',
                    'url' => '/index.php'
                ],
                [
                    'text' => 'Dark Theme',
                    'url' => null
                ]
            ]
        ]
    ]
];

// Function to render the page header
function renderPageHeader($config) {
    $header_config = $config;
    ob_start();
    include PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
    return ob_get_clean();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Header Responsive Test</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Page Header CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/layout/page_header.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .test-container {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .test-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .viewport-container {
            border: 1px solid #ddd;
            margin: 15px;
            overflow: hidden;
        }
        
        .viewport-header {
            background-color: #e9ecef;
            padding: 5px 10px;
            font-weight: 500;
        }
        
        .viewport-frame {
            width: 100%;
            border: none;
            resize: both;
            overflow: auto;
        }
        
        .controls {
            position: sticky;
            top: 0;
            background-color: #fff;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            z-index: 1000;
        }
        
        .test-results {
            padding: 15px;
        }
        
        .result-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .result-pass {
            border-color: #4CAF50;
            background-color: rgba(76, 175, 80, 0.1);
        }
        
        .result-fail {
            border-color: #F44336;
            background-color: rgba(244, 67, 54, 0.1);
        }
    </style>
</head>
<body>
    <div class="controls">
        <div class="container">
            <h1>Page Header Responsive Test</h1>
            <p>This page tests the responsive behavior of the page header component across different viewport sizes.</p>
            <div class="row">
                <div class="col-md-6">
                    <h3>Test Instructions</h3>
                    <ol>
                        <li>Examine each viewport size for proper display</li>
                        <li>Check that text remains readable at all sizes</li>
                        <li>Verify spacing and alignment are maintained</li>
                        <li>Confirm that contrast is sufficient for readability</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h3>Requirements Being Tested</h3>
                    <ul>
                        <li><strong>3.1:</strong> Responsive behavior on different screen sizes</li>
                        <li><strong>3.2:</strong> Consistent styling that matches the overall application design</li>
                        <li><strong>3.3:</strong> Adequate spacing and visual hierarchy</li>
                        <li><strong>3.4:</strong> Sufficient contrast for readability</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="test-results">
            <h2>Test Results</h2>
            <p>Record your observations for each test case and viewport size below:</p>
            
            <form id="testForm">
                <?php foreach ($testConfigs as $configIndex => $testConfig): ?>
                <div class="result-item">
                    <h3><?php echo htmlspecialchars($testConfig['name']); ?></h3>
                    
                    <?php foreach ($viewports as $viewportName => $viewportWidth): ?>
                    <div class="mb-3">
                        <label class="form-label"><strong><?php echo htmlspecialchars($viewportName); ?> (<?php echo htmlspecialchars($viewportWidth); ?>)</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="result_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>" id="pass_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>" value="pass">
                            <label class="form-check-label" for="pass_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>">
                                Pass - Displays correctly
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="result_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>" id="fail_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>" value="fail">
                            <label class="form-check-label" for="fail_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>">
                                Fail - Issues detected
                            </label>
                        </div>
                        <div class="mb-3">
                            <label for="notes_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>" class="form-label">Notes:</label>
                            <textarea class="form-control" id="notes_<?php echo $configIndex; ?>_<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>" rows="2"></textarea>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="saveResults">Save Results</button>
                </div>
            </form>
            
            <div id="savedResults" class="mt-4" style="display: none;">
                <h3>Saved Test Results</h3>
                <pre id="resultsOutput"></pre>
            </div>
        </div>
    </div>
    
    <?php foreach ($testConfigs as $configIndex => $testConfig): ?>
    <div class="test-container">
        <div class="test-header">
            <h2><?php echo htmlspecialchars($testConfig['name']); ?></h2>
        </div>
        
        <?php foreach ($viewports as $viewportName => $viewportWidth): ?>
        <div class="viewport-container">
            <div class="viewport-header">
                <?php echo htmlspecialchars($viewportName); ?> (<?php echo htmlspecialchars($viewportWidth); ?>)
            </div>
            <div style="width: <?php echo htmlspecialchars($viewportWidth); ?>; max-width: 100%; margin: 0 auto; border: 1px dashed #ccc;">
                <?php echo renderPageHeader($testConfig['config']); ?>
                <div class="container">
                    <p>Main content would appear here...</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    
    <script>
        document.getElementById('saveResults').addEventListener('click', function() {
            const form = document.getElementById('testForm');
            const formData = new FormData(form);
            const results = {};
            
            // Process form data
            for (const [key, value] of formData.entries()) {
                results[key] = value;
            }
            
            // Add notes
            document.querySelectorAll('textarea').forEach(textarea => {
                results[textarea.id] = textarea.value;
            });
            
            // Display results
            document.getElementById('resultsOutput').textContent = JSON.stringify(results, null, 2);
            document.getElementById('savedResults').style.display = 'block';
        });
    </script>
</body>
</html>