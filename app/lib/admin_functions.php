<?php
/**
 * Admin Functions
 * 
 * This file has been reorganized into a modular structure.
 * The actual implementation of admin functions can now be found in:
 * - includes/admins/core.php
 * - includes/admins/periods.php
 * - includes/admins/statistics.php
 * - includes/admins/users.php
 * - includes/admins/metrics.php
 * 
 * This file remains for backward compatibility and simply includes
 * the new modular structure.
 */

// Include all admin functions from the new modular structure
require_once __DIR__ . '/admins/index.php';
?>
