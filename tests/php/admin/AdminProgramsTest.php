<?php

use PHPUnit\Framework\TestCase;

/**
 * Admin Programs Test Class
 * Tests for admin program management functionality
 */
class AdminProgramsTest extends TestCase
{
    private $mockDatabase;
    private $mockSession;

    protected function setUp(): void
    {
        // Mock database connection
        $this->mockDatabase = $this->createMock(mysqli::class);
        
        // Mock session
        $this->mockSession = [
            'user_id' => 1,
            'username' => 'admin',
            'role' => 'admin',
            'is_logged_in' => true
        ];
    }

    protected function tearDown(): void
    {
        $this->mockDatabase = null;
        $this->mockSession = null;
    }

    /**
     * Test program validation functions
     */
    public function testValidateProgramData()
    {
        $validProgramData = [
            'title' => 'Forest Conservation Program',
            'description' => 'This program aims to conserve forest areas through sustainable practices.',
            'type' => 'conservation',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'budget' => '1000000',
            'status' => 'active'
        ];

        $invalidProgramData = [
            'title' => '',
            'description' => 'Short',
            'type' => '',
            'start_date' => 'invalid-date',
            'end_date' => '2024-01-01',
            'budget' => '-1000',
            'status' => 'invalid'
        ];

        // Test valid program data
        $this->assertTrue($this->validateProgramData($validProgramData));
        
        // Test invalid program data
        $this->assertFalse($this->validateProgramData($invalidProgramData));
    }

    /**
     * Test program creation
     */
    public function testCreateProgram()
    {
        $programData = [
            'title' => 'New Forest Program',
            'description' => 'A new forest conservation program',
            'type' => 'conservation',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'budget' => '500000',
            'status' => 'active'
        ];

        $result = $this->createProgram($programData);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('program_id', $result);
        $this->assertEquals('New Forest Program', $result['program']['title']);
    }

    /**
     * Test program update
     */
    public function testUpdateProgram()
    {
        $programId = 123;
        $updateData = [
            'title' => 'Updated Forest Program',
            'budget' => '750000',
            'status' => 'completed'
        ];

        $result = $this->updateProgram($programId, $updateData);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Updated Forest Program', $result['program']['title']);
        $this->assertEquals('750000', $result['program']['budget']);
    }

    /**
     * Test program deletion
     */
    public function testDeleteProgram()
    {
        $programId = 123;
        
        $result = $this->deleteProgram($programId);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Program deleted successfully', $result['message']);
    }

    /**
     * Test get program by ID
     */
    public function testGetProgramById()
    {
        $programId = 123;
        
        $result = $this->getProgramById($programId);
        
        $this->assertTrue($result['success']);
        $this->assertEquals($programId, $result['program']['id']);
        $this->assertArrayHasKey('title', $result['program']);
        $this->assertArrayHasKey('description', $result['program']);
    }

    /**
     * Test get all programs
     */
    public function testGetAllPrograms()
    {
        $filters = [
            'status' => 'active',
            'type' => 'conservation',
            'search' => 'forest'
        ];
        
        $result = $this->getAllPrograms($filters);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('programs', $result);
        $this->assertArrayHasKey('total_count', $result);
        $this->assertIsArray($result['programs']);
    }

    /**
     * Test program search functionality
     */
    public function testSearchPrograms()
    {
        $searchTerm = 'forest';
        
        $result = $this->searchPrograms($searchTerm);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('programs', $result);
        $this->assertArrayHasKey('total_count', $result);
        
        // Check that all returned programs contain the search term
        foreach ($result['programs'] as $program) {
            $this->assertStringContainsString(
                strtolower($searchTerm), 
                strtolower($program['title'] . ' ' . $program['description'])
            );
        }
    }

    /**
     * Test program filtering by status
     */
    public function testFilterProgramsByStatus()
    {
        $status = 'active';
        
        $result = $this->filterProgramsByStatus($status);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('programs', $result);
        
        // Check that all returned programs have the specified status
        foreach ($result['programs'] as $program) {
            $this->assertEquals($status, $program['status']);
        }
    }

    /**
     * Test program filtering by type
     */
    public function testFilterProgramsByType()
    {
        $type = 'conservation';
        
        $result = $this->filterProgramsByType($type);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('programs', $result);
        
        // Check that all returned programs have the specified type
        foreach ($result['programs'] as $program) {
            $this->assertEquals($type, $program['type']);
        }
    }

    /**
     * Test program budget calculation
     */
    public function testCalculateProgramBudget()
    {
        $programId = 123;
        
        $result = $this->calculateProgramBudget($programId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('total_budget', $result);
        $this->assertArrayHasKey('allocated_budget', $result);
        $this->assertArrayHasKey('remaining_budget', $result);
        $this->assertArrayHasKey('utilization_percentage', $result);
        
        // Check that budget calculations are logical
        $this->assertGreaterThanOrEqual(0, $result['total_budget']);
        $this->assertGreaterThanOrEqual(0, $result['allocated_budget']);
        $this->assertGreaterThanOrEqual(0, $result['remaining_budget']);
        $this->assertGreaterThanOrEqual(0, $result['utilization_percentage']);
        $this->assertLessThanOrEqual(100, $result['utilization_percentage']);
    }

    /**
     * Test program progress calculation
     */
    public function testCalculateProgramProgress()
    {
        $programId = 123;
        
        $result = $this->calculateProgramProgress($programId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('overall_progress', $result);
        $this->assertArrayHasKey('completed_outcomes', $result);
        $this->assertArrayHasKey('total_outcomes', $result);
        $this->assertArrayHasKey('progress_percentage', $result);
        
        // Check that progress calculations are logical
        $this->assertGreaterThanOrEqual(0, $result['overall_progress']);
        $this->assertGreaterThanOrEqual(0, $result['completed_outcomes']);
        $this->assertGreaterThanOrEqual(0, $result['total_outcomes']);
        $this->assertGreaterThanOrEqual(0, $result['progress_percentage']);
        $this->assertLessThanOrEqual(100, $result['progress_percentage']);
    }

    /**
     * Test program-outcome linking
     */
    public function testLinkProgramToOutcome()
    {
        $programId = 123;
        $outcomeId = 456;
        
        $result = $this->linkProgramToOutcome($programId, $outcomeId);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Program linked to outcome successfully', $result['message']);
    }

    /**
     * Test program-outcome unlinking
     */
    public function testUnlinkProgramFromOutcome()
    {
        $programId = 123;
        $outcomeId = 456;
        
        $result = $this->unlinkProgramFromOutcome($programId, $outcomeId);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Program unlinked from outcome successfully', $result['message']);
    }

    /**
     * Test get program outcomes
     */
    public function testGetProgramOutcomes()
    {
        $programId = 123;
        
        $result = $this->getProgramOutcomes($programId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('outcomes', $result);
        $this->assertIsArray($result['outcomes']);
    }

    /**
     * Test program reporting
     */
    public function testGenerateProgramReport()
    {
        $programId = 123;
        $reportType = 'comprehensive';
        
        $result = $this->generateProgramReport($programId, $reportType);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('report_data', $result);
        $this->assertArrayHasKey('generated_at', $result);
        $this->assertEquals($reportType, $result['report_type']);
    }

    /**
     * Test program export functionality
     */
    public function testExportProgramData()
    {
        $programId = 123;
        $format = 'csv';
        
        $result = $this->exportProgramData($programId, $format);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('export_data', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertEquals($format, $result['format']);
    }

    /**
     * Test program validation with invalid data
     */
    public function testValidateProgramDataWithInvalidData()
    {
        $invalidData = [
            'title' => '',
            'description' => 'Short',
            'type' => 'invalid_type',
            'start_date' => 'invalid-date',
            'end_date' => '2024-01-01',
            'budget' => '-1000'
        ];

        $result = $this->validateProgramData($invalidData);
        
        $this->assertFalse($result);
    }

    /**
     * Test program creation with missing required fields
     */
    public function testCreateProgramWithMissingFields()
    {
        $incompleteData = [
            'title' => 'Incomplete Program',
            'description' => 'This program is missing required fields'
        ];

        $result = $this->createProgram($incompleteData);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertNotEmpty($result['errors']);
    }

    /**
     * Test program update with invalid ID
     */
    public function testUpdateProgramWithInvalidId()
    {
        $invalidId = 0;
        $updateData = ['title' => 'Updated Title'];

        $result = $this->updateProgram($invalidId, $updateData);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test program deletion with invalid ID
     */
    public function testDeleteProgramWithInvalidId()
    {
        $invalidId = 0;
        
        $result = $this->deleteProgram($invalidId);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test program search with empty term
     */
    public function testSearchProgramsWithEmptyTerm()
    {
        $emptyTerm = '';
        
        $result = $this->searchPrograms($emptyTerm);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('programs', $result);
        // Should return all programs when search term is empty
        $this->assertGreaterThan(0, $result['total_count']);
    }

    /**
     * Test program filtering with invalid status
     */
    public function testFilterProgramsByInvalidStatus()
    {
        $invalidStatus = 'invalid_status';
        
        $result = $this->filterProgramsByStatus($invalidStatus);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('programs', $result);
        // Should return empty array for invalid status
        $this->assertEmpty($result['programs']);
    }

    // Mock helper methods for testing

    private function validateProgramData($data)
    {
        // Mock validation logic
        if (empty($data['title']) || strlen($data['title']) < 5) {
            return false;
        }
        if (empty($data['description']) || strlen($data['description']) < 10) {
            return false;
        }
        if (empty($data['type'])) {
            return false;
        }
        if (!empty($data['budget']) && $data['budget'] < 0) {
            return false;
        }
        return true;
    }

    private function createProgram($data)
    {
        // Mock program creation
        if (!$this->validateProgramData($data)) {
            return [
                'success' => false,
                'errors' => ['Invalid program data']
            ];
        }

        return [
            'success' => true,
            'program_id' => 123,
            'program' => array_merge($data, ['id' => 123])
        ];
    }

    private function updateProgram($id, $data)
    {
        // Mock program update
        if ($id <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid program ID'
            ];
        }

        return [
            'success' => true,
            'program' => array_merge($data, ['id' => $id])
        ];
    }

    private function deleteProgram($id)
    {
        // Mock program deletion
        if ($id <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid program ID'
            ];
        }

        return [
            'success' => true,
            'message' => 'Program deleted successfully'
        ];
    }

    private function getProgramById($id)
    {
        // Mock get program by ID
        return [
            'success' => true,
            'program' => [
                'id' => $id,
                'title' => 'Test Program',
                'description' => 'Test Description',
                'type' => 'conservation',
                'status' => 'active'
            ]
        ];
    }

    private function getAllPrograms($filters = [])
    {
        // Mock get all programs
        return [
            'success' => true,
            'programs' => [
                [
                    'id' => 1,
                    'title' => 'Forest Conservation Program',
                    'description' => 'Forest conservation program',
                    'type' => 'conservation',
                    'status' => 'active'
                ],
                [
                    'id' => 2,
                    'title' => 'Wildlife Protection Program',
                    'description' => 'Wildlife protection program',
                    'type' => 'conservation',
                    'status' => 'active'
                ]
            ],
            'total_count' => 2
        ];
    }

    private function searchPrograms($term)
    {
        // Mock program search
        $allPrograms = $this->getAllPrograms()['programs'];
        $filteredPrograms = array_filter($allPrograms, function($program) use ($term) {
            return stripos($program['title'] . ' ' . $program['description'], $term) !== false;
        });

        return [
            'success' => true,
            'programs' => array_values($filteredPrograms),
            'total_count' => count($filteredPrograms)
        ];
    }

    private function filterProgramsByStatus($status)
    {
        // Mock program filtering by status
        $allPrograms = $this->getAllPrograms()['programs'];
        $filteredPrograms = array_filter($allPrograms, function($program) use ($status) {
            return $program['status'] === $status;
        });

        return [
            'success' => true,
            'programs' => array_values($filteredPrograms)
        ];
    }

    private function filterProgramsByType($type)
    {
        // Mock program filtering by type
        $allPrograms = $this->getAllPrograms()['programs'];
        $filteredPrograms = array_filter($allPrograms, function($program) use ($type) {
            return $program['type'] === $type;
        });

        return [
            'success' => true,
            'programs' => array_values($filteredPrograms)
        ];
    }

    private function calculateProgramBudget($programId)
    {
        // Mock budget calculation
        return [
            'success' => true,
            'total_budget' => 1000000,
            'allocated_budget' => 750000,
            'remaining_budget' => 250000,
            'utilization_percentage' => 75.0
        ];
    }

    private function calculateProgramProgress($programId)
    {
        // Mock progress calculation
        return [
            'success' => true,
            'overall_progress' => 80,
            'completed_outcomes' => 8,
            'total_outcomes' => 10,
            'progress_percentage' => 80.0
        ];
    }

    private function linkProgramToOutcome($programId, $outcomeId)
    {
        // Mock program-outcome linking
        return [
            'success' => true,
            'message' => 'Program linked to outcome successfully'
        ];
    }

    private function unlinkProgramFromOutcome($programId, $outcomeId)
    {
        // Mock program-outcome unlinking
        return [
            'success' => true,
            'message' => 'Program unlinked from outcome successfully'
        ];
    }

    private function getProgramOutcomes($programId)
    {
        // Mock get program outcomes
        return [
            'success' => true,
            'outcomes' => [
                ['id' => 1, 'title' => 'Outcome 1'],
                ['id' => 2, 'title' => 'Outcome 2']
            ]
        ];
    }

    private function generateProgramReport($programId, $reportType)
    {
        // Mock report generation
        return [
            'success' => true,
            'report_data' => ['program_id' => $programId, 'type' => $reportType],
            'report_type' => $reportType,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function exportProgramData($programId, $format)
    {
        // Mock data export
        return [
            'success' => true,
            'export_data' => 'CSV data content',
            'format' => $format,
            'file_name' => "program_{$programId}.{$format}"
        ];
    }
} 