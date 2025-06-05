<?php
/**
 * Admin Settings Functions
 * 
 * Functions for managing system settings
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once dirname(__DIR__) . '/audit_log.php';
require_once 'core.php';

/**
 * Update the multi-sector enabled setting
 * 
 * This function updates the MULTI_SECTOR_ENABLED constant value in the config.php file
 * 
 * @param bool $enabled Whether multi-sector functionality should be enabled
 * @return array Result of the update operation
 */
function update_multi_sector_setting($enabled) {
    // Only admin can update system settings
    if (!is_admin()) {
        // Log unauthorized settings access attempt
        log_audit_action(
            'settings_update_denied',
            'Unauthorized attempt to update multi-sector setting',
            'failure'
        );
        return ['error' => 'Permission denied'];
    }
    
    // Convert to boolean to ensure clean value
    $enabled = (bool)$enabled;
    
    // Get config file path
    $config_file = dirname(dirname(__DIR__)) . '/config/config.php';
    
    if (!file_exists($config_file)) {
        return ['error' => 'Config file not found'];
    }
    
    // Read the config file
    $config_content = file_get_contents($config_file);
    if ($config_content === false) {
        return ['error' => 'Could not read config file'];
    }
    
    // Replace the MULTI_SECTOR_ENABLED value
    $new_value = $enabled ? 'true' : 'false';
    $pattern = "/(define\('MULTI_SECTOR_ENABLED',\s*)(true|false)(\);)/i";
    $replacement = "$1$new_value$3";
    
    $updated_content = preg_replace($pattern, $replacement, $config_content);
    
    if ($updated_content === $config_content && $updated_content !== null) {
        // No changes made - pattern not found or already set to desired value
        return ['warning' => 'No changes needed, setting already set to ' . ($enabled ? 'enabled' : 'disabled')];
    }
    
    if ($updated_content === null) {
        // preg_replace error
        return ['error' => 'Error updating config file'];
    }
      // Write the updated content back to the file
    $result = file_put_contents($config_file, $updated_content);
    
    if ($result === false) {
        // Log failed setting change
        log_audit_action(
            'settings_update_failed',
            'Failed to write multi-sector setting to config file',
            'failure'
        );
        return ['error' => 'Could not write to config file'];
    }
    
    // Log successful setting change
    log_audit_action(
        'settings_update',
        'Multi-sector setting updated to: ' . ($enabled ? 'Enabled' : 'Disabled'),
        'success'
    );
    
    return [
        'success' => true,
        'message' => 'Multi-Sector mode has been ' . ($enabled ? 'enabled' : 'disabled') . '. You may need to refresh the page to see the changes.'
    ];
}

/**
 * Get the current state of the MULTI_SECTOR_ENABLED setting
 * 
 * @return bool Current state of MULTI_SECTOR_ENABLED
 */
function get_multi_sector_setting() {
    return defined('MULTI_SECTOR_ENABLED') ? MULTI_SECTOR_ENABLED : false;
}

/**
 * Update the outcome creation setting
 * 
 * This function updates the ALLOW_OUTCOME_CREATION constant value in the config.php file
 * 
 * @param bool $enabled Whether outcome creation should be enabled
 * @return array Result of the update operation
 */
function update_outcome_creation_setting($enabled) {
    // Only admin can update system settings
    if (!is_admin()) {
        // Log unauthorized settings access attempt
        log_audit_action(
            'settings_update_denied',
            'Unauthorized attempt to update outcome creation setting',
            'failure'
        );
        return ['error' => 'Permission denied'];
    }
    
    // Convert to boolean to ensure clean value
    $enabled = (bool)$enabled;
    
    // Get config file path
    $config_file = dirname(dirname(__DIR__)) . '/config/config.php';
    
    if (!file_exists($config_file)) {
        return ['error' => 'Config file not found'];
    }
    
    // Read the config file
    $config_content = file_get_contents($config_file);
    if ($config_content === false) {
        return ['error' => 'Could not read config file'];
    }
    
    // Replace the ALLOW_OUTCOME_CREATION value
    $new_value = $enabled ? 'true' : 'false';
    $pattern = "/(define\('ALLOW_OUTCOME_CREATION',\s*)(true|false)(\);)/i";
    $replacement = "$1$new_value$3";
    
    $updated_content = preg_replace($pattern, $replacement, $config_content);
    
    if ($updated_content === $config_content && $updated_content !== null) {
        // No changes made - pattern not found or already set to desired value
        return ['warning' => 'No changes needed, setting already set to ' . ($enabled ? 'enabled' : 'disabled')];
    }
    
    if ($updated_content === null) {
        // preg_replace error
        return ['error' => 'Error updating config file'];
    }
      // Write the updated content back to the file
    $result = file_put_contents($config_file, $updated_content);
    
    if ($result === false) {
        // Log failed setting change
        log_audit_action(
            'settings_update_failed',
            'Failed to write outcome creation setting to config file',
            'failure'
        );
        return ['error' => 'Could not write to config file'];
    }
    
    // Log successful setting change
    log_audit_action(
        'settings_update',
        'Outcome creation setting updated to: ' . ($enabled ? 'Enabled' : 'Disabled'),
        'success'
    );
    
    return [
        'success' => true,
        'message' => 'Outcome creation has been ' . ($enabled ? 'enabled' : 'disabled') . '. You may need to refresh the page to see the changes.'
    ];
}

/**
 * Get the current state of the ALLOW_OUTCOME_CREATION setting
 * 
 * @return bool Current state of ALLOW_OUTCOME_CREATION
 */
function get_outcome_creation_setting() {
    return defined('ALLOW_OUTCOME_CREATION') ? ALLOW_OUTCOME_CREATION : false;
}