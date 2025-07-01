<?php
/**
 * Test Script for Gantt Chart
 * This redirects to the actual test script for easier access.
 */

header('Location: scripts/test_gantt_data.php' . (isset($_GET['id']) ? '?id=' . $_GET['id'] : ''));
exit;
?>
