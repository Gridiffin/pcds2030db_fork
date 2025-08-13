<?php

use PHPUnit\Framework\TestCase;

/**
 * Admin Core Functions Test
 * Tests for core admin authentication and permission functions
 */
class AdminCoreTest extends TestCase
{
    protected function setUp(): void
    {
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session data
        $_SESSION = [];
        
        // Mock database connection
        global $conn;
        $conn = null;
    }

    protected function tearDown(): void
    {
        // Clear session data
        $_SESSION = [];
    }

    /**
     * Test is_admin function with valid admin session
     */
    public function testIsAdminWithValidAdminSession()
    {
        // Set up admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = is_admin();
        
        $this->assertTrue($result, 'is_admin() should return true for valid admin session');
    }

    /**
     * Test is_admin function with non-admin session
     */
    public function testIsAdminWithNonAdminSession()
    {
        // Set up non-admin session
        $_SESSION['user_id'] = 2;
        $_SESSION['role'] = 'agency';

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = is_admin();
        
        $this->assertFalse($result, 'is_admin() should return false for non-admin session');
    }

    /**
     * Test is_admin function with no session
     */
    public function testIsAdminWithNoSession()
    {
        // Clear session
        $_SESSION = [];

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = is_admin();
        
        $this->assertFalse($result, 'is_admin() should return false when no session exists');
    }

    /**
     * Test is_admin function with missing user_id
     */
    public function testIsAdminWithMissingUserId()
    {
        // Set up session with missing user_id
        $_SESSION['role'] = 'admin';

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = is_admin();
        
        $this->assertFalse($result, 'is_admin() should return false when user_id is missing');
    }

    /**
     * Test is_admin function with missing role
     */
    public function testIsAdminWithMissingRole()
    {
        // Set up session with missing role
        $_SESSION['user_id'] = 1;

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = is_admin();
        
        $this->assertFalse($result, 'is_admin() should return false when role is missing');
    }

    /**
     * Test check_admin_permission function with valid admin
     */
    public function testCheckAdminPermissionWithValidAdmin()
    {
        // Set up admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = check_admin_permission();
        
        $this->assertNull($result, 'check_admin_permission() should return null for valid admin');
    }

    /**
     * Test check_admin_permission function with non-admin user
     */
    public function testCheckAdminPermissionWithNonAdmin()
    {
        // Set up non-admin session
        $_SESSION['user_id'] = 2;
        $_SESSION['role'] = 'agency';

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = check_admin_permission();
        
        $this->assertIsArray($result, 'check_admin_permission() should return error array for non-admin');
        $this->assertArrayHasKey('error', $result, 'Error array should have error key');
        $this->assertEquals('Permission denied', $result['error'], 'Error message should be "Permission denied"');
        $this->assertEquals(403, $result['code'] ?? null, 'Error code should be 403');
    }

    /**
     * Test check_admin_permission function with no session
     */
    public function testCheckAdminPermissionWithNoSession()
    {
        // Clear session
        $_SESSION = [];

        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $result = check_admin_permission();
        
        $this->assertIsArray($result, 'check_admin_permission() should return error array when no session');
        $this->assertArrayHasKey('error', $result, 'Error array should have error key');
        $this->assertEquals('Permission denied', $result['error'], 'Error message should be "Permission denied"');
    }

    /**
     * Test admin session validation with various role types
     */
    public function testAdminSessionValidationWithVariousRoles()
    {
        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $testCases = [
            ['role' => 'admin', 'expected' => true],
            ['role' => 'agency', 'expected' => false],
            ['role' => 'focal', 'expected' => false],
            ['role' => 'user', 'expected' => false],
            ['role' => 'super_admin', 'expected' => false],
            ['role' => '', 'expected' => false],
            ['role' => null, 'expected' => false]
        ];

        foreach ($testCases as $testCase) {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = $testCase['role'];

            $result = is_admin();
            $this->assertEquals(
                $testCase['expected'], 
                $result, 
                "is_admin() should return {$testCase['expected']} for role '{$testCase['role']}'"
            );
        }
    }

    /**
     * Test admin permission check with various session states
     */
    public function testAdminPermissionCheckWithVariousSessionStates()
    {
        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $testCases = [
            [
                'session' => ['user_id' => 1, 'role' => 'admin'],
                'expected' => null,
                'description' => 'valid admin session'
            ],
            [
                'session' => ['user_id' => 2, 'role' => 'agency'],
                'expected' => 'Permission denied',
                'description' => 'agency user session'
            ],
            [
                'session' => ['user_id' => 1],
                'expected' => 'Permission denied',
                'description' => 'missing role'
            ],
            [
                'session' => ['role' => 'admin'],
                'expected' => 'Permission denied',
                'description' => 'missing user_id'
            ],
            [
                'session' => [],
                'expected' => 'Permission denied',
                'description' => 'empty session'
            ]
        ];

        foreach ($testCases as $testCase) {
            $_SESSION = $testCase['session'];

            $result = check_admin_permission();
            
            if ($testCase['expected'] === null) {
                $this->assertNull(
                    $result, 
                    "check_admin_permission() should return null for {$testCase['description']}"
                );
            } else {
                $this->assertIsArray(
                    $result, 
                    "check_admin_permission() should return array for {$testCase['description']}"
                );
                $this->assertEquals(
                    $testCase['expected'], 
                    $result['error'], 
                    "Error message should be '{$testCase['expected']}' for {$testCase['description']}"
                );
            }
        }
    }

    /**
     * Test session persistence across function calls
     */
    public function testSessionPersistenceAcrossFunctionCalls()
    {
        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        // Set up admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        // Call is_admin multiple times
        $result1 = is_admin();
        $result2 = is_admin();
        $result3 = is_admin();

        $this->assertTrue($result1, 'First call should return true');
        $this->assertTrue($result2, 'Second call should return true');
        $this->assertTrue($result3, 'Third call should return true');

        // Change to non-admin
        $_SESSION['role'] = 'agency';

        $result4 = is_admin();
        $this->assertFalse($result4, 'Should return false after role change');
    }

    /**
     * Test admin permission check with session changes
     */
    public function testAdminPermissionCheckWithSessionChanges()
    {
        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        // Start with admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';

        $result1 = check_admin_permission();
        $this->assertNull($result1, 'Should return null for admin session');

        // Change to non-admin
        $_SESSION['role'] = 'agency';

        $result2 = check_admin_permission();
        $this->assertIsArray($result2, 'Should return error array for non-admin session');
        $this->assertEquals('Permission denied', $result2['error']);

        // Remove session
        $_SESSION = [];

        $result3 = check_admin_permission();
        $this->assertIsArray($result3, 'Should return error array for no session');
        $this->assertEquals('Permission denied', $result3['error']);
    }

    /**
     * Test edge cases with invalid session data
     */
    public function testEdgeCasesWithInvalidSessionData()
    {
        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        $testCases = [
            [
                'session' => ['user_id' => 0, 'role' => 'admin'],
                'expected' => true,
                'description' => 'zero user_id with admin role'
            ],
            [
                'session' => ['user_id' => -1, 'role' => 'admin'],
                'expected' => true,
                'description' => 'negative user_id with admin role'
            ],
            [
                'session' => ['user_id' => '1', 'role' => 'admin'],
                'expected' => true,
                'description' => 'string user_id with admin role'
            ],
            [
                'session' => ['user_id' => 1, 'role' => 'ADMIN'],
                'expected' => false,
                'description' => 'uppercase role'
            ],
            [
                'session' => ['user_id' => 1, 'role' => 'Admin'],
                'expected' => false,
                'description' => 'capitalized role'
            ]
        ];

        foreach ($testCases as $testCase) {
            $_SESSION = $testCase['session'];

            $result = is_admin();
            $this->assertEquals(
                $testCase['expected'], 
                $result, 
                "is_admin() should return {$testCase['expected']} for {$testCase['description']}"
            );
        }
    }

    /**
     * Test function availability and callability
     */
    public function testFunctionAvailabilityAndCallability()
    {
        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        // Test that functions exist and are callable
        $this->assertTrue(function_exists('is_admin'), 'is_admin function should exist');
        $this->assertTrue(function_exists('check_admin_permission'), 'check_admin_permission function should exist');
        $this->assertTrue(is_callable('is_admin'), 'is_admin function should be callable');
        $this->assertTrue(is_callable('check_admin_permission'), 'check_admin_permission function should be callable');
    }

    /**
     * Test function return types
     */
    public function testFunctionReturnTypes()
    {
        // Include the core functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/core.php';

        // Test is_admin return type
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        $result = is_admin();
        $this->assertIsBool($result, 'is_admin() should return boolean');

        // Test check_admin_permission return type
        $result = check_admin_permission();
        $this->assertTrue(
            is_null($result) || is_array($result), 
            'check_admin_permission() should return null or array'
        );
    }
} 