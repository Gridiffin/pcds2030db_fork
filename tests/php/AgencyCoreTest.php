<?php

use PHPUnit\Framework\TestCase;

/**
 * Agency Core Functions Test
 * 
 * Tests the core agency functionality including session management
 * and permission checking.
 */
class AgencyCoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset session data before each test
        $_SESSION = [];
        
        // Mock some basic session data
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'test_user';
        $_SESSION['role'] = 'agency';
        $_SESSION['agency_id'] = 2;
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $_SESSION = [];
        parent::tearDown();
    }

    public function testIsLoggedInWithValidSession()
    {
        // Test that is_logged_in returns true with valid session
        $result = is_logged_in();
        $this->assertTrue($result, 'User should be logged in with valid session');
    }

    public function testIsLoggedInWithoutSession()
    {
        // Clear session
        $_SESSION = [];
        
        $result = is_logged_in();
        $this->assertFalse($result, 'User should not be logged in without session');
    }

    public function testIsAgencyWithAgencyRole()
    {
        $_SESSION['role'] = 'agency';
        
        $result = is_agency();
        $this->assertTrue($result, 'Should return true for agency role');
    }

    public function testIsAgencyWithAdminRole()
    {
        $_SESSION['role'] = 'admin';
        
        $result = is_agency();
        $this->assertFalse($result, 'Should return false for admin role');
    }

    public function testIsAgencyWithFocalRole()
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'focal';
        
        $result = is_agency();
        $this->assertTrue($result, 'Should return true for focal role (focal users are considered agencies)');
    }

    public function testGetAgencyIdWithValidSession()
    {
        $_SESSION['agency_id'] = 3;
        
        $result = get_agency_id();
        $this->assertEquals(3, $result, 'Should return correct agency ID');
    }

    public function testGetAgencyIdWithoutAgencyId()
    {
        unset($_SESSION['agency_id']);
        
        $result = get_agency_id();
        $this->assertNull($result, 'Should return null when agency_id is not set');
    }

    public function testIsAdminWithAdminRole()
    {
        $_SESSION['role'] = 'admin';
        
        $result = is_admin();
        $this->assertTrue($result, 'Should return true for admin role');
    }

    public function testIsAdminWithNonAdminRole()
    {
        $_SESSION['role'] = 'agency';
        
        $result = is_admin();
        $this->assertFalse($result, 'Should return false for non-admin role');
    }

    public function testIsFocalWithFocalRole()
    {
        $_SESSION['role'] = 'focal';
        
        $result = is_focal_user();
        $this->assertTrue($result, 'Should return true for focal role');
    }

    public function testIsFocalWithNonFocalRole()
    {
        $_SESSION['role'] = 'agency';
        
        $result = is_focal_user();
        $this->assertFalse($result, 'Should return false for non-focal role');
    }

    /**
     * Test session validation with various scenarios
     */
    public function testSessionValidationWithMissingUsername()
    {
        unset($_SESSION['user_id']);
        
        $result = is_logged_in();
        $this->assertFalse($result, 'Should return false when user_id is missing');
    }

    public function testSessionValidationWithMissingRole()
    {
        $_SESSION['user_id'] = 1;
        unset($_SESSION['role']);
        
        $result = is_logged_in();
        $this->assertTrue($result, 'Should return true when user_id exists even if role is missing');
    }

    /**
     * Test edge cases and error handling
     */
    public function testAgencyFunctionsWithEmptySession()
    {
        $_SESSION = [];
        
        $this->assertFalse(is_logged_in());
        $this->assertFalse(is_agency());
        $this->assertFalse(is_admin());
        $this->assertFalse(is_focal_user());
        $this->assertNull(get_agency_id());
    }

    /**
     * Test that functions handle null/undefined values gracefully
     */
    public function testFunctionsWithNullValues()
    {
        $_SESSION['role'] = null;
        $_SESSION['agency_id'] = null;
        
        $this->assertFalse(is_agency());
        $this->assertFalse(is_admin());
        $this->assertFalse(is_focal_user());
        $this->assertNull(get_agency_id());
    }
}
