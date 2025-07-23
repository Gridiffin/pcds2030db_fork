<?php
/**
 * Page Header Component Test
 * 
 * This file tests the page header component with different configuration options
 * to ensure it meets the requirements specified in the design document.
 * 
 * Requirements being tested:
 * - 2.1: Configure title and subtitle
 * - 2.2: Configure breadcrumb trail
 * - 2.3: Backward compatibility
 * - 2.4: Sensible defaults
 */

// Ensure PROJECT_ROOT_PATH is defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';

// Test case class
class PageHeaderTest {
    private $testCases = [];
    private $currentTestCase = null;
    private $results = [];
    
    public function __construct() {
        $this->setupTestCases();
    }
    
    private function setupTestCases() {
        // Test Case 1: Basic configuration with title only
        $this->testCases[] = [
            'name' => 'Basic Title Only',
            'config' => [
                'title' => 'Test Page Title'
            ],
            'expected' => [
                'title' => 'Test Page Title',
                'subtitle' => false,
                'breadcrumb' => false
            ]
        ];
        
        // Test Case 2: Title and subtitle
        $this->testCases[] = [
            'name' => 'Title and Subtitle',
            'config' => [
                'title' => 'Test Page Title',
                'subtitle' => 'This is a test subtitle'
            ],
            'expected' => [
                'title' => 'Test Page Title',
                'subtitle' => 'This is a test subtitle',
                'breadcrumb' => false
            ]
        ];
        
        // Test Case 3: Title with breadcrumb
        $this->testCases[] = [
            'name' => 'Title with Breadcrumb',
            'config' => [
                'title' => 'Test Page Title',
                'breadcrumb' => [
                    [
                        'text' => 'Home',
                        'url' => '/index.php'
                    ],
                    [
                        'text' => 'Test Page',
                        'url' => null
                    ]
                ]
            ],
            'expected' => [
                'title' => 'Test Page Title',
                'subtitle' => false,
                'breadcrumb' => true,
                'breadcrumbItems' => 2
            ]
        ];
        
        // Test Case 4: Complete configuration
        $this->testCases[] = [
            'name' => 'Complete Configuration',
            'config' => [
                'title' => 'Test Page Title',
                'subtitle' => 'This is a test subtitle',
                'breadcrumb' => [
                    [
                        'text' => 'Home',
                        'url' => '/index.php'
                    ],
                    [
                        'text' => 'Category',
                        'url' => '/index.php?page=category'
                    ],
                    [
                        'text' => 'Test Page',
                        'url' => null
                    ]
                ],
                'classes' => 'custom-header-class'
            ],
            'expected' => [
                'title' => 'Test Page Title',
                'subtitle' => 'This is a test subtitle',
                'breadcrumb' => true,
                'breadcrumbItems' => 3,
                'customClass' => true
            ]
        ];
        
        // Test Case 5: Legacy configuration
        $this->testCases[] = [
            'name' => 'Legacy Configuration',
            'legacy' => true,
            'config' => [
                'headerTitle' => 'Legacy Title',
                'headerSubtitle' => 'Legacy Subtitle',
                'breadcrumbItems' => [
                    [
                        'label' => 'Home',
                        'link' => '/index.php'
                    ],
                    [
                        'label' => 'Legacy Page',
                        'link' => null
                    ]
                ]
            ],
            'expected' => [
                'title' => 'Legacy Title',
                'subtitle' => 'Legacy Subtitle',
                'breadcrumb' => true,
                'breadcrumbItems' => 2
            ]
        ];
        
        // Test Case 6: Theme variants
        $this->testCases[] = [
            'name' => 'Theme Variants',
            'config' => [
                'title' => 'Themed Header',
                'subtitle' => 'With dark theme',
                'theme' => 'dark'
            ],
            'expected' => [
                'title' => 'Themed Header',
                'subtitle' => 'With dark theme',
                'theme' => 'dark'
            ]
        ];
        
        // Test Case 7: Hidden elements
        $this->testCases[] = [
            'name' => 'Hidden Elements',
            'config' => [
                'title' => 'Hidden Elements Test',
                'subtitle' => 'This subtitle should be hidden',
                'showSubtitle' => false,
                'breadcrumb' => [
                    [
                        'text' => 'Home',
                        'url' => '/index.php'
                    ],
                    [
                        'text' => 'Hidden Breadcrumb',
                        'url' => null
                    ]
                ],
                'showBreadcrumb' => false
            ],
            'expected' => [
                'title' => 'Hidden Elements Test',
                'subtitle' => false,
                'breadcrumb' => false
            ]
        ];
        
        // Test Case 8: Custom title tag
        $this->testCases[] = [
            'name' => 'Custom Title Tag',
            'config' => [
                'title' => 'H2 Title',
                'titleTag' => 'h2'
            ],
            'expected' => [
                'title' => 'H2 Title',
                'titleTag' => 'h2'
            ]
        ];
    }
    
    public function runTests() {
        foreach ($this->testCases as $index => $testCase) {
            $this->currentTestCase = $testCase;
            $this->runSingleTest($index);
        }
        
        return $this->results;
    }
    
    private function runSingleTest($index) {
        // Clear any previous variables
        unset($header_config, $pageTitle, $headerTitle, $headerSubtitle, $breadcrumbItems);
        
        // Set up test environment
        if (isset($this->currentTestCase['legacy']) && $this->currentTestCase['legacy']) {
            // Set legacy variables
            foreach ($this->currentTestCase['config'] as $key => $value) {
                $$key = $value;
            }
        } else {
            // Set modern configuration
            $header_config = $this->currentTestCase['config'];
        }
        
        // Capture output
        ob_start();
        include PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
        $output = ob_get_clean();
        
        // Analyze output
        $result = $this->analyzeOutput($output);
        
        // Store result
        $this->results[$index] = [
            'name' => $this->currentTestCase['name'],
            'passed' => $this->validateResult($result),
            'output' => $output,
            'result' => $result,
            'expected' => $this->currentTestCase['expected']
        ];
    }
    
    private function analyzeOutput($output) {
        $result = [];
        
        // Check for title
        preg_match('/<(?:h1|h2|h3|h4|h5|h6) class="page-header__title">(.*?)<\/(?:h1|h2|h3|h4|h5|h6)>/s', $output, $titleMatches);
        $result['title'] = $titleMatches[1] ?? null;
        
        // Check for subtitle
        preg_match('/<p class="page-header__subtitle">(.*?)<\/p>/s', $output, $subtitleMatches);
        $result['subtitle'] = $subtitleMatches[1] ?? null;
        
        // Check for breadcrumb
        $result['hasBreadcrumb'] = strpos($output, 'class="page-header__breadcrumb"') !== false;
        
        // Count breadcrumb items
        preg_match_all('/<li class="breadcrumb-item(?:\s+active)?"/s', $output, $breadcrumbMatches);
        $result['breadcrumbItems'] = count($breadcrumbMatches[0]);
        
        // Check for custom classes
        if (isset($this->currentTestCase['config']['classes'])) {
            $result['hasCustomClass'] = strpos($output, $this->currentTestCase['config']['classes']) !== false;
        }
        
        // Check for theme
        if (isset($this->currentTestCase['config']['theme'])) {
            $result['hasTheme'] = strpos($output, 'page-header--' . $this->currentTestCase['config']['theme']) !== false;
        }
        
        // Check for title tag
        if (isset($this->currentTestCase['config']['titleTag'])) {
            $tag = $this->currentTestCase['config']['titleTag'];
            $result['hasTitleTag'] = strpos($output, "<{$tag} class=\"page-header__title\">") !== false;
        }
        
        return $result;
    }
    
    private function validateResult($result) {
        $expected = $this->currentTestCase['expected'];
        $valid = true;
        
        // Validate title
        if (isset($expected['title']) && $result['title'] !== $expected['title']) {
            $valid = false;
        }
        
        // Validate subtitle
        if (isset($expected['subtitle'])) {
            if ($expected['subtitle'] === false && $result['subtitle'] !== null) {
                $valid = false;
            } elseif ($expected['subtitle'] !== false && $result['subtitle'] !== $expected['subtitle']) {
                $valid = false;
            }
        }
        
        // Validate breadcrumb
        if (isset($expected['breadcrumb'])) {
            if ($expected['breadcrumb'] === false && $result['hasBreadcrumb']) {
                $valid = false;
            } elseif ($expected['breadcrumb'] === true && !$result['hasBreadcrumb']) {
                $valid = false;
            }
        }
        
        // Validate breadcrumb items
        if (isset($expected['breadcrumbItems']) && $result['breadcrumbItems'] !== $expected['breadcrumbItems']) {
            $valid = false;
        }
        
        // Validate custom class
        if (isset($expected['customClass']) && $expected['customClass'] && !$result['hasCustomClass']) {
            $valid = false;
        }
        
        // Validate theme
        if (isset($expected['theme']) && !$result['hasTheme']) {
            $valid = false;
        }
        
        // Validate title tag
        if (isset($expected['titleTag']) && !$result['hasTitleTag']) {
            $valid = false;
        }
        
        return $valid;
    }
    
    public function displayResults() {
        echo "<h1>Page Header Component Test Results</h1>";
        
        foreach ($this->results as $index => $result) {
            echo "<div style='margin-bottom: 20px; padding: 15px; border: 1px solid " . ($result['passed'] ? "#4CAF50" : "#F44336") . "; border-radius: 5px;'>";
            echo "<h2>Test Case " . ($index + 1) . ": " . htmlspecialchars($result['name']) . "</h2>";
            echo "<p><strong>Status:</strong> " . ($result['passed'] ? "PASSED" : "FAILED") . "</p>";
            
            if (!$result['passed']) {
                echo "<h3>Expected vs Actual:</h3>";
                echo "<pre>";
                print_r([
                    'expected' => $result['expected'],
                    'actual' => $result['result']
                ]);
                echo "</pre>";
            }
            
            echo "<h3>Rendered Output:</h3>";
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin-top: 10px;'>";
            echo $result['output'];
            echo "</div>";
            
            echo "</div>";
        }
    }
}

// Run the tests
$tester = new PageHeaderTest();
$results = $tester->runTests();

// Display results
$tester->displayResults();