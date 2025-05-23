<?php
/**
 * Agency Functions
 * 
 * This file has been reorganized into a modular structure.
 * The actual implementation of agency functions can now be found in:
 * - includes/agencies/core.php
 * - includes/agencies/programs.php
 * - includes/agencies/metrics.php
 * - includes/agencies/statistics.php
 * 
 * This file remains for backward compatibility and simply includes
 * the new modular structure.
 */

// Include all agency functions from the new modular structure
require_once __DIR__ . '/agencies/index.php';
?>
