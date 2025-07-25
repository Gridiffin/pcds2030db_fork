<?php

/**
 * Vite Asset Helper - Modern Bundle Loading System
 * Loads the correct CSS/JS bundle based on current page
 * Eliminates individual CSS file loading
 */

function vite_assets($bundle_name = null) {
    $is_dev = getenv('APP_ENV') === 'development';
    
    // Get proper document root - handle both web and CLI contexts
    if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
    } else {
        // Fallback for CLI or when DOCUMENT_ROOT is not set
        $doc_root = dirname(dirname(dirname(__FILE__)));
    }
    
    // For this specific project structure, ensure we use the correct path
    if (strpos($doc_root, 'pcds2030_dashboard_fork') === false) {
        // We're likely in a web context where DOCUMENT_ROOT points to a different location
        $doc_root = dirname(dirname(dirname(__FILE__)));
    }
    
    $dist_path = rtrim($doc_root, '/') . '/dist/';
    
    // Auto-detect bundle name if not provided
    if (!$bundle_name) {
        $bundle_name = detect_bundle_name();
    }
    
    // Debug output (remove in production)
    if (isset($_GET['debug_vite'])) {
        echo "<!-- DEBUG: Bundle name detected: $bundle_name -->\n";
        echo "<!-- DEBUG: Dist path: $dist_path -->\n";
        echo "<!-- DEBUG: REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . " -->\n";
        echo "<!-- DEBUG: SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . " -->\n";
    }
    
    if ($is_dev) {
        return '
            <script type="module" src="http://localhost:3000/@vite/client"></script>
            <script type="module" src="http://localhost:3000/assets/js/' . $bundle_name . '.js"></script>
        ';
    }

    // Production mode - load bundled assets
    $html = '';
    
    // Determine the web-accessible path based on current request
    $app_base_path = '';
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/pcds2030_dashboard_fork/') !== false) {
        $app_base_path = '/pcds2030_dashboard_fork';
    }
    
    // Load CSS bundle
    $css_file = $dist_path . 'css/' . $bundle_name . '.bundle.css';
    if (file_exists($css_file)) {
        $css_url = $app_base_path . '/dist/css/' . $bundle_name . '.bundle.css';
        $html .= '<link rel="stylesheet" href="' . $css_url . '">' . "\n";
        if (isset($_GET['debug_vite'])) {
            echo "<!-- DEBUG: CSS file found and loaded: $css_file -->\n";
            echo "<!-- DEBUG: CSS URL: $css_url -->\n";
        }
    } else {
        if (isset($_GET['debug_vite'])) {
            echo "<!-- DEBUG: CSS file NOT found: $css_file -->\n";
        }
    }
    
    // Load JS bundle
    $js_file = $dist_path . 'js/' . $bundle_name . '.bundle.js';
    if (file_exists($js_file)) {
        $js_url = $app_base_path . '/dist/js/' . $bundle_name . '.bundle.js';
        $html .= '<script type="module" src="' . $js_url . '"></script>' . "\n";
        if (isset($_GET['debug_vite'])) {
            echo "<!-- DEBUG: JS file found and loaded: $js_file -->\n";
            echo "<!-- DEBUG: JS URL: $js_url -->\n";
        }
    } else {
        if (isset($_GET['debug_vite'])) {
            echo "<!-- DEBUG: JS file NOT found: $js_file -->\n";
        }
    }

    return $html;
}

/**
 * Auto-detect bundle name based on current page/route
 * Maps URLs to bundle names for optimal loading
 */
function detect_bundle_name() {
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Extract page name from URL
    $page = basename($script_name, '.php');
    $path_parts = explode('/', trim($request_uri, '/'));
    
    // Debug output (remove in production)
    if (isset($_GET['debug_vite'])) {
        echo "<!-- DEBUG: detect_bundle_name() called -->\n";
        echo "<!-- DEBUG: Page extracted: $page -->\n";
        echo "<!-- DEBUG: Path parts: " . implode(', ', $path_parts) . " -->\n";
    }
    
    // Agency module mapping
    if (strpos($request_uri, '/agency/') !== false) {
        // Programs module (check first to avoid dashboard fallback)
        if (strpos($request_uri, '/programs/') !== false || $page === 'view_programs' || strpos($request_uri, 'view_programs') !== false) {
            if (isset($_GET['debug_vite'])) {
                echo "<!-- DEBUG: In programs module detection -->\n";
                echo "<!-- DEBUG: Page check: $page === 'view_programs' = " . ($page === 'view_programs' ? 'true' : 'false') . " -->\n";
                echo "<!-- DEBUG: view_programs in URI: " . (strpos($request_uri, 'view_programs') !== false ? 'true' : 'false') . " -->\n";
            }
            if ($page === 'view_programs' || $page === 'view_programs_content' || strpos($request_uri, 'view_programs') !== false) {
                if (isset($_GET['debug_vite'])) {
                    echo "<!-- DEBUG: Returning agency-view-programs bundle -->\n";
                }
                return 'agency-view-programs';
            }
            if ($page === 'create_program') {
                return 'agency-create-program';
            }
            if ($page === 'edit_program') {
                return 'agency-edit-program';
            }
            if ($page === 'add_submission') {
                return 'agency-add-submission';
            }
            if ($page === 'program_details') {
                return 'agency-program-details';
            }
            if ($page === 'edit_submission') {
                return 'agency-edit-submission';
            }
            if ($page === 'view_submissions') {
                return 'agency-view-submissions';
            }
            if ($page === 'view_other_agency_programs') {
                return 'agency-view-other-programs';
            }
        }
        
        // Dashboard
        if (strpos($request_uri, '/dashboard/') !== false || $page === 'dashboard') {
            return 'agency-dashboard';
        }
        
        // Initiatives module
        if (strpos($request_uri, '/initiatives/') !== false) {
            if ($page === 'view_initiative') {
                return 'agency-view-initiative';
            }
            return 'agency-initiatives';
        }
        
        // Outcomes module
        if (strpos($request_uri, '/outcomes/') !== false) {
            if ($page === 'submit_outcomes') {
                return 'agency-submit-outcomes';
            }
            return 'agency-outcomes';
        }
        
        // Reports module
        if (strpos($request_uri, '/reports/') !== false) {
            return 'agency-reports';
        }
        
        // Users/Notifications
        if (strpos($request_uri, '/users/') !== false) {
            return 'agency-notifications';
        }
    }
    
    // Admin module mapping
    if (strpos($request_uri, '/admin/') !== false) {
        if (strpos($request_uri, '/programs/') !== false) {
            return 'admin-programs';
        }
        if (strpos($request_uri, '/initiatives/') !== false) {
            return 'admin-manage-initiatives';
        }
        if (strpos($request_uri, '/reports/') !== false) {
            return 'admin-reports';
        }
        return 'admin-common';
    }
    
    // Login page
    if ($page === 'login' || strpos($request_uri, 'login') !== false) {
        return 'login';
    }
    
    // Default fallback
    $bundle_name = 'agency-dashboard';
    if (isset($_GET['debug_vite'])) {
        echo "<!-- DEBUG: Returning default bundle: $bundle_name -->\n";
    }
    return $bundle_name;
}

/**
 * Get bundle CSS path for current page
 * Used to eliminate individual CSS loading
 */
function get_bundle_css($bundle_name = null) {
    $bundle_name = $bundle_name ?: detect_bundle_name();
    return "/dist/css/{$bundle_name}.bundle.css";
}

/**
 * Check if bundle exists for current page
 */
function bundle_exists($bundle_name = null) {
    $bundle_name = $bundle_name ?: detect_bundle_name();
    $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(dirname(__FILE__)));
    $css_file = rtrim($doc_root, '/') . "/dist/css/{$bundle_name}.bundle.css";
    return file_exists($css_file);
} 