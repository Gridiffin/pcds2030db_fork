<?php

/**
 * Simple test that doesn't try to create programs in the database
 */
function test_program_creation_simple() {
    $_SESSION['user_id'] = 1;
    
    try {
        // Just test creating the notification directly
        $test_message = "New program 'Test Program' created by Test User - " . time();
        $result = create_notification(1, $test_message, 'program_created', '/test-program');
        
        if (!$result) {
            return "Failed to create program creation notification";
        }
        
        return true;
        
    } catch (Exception $e) {
        return "Test error: " . $e->getMessage();
    }
}

/**
 * Simple system-wide notification test
 */
function test_system_wide_simple() {
    $_SESSION['user_id'] = 1;
    
    try {
        // Test creating a system notification directly
        $test_message = "Test system-wide notification - " . time();
        $result = create_notification(1, $test_message, 'system', '/test-url');
        
        if (!$result) {
            return "Failed to create system notification";
        }
        
        return true;
        
    } catch (Exception $e) {
        return "Test error: " . $e->getMessage();
    }
}

?>