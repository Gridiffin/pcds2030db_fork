<?php
/**
 * Program Validation Tests
 * Tests for program validation helper functions
 */

use PHPUnit\Framework\TestCase;

class ProgramValidationTest extends TestCase
{
    protected function setUp(): void
    {
        // Include the validation functions
        require_once PROJECT_ROOT_PATH . '/app/lib/agencies/program_validation.php';
    }

    public function testValidateProgramNameSuccess()
    {
        $result = validate_program_name('Valid Program Name');
        
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['message']);
    }

    public function testValidateProgramNameEmpty()
    {
        $result = validate_program_name('');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Program name is required', $result['message']);
    }

    public function testValidateProgramNameWhitespaceOnly()
    {
        $result = validate_program_name('   ');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Program name is required', $result['message']);
    }

    public function testValidateProgramNameTooLong()
    {
        $longName = str_repeat('a', 256);
        $result = validate_program_name($longName);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Program name is too long (max 255 characters)', $result['message']);
    }

    public function testValidateProgramNameMaxLength()
    {
        $maxLengthName = str_repeat('a', 255);
        $result = validate_program_name($maxLengthName);
        
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['message']);
    }

    public function testValidateProgramNameWithSpecialCharacters()
    {
        $nameWithSpecialChars = 'Program #123 (2025) - Test & Development';
        $result = validate_program_name($nameWithSpecialChars);
        
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['message']);
    }

    public function testValidateProgramNameWithUnicode()
    {
        $unicodeName = 'Üñíčødé Prögram Naïve 中文';
        $result = validate_program_name($unicodeName);
        
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['message']);
    }

    /**
     * @dataProvider programNumberValidationProvider
     */
    public function testValidateProgramNumber($number, $initiative, $expectedSuccess, $expectedMessage)
    {
        $result = validate_program_number($number, $initiative);
        
        $this->assertEquals($expectedSuccess, $result['success'], 
            "Failed for number: '$number', initiative: '$initiative'");
        
        if (!$expectedSuccess) {
            $this->assertStringContainsString($expectedMessage, $result['message']);
        }
    }

    public function programNumberValidationProvider()
    {
        return [
            // Valid cases
            ['1.1.A', '1.1', true, ''],
            ['1.1.1', '1.1', true, ''],
            ['1.1.2B', '1.1', true, ''],
            ['2.3.1.Alpha', '2.3.1', true, ''],
            ['', '1.1', true, ''], // Empty is allowed
            
            // Invalid format
            ['1.1@invalid', '1.1', false, 'Invalid format'],
            ['1.1.<script>', '1.1', false, 'Invalid format'],
            ['1.1.A B', '1.1', false, 'Invalid format'],
            ['1.1.A#', '1.1', false, 'Invalid format'],
            
            // Wrong prefix
            ['2.1.A', '1.1', false, 'must start with "1.1."'],
            ['1.2.A', '1.1', false, 'must start with "1.1."'],
            
            // No suffix
            ['1.1.', '1.1', false, 'Please add a suffix'],
            ['1.1', '1.1', false, 'Please add a suffix'],
            
            // Too long
            [str_repeat('1.1.', 10), '1.1', false, 'too long'],
        ];
    }

    public function testValidateProgramNumberMaxLength()
    {
        // Test exactly 20 characters
        $exactLength = '1.1.' . str_repeat('A', 13); // Total 20 chars
        $result = validate_program_number($exactLength, '1.1');
        $this->assertTrue($result['success']);
        
        // Test 21 characters (too long)
        $tooLong = '1.1.' . str_repeat('A', 14); // Total 21 chars
        $result = validate_program_number($tooLong, '1.1');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('too long', $result['message']);
    }

    public function testValidateProgramNumberWithComplexInitiative()
    {
        $complexInitiative = '2.3.1.4';
        $validNumber = $complexInitiative . '.Beta';
        
        $result = validate_program_number($validNumber, $complexInitiative);
        $this->assertTrue($result['success']);
    }

    public function testValidateProgramNumberRegexEscaping()
    {
        // Test that special regex characters in initiative are properly escaped
        $initiativeWithDots = '1.1';
        $validNumber = '1.1.A';
        
        $result = validate_program_number($validNumber, $initiativeWithDots);
        $this->assertTrue($result['success']);
    }

    /**
     * @dataProvider programDatesValidationProvider
     */
    public function testValidateProgramDates($startDate, $endDate, $expectedSuccess, $expectedMessage)
    {
        $result = validate_program_dates($startDate, $endDate);
        
        $this->assertEquals($expectedSuccess, $result['success'],
            "Failed for start: '$startDate', end: '$endDate'");
        
        if (!$expectedSuccess) {
            $this->assertStringContainsString($expectedMessage, $result['message']);
        }
    }

    public function programDatesValidationProvider()
    {
        return [
            // Valid cases
            [null, null, true, ''],
            ['', '', true, ''],
            ['2025-01-01', '2025-12-31', true, ''],
            ['2025-01-01', '2025-01-01', true, ''], // Same day
            ['2024-12-31', '2025-01-01', true, ''], // Cross year
            ['2025-01-01', null, true, ''], // Only start date
            [null, '2025-12-31', true, ''], // Only end date
            
            // Invalid format (assuming validate_program_dates checks format)
            ['invalid-date', '2025-12-31', false, 'Invalid date format'],
            ['2025-01-01', 'invalid-date', false, 'Invalid date format'],
            ['01/01/2025', '2025-12-31', false, 'Invalid date format'],
            
            // Invalid range
            ['2025-12-31', '2025-01-01', false, 'Start date must be before'],
            ['2025-01-02', '2025-01-01', false, 'Start date must be before'],
        ];
    }

    public function testValidateProgramDatesWithEdgeCases()
    {
        // Test leap year
        $result = validate_program_dates('2024-02-29', '2024-03-01');
        $this->assertTrue($result['success']);
        
        // Test invalid leap year date
        $result = validate_program_dates('2025-02-29', '2025-03-01');
        $this->assertFalse($result['success']);
    }

    public function testValidateProgramDatesTimezoneHandling()
    {
        // Test dates that might be affected by timezone parsing
        $result = validate_program_dates('2025-01-01', '2025-01-01');
        $this->assertTrue($result['success']);
        
        $result = validate_program_dates('2025-12-31', '2026-01-01');
        $this->assertTrue($result['success']);
    }

    // Test security concerns
    public function testValidateProgramNameSQLInjectionPrevention()
    {
        $maliciousName = "'; DROP TABLE programs; --";
        $result = validate_program_name($maliciousName);
        
        // Should accept the name (SQL injection protection should be in queries, not validation)
        $this->assertTrue($result['success']);
    }

    public function testValidateProgramNumberSQLInjectionPrevention()
    {
        $maliciousNumber = "1.1'; DROP TABLE programs; --";
        $result = validate_program_number($maliciousNumber, '1.1');
        
        // Should reject due to invalid format
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid format', $result['message']);
    }

    public function testValidateProgramNumberXSSPrevention()
    {
        $xssNumber = '1.1.<script>alert("xss")</script>';
        $result = validate_program_number($xssNumber, '1.1');
        
        // Should reject due to invalid format
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid format', $result['message']);
    }

    // Test null and undefined handling
    public function testValidateProgramNameNullHandling()
    {
        $result = validate_program_name(null);
        $this->assertFalse($result['success']);
        $this->assertEquals('Program name is required', $result['message']);
    }

    public function testValidateProgramNumberNullHandling()
    {
        $result = validate_program_number(null, '1.1');
        $this->assertTrue($result['success']); // Null is treated as empty string, which is valid
        
        $result = validate_program_number('1.1.A', null);
        $this->assertFalse($result['success']); // Should handle null initiative gracefully
    }

    // Test boundary conditions
    public function testValidateProgramNameBoundaryConditions()
    {
        // Test exactly 255 characters
        $exactLength = str_repeat('a', 255);
        $result = validate_program_name($exactLength);
        $this->assertTrue($result['success']);
        
        // Test 256 characters
        $tooLong = str_repeat('a', 256);
        $result = validate_program_name($tooLong);
        $this->assertFalse($result['success']);
    }

    // Test performance with large inputs
    public function testValidateProgramNamePerformance()
    {
        $start = microtime(true);
        
        // Test with very large input
        $hugeString = str_repeat('a', 10000);
        $result = validate_program_name($hugeString);
        
        $end = microtime(true);
        $executionTime = $end - $start;
        
        $this->assertLessThan(0.1, $executionTime, 'Validation should complete within 100ms');
        $this->assertFalse($result['success']); // Should reject due to length
    }

    // Test consistency
    public function testValidationConsistency()
    {
        $testCases = [
            'Valid Program Name',
            'Another Valid Name',
            'Program with Numbers 123',
            'Special Chars: !@#$%^&*()'
        ];
        
        foreach ($testCases as $name) {
            $result1 = validate_program_name($name);
            $result2 = validate_program_name($name);
            
            $this->assertEquals($result1, $result2, 
                "Validation should be consistent for: $name");
        }
    }
}
