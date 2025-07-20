<?php
/**
 * Tests for Agency Reports Library
 * Testing app/lib/agencies/reports.php functionality with database mocking
 */

use PHPUnit\Framework\TestCase;

class AgencyReportsTest extends TestCase
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
        require_once PROJECT_ROOT_PATH . '/app/lib/agencies/reports.php';
    }

    protected function tearDown(): void
    {
        // Restore original session and connection
        $_SESSION = $this->originalSession;
        global $conn;
        $conn = $this->originalConn;
    }

    /**
     * Create a mock database connection that returns empty arrays
     */
    private function createMockConnection()
    {
        // Create a simple mock object that doesn't use MySQLi classes
        // This prevents actual database calls while allowing functions to complete
        $mockConn = new stdClass();
        $mockConn->connect_error = null;
        
        return $mockConn;
    }

    /**
     * Test get_agency_reports() function
     */
    public function testGetAgencyReports()
    {
        $reports = get_agency_reports();
        
        $this->assertIsArray($reports, 'get_agency_reports should return an array');
        
        if (!empty($reports)) {
            $firstReport = $reports[0];
            $this->assertIsArray($firstReport, 'Each report should be an array');
            $this->assertArrayHasKey('id', $firstReport, 'Report should have ID');
            $this->assertArrayHasKey('title', $firstReport, 'Report should have title');
        }
    }

    /**
     * Test get_agency_public_reports() function
     */
    public function testGetAgencyPublicReports()
    {
        $publicReports = get_agency_public_reports();
        
        $this->assertIsArray($publicReports, 'get_agency_public_reports should return an array');
        
        if (!empty($publicReports)) {
            $firstReport = $publicReports[0];
            $this->assertIsArray($firstReport, 'Each public report should be an array');
            $this->assertArrayHasKey('id', $firstReport, 'Public report should have ID');
            $this->assertArrayHasKey('title', $firstReport, 'Public report should have title');
        }
    }

    /**
     * Test get_agency_reports with different agency
     */
    public function testGetAgencyReportsWithDifferentAgency()
    {
        // Test with different agency ID
        $_SESSION['agency_id'] = 2;
        
        $reports = get_agency_reports();
        $this->assertIsArray($reports, 'Should handle different agency IDs');
    }

    /**
     * Test get_agency_public_reports accessibility
     */
    public function testPublicReportsAccessibility()
    {
        // Test that public reports don't require specific agency access
        unset($_SESSION['agency_id']);
        
        $publicReports = get_agency_public_reports();
        $this->assertIsArray($publicReports, 'Public reports should be accessible without agency ID');
    }

    /**
     * Test session validation for reports
     */
    public function testSessionValidationForReports()
    {
        // Test with no session
        $_SESSION = [];
        
        $reports = get_agency_reports();
        $this->assertIsArray($reports, 'Should handle missing session gracefully');
    }

    /**
     * Test database connection handling
     */
    public function testDatabaseConnectionHandling()
    {
        // This tests that functions handle database errors gracefully
        $reports = get_agency_reports();
        $this->assertIsArray($reports, 'Should return array even if database issues occur');
        
        $publicReports = get_agency_public_reports();
        $this->assertIsArray($publicReports, 'Public reports should handle database issues');
    }

    /**
     * Test function existence and callability - This should work without database
     */
    public function testFunctionExistence()
    {
        // Set up minimal environment
        if (!defined('PROJECT_ROOT_PATH')) {
            define('PROJECT_ROOT_PATH', 'C:\laragon\www\pcds2030_dashboard_fork' . DIRECTORY_SEPARATOR);
        }
        
        // Initialize session
        $_SESSION = [
            'user_id' => 1,
            'agency_id' => 1,
            'role' => 'agency',
            'username' => 'test_agency'
        ];

        // Load the reports file
        require_once PROJECT_ROOT_PATH . 'app/lib/agencies/reports.php';
        
        $this->assertTrue(
            function_exists('get_agency_reports'),
            'get_agency_reports function should exist'
        );
        
        $this->assertTrue(
            function_exists('get_agency_public_reports'),
            'get_agency_public_reports function should exist'
        );
        
        $this->assertTrue(
            is_callable('get_agency_reports'),
            'get_agency_reports should be callable'
        );
        
        $this->assertTrue(
            is_callable('get_agency_public_reports'),
            'get_agency_public_reports should be callable'
        );
    }

    /**
     * Test data structure consistency
     */
    public function testDataStructureConsistency()
    {
        $reports = get_agency_reports();
        $publicReports = get_agency_public_reports();
        
        // Both should return arrays
        $this->assertIsArray($reports, 'Agency reports should be array');
        $this->assertIsArray($publicReports, 'Public reports should be array');
        
        // Test structure consistency if data exists
        if (!empty($reports) && !empty($publicReports)) {
            $reportKeys = array_keys($reports[0]);
            $publicReportKeys = array_keys($publicReports[0]);
            
            // Both should have similar basic structure
            $this->assertContains('id', $reportKeys, 'Reports should have ID field');
            $this->assertContains('id', $publicReportKeys, 'Public reports should have ID field');
        }
    }

    /**
     * Test performance of report functions
     */
    public function testReportFunctionPerformance()
    {
        $startTime = microtime(true);
        
        get_agency_reports();
        
        $agencyReportsTime = microtime(true) - $startTime;
        
        $startTime = microtime(true);
        
        get_agency_public_reports();
        
        $publicReportsTime = microtime(true) - $startTime;
        
        // Both functions should complete within reasonable time (2 seconds)
        $this->assertLessThan(2.0, $agencyReportsTime, 'Agency reports should load within 2 seconds');
        $this->assertLessThan(2.0, $publicReportsTime, 'Public reports should load within 2 seconds');
    }

    /**
     * Test error handling
     */
    public function testErrorHandling()
    {
        // Test with invalid session data
        $_SESSION['agency_id'] = 'invalid';
        
        $reports = get_agency_reports();
        $this->assertIsArray($reports, 'Should handle invalid agency ID gracefully');
        
        // Test with null agency ID
        $_SESSION['agency_id'] = null;
        
        $reports = get_agency_reports();
        $this->assertIsArray($reports, 'Should handle null agency ID gracefully');
    }
}
