<?php
/**
 * Asset URL helper functions
 */

if (!function_exists('asset_url')) {
    /**
     * Generate URL for assets with improved cross-environment compatibility
     * 
     * @param string $type Type of asset (css, js, img, etc.)
     * @param string $file The file name
     * @return string The complete URL to the asset
     */
    function asset_url($type, $file) {
        // Use APP_URL if defined, otherwise fallback to relative path detection
        if (defined('APP_URL')) {
            return APP_URL . '/assets/' . $type . '/' . $file;
        }
        
        // Fallback for cases where APP_URL is not available
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Try to detect the base path
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        $base_path = dirname($script_name);
        
        // Clean up the path
        $base_path = str_replace('\\', '/', $base_path); // Windows compatibility
        $base_path = rtrim($base_path, '/');
        
        // If we're in a subdirectory, adjust the path
        if (strpos($base_path, '/app/') !== false) {
            $base_path = substr($base_path, 0, strpos($base_path, '/app/'));
        }
        
        return $protocol . '://' . $host . $base_path . '/assets/' . $type . '/' . $file;
    }
}

if (!function_exists('asset_path')) {
    /**
     * Generate physical file path for assets
     * 
     * @param string $type Type of asset (css, js, img, etc.)
     * @param string $file The file name
     * @return string The complete file path to the asset
     */
    function asset_path($type, $file) {
        $root_path = defined('ROOT_PATH') ? ROOT_PATH : (defined('PROJECT_ROOT_PATH') ? PROJECT_ROOT_PATH : dirname(dirname(dirname(__FILE__))) . '/');
        return $root_path . 'assets/' . $type . '/' . $file;
    }
}

if (!function_exists('asset_exists')) {
    /**
     * Check if an asset file exists
     * 
     * @param string $type Type of asset (css, js, img, etc.)
     * @param string $file The file name
     * @return bool True if the asset file exists
     */
    function asset_exists($type, $file) {
        return file_exists(asset_path($type, $file));
    }
}
