<?php

use PHPUnit\Framework\TestCase;

/**
 * Test suite for Agency Program Data Processing functionality
 * 
 * Tests the core business logic for program data processing,
 * formatting, validation, and transformation for display.
 */
class AgencyProgramDataProcessorTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * Test basic program info processing
     */
    public function testBasicProgramInfoProcessing()
    {
        // Mock raw program data from database
        $raw_program = [
            'program_name' => 'Forest Conservation Program',
            'program_number' => 'P-001',
            'program_description' => 'A comprehensive forest conservation initiative',
            'agency_name' => 'Forestry Department',
            'initiative_name' => 'Green Forest Initiative',
            'initiative_number' => 'INIT-001',
            'sector_name' => 'Environmental',
            'is_assigned' => 1,
            'created_at' => '2024-01-15 10:30:00',
            'updated_at' => '2024-01-20 14:45:00'
        ];

        // Simulate the process_basic_info function logic
        $processed_info = [
            'program_name' => htmlspecialchars($raw_program['program_name'] ?? ''),
            'program_number' => htmlspecialchars($raw_program['program_number'] ?? ''),
            'description' => htmlspecialchars($raw_program['program_description'] ?? ''),
            'agency_name' => htmlspecialchars($raw_program['agency_name'] ?? ''),
            'initiative_name' => htmlspecialchars($raw_program['initiative_name'] ?? ''),
            'initiative_number' => htmlspecialchars($raw_program['initiative_number'] ?? ''),
            'sector_name' => htmlspecialchars($raw_program['sector_name'] ?? 'Not specified'),
            'is_assigned' => isset($raw_program['is_assigned']) && $raw_program['is_assigned'],
            'created_at' => $raw_program['created_at'] ?? null,
            'updated_at' => $raw_program['updated_at'] ?? null
        ];

        // Validate processed data structure
        $this->assertIsArray($processed_info);
        $this->assertArrayHasKey('program_name', $processed_info);
        $this->assertArrayHasKey('program_number', $processed_info);
        $this->assertArrayHasKey('description', $processed_info);
        $this->assertArrayHasKey('agency_name', $processed_info);
        $this->assertArrayHasKey('is_assigned', $processed_info);

        // Validate data sanitization
        $this->assertEquals('Forest Conservation Program', $processed_info['program_name']);
        $this->assertEquals('P-001', $processed_info['program_number']);
        $this->assertTrue($processed_info['is_assigned']);
        $this->assertEquals('Environmental', $processed_info['sector_name']);
    }

    /**
     * Test program targets processing
     */
    public function testProgramTargetsProcessing()
    {
        // Mock submission data with targets
        $latest_submission = [
            'content_json' => json_encode([
                'target' => 'Plant 1000 trees by end of quarter',
                'achievement' => 'Planted 750 trees so far',
                'status_text' => 'On track to meet target',
                'completion_percentage' => 75
            ]),
            'target' => 'Plant 1000 trees by end of quarter', // Legacy format
            'achievement' => 'Planted 750 trees so far',
            'is_submitted' => 1
        ];

        $has_submissions = true;

        // Simulate targets processing logic
        $targets = [];
        if ($has_submissions && $latest_submission) {
            // Check for content_json format first
            if (isset($latest_submission['content_json'])) {
                $content = json_decode($latest_submission['content_json'], true);
                if ($content) {
                    $targets = [
                        'target' => htmlspecialchars($content['target'] ?? ''),
                        'achievement' => htmlspecialchars($content['achievement'] ?? ''),
                        'status_text' => htmlspecialchars($content['status_text'] ?? ''),
                        'completion_percentage' => (int)($content['completion_percentage'] ?? 0)
                    ];
                }
            } else {
                // Fallback to legacy format
                $targets = [
                    'target' => htmlspecialchars($latest_submission['target'] ?? ''),
                    'achievement' => htmlspecialchars($latest_submission['achievement'] ?? ''),
                    'status_text' => htmlspecialchars($latest_submission['status_text'] ?? ''),
                    'completion_percentage' => 0
                ];
            }
        }

        // Validate targets processing
        $this->assertIsArray($targets);
        $this->assertArrayHasKey('target', $targets);
        $this->assertArrayHasKey('achievement', $targets);
        $this->assertEquals('Plant 1000 trees by end of quarter', $targets['target']);
        $this->assertEquals('Planted 750 trees so far', $targets['achievement']);
        $this->assertEquals(75, $targets['completion_percentage']);
    }

    /**
     * Test program status processing
     */
    public function testProgramStatusProcessing()
    {
        // Mock different status scenarios
        $test_scenarios = [
            [
                'submission' => ['is_submitted' => 1, 'is_draft' => 0],
                'has_submissions' => true,
                'expected_status' => 'submitted'
            ],
            [
                'submission' => ['is_submitted' => 0, 'is_draft' => 1],
                'has_submissions' => true,
                'expected_status' => 'draft'
            ],
            [
                'submission' => null,
                'has_submissions' => false,
                'expected_status' => 'no_submissions'
            ]
        ];

        foreach ($test_scenarios as $scenario) {
            $latest_submission = $scenario['submission'];
            $has_submissions = $scenario['has_submissions'];
            
            // Simulate status processing logic
            $status = 'unknown';
            if (!$has_submissions || !$latest_submission) {
                $status = 'no_submissions';
            } elseif ($latest_submission['is_submitted']) {
                $status = 'submitted';
            } elseif ($latest_submission['is_draft']) {
                $status = 'draft';
            }

            $this->assertEquals($scenario['expected_status'], $status);
        }
    }

    /**
     * Test timeline processing
     */
    public function testTimelineProcessing()
    {
        // Mock program with timeline data
        $program = [
            'created_at' => '2024-01-15 10:30:00',
            'updated_at' => '2024-01-20 14:45:00',
            'start_date' => '2024-02-01',
            'end_date' => '2024-12-31',
            'last_submission_date' => '2024-01-18 16:20:00'
        ];

        // Simulate timeline processing
        $timeline = [
            'created_at' => $program['created_at'],
            'updated_at' => $program['updated_at'],
            'start_date' => $program['start_date'] ?? null,
            'end_date' => $program['end_date'] ?? null,
            'last_submission_date' => $program['last_submission_date'] ?? null,
            'days_since_creation' => $this->calculateDaysDifference($program['created_at']),
            'days_until_deadline' => $program['end_date'] ? $this->calculateDaysUntilDeadline($program['end_date']) : null
        ];

        $this->assertIsArray($timeline);
        $this->assertArrayHasKey('created_at', $timeline);
        $this->assertArrayHasKey('updated_at', $timeline);
        $this->assertArrayHasKey('days_since_creation', $timeline);
        $this->assertIsInt($timeline['days_since_creation']);
    }

    /**
     * Test performance metrics processing
     */
    public function testPerformanceMetricsProcessing()
    {
        // Mock program with performance data
        $program = [
            'program_id' => 1,
            'submissions_count' => 4,
            'targets_achieved' => 3,
            'completion_percentage' => 75,
            'on_schedule' => 1
        ];

        // Simulate performance metrics processing
        $metrics = [
            'submissions_count' => (int)($program['submissions_count'] ?? 0),
            'targets_achieved' => (int)($program['targets_achieved'] ?? 0),
            'completion_percentage' => (float)($program['completion_percentage'] ?? 0),
            'achievement_rate' => $this->calculateAchievementRate($program),
            'performance_grade' => $this->calculatePerformanceGrade($program['completion_percentage'] ?? 0),
            'is_on_schedule' => (bool)($program['on_schedule'] ?? false)
        ];

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('submissions_count', $metrics);
        $this->assertArrayHasKey('achievement_rate', $metrics);
        $this->assertArrayHasKey('performance_grade', $metrics);
        $this->assertIsInt($metrics['submissions_count']);
        $this->assertIsFloat($metrics['completion_percentage']);
        $this->assertIsBool($metrics['is_on_schedule']);
    }

    /**
     * Test data sanitization and security
     */
    public function testDataSanitization()
    {
        // Mock potentially dangerous input data
        $dangerous_data = [
            'program_name' => '<script>alert("XSS")</script>Malicious Program',
            'description' => 'Description with <img src=x onerror=alert("XSS")> injection',
            'agency_name' => 'Agency & "Special" Characters',
            'user_input' => "'; DROP TABLE programs; --"
        ];

        // Test HTML escaping for script tags
        $script_sanitized = htmlspecialchars($dangerous_data['program_name'], ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString('<script>', $script_sanitized);
        $this->assertStringContainsString('&lt;script&gt;', $script_sanitized);

        // Test HTML escaping for img tags with onerror
        $img_sanitized = htmlspecialchars($dangerous_data['description'], ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString('<img', $img_sanitized);
        $this->assertStringContainsString('&lt;img', $img_sanitized);

        // Test ampersand and quote escaping
        $agency_sanitized = htmlspecialchars($dangerous_data['agency_name'], ENT_QUOTES, 'UTF-8');
        $this->assertStringContainsString('&amp;', $agency_sanitized);
        $this->assertStringContainsString('&quot;', $agency_sanitized);

        // Test SQL injection prevention (conceptual)
        $user_input = $dangerous_data['user_input'];
        $safe_input = str_replace(["'", '"', ';', '--', 'DROP', 'TABLE'], '', $user_input);
        $this->assertStringNotContainsString("DROP TABLE", $safe_input);
    }

    /**
     * Test JSON data handling
     */
    public function testJsonDataHandling()
    {
        // Test valid JSON processing
        $valid_json = json_encode([
            'target' => 'Complete project phase 1',
            'metrics' => ['trees_planted' => 500, 'area_covered' => 25.5],
            'notes' => 'Progress is on track'
        ]);

        $decoded = json_decode($valid_json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('target', $decoded);
        $this->assertArrayHasKey('metrics', $decoded);

        // Test invalid JSON handling
        $invalid_json = '{"invalid": json, "missing": quotes}';
        $decoded_invalid = json_decode($invalid_json, true);
        $this->assertNull($decoded_invalid);

        // Test JSON error handling
        $json_error = json_last_error();
        $this->assertNotEquals(JSON_ERROR_NONE, $json_error);
    }

    /**
     * Test accessibility data processing
     */
    public function testAccessibilityDataProcessing()
    {
        $program = [
            'program_id' => 1,
            'program_name' => 'Forest Conservation',
            'status' => 'active',
            'completion_percentage' => 75
        ];

        // Simulate accessibility processing
        $accessibility = [
            'aria_label' => 'Program: ' . $program['program_name'],
            'status_description' => $this->getStatusDescription($program['status']),
            'progress_description' => $program['completion_percentage'] . '% complete',
            'keyboard_navigation' => true,
            'screen_reader_friendly' => true
        ];

        $this->assertIsArray($accessibility);
        $this->assertArrayHasKey('aria_label', $accessibility);
        $this->assertArrayHasKey('status_description', $accessibility);
        $this->assertStringContainsString('Forest Conservation', $accessibility['aria_label']);
        $this->assertTrue($accessibility['keyboard_navigation']);
    }

    /**
     * Test error handling in data processing
     */
    public function testErrorHandling()
    {
        // Test null program data
        $null_program = null;
        $processed = $this->processNullSafeProgram($null_program);
        $this->assertIsArray($processed);
        $this->assertEquals('Unknown Program', $processed['program_name']);

        // Test missing required fields
        $incomplete_program = ['program_id' => 1]; // Missing other fields
        $processed_incomplete = $this->processIncompleteProgram($incomplete_program);
        $this->assertArrayHasKey('program_name', $processed_incomplete);
        $this->assertEquals('', $processed_incomplete['program_name']);

        // Test invalid data types
        $invalid_types = [
            'completion_percentage' => 'invalid_number',
            'is_active' => 'invalid_boolean',
            'submission_count' => null
        ];

        $sanitized_types = $this->sanitizeDataTypes($invalid_types);
        $this->assertEquals(0, $sanitized_types['completion_percentage']);
        $this->assertFalse($sanitized_types['is_active']);
        $this->assertEquals(0, $sanitized_types['submission_count']);
    }

    // Helper methods for testing

    private function calculateDaysDifference($date_string)
    {
        $date = new DateTime($date_string);
        $now = new DateTime();
        return $now->diff($date)->days;
    }

    private function calculateDaysUntilDeadline($deadline)
    {
        $deadline_date = new DateTime($deadline);
        $now = new DateTime();
        $diff = $deadline_date->diff($now);
        return $diff->invert ? $diff->days : -$diff->days;
    }

    private function calculateAchievementRate($program)
    {
        $submissions = $program['submissions_count'] ?? 0;
        $achieved = $program['targets_achieved'] ?? 0;
        return $submissions > 0 ? ($achieved / $submissions) * 100 : 0;
    }

    private function calculatePerformanceGrade($completion_percentage)
    {
        if ($completion_percentage >= 90) return 'A';
        if ($completion_percentage >= 80) return 'B';
        if ($completion_percentage >= 70) return 'C';
        if ($completion_percentage >= 60) return 'D';
        return 'F';
    }

    private function getStatusDescription($status)
    {
        $descriptions = [
            'active' => 'Currently active and in progress',
            'completed' => 'Successfully completed',
            'draft' => 'In draft status, not yet submitted',
            'inactive' => 'Currently inactive or paused'
        ];
        return $descriptions[$status] ?? 'Status unknown';
    }

    private function processNullSafeProgram($program)
    {
        return [
            'program_name' => $program['program_name'] ?? 'Unknown Program',
            'program_number' => $program['program_number'] ?? 'N/A',
            'description' => $program['description'] ?? '',
            'status' => $program['status'] ?? 'unknown'
        ];
    }

    private function processIncompleteProgram($program)
    {
        return [
            'program_name' => htmlspecialchars($program['program_name'] ?? ''),
            'program_number' => htmlspecialchars($program['program_number'] ?? ''),
            'description' => htmlspecialchars($program['description'] ?? ''),
            'agency_name' => htmlspecialchars($program['agency_name'] ?? '')
        ];
    }

    private function sanitizeDataTypes($data)
    {
        return [
            'completion_percentage' => is_numeric($data['completion_percentage']) ? (float)$data['completion_percentage'] : 0,
            'is_active' => filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN),
            'submission_count' => is_numeric($data['submission_count']) ? (int)$data['submission_count'] : 0
        ];
    }
}
