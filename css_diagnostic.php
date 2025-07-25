<?php
/**
 * Diagnostic CSS Check - Insert at the top of edit_program.php temporarily
 */

// Check if this diagnostic is already included to prevent duplicate includes
if (!defined('CSS_DIAGNOSTIC_INCLUDED')) {
    define('CSS_DIAGNOSTIC_INCLUDED', true);
    
    // Start output buffering to capture any output from vite_assets
    ob_start();
    
    echo "<!-- CSS DIAGNOSTIC START -->\n";
    echo "<!-- Current URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . " -->\n";
    echo "<!-- Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . " -->\n";
    
    // Check if vite_assets function exists
    if (function_exists('vite_assets')) {
        echo "<!-- vite_assets function: EXISTS -->\n";
        
        // Test bundle detection
        if (function_exists('detect_bundle_name')) {
            $detected_bundle = detect_bundle_name();
            echo "<!-- Detected bundle: $detected_bundle -->\n";
        }
        
        // Generate the assets
        $vite_output = vite_assets();
        echo "<!-- Vite assets output length: " . strlen($vite_output) . " characters -->\n";
        echo "<!-- Vite assets output: " . htmlspecialchars($vite_output) . " -->\n";
        
        // Check if output contains CSS link
        if (strpos($vite_output, 'admin-programs.bundle.css') !== false) {
            echo "<!-- ✅ admin-programs.bundle.css found in output -->\n";
        } else {
            echo "<!-- ❌ admin-programs.bundle.css NOT found in output -->\n";
        }
        
    } else {
        echo "<!-- ❌ vite_assets function: NOT FOUND -->\n";
    }
    
    // Check if the CSS file physically exists
    $css_file = dirname(dirname(dirname(dirname(__FILE__)))) . '/dist/css/admin-programs.bundle.css';
    echo "<!-- CSS file path: $css_file -->\n";
    echo "<!-- CSS file exists: " . (file_exists($css_file) ? 'YES' : 'NO') . " -->\n";
    if (file_exists($css_file)) {
        echo "<!-- CSS file size: " . filesize($css_file) . " bytes -->\n";
    }
    
    echo "<!-- CSS DIAGNOSTIC END -->\n";
    
    // Get any captured output and clean the buffer
    $diagnostic_output = ob_get_clean();
    
    // Output the diagnostic
    echo $diagnostic_output;
}
?>
