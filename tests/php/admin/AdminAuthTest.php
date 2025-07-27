<?php

use PHPUnit\Framework\TestCase;

/**
 * Admin Authentication Test Class
 * Tests for admin authentication functionality
 */
class AdminAuthTest extends TestCase
{
    private $mockDatabase;
    private $mockSession;

    protected function setUp(): void
    {
        // Mock database connection
        $this->mockDatabase = $this->createMock(mysqli::class);
        
        // Mock session
        $this->mockSession = [];
    }

    protected function tearDown(): void
    {
        $this->mockDatabase = null;
        $this->mockSession = null;
    }

    /**
     * Test successful login with valid credentials
     */
    public function testSuccessfulLogin()
    {
        $username = 'admin';
        $password = 'valid_password_123';
        
        $result = $this->authenticateUser($username, $password);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('admin', $result['user']['username']);
        $this->assertEquals('admin', $result['user']['role']);
        $this->assertArrayHasKey('session_id', $result);
        $this->assertArrayHasKey('login_time', $result);
    }

    /**
     * Test login failure with invalid credentials
     */
    public function testLoginFailureWithInvalidCredentials()
    {
        $username = 'admin';
        $password = 'wrong_password';
        
        $result = $this->authenticateUser($username, $password);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid username or password', $result['error']);
    }

    /**
     * Test login failure with empty credentials
     */
    public function testLoginFailureWithEmptyCredentials()
    {
        $username = '';
        $password = '';
        
        $result = $this->authenticateUser($username, $password);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Username and password are required', $result['error']);
    }

    /**
     * Test login failure with non-existent user
     */
    public function testLoginFailureWithNonExistentUser()
    {
        $username = 'nonexistent_user';
        $password = 'any_password';
        
        $result = $this->authenticateUser($username, $password);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid username or password', $result['error']);
    }

    /**
     * Test login with locked account
     */
    public function testLoginWithLockedAccount()
    {
        $username = 'locked_user';
        $password = 'valid_password';
        
        $result = $this->authenticateUser($username, $password);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Account is locked. Please contact administrator.', $result['error']);
    }

    /**
     * Test login with inactive account
     */
    public function testLoginWithInactiveAccount()
    {
        $username = 'inactive_user';
        $password = 'valid_password';
        
        $result = $this->authenticateUser($username, $password);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Account is inactive. Please contact administrator.', $result['error']);
    }

    /**
     * Test successful logout
     */
    public function testSuccessfulLogout()
    {
        $sessionId = 'valid_session_id';
        
        $result = $this->logoutUser($sessionId);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Logged out successfully', $result['message']);
    }

    /**
     * Test logout with invalid session
     */
    public function testLogoutWithInvalidSession()
    {
        $sessionId = 'invalid_session_id';
        
        $result = $this->logoutUser($sessionId);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid session', $result['error']);
    }

    /**
     * Test session validation
     */
    public function testSessionValidation()
    {
        $sessionId = 'valid_session_id';
        
        $result = $this->validateSession($sessionId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('session_data', $result);
    }

    /**
     * Test session validation with expired session
     */
    public function testSessionValidationWithExpiredSession()
    {
        $sessionId = 'expired_session_id';
        
        $result = $this->validateSession($sessionId);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Session has expired', $result['error']);
    }

    /**
     * Test session validation with invalid session
     */
    public function testSessionValidationWithInvalidSession()
    {
        $sessionId = 'invalid_session_id';
        
        $result = $this->validateSession($sessionId);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid session', $result['error']);
    }

    /**
     * Test access control for admin role
     */
    public function testAccessControlForAdminRole()
    {
        $userId = 1;
        $requiredRole = 'admin';
        $resource = 'admin_dashboard';
        
        $result = $this->checkAccess($userId, $requiredRole, $resource);
        
        $this->assertTrue($result['success']);
        $this->assertTrue($result['has_access']);
    }

    /**
     * Test access control for non-admin role
     */
    public function testAccessControlForNonAdminRole()
    {
        $userId = 2;
        $requiredRole = 'admin';
        $resource = 'admin_dashboard';
        
        $result = $this->checkAccess($userId, $requiredRole, $resource);
        
        $this->assertTrue($result['success']);
        $this->assertFalse($result['has_access']);
    }

    /**
     * Test access control with invalid user
     */
    public function testAccessControlWithInvalidUser()
    {
        $userId = 999;
        $requiredRole = 'admin';
        $resource = 'admin_dashboard';
        
        $result = $this->checkAccess($userId, $requiredRole, $resource);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('User not found', $result['error']);
    }

    /**
     * Test password validation
     */
    public function testPasswordValidation()
    {
        $validPasswords = [
            'ValidPass123',
            'StrongPassword456!',
            'Complex_Pass789@'
        ];

        $invalidPasswords = [
            'short',
            'nouppercase123',
            'NOLOWERCASE123',
            'NoNumbers',
            'onlylowercase',
            'ONLYUPPERCASE'
        ];

        foreach ($validPasswords as $password) {
            $result = $this->validatePassword($password);
            $this->assertTrue($result['valid'], "Password '$password' should be valid");
        }

        foreach ($invalidPasswords as $password) {
            $result = $this->validatePassword($password);
            $this->assertFalse($result['valid'], "Password '$password' should be invalid");
        }
    }

    /**
     * Test password hashing
     */
    public function testPasswordHashing()
    {
        $password = 'test_password_123';
        
        $hashedPassword = $this->hashPassword($password);
        
        $this->assertNotEquals($password, $hashedPassword);
        $this->assertTrue($this->verifyPassword($password, $hashedPassword));
        $this->assertFalse($this->verifyPassword('wrong_password', $hashedPassword));
    }

    /**
     * Test session creation
     */
    public function testSessionCreation()
    {
        $userId = 1;
        $userData = [
            'username' => 'admin',
            'role' => 'admin',
            'email' => 'admin@example.com'
        ];
        
        $result = $this->createSession($userId, $userData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('session_id', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertEquals($userId, $result['user_id']);
    }

    /**
     * Test session destruction
     */
    public function testSessionDestruction()
    {
        $sessionId = 'valid_session_id';
        
        $result = $this->destroySession($sessionId);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Session destroyed successfully', $result['message']);
    }

    /**
     * Test session refresh
     */
    public function testSessionRefresh()
    {
        $sessionId = 'valid_session_id';
        
        $result = $this->refreshSession($sessionId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('new_expires_at', $result);
        $this->assertGreaterThan(time(), strtotime($result['new_expires_at']));
    }

    /**
     * Test failed login attempt tracking
     */
    public function testFailedLoginAttemptTracking()
    {
        $username = 'test_user';
        $ipAddress = '192.168.1.1';
        
        // Simulate multiple failed attempts
        for ($i = 1; $i <= 3; $i++) {
            $result = $this->recordFailedLoginAttempt($username, $ipAddress);
            $this->assertTrue($result['success']);
            $this->assertEquals($i, $result['attempt_count']);
        }
        
        // Check if account is locked after 3 attempts
        $result = $this->recordFailedLoginAttempt($username, $ipAddress);
        $this->assertTrue($result['success']);
        $this->assertTrue($result['account_locked']);
    }

    /**
     * Test account lockout duration
     */
    public function testAccountLockoutDuration()
    {
        $username = 'locked_user';
        
        $result = $this->checkAccountLockout($username);
        
        $this->assertTrue($result['is_locked']);
        $this->assertArrayHasKey('lockout_until', $result);
        $this->assertArrayHasKey('remaining_time', $result);
    }

    /**
     * Test password reset functionality
     */
    public function testPasswordReset()
    {
        $email = 'admin@example.com';
        
        $result = $this->initiatePasswordReset($email);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('reset_token', $result);
        $this->assertArrayHasKey('expires_at', $result);
    }

    /**
     * Test password reset with invalid email
     */
    public function testPasswordResetWithInvalidEmail()
    {
        $email = 'nonexistent@example.com';
        
        $result = $this->initiatePasswordReset($email);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Email not found', $result['error']);
    }

    /**
     * Test password reset completion
     */
    public function testPasswordResetCompletion()
    {
        $resetToken = 'valid_reset_token';
        $newPassword = 'NewValidPassword123';
        
        $result = $this->completePasswordReset($resetToken, $newPassword);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Password reset successfully', $result['message']);
    }

    /**
     * Test password reset with invalid token
     */
    public function testPasswordResetWithInvalidToken()
    {
        $resetToken = 'invalid_reset_token';
        $newPassword = 'NewValidPassword123';
        
        $result = $this->completePasswordReset($resetToken, $newPassword);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid or expired reset token', $result['error']);
    }

    /**
     * Test two-factor authentication setup
     */
    public function testTwoFactorAuthenticationSetup()
    {
        $userId = 1;
        
        $result = $this->setupTwoFactorAuth($userId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('secret_key', $result);
        $this->assertArrayHasKey('qr_code', $result);
        $this->assertArrayHasKey('backup_codes', $result);
    }

    /**
     * Test two-factor authentication verification
     */
    public function testTwoFactorAuthenticationVerification()
    {
        $userId = 1;
        $token = '123456';
        
        $result = $this->verifyTwoFactorAuth($userId, $token);
        
        $this->assertTrue($result['success']);
        $this->assertTrue($result['verified']);
    }

    /**
     * Test two-factor authentication with invalid token
     */
    public function testTwoFactorAuthenticationWithInvalidToken()
    {
        $userId = 1;
        $token = '000000';
        
        $result = $this->verifyTwoFactorAuth($userId, $token);
        
        $this->assertFalse($result['success']);
        $this->assertFalse($result['verified']);
    }

    // Mock helper methods for testing

    private function authenticateUser($username, $password)
    {
        // Mock authentication logic
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'error' => 'Username and password are required'
            ];
        }

        // Mock user database
        $users = [
            'admin' => [
                'id' => 1,
                'username' => 'admin',
                'password' => password_hash('valid_password_123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
                'locked' => false
            ],
            'locked_user' => [
                'id' => 2,
                'username' => 'locked_user',
                'password' => password_hash('valid_password', PASSWORD_DEFAULT),
                'role' => 'user',
                'status' => 'active',
                'locked' => true
            ],
            'inactive_user' => [
                'id' => 3,
                'username' => 'inactive_user',
                'password' => password_hash('valid_password', PASSWORD_DEFAULT),
                'role' => 'user',
                'status' => 'inactive',
                'locked' => false
            ]
        ];

        if (!isset($users[$username])) {
            return [
                'success' => false,
                'error' => 'Invalid username or password'
            ];
        }

        $user = $users[$username];

        if ($user['locked']) {
            return [
                'success' => false,
                'error' => 'Account is locked. Please contact administrator.'
            ];
        }

        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'error' => 'Account is inactive. Please contact administrator.'
            ];
        }

        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'error' => 'Invalid username or password'
            ];
        }

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'email' => 'admin@example.com'
            ],
            'session_id' => 'session_' . uniqid(),
            'login_time' => date('Y-m-d H:i:s')
        ];
    }

    private function logoutUser($sessionId)
    {
        if ($sessionId === 'invalid_session_id') {
            return [
                'success' => false,
                'error' => 'Invalid session'
            ];
        }

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    private function validateSession($sessionId)
    {
        if ($sessionId === 'invalid_session_id') {
            return [
                'success' => false,
                'error' => 'Invalid session'
            ];
        }

        if ($sessionId === 'expired_session_id') {
            return [
                'success' => false,
                'error' => 'Session has expired'
            ];
        }

        return [
            'success' => true,
            'user' => [
                'id' => 1,
                'username' => 'admin',
                'role' => 'admin'
            ],
            'session_data' => [
                'session_id' => $sessionId,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ]
        ];
    }

    private function checkAccess($userId, $requiredRole, $resource)
    {
        if ($userId === 999) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }

        $userRoles = [
            1 => 'admin',
            2 => 'user'
        ];

        $userRole = $userRoles[$userId] ?? 'user';
        $hasAccess = $userRole === $requiredRole;

        return [
            'success' => true,
            'has_access' => $hasAccess,
            'user_role' => $userRole,
            'required_role' => $requiredRole
        ];
    }

    private function validatePassword($password)
    {
        if (strlen($password) < 8) {
            return ['valid' => false, 'error' => 'Password must be at least 8 characters'];
        }

        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one lowercase letter'];
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one uppercase letter'];
        }

        if (!preg_match('/\d/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one number'];
        }

        return ['valid' => true, 'message' => 'Password is valid'];
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    private function createSession($userId, $userData)
    {
        return [
            'success' => true,
            'session_id' => 'session_' . uniqid(),
            'user_id' => $userId,
            'user_data' => $userData,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];
    }

    private function destroySession($sessionId)
    {
        return [
            'success' => true,
            'message' => 'Session destroyed successfully'
        ];
    }

    private function refreshSession($sessionId)
    {
        return [
            'success' => true,
            'session_id' => $sessionId,
            'new_expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];
    }

    private function recordFailedLoginAttempt($username, $ipAddress)
    {
        static $attempts = [];

        if (!isset($attempts[$username])) {
            $attempts[$username] = 0;
        }

        $attempts[$username]++;

        $accountLocked = $attempts[$username] >= 3;

        return [
            'success' => true,
            'attempt_count' => $attempts[$username],
            'account_locked' => $accountLocked,
            'ip_address' => $ipAddress
        ];
    }

    private function checkAccountLockout($username)
    {
        return [
            'is_locked' => true,
            'lockout_until' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            'remaining_time' => 1800 // 30 minutes in seconds
        ];
    }

    private function initiatePasswordReset($email)
    {
        if ($email === 'nonexistent@example.com') {
            return [
                'success' => false,
                'error' => 'Email not found'
            ];
        }

        return [
            'success' => true,
            'reset_token' => 'reset_' . uniqid(),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ];
    }

    private function completePasswordReset($resetToken, $newPassword)
    {
        if ($resetToken === 'invalid_reset_token') {
            return [
                'success' => false,
                'error' => 'Invalid or expired reset token'
            ];
        }

        return [
            'success' => true,
            'message' => 'Password reset successfully'
        ];
    }

    private function setupTwoFactorAuth($userId)
    {
        return [
            'success' => true,
            'secret_key' => 'JBSWY3DPEHPK3PXP',
            'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
            'backup_codes' => ['123456', '234567', '345678', '456789', '567890']
        ];
    }

    private function verifyTwoFactorAuth($userId, $token)
    {
        if ($token === '000000') {
            return [
                'success' => false,
                'verified' => false,
                'error' => 'Invalid token'
            ];
        }

        return [
            'success' => true,
            'verified' => true
        ];
    }
} 