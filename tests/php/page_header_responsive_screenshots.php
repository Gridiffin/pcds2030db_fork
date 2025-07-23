<?php
/**
 * Page Header Responsive Screenshots
 * 
 * This file generates screenshots of the page header component at different viewport sizes
 * to help with visual testing of responsive behavior.
 * 
 * Note: This requires html2canvas.js to be included in the page.
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
            'title' => 'This is a very long page title that should wrap on smaller screens',
            'subtitle' => 'This is also a very long subtitle that contains a lot of text to see how it wraps',
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
    <title>Page Header Responsive Screenshots</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Page Header CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/layout/page_header.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        
        .controls {
            position: sticky;
            top: 0;
            background-color: #fff;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            z-index: 1000;
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
            margin: 15px;
            overflow: hidden;
        }
        
        .viewport-header {
            background-color: #e9ecef;
            padding: 5px 10px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .screenshot-container {
            margin-top: 20px;
        }
        
        .screenshot {
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="controls">
        <div class="container">
            <h1>Page Header Responsive Screenshots</h1>
            <p>This page generates screenshots of the page header component at different viewport sizes to help with visual testing of responsive behavior.</p>
            <button id="captureAll" class="btn btn-primary">Capture All Screenshots</button>
        </div>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2>Test Cases</h2>
                
                <?php foreach ($testConfigs as $configIndex => $testConfig): ?>
                <div class="test-container" id="test-<?php echo $configIndex; ?>">
                    <div class="test-header">
                        <h3><?php echo htmlspecialchars($testConfig['name']); ?></h3>
                    </div>
                    
                    <?php foreach ($viewports as $viewportName => $viewportWidth): ?>
                    <div class="viewport-container">
                        <div class="viewport-header">
                            <?php echo htmlspecialchars($viewportName); ?> (<?php echo htmlspecialchars($viewportWidth); ?>)
                        </div>
                        <div class="header-container" style="width: <?php echo htmlspecialchars($viewportWidth); ?>; max-width: 100%;" id="header-<?php echo $configIndex; ?>-<?php echo str_replace(' ', '_', strtolower($viewportName)); ?>">
                            <?php echo renderPageHeader($testConfig['config']); ?>
                            <div class="container">
                                <p>Main content would appear here...</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="col-md-6">
                <h2>Screenshots</h2>
                <div class="screenshot-container" id="screenshots">
                    <p>Click "Capture All Screenshots" to generate screenshots.</p>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <h2>Responsive Testing Checklist</h2>
                <form id="checklistForm">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Mobile Viewport (320px - 425px)</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="mobile-readable">
                                <label class="form-check-label" for="mobile-readable">
                                    Text is readable without zooming
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="mobile-spacing">
                                <label class="form-check-label" for="mobile-spacing">
                                    Elements have adequate spacing
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="mobile-alignment">
                                <label class="form-check-label" for="mobile-alignment">
                                    Elements are properly aligned
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="mobile-contrast">
                                <label class="form-check-label" for="mobile-contrast">
                                    Text has sufficient contrast
                                </label>
                            </div>
                            <div class="mb-3">
                                <label for="mobile-notes" class="form-label">Notes:</label>
                                <textarea class="form-control" id="mobile-notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Tablet Viewport (768px)</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="tablet-readable">
                                <label class="form-check-label" for="tablet-readable">
                                    Text is readable without zooming
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="tablet-spacing">
                                <label class="form-check-label" for="tablet-spacing">
                                    Elements have adequate spacing
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="tablet-alignment">
                                <label class="form-check-label" for="tablet-alignment">
                                    Elements are properly aligned
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="tablet-contrast">
                                <label class="form-check-label" for="tablet-contrast">
                                    Text has sufficient contrast
                                </label>
                            </div>
                            <div class="mb-3">
                                <label for="tablet-notes" class="form-label">Notes:</label>
                                <textarea class="form-control" id="tablet-notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Desktop Viewport (1024px - 1440px)</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="desktop-readable">
                                <label class="form-check-label" for="desktop-readable">
                                    Text is readable without zooming
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="desktop-spacing">
                                <label class="form-check-label" for="desktop-spacing">
                                    Elements have adequate spacing
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="desktop-alignment">
                                <label class="form-check-label" for="desktop-alignment">
                                    Elements are properly aligned
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="desktop-contrast">
                                <label class="form-check-label" for="desktop-contrast">
                                    Text has sufficient contrast
                                </label>
                            </div>
                            <div class="mb-3">
                                <label for="desktop-notes" class="form-label">Notes:</label>
                                <textarea class="form-control" id="desktop-notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Overall Assessment</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="overall-result" class="form-label">Overall Result:</label>
                                <select class="form-select" id="overall-result">
                                    <option value="">Select result...</option>
                                    <option value="pass">Pass - All requirements met</option>
                                    <option value="partial">Partial - Most requirements met</option>
                                    <option value="fail">Fail - Requirements not met</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="overall-notes" class="form-label">Overall Notes:</label>
                                <textarea class="form-control" id="overall-notes" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary" id="saveChecklist">Save Checklist</button>
                </form>
                
                <div id="savedChecklist" class="mt-4" style="display: none;">
                    <h3>Saved Checklist</h3>
                    <pre id="checklistOutput"></pre>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include html2canvas for screenshots -->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const captureAllButton = document.getElementById('captureAll');
            const screenshotsContainer = document.getElementById('screenshots');
            
            captureAllButton.addEventListener('click', function() {
                // Clear previous screenshots
                screenshotsContainer.innerHTML = '<h3>Generating screenshots...</h3>';
                
                const headerContainers = document.querySelectorAll('[id^="header-"]');
                let capturedCount = 0;
                let totalCount = headerContainers.length;
                
                // Create a container for screenshots
                const screenshotResults = document.createElement('div');
                
                // Capture each header container
                headerContainers.forEach(function(container) {
                    const id = container.id;
                    const parts = id.split('-');
                    const configIndex = parts[1];
                    const viewportName = parts.slice(2).join('_').replace(/_/g, ' ');
                    
                    html2canvas(container).then(function(canvas) {
                        // Create a container for this screenshot
                        const screenshotDiv = document.createElement('div');
                        screenshotDiv.className = 'screenshot';
                        
                        // Add header with test case and viewport info
                        const header = document.createElement('div');
                        header.className = 'viewport-header';
                        header.textContent = document.querySelector(`#test-${configIndex} .test-header h3`).textContent + 
                                            ' - ' + viewportName;
                        screenshotDiv.appendChild(header);
                        
                        // Add the canvas
                        canvas.className = 'img-fluid';
                        screenshotDiv.appendChild(canvas);
                        
                        // Add to results
                        screenshotResults.appendChild(screenshotDiv);
                        
                        // Update count
                        capturedCount++;
                        
                        // Check if all screenshots have been captured
                        if (capturedCount === totalCount) {
                            screenshotsContainer.innerHTML = '';
                            screenshotsContainer.appendChild(screenshotResults);
                        }
                    });
                });
            });
            
            // Handle checklist form submission
            document.getElementById('saveChecklist').addEventListener('click', function() {
                const checklist = {
                    mobile: {
                        readable: document.getElementById('mobile-readable').checked,
                        spacing: document.getElementById('mobile-spacing').checked,
                        alignment: document.getElementById('mobile-alignment').checked,
                        contrast: document.getElementById('mobile-contrast').checked,
                        notes: document.getElementById('mobile-notes').value
                    },
                    tablet: {
                        readable: document.getElementById('tablet-readable').checked,
                        spacing: document.getElementById('tablet-spacing').checked,
                        alignment: document.getElementById('tablet-alignment').checked,
                        contrast: document.getElementById('tablet-contrast').checked,
                        notes: document.getElementById('tablet-notes').value
                    },
                    desktop: {
                        readable: document.getElementById('desktop-readable').checked,
                        spacing: document.getElementById('desktop-spacing').checked,
                        alignment: document.getElementById('desktop-alignment').checked,
                        contrast: document.getElementById('desktop-contrast').checked,
                        notes: document.getElementById('desktop-notes').value
                    },
                    overall: {
                        result: document.getElementById('overall-result').value,
                        notes: document.getElementById('overall-notes').value
                    },
                    timestamp: new Date().toISOString()
                };
                
                // Display checklist
                document.getElementById('checklistOutput').textContent = JSON.stringify(checklist, null, 2);
                document.getElementById('savedChecklist').style.display = 'block';
            });
        });
    </script>
</body>
</html>