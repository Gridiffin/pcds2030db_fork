<?php

use PHPUnit\Framework\TestCase;

/**
 * Test suite for Agency Outcomes functionality
 * 
 * Tests the core business logic for agency outcomes management
 * including data retrieval, manipulation, and processing.
 */
class AgencyOutcomesTest extends TestCase
{
    protected $mockConnection;
    protected $mockStatement;
    protected $mockResult;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset session data before each test
        $_SESSION = [];
        
        // Mock basic session data for agency user
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'test_agency_user';
        $_SESSION['role'] = 'agency';
        $_SESSION['agency_id'] = 2;
        
        // Create mock database objects
        $this->mockConnection = $this->createMock(mysqli::class);
        $this->mockStatement = $this->createMock(mysqli_stmt::class);
        $this->mockResult = $this->createMock(mysqli_result::class);
        
        // Set global database connection
        $GLOBALS['conn'] = $this->mockConnection;
        
        // Define ROOT_PATH if not already defined
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
        }
        
        // Mock require_once calls by including the file directly in setup
        if (!function_exists('get_all_outcomes')) {
            require_once ROOT_PATH . 'app/lib/agencies/outcomes.php';
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($GLOBALS['conn']);
    }

    /**
     * Test get_all_outcomes function
     */
    public function testGetAllOutcomes()
    {
        // Prepare mock data
        $mockData = [
            [
                'id' => 1,
                'code' => 'FDS_01',
                'title' => 'Forest Conservation',
                'type' => 'chart',
                'data' => '{"rows": [{"month": "Jan", "2024": 1000}], "columns": ["2024"]}'
            ],
            [
                'id' => 2,
                'code' => 'SFC_01',
                'title' => 'Sustainable Logging',
                'type' => 'kpi',
                'data' => '{"value": 85, "unit": "%"}'
            ]
        ];

        // Mock the query execution
        $this->mockConnection
            ->expects($this->once())
            ->method('query')
            ->with('SELECT * FROM outcomes ORDER BY id ASC')
            ->willReturn($this->mockResult);

        // Mock result fetching
        $this->mockResult
            ->expects($this->exactly(3)) // 2 data rows + 1 final null
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(
                $mockData[0],
                $mockData[1],
                null
            );

        // Execute the function
        $result = get_all_outcomes();

        // Assertions
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('FDS_01', $result[0]['code']);
        $this->assertIsArray($result[0]['data']); // Should be decoded JSON
        $this->assertEquals(['rows' => [['month' => 'Jan', '2024' => 1000]], 'columns' => ['2024']], $result[0]['data']);
    }

    /**
     * Test get_all_outcomes with empty result
     */
    public function testGetAllOutcomesEmpty()
    {
        // Mock empty query result
        $this->mockConnection
            ->expects($this->once())
            ->method('query')
            ->willReturn($this->mockResult);

        $this->mockResult
            ->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(null);

        $result = get_all_outcomes();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test get_all_outcomes with query failure
     */
    public function testGetAllOutcomesQueryFailure()
    {
        // Mock failed query
        $this->mockConnection
            ->expects($this->once())
            ->method('query')
            ->willReturn(false);

        $result = get_all_outcomes();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test get_outcome_by_code function
     */
    public function testGetOutcomeByCode()
    {
        $testCode = 'FDS_01';
        $mockData = [
            'id' => 1,
            'code' => 'FDS_01',
            'title' => 'Forest Conservation',
            'type' => 'chart',
            'data' => '{"rows": [], "columns": []}'
        ];

        // Mock prepared statement
        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM outcomes WHERE code = ?')
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param')
            ->with('s', $testCode);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('get_result')
            ->willReturn($this->mockResult);

        // Use a different approach - set expectations on method calls instead
        $this->mockResult
            ->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn($mockData);

        // Mock the num_rows check by having fetch_assoc return data
        $result = get_outcome_by_code($testCode);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('FDS_01', $result['code']);
        $this->assertIsArray($result['data']); // Should be decoded JSON
    }

    /**
     * Test get_outcome_by_code with no results
     */
    public function testGetOutcomeByCodeNotFound()
    {
        $testCode = 'NONEXISTENT';

        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param');

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('get_result')
            ->willReturn($this->mockResult);

        // Mock no results by having fetch_assoc return null
        $this->mockResult
            ->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(null);

        $result = get_outcome_by_code($testCode);

        $this->assertNull($result);
    }

    /**
     * Test get_outcome_by_id function
     */
    public function testGetOutcomeById()
    {
        $testId = 1;
        $mockData = [
            'id' => 1,
            'code' => 'FDS_01',
            'title' => 'Forest Conservation',
            'type' => 'chart',
            'data' => '{"rows": [], "columns": []}'
        ];

        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM outcomes WHERE id = ?')
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param')
            ->with('i', $testId);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute');

        $this->mockStatement
            ->expects($this->once())
            ->method('get_result')
            ->willReturn($this->mockResult);

        // Mock successful result by having fetch_assoc return data
        $this->mockResult
            ->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn($mockData);

        $result = get_outcome_by_id($testId);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertIsArray($result['data']);
    }

    /**
     * Test update_outcome_data_by_code function
     */
    public function testUpdateOutcomeDataByCode()
    {
        $testCode = 'FDS_01';
        $testData = ['rows' => [['month' => 'Jan', '2024' => 1500]], 'columns' => ['2024']];

        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->with('UPDATE outcomes SET data = ?, updated_at = NOW() WHERE code = ?')
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param')
            ->with('ss', json_encode($testData), $testCode);

        $this->mockStatement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = update_outcome_data_by_code($testCode, $testData);

        $this->assertTrue($result);
    }

    /**
     * Test update_outcome_data_by_code failure
     */
    public function testUpdateOutcomeDataByCodeFailure()
    {
        $testCode = 'FDS_01';
        $testData = ['test' => 'data'];

        $this->mockConnection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStatement);

        $this->mockStatement
            ->expects($this->once())
            ->method('bind_param');

        $this->mockStatement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $result = update_outcome_data_by_code($testCode, $testData);

        $this->assertFalse($result);
    }

    /**
     * Test parse_outcome_json function
     */
    public function testParseOutcomeJson()
    {
        $validJson = '{"test": "data", "number": 123}';
        $result = parse_outcome_json($validJson);

        $this->assertIsArray($result);
        $this->assertEquals('data', $result['test']);
        $this->assertEquals(123, $result['number']);
    }

    /**
     * Test parse_outcome_json with invalid JSON
     */
    public function testParseOutcomeJsonInvalid()
    {
        $invalidJson = '{"invalid": json}';
        $result = parse_outcome_json($invalidJson);

        $this->assertNull($result);
    }

    /**
     * Test parse_outcome_json with empty data
     */
    public function testParseOutcomeJsonEmpty()
    {
        $result = parse_outcome_json('');
        $this->assertNull($result);

        $result = parse_outcome_json(null);
        $this->assertNull($result);
    }

    /**
     * Test get_agency_outcomes_statistics function
     */
    public function testGetAgencyOutcomesStatistics()
    {
        $sectorId = 1;
        
        // Mock total outcomes query
        $totalResult = $this->createMock(mysqli_result::class);
        $totalResult
            ->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(['total' => 5]);

        // Mock type-based query
        $typeResult = $this->createMock(mysqli_result::class);
        $typeResult
            ->expects($this->exactly(3)) // 2 data rows + 1 final null
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(
                ['type' => 'chart', 'count' => 3],
                ['type' => 'kpi', 'count' => 2],
                null
            );

        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function($query) use ($totalResult, $typeResult) {
                if (strpos($query, 'COUNT(*)') !== false) {
                    return $totalResult;
                } else {
                    return $typeResult;
                }
            });

        $result = get_agency_outcomes_statistics($sectorId);

        $this->assertIsArray($result);
        $this->assertEquals(5, $result['total_outcomes']);
        $this->assertEquals(3, $result['chart_outcomes']);
        $this->assertEquals(2, $result['kpi_outcomes']);
        $this->assertEquals(0, $result['submitted_outcomes']); // Fixed outcomes don't have submission status
        $this->assertEquals(0, $result['draft_outcomes']);
        $this->assertIsArray($result['outcomes_by_type']);
        $this->assertEquals(3, $result['outcomes_by_type']['chart']);
        $this->assertEquals(2, $result['outcomes_by_type']['kpi']);
    }

    /**
     * Test get_agency_outcomes_statistics with query failures
     */
    public function testGetAgencyOutcomesStatisticsQueryFailure()
    {
        $sectorId = 1;

        // Mock failed queries
        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturn(false);

        $result = get_agency_outcomes_statistics($sectorId);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total_outcomes']);
        $this->assertEquals(0, $result['chart_outcomes']);
        $this->assertEquals(0, $result['kpi_outcomes']);
        $this->assertIsArray($result['outcomes_by_type']);
        $this->assertEmpty($result['outcomes_by_type']);
    }

    /**
     * Test get_agency_outcomes_statistics with period ID
     */
    public function testGetAgencyOutcomesStatisticsWithPeriod()
    {
        $sectorId = 1;
        $periodId = 2;

        // Mock successful queries (behavior should be same as without period for fixed outcomes)
        $totalResult = $this->createMock(mysqli_result::class);
        $totalResult
            ->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(['total' => 3]);

        $typeResult = $this->createMock(mysqli_result::class);
        $typeResult
            ->expects($this->exactly(2))
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(
                ['type' => 'chart', 'count' => 2],
                null
            );

        $this->mockConnection
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturnOnConsecutiveCalls($totalResult, $typeResult);

        $result = get_agency_outcomes_statistics($sectorId, $periodId);

        $this->assertIsArray($result);
        $this->assertEquals(3, $result['total_outcomes']);
        $this->assertEquals(2, $result['chart_outcomes']);
        $this->assertEquals(0, $result['kpi_outcomes']); // Not present in this test
    }

    /**
     * Test data integrity and edge cases
     */
    public function testDataIntegrityAndEdgeCases()
    {
        // Test with malformed JSON in outcome data
        $malformedData = [
            'id' => 1,
            'code' => 'TEST_01',
            'title' => 'Test Outcome',
            'type' => 'chart',
            'data' => '{malformed json'
        ];

        $this->mockConnection
            ->expects($this->once())
            ->method('query')
            ->willReturn($this->mockResult);

        $this->mockResult
            ->expects($this->exactly(2))
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls($malformedData, null);

        $result = get_all_outcomes();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertNull($result[0]['data']); // Should be null due to JSON decode failure
    }

    /**
     * Test performance with large datasets
     */
    public function testPerformanceWithLargeDatasets()
    {
        // Create mock data for 100 outcomes
        $mockData = [];
        for ($i = 1; $i <= 100; $i++) {
            $mockData[] = [
                'id' => $i,
                'code' => "TEST_" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'title' => "Test Outcome $i",
                'type' => $i % 2 === 0 ? 'chart' : 'kpi',
                'data' => '{"test": true}'
            ];
        }

        $this->mockConnection
            ->expects($this->once())
            ->method('query')
            ->willReturn($this->mockResult);

        // Mock fetch_assoc to return all data + final null
        $fetchCalls = array_merge($mockData, [null]);
        $this->mockResult
            ->expects($this->exactly(101))
            ->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(...$fetchCalls);

        $startTime = microtime(true);
        $result = get_all_outcomes();
        $endTime = microtime(true);

        $this->assertIsArray($result);
        $this->assertCount(100, $result);
        
        // Performance assertion - should complete within reasonable time
        $executionTime = $endTime - $startTime;
        $this->assertLessThan(1.0, $executionTime, 'Function should complete within 1 second');
    }

    /**
     * Test error handling and logging
     */
    public function testErrorHandlingAndLogging()
    {
        // Test database connection error by using a mock that returns false
        $failingConnection = $this->createMock(mysqli::class);
        $failingConnection
            ->expects($this->once())
            ->method('query')
            ->willReturn(false);
        
        $GLOBALS['conn'] = $failingConnection;

        // This should not throw an exception but handle gracefully
        $result = get_all_outcomes();
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        // Restore connection for other tests
        $GLOBALS['conn'] = $this->mockConnection;
    }
}
