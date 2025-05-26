<?php
/**
 * Delete Outcome
 * 
 * Admin page to delete an outcome.
 */


require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php'; // Contains legacy functions
require_once ROOT_PATH . 'app/lib/admins/outcomes.php'; // Contains updated outcome functions
require_once ROOT_PATH . 'app/lib/admins/index.php'; // Contains is_admin

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if outcome_id is provided
if (!isset($_GET['outcome_id']) || !is_numeric($_GET['outcome_id'])) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: ' . APP_URL . '/app/views/admin/outcomes/manage_outcomes.php');
    exit;
}

$outcome_id = (int) $_GET['outcome_id'];

// Verify that the outcome exists using the updated function
$outcome_data = get_outcome_data($outcome_id); // Using the updated function for outcomes

if (!$outcome_data) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: ' . APP_URL . '/app/views/admin/outcomes/manage_outcomes.php');
    exit;
}

// Proceed with deletion (assuming a function like delete_outcome exists or direct DB operation)
// This part needs to be adapted based on how outcomes are actually deleted (e.g., a specific function or direct SQL)
// For now, let's assume a function delete_outcome_record($outcome_id, $conn) exists in functions.php or admins/outcomes.php

// Example: Direct SQL deletion (ensure this is the correct table and logic)
$table_name = $outcome_data['table_name']; // This might be needed if data is in dynamic tables

// Start a transaction
$conn->begin_transaction();

try {
    // 1. Delete from the main sector_outcomes_data table (or equivalent)
    // IMPORTANT: Adjust the table name and conditions as per your actual database schema for outcomes.
    // This is a placeholder query.
    $query_main = "DELETE FROM sector_outcomes_data WHERE outcome_id = ?"; // Assuming 'sector_outcomes_data' and 'outcome_id'
    $stmt_main = $conn->prepare($query_main);
    if (!$stmt_main) {
        throw new Exception("Prepare failed (main): (" . $conn->errno . ") " . $conn->error);
    }
    $stmt_main->bind_param("i", $outcome_id);
    $stmt_main->execute();

    // 2. Potentially delete from a dynamic data table if $table_name is used and relevant
    // if (!empty($table_name) && preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) { // Basic validation for table name
    //     $query_dynamic = "DROP TABLE IF EXISTS " . $table_name; // Or DELETE FROM based on structure
    //     if (!$conn->query($query_dynamic)) {
    //         throw new Exception("Failed to delete dynamic table '{$table_name}': (" . $conn->errno . ") " . $conn->error);
    //     }
    // }

    // If using a specific function, it might look like:
    // if (!delete_outcome_entry($outcome_id, $conn)) { // Assuming such a function handles all deletion logic
    //     throw new Exception("Failed to delete outcome.");
    // }

    $conn->commit();
    $_SESSION['success_message'] = 'Outcome (ID: ' . $outcome_id . ') and associated data deleted successfully.';

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error deleting outcome ID {$outcome_id}: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error deleting outcome: ' . $e->getMessage();
}

// Redirect back to the manage outcomes page
header('Location: ' . APP_URL . '/app/views/admin/outcomes/manage_outcomes.php');
exit;
?>
