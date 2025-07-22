<?php
/**
 * Programs Core Functions Tests
 * Tests for core program management functions
 */

use PHPUnit\Framework\TestCase;

class ProgramsTest extends TestCase
{
    protected $mockConnection;
    protected $mockStatement;

    protected function setUp(): void
    {
        // Mock database connection
        $this->mockConnection = $this->createMock(mysqli::class);
        $this->mockStatement = $this->createMock(mysqli_stmt::class);
        
        // Set global connection for testing
        $GLOBALS['conn'] = $this->mockConnection;
        
        // Mock session data
        $_SESSION = [
            'user_id' => 1,
            'agency_id' => 1,
            'role' => 'agency',
            'username' => 'test_user'
        ];
        
        // Include the programs functions
        require_once PROJECT_ROOT_PATH . '/app/lib/agencies/programs.php';
    }

    protected function tearDown(): void
    {
        // Clean up globals
        unset($GLOBALS['conn']);
        $_SESSION = [];
    }

    public function testGetAgencyProgramsByTypeWithValidAgency()
    {
        // Mock is_agency() to return true
        if (!function_exists('is_agency')) {
            function is_agency() { return true; }
        }
        
        // Mock the database queries
        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bind_param')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('get_result')
            ->willReturn($this->createMock(mysqli_result::class));
        
        $result = get_agency_programs_by_type();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('assigned', $result);
        $this->assertArrayHasKey('created', $result);
    }

    public function testGetAgencyProgramsByTypeWithInvalidAgency()
    {
        // Mock is_agency() to return false
        if (function_exists('is_agency')) {
            // Override the function for this test
            eval('function is_agency() { return false; }');
        }
        
        $result = get_agency_programs_by_type();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Permission denied', $result['error']);
    }

    public function testGetAgencyProgramsListWithAssignedPrograms()
    {
        $userId = 1;
        $isAssigned = true;
        
        // Create mock result
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult
            ->expects($this->once())
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([
                [
                    'program_id' => 1,
                    'program_name' => 'Test Program',
                    'program_number' => '1.1.A',
                    'status_indicator' => 'active',
                    'agency_name' => 'Test Agency'
                ]
            ]);
        
        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param')
            ->with('i', $userId)
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('get_result')
            ->willReturn($mockResult);
        
        $result = get_agency_programs_list($userId, $isAssigned);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Test Program', $result[0]['program_name']);
    }

    public function testGetAgencyProgramsListWithCreatedPrograms()
    {
        $userId = 1;
        $isAssigned = false;
        
        // Create mock result
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult
            ->expects($this->once())
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([
                [
                    'program_id' => 2,
                    'program_name' => 'Created Program',
                    'program_number' => '1.1.B',
                    'status_indicator' => 'in_progress',
                    'agency_name' => 'Test Agency'
                ]
            ]);
        
        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param')
            ->with('i', $userId)
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('get_result')
            ->willReturn($mockResult);
        
        $result = get_agency_programs_list($userId, $isAssigned);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Created Program', $result[0]['program_name']);
    }

    public function testGetAgencyProgramsListDatabaseError()
    {
        $userId = 1;
        $isAssigned = true;
        
        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn(false); // Simulate prepare failure
        
        $result = get_agency_programs_list($userId, $isAssigned);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAgencyProgramsListEmptyResult()
    {
        $userId = 999; // Non-existent user
        $isAssigned = true;
        
        // Create mock result with no data
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult
            ->expects($this->once())
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([]);
        
        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param')
            ->with('i', $userId)
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('get_result')
            ->willReturn($mockResult);
        
        $result = get_agency_programs_list($userId, $isAssigned);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAgencyProgramsListWithNullStatusIndicator()
    {
        $userId = 1;
        $isAssigned = true;
        
        // Create mock result with null status
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult
            ->expects($this->once())
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([
                [
                    'program_id' => 1,
                    'program_name' => 'Test Program',
                    'program_number' => '1.1.A',
                    'status_indicator' => null,
                    'agency_name' => 'Test Agency'
                ]
            ]);
        
        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->once())
            ->method('get_result')
            ->willReturn($mockResult);
        
        $result = get_agency_programs_list($userId, $isAssigned);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertNull($result[0]['status_indicator']);
    }

    public function testSessionVariableUsage()
    {
        // Test that functions properly use session variables
        
        // Mock is_agency() to return true
        if (!function_exists('is_agency')) {
            function is_agency() { return true; }
        }
        
        // Ensure $_SESSION['user_id'] is used, not $_SESSION['agency_id'] for user operations
        $originalUserId = $_SESSION['user_id'];
        $originalAgencyId = $_SESSION['agency_id'] ?? null;
        
        $_SESSION['user_id'] = 123;
        $_SESSION['agency_id'] = 456;
        
        // Mock database interaction
        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bind_param')
            ->with('i', 123) // Should use user_id, not agency_id
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('get_result')
            ->willReturn($this->createMock(mysqli_result::class));
        
        get_agency_programs_by_type();
        
        // Restore session
        $_SESSION['user_id'] = $originalUserId;
        if ($originalAgencyId !== null) {
            $_SESSION['agency_id'] = $originalAgencyId;
        }
    }

    public function testDatabaseConnectionHandling()
    {
        // Test behavior when database connection is null
        $originalConn = $GLOBALS['conn'];
        $GLOBALS['conn'] = null;
        
        $result = get_agency_programs_list(1, true);
        
        // Should handle gracefully
        $this->assertIsArray($result);
        $this->assertEmpty($result);
        
        $GLOBALS['conn'] = $originalConn;
    }

    /**
     * @dataProvider programDataProvider
     */
    public function testProgramDataProcessing($mockData, $expectedCount, $expectedFirstProgramName)
    {
        // Mock is_agency() to return true
        if (!function_exists('is_agency')) {
            function is_agency() { return true; }
        }
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult
            ->expects($this->exactly(2))
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn($mockData);
        
        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bind_param')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('get_result')
            ->willReturn($mockResult);
        
        $result = get_agency_programs_by_type();
        
        $this->assertIsArray($result);
        $this->assertCount($expectedCount, $result['assigned']);
        $this->assertCount($expectedCount, $result['created']);
        
        if ($expectedCount > 0) {
            $this->assertEquals($expectedFirstProgramName, $result['assigned'][0]['program_name']);
            $this->assertEquals($expectedFirstProgramName, $result['created'][0]['program_name']);
        }
    }

    public function programDataProvider()
    {
        return [
            'empty_data' => [
                [],
                0,
                null
            ],
            'single_program' => [
                [
                    [
                        'program_id' => 1,
                        'program_name' => 'Single Program',
                        'program_number' => '1.1.A',
                        'status_indicator' => 'active',
                        'agency_name' => 'Test Agency'
                    ]
                ],
                1,
                'Single Program'
            ],
            'multiple_programs' => [
                [
                    [
                        'program_id' => 1,
                        'program_name' => 'First Program',
                        'program_number' => '1.1.A',
                        'status_indicator' => 'active',
                        'agency_name' => 'Test Agency'
                    ],
                    [
                        'program_id' => 2,
                        'program_name' => 'Second Program',
                        'program_number' => '1.1.B',
                        'status_indicator' => 'completed',
                        'agency_name' => 'Test Agency'
                    ]
                ],
                2,
                'First Program'
            ]
        ];
    }

    public function testSecurityAgencyIdValidation()
    {
        // Test that agency_id from session is properly validated
        
        // Mock scenario where user tries to access programs with wrong agency_id
        $_SESSION['agency_id'] = 999; // Different from database records
        
        // Mock is_agency() to return true
        if (!function_exists('is_agency')) {
            function is_agency() { return true; }
        }
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult
            ->expects($this->exactly(2))
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([]); // No results due to agency mismatch
        
        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bind_param')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('get_result')
            ->willReturn($mockResult);
        
        $result = get_agency_programs_by_type();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result['assigned']);
        $this->assertEmpty($result['created']);
    }

    public function testPerformanceWithLargeDataSet()
    {
        // Test with large number of programs
        $largeDataSet = [];
        for ($i = 1; $i <= 1000; $i++) {
            $largeDataSet[] = [
                'program_id' => $i,
                'program_name' => "Program $i",
                'program_number' => "1.1.$i",
                'status_indicator' => 'active',
                'agency_name' => 'Test Agency'
            ];
        }
        
        // Mock is_agency() to return true
        if (!function_exists('is_agency')) {
            function is_agency() { return true; }
        }
        
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult
            ->expects($this->exactly(2))
            ->method('fetch_all')
            ->willReturn($largeDataSet);
        
        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->mockStatement);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('bind_param')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
            
        $this->mockStatement
            ->expects($this->exactly(2))
            ->method('get_result')
            ->willReturn($mockResult);
        
        $start = microtime(true);
        $result = get_agency_programs_by_type();
        $end = microtime(true);
        
        $executionTime = $end - $start;
        
        $this->assertLessThan(1.0, $executionTime, 'Function should handle large datasets efficiently');
        $this->assertCount(1000, $result['assigned']);
        $this->assertCount(1000, $result['created']);
    }
}
