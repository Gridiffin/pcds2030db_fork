<?php

use PHPUnit\Framework\TestCase;

/**
 * Admin Users Management Test
 * Tests for admin user management functions
 */
class AdminUsersTest extends TestCase
{
    protected $mockConn;

    protected function setUp(): void
    {
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session data
        $_SESSION = [];
        
        // Set up admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        
        // Mock database connection
        $this->mockConn = $this->createMock(mysqli::class);
        global $conn;
        $conn = $this->mockConn;
    }

    protected function tearDown(): void
    {
        // Clear session data
        $_SESSION = [];
    }

    /**
     * Test get_all_agencies function
     */
    public function testGetAllAgencies()
    {
        // Mock database result
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(
                ['agency_id' => 1, 'agency_name' => 'Agency 1'],
                ['agency_id' => 2, 'agency_name' => 'Agency 2'],
                false
            );

        $this->mockConn->method('query')
            ->with($this->stringContains("SELECT"))
            ->willReturn($mockResult);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $result = get_all_agencies($this->mockConn);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Agency 1', $result[0]['agency_name']);
        $this->assertEquals('Agency 2', $result[1]['agency_name']);
    }

    /**
     * Test get_all_users function
     */
    public function testGetAllUsers()
    {
        // Mock database result
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'user_id' => 1,
                    'username' => 'admin1',
                    'role' => 'admin',
                    'fullname' => 'Admin User',
                    'email' => 'admin@example.com',
                    'agency_name' => null
                ],
                [
                    'user_id' => 2,
                    'username' => 'agency1',
                    'role' => 'agency',
                    'fullname' => 'Agency User',
                    'email' => 'agency@example.com',
                    'agency_name' => 'Test Agency'
                ],
                false
            );

        $this->mockConn->method('query')
            ->with($this->stringContains("SELECT u.*, a.agency_name"))
            ->willReturn($mockResult);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $result = get_all_users();
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('admin1', $result[0]['username']);
        $this->assertEquals('agency1', $result[1]['username']);
    }

    /**
     * Test add_user function with valid data
     */
    public function testAddUserWithValidData()
    {
        // Mock prepared statement for username check
        $mockStmt1 = $this->createMock(mysqli_stmt::class);
        $mockStmt1->method('bind_param')->willReturn(true);
        $mockStmt1->method('execute')->willReturn(true);
        
        $mockResult1 = $this->createMock(mysqli_result::class);
        $mockStmt1->method('get_result')->willReturn($mockResult1);

        // Mock prepared statement for agency check
        $mockStmt2 = $this->createMock(mysqli_stmt::class);
        $mockStmt2->method('bind_param')->willReturn(true);
        $mockStmt2->method('execute')->willReturn(true);
        
        $mockResult2 = $this->createMock(mysqli_result::class);
        $mockStmt2->method('get_result')->willReturn($mockResult2);

        // Mock prepared statement for email check
        $mockStmt3 = $this->createMock(mysqli_stmt::class);
        $mockStmt3->method('bind_param')->willReturn(true);
        $mockStmt3->method('execute')->willReturn(true);
        
        $mockResult3 = $this->createMock(mysqli_result::class);
        $mockStmt3->method('get_result')->willReturn($mockResult3);

        // Mock prepared statement for insert
        $mockStmt4 = $this->createMock(mysqli_stmt::class);
        $mockStmt4->method('bind_param')->willReturn(true);
        $mockStmt4->method('execute')->willReturn(true);
        $mockStmt4->method('insert_id')->willReturn(123);

        // Mock transaction methods
        $this->mockConn->method('begin_transaction')->willReturn(true);
        $this->mockConn->method('rollback')->willReturn(true);

        // Configure prepare to return different statements based on query
        $this->mockConn->method('prepare')
            ->willReturnMap([
                ["SELECT user_id FROM users WHERE username = ?", $mockStmt1],
                ["SELECT agency_id FROM agency WHERE agency_id = ?", $mockStmt2],
                ["SELECT user_id FROM users WHERE email = ?", $mockStmt3],
                ["INSERT INTO users", $mockStmt4]
            ]);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $userData = [
            'username' => 'newuser',
            'role' => 'admin',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'fullname' => 'New User',
            'email' => 'newuser@example.com'
        ];

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Test add_user function with missing required fields
     */
    public function testAddUserWithMissingRequiredFields()
    {
        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $userData = [
            'username' => '',
            'role' => 'admin',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'fullname' => '',
            'email' => 'newuser@example.com'
        ];

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('required', $result['error']);
    }

    /**
     * Test add_user function with existing username
     */
    public function testAddUserWithExistingUsername()
    {
        // Mock prepared statement for username check
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockStmt->method('get_result')->willReturn($mockResult);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $userData = [
            'username' => 'existinguser',
            'role' => 'admin',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'fullname' => 'Existing User',
            'email' => 'existing@example.com'
        ];

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('already exists', $result['error']);
    }

    /**
     * Test add_user function with agency role requiring agency_id
     */
    public function testAddUserWithAgencyRoleRequiringAgencyId()
    {
        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $userData = [
            'username' => 'agencyuser',
            'role' => 'agency',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'fullname' => 'Agency User',
            'email' => 'agency@example.com'
            // Missing agency_id
        ];

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Agency id', $result['error']);
    }

    /**
     * Test update_user function with valid data
     */
    public function testUpdateUserWithValidData()
    {
        // Mock prepared statement for user existence check
        $mockStmt1 = $this->createMock(mysqli_stmt::class);
        $mockStmt1->method('bind_param')->willReturn(true);
        $mockStmt1->method('execute')->willReturn(true);
        
        $mockResult1 = $this->createMock(mysqli_result::class);
        $mockStmt1->method('get_result')->willReturn($mockResult1);

        // Mock prepared statement for update
        $mockStmt2 = $this->createMock(mysqli_stmt::class);
        $mockStmt2->method('bind_param')->willReturn(true);
        $mockStmt2->method('execute')->willReturn(true);

        $this->mockConn->method('prepare')
            ->willReturnMap([
                ["SELECT user_id FROM users WHERE user_id = ?", $mockStmt1],
                ["UPDATE users", $mockStmt2]
            ]);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $userData = [
            'user_id' => 1,
            'username' => 'updateduser',
            'role' => 'admin',
            'fullname' => 'Updated User',
            'email' => 'updated@example.com'
        ];

        $result = update_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Test delete_user function
     */
    public function testDeleteUser()
    {
        // Mock prepared statement
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $result = delete_user(1);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Test delete_user function with non-existent user
     */
    public function testDeleteUserWithNonExistentUser()
    {
        // Mock prepared statement
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $result = delete_user(999);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('not found', $result['error']);
    }

    /**
     * Test get_user_by_id function
     */
    public function testGetUserById()
    {
        // Mock prepared statement
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')
            ->willReturn([
                'user_id' => 1,
                'username' => 'testuser',
                'role' => 'admin',
                'fullname' => 'Test User',
                'email' => 'test@example.com'
            ]);
        $mockStmt->method('get_result')->willReturn($mockResult);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';
        require_once PROJECT_ROOT_PATH . '/app/lib/user_functions.php'; // For get_user_by_id function

        $result = get_user_by_id($this->mockConn, 1);
        
        $this->assertIsArray($result);
        $this->assertEquals('testuser', $result['username']);
        $this->assertEquals('admin', $result['role']);
    }

    /**
     * Test get_user_by_id function with non-existent user
     */
    public function testGetUserByIdWithNonExistentUser()
    {
        // Mock prepared statement
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')->willReturn(false);
        $mockStmt->method('get_result')->willReturn($mockResult);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';
        require_once PROJECT_ROOT_PATH . '/app/lib/user_functions.php'; // For get_user_by_id function

        $result = get_user_by_id($this->mockConn, 999);
        
        $this->assertNull($result);
    }

    /**
     * Test password validation
     */
    public function testPasswordValidation()
    {
        // Mock prepared statement for username check
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockStmt->method('get_result')->willReturn($mockResult);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        // Test short password
        $userData = [
            'username' => 'testuser',
            'role' => 'admin',
            'password' => '123',
            'confirm_password' => '123',
            'fullname' => 'Test User',
            'email' => 'test@example.com'
        ];

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('8 characters', $result['error']);

        // Test mismatched passwords
        $userData['password'] = 'password123';
        $userData['confirm_password'] = 'different123';

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('do not match', $result['error']);
    }

    /**
     * Test email validation
     */
    public function testEmailValidation()
    {
        // Mock prepared statement for username check
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockStmt->method('get_result')->willReturn($mockResult);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        // Test invalid email format
        $userData = [
            'username' => 'testuser',
            'role' => 'admin',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'fullname' => 'Test User',
            'email' => 'invalid-email'
        ];

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Invalid email format', $result['error']);
    }

    /**
     * Test role-based field validation
     */
    public function testRoleBasedFieldValidation()
    {
        // Mock prepared statement for username check
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockStmt->method('get_result')->willReturn($mockResult);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        // Test agency role without agency_id
        $userData = [
            'username' => 'agencyuser',
            'role' => 'agency',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'fullname' => 'Agency User',
            'email' => 'agency@example.com'
            // Missing agency_id
        ];

        $result = add_user($userData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Agency id', $result['error']);
    }

    /**
     * Test function availability and callability
     */
    public function testFunctionAvailabilityAndCallability()
    {
        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $this->assertTrue(function_exists('get_all_agencies'), 'get_all_agencies function should exist');
        $this->assertTrue(function_exists('get_all_users'), 'get_all_users function should exist');
        $this->assertTrue(function_exists('add_user'), 'add_user function should exist');
        $this->assertTrue(function_exists('update_user'), 'update_user function should exist');
        $this->assertTrue(function_exists('delete_user'), 'delete_user function should exist');
        $this->assertTrue(function_exists('get_user_by_id'), 'get_user_by_id function should exist');
    }

    /**
     * Test function return types
     */
    public function testFunctionReturnTypes()
    {
        // Mock prepared statement
        $mockStmt = $this->createMock(mysqli_stmt::class);
        $mockStmt->method('bind_param')->willReturn(true);
        $mockStmt->method('execute')->willReturn(true);
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockStmt->method('get_result')->willReturn($mockResult);

        $this->mockConn->method('prepare')
            ->willReturn($mockStmt);

        // Include the users functions
        require_once PROJECT_ROOT_PATH . '/app/lib/admins/users.php';

        $this->assertIsArray(get_all_agencies($this->mockConn), 'get_all_agencies should return array');
        $this->assertIsArray(get_all_users(), 'get_all_users should return array');
        $this->assertIsArray(add_user([]), 'add_user should return array');
        $this->assertIsArray(update_user([]), 'update_user should return array');
        $this->assertIsArray(delete_user(1), 'delete_user should return array');
    }
} 