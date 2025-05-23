<?php
/**
 * Asset URL helper functions
 */

if (!function_exists('asset_url')) {
    /**
     * Generate URL for assets
     * 
     * @param string $type Type of asset (css, js, img, etc.)
     * @param string $file The file name
     * @return string The complete URL to the asset
     */
    function asset_url($type, $file) {
        return APP_URL . '/assets/' . $type . '/' . $file;
    }
}
