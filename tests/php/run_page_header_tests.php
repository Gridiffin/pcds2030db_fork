<?php
/**
 * Run Page Header Tests
 * 
 * This script runs all the page header component tests and generates a report.
 */

// Ensure PROJECT_ROOT_PATH is defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
}

// Set up test environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/tests/php/run_page_header_tests.php';

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';

// Define test files
$testFiles = [
    'Configuration Tests' => 'page_header_test.php',
    'Responsive Tests' => 'page_header_responsive_test.php'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Header Component Tests</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        
        .test-nav {
            position: sticky;
            top: 0;
            background-color: #fff;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            z-index: 1000;
        }
        
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-nav">
            <h1>Page Header Component Tests</h1>
            <p>This page runs all tests for the page header component.</p>
            
            <div class="mb-3">
                <h3>Test Navigation</h3>
                <ul class="nav nav-pills">
                    <?php foreach ($testFiles as $name => $file): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#<?php echo str_replace(' ', '_', strtolower($name)); ?>"><?php echo htmlspecialchars($name); ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <?php foreach ($testFiles as $name => $file): ?>
        <div id="<?php echo str_replace(' ', '_', strtolower($name)); ?>" class="test-section">
            <h2><?php echo htmlspecialchars($name); ?></h2>
            <div class="test-frame">
                <iframe src="<?php echo htmlspecialchars($file); ?>" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
            <div class="mt-3">
                <a href="<?php echo htmlspecialchars($file); ?>" target="_blank" class="btn btn-primary">Open in New Tab</a>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="test-section">
            <h2>Test Summary Report</h2>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Configuration Tests</h3>
                </div>
                <div class="card-body">
                    <p>These tests verify that the page header component correctly handles different configuration options:</p>
                    <ul>
                        <li><strong>Title and Subtitle Configuration:</strong> The component should correctly display the title and subtitle based on the provided configuration.</li>
                        <li><strong>Breadcrumb Configuration:</strong> The component should correctly display breadcrumb navigation based on the provided configuration.</li>
                        <li><strong>Backward Compatibility:</strong> The component should support legacy configuration formats.</li>
                        <li><strong>Default Values:</strong> The component should provide sensible defaults when configuration options are missing.</li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Responsive Tests</h3>
                </div>
                <div class="card-body">
                    <p>These tests verify that the page header component behaves correctly across different viewport sizes:</p>
                    <ul>
                        <li><strong>Mobile Responsiveness:</strong> The component should be readable and properly formatted on mobile devices.</li>
                        <li><strong>Tablet Responsiveness:</strong> The component should be readable and properly formatted on tablet devices.</li>
                        <li><strong>Desktop Responsiveness:</strong> The component should be readable and properly formatted on desktop devices.</li>
                        <li><strong>Styling Consistency:</strong> The component should maintain consistent styling across all viewport sizes.</li>
                        <li><strong>Spacing and Hierarchy:</strong> The component should maintain adequate spacing and visual hierarchy between elements.</li>
                        <li><strong>Contrast and Readability:</strong> The component should ensure sufficient contrast for readability across all viewport sizes.</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Test Results</h3>
                </div>
                <div class="card-body">
                    <p>Please record your test results here:</p>
                    
                    <form id="summaryForm">
                        <div class="mb-3">
                            <label for="configTestResults" class="form-label">Configuration Tests Results:</label>
                            <select class="form-select" id="configTestResults">
                                <option value="">Select result...</option>
                                <option value="pass">All tests passed</option>
                                <option value="partial">Some tests passed</option>
                                <option value="fail">Tests failed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="configTestNotes" class="form-label">Configuration Tests Notes:</label>
                            <textarea class="form-control" id="configTestNotes" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="responsiveTestResults" class="form-label">Responsive Tests Results:</label>
                            <select class="form-select" id="responsiveTestResults">
                                <option value="">Select result...</option>
                                <option value="pass">All tests passed</option>
                                <option value="partial">Some tests passed</option>
                                <option value="fail">Tests failed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="responsiveTestNotes" class="form-label">Responsive Tests Notes:</label>
                            <textarea class="form-control" id="responsiveTestNotes" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="overallResult" class="form-label">Overall Test Result:</label>
                            <select class="form-select" id="overallResult">
                                <option value="">Select result...</option>
                                <option value="pass">All requirements met</option>
                                <option value="partial">Most requirements met</option>
                                <option value="fail">Requirements not met</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="overallNotes" class="form-label">Overall Notes:</label>
                            <textarea class="form-control" id="overallNotes" rows="5"></textarea>
                        </div>
                        
                        <button type="button" class="btn btn-primary" id="saveSummary">Save Summary</button>
                    </form>
                    
                    <div id="savedSummary" class="mt-4" style="display: none;">
                        <h4>Saved Test Summary</h4>
                        <pre id="summaryOutput"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Activate the first tab
        document.querySelector('.nav-link').classList.add('active');
        
        // Handle navigation clicks
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                document.querySelectorAll('.nav-link').forEach(l => {
                    l.classList.remove('active');
                });
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Scroll to target section
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Handle summary form submission
        document.getElementById('saveSummary').addEventListener('click', function() {
            const summary = {
                configTests: {
                    result: document.getElementById('configTestResults').value,
                    notes: document.getElementById('configTestNotes').value
                },
                responsiveTests: {
                    result: document.getElementById('responsiveTestResults').value,
                    notes: document.getElementById('responsiveTestNotes').value
                },
                overall: {
                    result: document.getElementById('overallResult').value,
                    notes: document.getElementById('overallNotes').value
                },
                timestamp: new Date().toISOString()
            };
            
            // Display summary
            document.getElementById('summaryOutput').textContent = JSON.stringify(summary, null, 2);
            document.getElementById('savedSummary').style.display = 'block';
        });
    </script>
</body>
</html>