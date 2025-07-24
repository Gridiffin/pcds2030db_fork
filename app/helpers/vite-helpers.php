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
    $dist_path = rtrim($doc_root, '/') . '/dist/';
    
    // Auto-detect bundle name if not provided
    if (!$bundle_name) {
        $bundle_name = detect_bundle_name();
    }
    
    if ($is_dev) {
        return '
            <script type="module" src="http://localhost:3000/@vite/client"></script>
            <script type="module" src="http://localhost:3000/assets/js/' . $bundle_name . '.js"></script>
        ';
    }

    // Production mode - load bundled assets
    $html = '';
    
    // Load CSS bundle
    $css_file = $dist_path . 'css/' . $bundle_name . '.bundle.css';
    if (file_exists($css_file)) {
        $html .= '<link rel="stylesheet" href="/dist/css/' . $bundle_name . '.bundle.css">' . "\n";
    }
    
    // Load JS bundle
    $js_file = $dist_path . 'js/' . $bundle_name . '.bundle.js';
    if (file_exists($js_file)) {
        $html .= '<script type="module" src="/dist/js/' . $bundle_name . '.bundle.js"></script>' . "\n";
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
    
    // Agency module mapping
    if (strpos($request_uri, '/agency/') !== false) {
        // Programs module (check first to avoid dashboard fallback)
        if (strpos($request_uri, '/programs/') !== false || $page === 'view_programs' || strpos($request_uri, 'view_programs') !== false) {
            if ($page === 'view_programs' || $page === 'view_programs_content' || strpos($request_uri, 'view_programs') !== false) {
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
    return 'agency-dashboard';
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