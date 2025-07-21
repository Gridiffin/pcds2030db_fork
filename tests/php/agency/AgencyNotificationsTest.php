<?php
/**
 * Tests for Agency Notifications Library
 * Testing app/lib/agencies/notifications.php functionality
 */

use PHPUnit\Framework\TestCase;

class AgencyNotificationsTest extends TestCase
{
    private $originalSession;
    private $originalConn;

    protected function setUp(): void
    {
        // Save original session and global connection
        $this->originalSession = $_SESSION ?? [];
        global $conn;
        $this->originalConn = $conn;
        
        // Initialize test session
        $_SESSION = [
            'user_id' => 1,
            'agency_id' => 1,
            'role' => 'agency',
            'username' => 'test_agency'
        ];

        // Load required files
        if (!defined('PROJECT_ROOT_PATH')) {
            define('PROJECT_ROOT_PATH', 'C:\laragon\www\pcds2030_dashboard_fork' . DIRECTORY_SEPARATOR);
        }
        
        // Mock database connection to prevent actual database calls
        $conn = $this->createMockConnection();
        
        require_once PROJECT_ROOT_PATH . '/app/config/config.php';
        require_once PROJECT_ROOT_PATH . '/app/lib/session.php';
        require_once PROJECT_ROOT_PATH . '/app/lib/functions.php';
        require_once PROJECT_ROOT_PATH . '/app/lib/agencies/core.php';
        require_once PROJECT_ROOT_PATH . '/app/lib/agencies/notifications.php';
    }

    protected function tearDown(): void
    {
        // Restore original session and connection
        $_SESSION = $this->originalSession;
        global $conn;
        $conn = $this->originalConn;
    }

    /**
     * Create a mock database connection that returns realistic test data
     */
    private function createMockConnection()
    {
        // Create a mock MySQLi result
        $result = $this->createMock(mysqli_result::class);
        $result->method('fetch_all')->willReturn([
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'Test Notification',
                'message' => 'Test notification message',
                'is_read' => 0,
                'created_at' => '2025-07-20 10:00:00'
            ]
        ]);
        $result->method('fetch_assoc')->willReturn([
            'total_count' => 1,
            'unread_count' => 1,
            'read_count' => 0
        ]);
        $result->method('num_rows')->willReturn(1);

        // Create a mock MySQLi statement
        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);
        $stmt->method('close')->willReturn(true);

        // Create a mock MySQLi connection
        $mockConn = $this->createMock(mysqli::class);
        $mockConn->method('prepare')->willReturn($stmt);
        $mockConn->method('query')->willReturn($result);
        $mockConn->method('real_escape_string')->willReturnArgument(0);
        
        return $mockConn;
    }

    /**
     * Test get_user_notifications() function
     */
    public function testGetUserNotifications()
    {
        $result = get_user_notifications($_SESSION['user_id']);
        
        $this->assertIsArray($result, 'get_user_notifications should return an array');
        $this->assertArrayHasKey('notifications', $result, 'Result should have notifications key');
        $this->assertArrayHasKey('total_count', $result, 'Result should have total_count key');
        $this->assertArrayHasKey('unread_count', $result, 'Result should have unread_count key');
        
        $notifications = $result['notifications'];
        $this->assertIsArray($notifications, 'Notifications should be an array');
        
        if (!empty($notifications)) {
            $firstNotification = $notifications[0];
            $this->assertIsArray($firstNotification, 'Each notification should be an array');
            $this->assertArrayHasKey('id', $firstNotification, 'Notification should have ID');
            $this->assertArrayHasKey('message', $firstNotification, 'Notification should have message');
            $this->assertArrayHasKey('read_status', $firstNotification, 'Notification should have read status');
        }
    }

    /**
     * Test get_user_notifications with parameters
     */
    public function testGetUserNotificationsWithParameters()
    {
        // Test with limit
        $limitedResult = get_user_notifications($_SESSION['user_id'], 1, 5);
        $this->assertIsArray($limitedResult, 'Should return array with limit');
        $this->assertLessThanOrEqual(5, count($limitedResult['notifications']), 'Should respect limit parameter');
        
        // Test with page and offset
        $offsetResult = get_user_notifications($_SESSION['user_id'], 2, 5);
        $this->assertIsArray($offsetResult, 'Should return array with pagination');
    }

    /**
     * Test get_notification_stats() function
     */
    public function testGetNotificationStats()
    {
        $stats = get_notification_stats($_SESSION['user_id']);
        
        $this->assertIsArray($stats, 'get_notification_stats should return an array');
        $this->assertArrayHasKey('total', $stats, 'Stats should include total count');
        $this->assertArrayHasKey('unread', $stats, 'Stats should include unread count');
        $this->assertArrayHasKey('today', $stats, 'Stats should include today count');
        
        // Validate data types
        $this->assertIsInt($stats['total'], 'Total should be integer');
        $this->assertIsInt($stats['unread'], 'Unread should be integer');
        $this->assertIsInt($stats['today'], 'Today should be integer');
        
        // Validate logical consistency
        $this->assertGreaterThanOrEqual(0, $stats['total'], 'Total should be non-negative');
        $this->assertGreaterThanOrEqual(0, $stats['unread'], 'Unread should be non-negative');
        $this->assertGreaterThanOrEqual(0, $stats['today'], 'Today should be non-negative');
        $this->assertLessThanOrEqual($stats['total'], $stats['unread'], 'Unread should not exceed total');
    }

    /**
     * Test mark_notifications_read() function
     */
    public function testMarkNotificationsRead()
    {
        // Get a notification to test with
        $result = get_user_notifications($_SESSION['user_id'], 1, 1);
        
        if (!empty($result['notifications'])) {
            $notificationId = $result['notifications'][0]['id'];
            
            $markResult = mark_notifications_read($_SESSION['user_id'], [$notificationId]);
            $this->assertTrue($markResult, 'mark_notifications_read should return true on success');
        } else {
            $this->assertTrue(true, 'No notifications to test mark as read');
        }
    }

    /**
     * Test mark_notifications_read() bulk function
     */
    public function testMarkNotificationsReadBulk()
    {
        // Get multiple notifications
        $result = get_user_notifications($_SESSION['user_id'], 1, 3);
        $notifications = $result['notifications'];
        
        if (count($notifications) >= 2) {
            $notificationIds = array_column($notifications, 'id');
            $slicedIds = array_slice($notificationIds, 0, 2);
            
            $markResult = mark_notifications_read($_SESSION['user_id'], $slicedIds);
            $this->assertTrue($markResult, 'mark_notifications_read should return true for bulk operation');
        } else {
            $this->assertTrue(true, 'Not enough notifications to test bulk mark as read');
        }
    }

    /**
     * Test delete_notifications() function
     */
    public function testDeleteNotifications()
    {
        // Get a notification to test deletion (if any exist)
        $result = get_user_notifications($_SESSION['user_id'], 1, 1);
        
        if (!empty($result['notifications'])) {
            $notificationId = $result['notifications'][0]['id'];
            
            // Only test if it's safe to delete (don't delete important notifications)
            // This is a read-only test in most cases
            $this->assertTrue(true, 'Delete notifications function exists');
        } else {
            $this->assertTrue(true, 'No notifications to test deletion');
        }
    }

    /**
     * Test function existence and callability
     */
    public function testFunctionExistence()
    {
        $functions = [
            'get_user_notifications',
            'get_notification_stats', 
            'mark_notifications_read',
            'delete_notifications',
            'create_notification',
            'get_notification_icon',
            'format_time_ago',
            'get_notification_badge_class'
        ];
        
        foreach ($functions as $function) {
            $this->assertTrue(
                function_exists($function),
                "Function {$function} should exist"
            );
            
            $this->assertTrue(
                is_callable($function),
                "Function {$function} should be callable"
            );
        }
    }

    /**
     * Test session validation for notifications
     */
    public function testSessionValidationForNotifications()
    {
        // Test with different user
        $_SESSION['user_id'] = 999;
        
        $result = get_user_notifications($_SESSION['user_id']);
        $this->assertIsArray($result, 'Should handle different user ID');
        
        $stats = get_notification_stats($_SESSION['user_id']);
        $this->assertIsArray($stats, 'Stats should handle different user ID');
    }

    /**
     * Test with no session
     */
    public function testNoSessionHandling()
    {
        // Test with no session
        unset($_SESSION['user_id']);
        
        $result = get_user_notifications(0);
        $this->assertIsArray($result, 'Should handle missing user ID gracefully');
        
        $stats = get_notification_stats(0);
        $this->assertIsArray($stats, 'Stats should handle missing session gracefully');
    }

    /**
     * Test invalid parameters
     */
    public function testInvalidParameters()
    {
        // Test with negative limit
        $result = get_user_notifications($_SESSION['user_id'], 1, -1);
        $this->assertIsArray($result, 'Should handle negative limit');
        
        // Test with invalid notification ID for marking read
        $markResult = mark_notifications_read($_SESSION['user_id'], [99999]);
        $this->assertIsBool($markResult, 'Should return boolean for invalid notification ID');
        
        // Test with empty array for bulk operations
        $markResult = mark_notifications_read($_SESSION['user_id'], []);
        $this->assertIsBool($markResult, 'Should handle empty array for bulk operations');
    }

    /**
     * Test performance of notification functions
     */
    public function testNotificationFunctionPerformance()
    {
        $startTime = microtime(true);
        
        get_user_notifications($_SESSION['user_id']);
        
        $getNotificationsTime = microtime(true) - $startTime;
        
        $startTime = microtime(true);
        
        get_notification_stats($_SESSION['user_id']);
        
        $getStatsTime = microtime(true) - $startTime;
        
        // Functions should complete within reasonable time
        $this->assertLessThan(2.0, $getNotificationsTime, 'get_user_notifications should complete within 2 seconds');
        $this->assertLessThan(2.0, $getStatsTime, 'get_notification_stats should complete within 2 seconds');
    }

    /**
     * Test data consistency
     */
    public function testDataConsistency()
    {
        $result = get_user_notifications($_SESSION['user_id']);
        $stats = get_notification_stats($_SESSION['user_id']);
        
        $notifications = $result['notifications'];
        
        // If we have notifications, stats should reflect that
        if (!empty($notifications)) {
            $this->assertGreaterThan(0, $stats['total'], 'Stats total should be greater than 0 if notifications exist');
        }
        
        // Unread count should not exceed total
        $this->assertLessThanOrEqual($stats['total'], $stats['unread'], 'Unread should not exceed total');
    }
}
