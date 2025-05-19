<?php
/**
 * Get Sectors API
 * 
 * Returns all sectors
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

try {
    // Get all sectors
    $sectors_query = "SELECT * FROM sectors ORDER BY sector_name";
    $sectors_result = $conn->query($sectors_query);
    
    $sectors = [];
    while ($sector = $sectors_result->fetch_assoc()) {
        $sectors[] = $sector;
    }
    
    // Return the sectors
    ob_end_clean(); // Clear any buffered output
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'sectors' => $sectors]);
    
} catch (Exception $e) {
    // Handle any errors
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
