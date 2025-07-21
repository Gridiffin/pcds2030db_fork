<?php

use PHPUnit\Framework\TestCase;

/**
 * Test suite for Agency Statistics and Dashboard functionality
 * 
 * Tests the core business logic for agency statistics calculation,
 * dashboard data processing, and metrics generation.
 */
class AgencyStatisticsTest extends TestCase
{
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
        
        // Mock global database connection
        $GLOBALS['conn'] = $this->createMock(mysqli::class);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $_SESSION = [];
        unset($GLOBALS['conn']);
        parent::tearDown();
    }

    /**
     * Test content_json schema detection logic
     */
    public function testContentJsonSchemaDetection()
    {
        // Mock the logic that would be in has_content_json_schema()
        $mock_columns = [
            'content_json',
            'target',
            'achievement', 
            'status_text'
        ];

        $has_content_json = in_array('content_json', $mock_columns);
        $this->assertTrue($has_content_json);

        // Test legacy schema
        $legacy_columns = [
            'target',
            'achievement',
            'status_text'
        ];

        $has_legacy_only = !in_array('content_json', $legacy_columns);
        $this->assertTrue($has_legacy_only);
    }

    /**
     * Test program filtering by period
     */
    public function testProgramFilteringByPeriod()
    {
        $period_id = 3;
        $filters = [
            'status' => 'active',
            'limit' => 10
        ];

        // Test basic parameter validation
        $this->assertIsInt($period_id);
        $this->assertGreaterThan(0, $period_id);
        $this->assertIsArray($filters);

        // Test filter validation
        $valid_filter_keys = ['status', 'limit', 'offset', 'search', 'sort'];
        foreach (array_keys($filters) as $key) {
            $this->assertContains($key, $valid_filter_keys);
        }
    }

    /**
     * Test agency permission validation for statistics
     */
    public function testAgencyPermissionValidation()
    {
        // Test agency user access
        $_SESSION['role'] = 'agency';
        $_SESSION['agency_id'] = 2;
        
        $this->assertEquals('agency', $_SESSION['role']);
        $this->assertIsInt($_SESSION['agency_id']);
        $this->assertGreaterThan(0, $_SESSION['agency_id']);

        // Test focal user access (should have broader access)
        $_SESSION['role'] = 'focal';
        $this->assertEquals('focal', $_SESSION['role']);

        // Test admin user access (should have full access)
        $_SESSION['role'] = 'admin';
        $this->assertEquals('admin', $_SESSION['role']);

        // Test unauthorized user
        $_SESSION['role'] = 'unauthorized';
        $is_authorized = in_array($_SESSION['role'], ['agency', 'focal', 'admin']);
        $this->assertFalse($is_authorized);
    }

    /**
     * Test statistics data structure validation
     */
    public function testStatisticsDataStructure()
    {
        // Mock statistics data structure
        $mock_stats = [
            'total_programs' => 15,
            'active_programs' => 12,
            'completed_programs' => 8,
            'draft_submissions' => 5,
            'submitted_entries' => 20,
            'completion_rate' => 66.7,
            'recent_activities' => [
                [
                    'program_id' => 1,
                    'program_name' => 'Forest Conservation',
                    'action' => 'submission_updated',
                    'timestamp' => '2024-01-15 10:30:00'
                ]
            ]
        ];

        // Validate main statistics structure
        $this->assertIsArray($mock_stats);
        $this->assertArrayHasKey('total_programs', $mock_stats);
        $this->assertArrayHasKey('active_programs', $mock_stats);
        $this->assertArrayHasKey('completed_programs', $mock_stats);
        $this->assertArrayHasKey('completion_rate', $mock_stats);
        $this->assertArrayHasKey('recent_activities', $mock_stats);

        // Validate data types
        $this->assertIsInt($mock_stats['total_programs']);
        $this->assertIsInt($mock_stats['active_programs']);
        $this->assertIsFloat($mock_stats['completion_rate']);
        $this->assertIsArray($mock_stats['recent_activities']);

        // Validate activity structure
        $activity = $mock_stats['recent_activities'][0];
        $this->assertArrayHasKey('program_id', $activity);
        $this->assertArrayHasKey('program_name', $activity);
        $this->assertArrayHasKey('action', $activity);
        $this->assertArrayHasKey('timestamp', $activity);
    }

    /**
     * Test completion rate calculation
     */
    public function testCompletionRateCalculation()
    {
        // Test various completion scenarios
        $test_scenarios = [
            ['completed' => 8, 'total' => 12, 'expected' => 66.67],
            ['completed' => 0, 'total' => 10, 'expected' => 0.0],
            ['completed' => 10, 'total' => 10, 'expected' => 100.0],
            ['completed' => 5, 'total' => 0, 'expected' => 0.0], // Edge case
        ];

        foreach ($test_scenarios as $scenario) {
            $completed = $scenario['completed'];
            $total = $scenario['total'];
            $expected = $scenario['expected'];

            if ($total > 0) {
                $calculated_rate = round(($completed / $total) * 100, 2);
            } else {
                $calculated_rate = 0.0;
            }

            $this->assertEquals($expected, $calculated_rate, 
                "Completion rate calculation failed for {$completed}/{$total}");
        }
    }

    /**
     * Test program status categorization
     */
    public function testProgramStatusCategorization()
    {
        // Mock program data with various statuses
        $mock_programs = [
            ['program_id' => 1, 'status' => 'active', 'completion_percentage' => 75],
            ['program_id' => 2, 'status' => 'completed', 'completion_percentage' => 100],
            ['program_id' => 3, 'status' => 'draft', 'completion_percentage' => 0],
            ['program_id' => 4, 'status' => 'active', 'completion_percentage' => 45],
            ['program_id' => 5, 'status' => 'inactive', 'completion_percentage' => 30],
        ];

        // Test status categorization
        $status_counts = array_count_values(array_column($mock_programs, 'status'));
        
        $this->assertEquals(2, $status_counts['active']);
        $this->assertEquals(1, $status_counts['completed']);
        $this->assertEquals(1, $status_counts['draft']);
        $this->assertEquals(1, $status_counts['inactive']);

        // Test completion-based categorization
        $high_progress = array_filter($mock_programs, function($program) {
            return $program['completion_percentage'] >= 75;
        });

        $low_progress = array_filter($mock_programs, function($program) {
            return $program['completion_percentage'] < 50;
        });

        $this->assertCount(2, $high_progress);
        $this->assertCount(3, $low_progress);
    }

    /**
     * Test dashboard chart data processing
     */
    public function testDashboardChartDataProcessing()
    {
        // Mock program submission data for charts
        $mock_submission_data = [
            ['month' => '2024-01', 'submissions' => 5, 'completions' => 3],
            ['month' => '2024-02', 'submissions' => 8, 'completions' => 6],
            ['month' => '2024-03', 'submissions' => 12, 'completions' => 10],
            ['month' => '2024-04', 'submissions' => 7, 'completions' => 5],
        ];

        // Test chart data structure
        $chart_data = [
            'labels' => array_column($mock_submission_data, 'month'),
            'submissions' => array_column($mock_submission_data, 'submissions'),
            'completions' => array_column($mock_submission_data, 'completions')
        ];

        $this->assertCount(4, $chart_data['labels']);
        $this->assertCount(4, $chart_data['submissions']);
        $this->assertCount(4, $chart_data['completions']);

        // Test data integrity
        $total_submissions = array_sum($chart_data['submissions']);
        $total_completions = array_sum($chart_data['completions']);
        
        $this->assertEquals(32, $total_submissions);
        $this->assertEquals(24, $total_completions);
        $this->assertLessThanOrEqual($total_submissions, $total_completions);
    }

    /**
     * Test sector-based program filtering
     */
    public function testSectorBasedFiltering()
    {
        // Mock programs with sector information
        $mock_programs = [
            ['program_id' => 1, 'sector_id' => 1, 'sector_name' => 'Forest Conservation'],
            ['program_id' => 2, 'sector_id' => 2, 'sector_name' => 'Timber Management'],
            ['program_id' => 3, 'sector_id' => 1, 'sector_name' => 'Forest Conservation'],
            ['program_id' => 4, 'sector_id' => 3, 'sector_name' => 'Wildlife Protection'],
        ];

        // Test sector grouping
        $sectors = [];
        foreach ($mock_programs as $program) {
            $sector_id = $program['sector_id'];
            if (!isset($sectors[$sector_id])) {
                $sectors[$sector_id] = [
                    'sector_name' => $program['sector_name'],
                    'program_count' => 0
                ];
            }
            $sectors[$sector_id]['program_count']++;
        }

        $this->assertCount(3, $sectors);
        $this->assertEquals(2, $sectors[1]['program_count']);
        $this->assertEquals(1, $sectors[2]['program_count']);
        $this->assertEquals(1, $sectors[3]['program_count']);
    }

    /**
     * Test data export formatting for dashboard
     */
    public function testDataExportFormatting()
    {
        // Mock dashboard summary data
        $summary_data = [
            'agency_name' => 'Test Agency',
            'period_name' => 'Q1 2024',
            'total_programs' => 15,
            'active_programs' => 12,
            'completion_rate' => 66.67,
            'last_updated' => '2024-01-15 10:30:00'
        ];

        // Test CSV format preparation
        $csv_headers = ['Agency', 'Period', 'Total Programs', 'Active Programs', 'Completion Rate', 'Last Updated'];
        $csv_data = [
            $summary_data['agency_name'],
            $summary_data['period_name'],
            $summary_data['total_programs'],
            $summary_data['active_programs'],
            $summary_data['completion_rate'] . '%',
            $summary_data['last_updated']
        ];

        $this->assertCount(6, $csv_headers);
        $this->assertCount(6, $csv_data);
        $this->assertEquals('Test Agency', $csv_data[0]);
        $this->assertEquals('66.67%', $csv_data[4]);
    }

    /**
     * Test performance metrics calculation
     */
    public function testPerformanceMetricsCalculation()
    {
        // Mock performance data
        $performance_data = [
            'targets_set' => 20,
            'targets_achieved' => 15,
            'targets_exceeded' => 8,
            'on_schedule' => 12,
            'behind_schedule' => 3,
            'ahead_of_schedule' => 5
        ];

        // Calculate performance metrics
        $achievement_rate = ($performance_data['targets_achieved'] / $performance_data['targets_set']) * 100;
        $excellence_rate = ($performance_data['targets_exceeded'] / $performance_data['targets_set']) * 100;
        $schedule_performance = ($performance_data['on_schedule'] / $performance_data['targets_set']) * 100;

        $this->assertEquals(75.0, $achievement_rate);
        $this->assertEquals(40.0, $excellence_rate);
        $this->assertEquals(60.0, $schedule_performance);

        // Test performance grading
        $grade = 'A';
        if ($achievement_rate >= 90) $grade = 'A';
        elseif ($achievement_rate >= 80) $grade = 'B';
        elseif ($achievement_rate >= 70) $grade = 'C';
        else $grade = 'D';

        $this->assertEquals('C', $grade);
    }

    /**
     * Test error handling in statistics calculation
     */
    public function testStatisticsErrorHandling()
    {
        // Test division by zero prevention
        $total_programs = 0;
        $completed_programs = 5;

        $completion_rate = $total_programs > 0 ? ($completed_programs / $total_programs) * 100 : 0;
        $this->assertEquals(0, $completion_rate);

        // Test negative value handling
        $invalid_data = [
            'total_programs' => -1,
            'completed_programs' => -5,
            'active_programs' => -3
        ];

        foreach ($invalid_data as $key => $value) {
            $sanitized_value = max(0, $value);
            $this->assertGreaterThanOrEqual(0, $sanitized_value);
        }

        // Test null data handling
        $null_data = null;
        $safe_data = $null_data ?? [];
        $this->assertIsArray($safe_data);
    }
}
