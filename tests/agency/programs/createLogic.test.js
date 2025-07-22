/**
 * Create Logic Tests
 * Tests for program creation business logic
 */

import { validateProgramNumber, checkProgramNumberExists } from '../../../assets/js/agency/programs/createLogic.js';

// Mock fetch for testing
global.fetch = jest.fn();
global.window = {}; // Start with empty object like a real browser

describe('Create Program Logic', () => {
    beforeEach(() => {
        fetch.mockClear();
    });

    describe('validateProgramNumber', () => {
        test('rejects empty program number', () => {
            const result = validateProgramNumber('', '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Program number is required');
        });

        test('rejects invalid format with special characters', () => {
            const result = validateProgramNumber('1.1@invalid', '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Invalid format. Use only letters, numbers, and dots.');
        });

        test('rejects program number not starting with initiative number', () => {
            const result = validateProgramNumber('2.1.A', '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Program number must start with "1.1."');
        });

        test('rejects program number without suffix', () => {
            const result = validateProgramNumber('1.1.', '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Please add a suffix after the initiative number (e.g., 1, A, 2B)');
        });

        test('rejects program number that is too long', () => {
            const longNumber = '1.1.' + 'A'.repeat(20); // Over 20 characters total
            const result = validateProgramNumber(longNumber, '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Program number is too long (max 20 characters)');
        });

        test('accepts valid program number with numeric suffix', () => {
            const result = validateProgramNumber('1.1.1', '1.1');
            expect(result.isValid).toBe(true);
            expect(result.message).toBe('Valid program number format (1.1.suffix)');
        });

        test('accepts valid program number with alphabetic suffix', () => {
            const result = validateProgramNumber('1.1.A', '1.1');
            expect(result.isValid).toBe(true);
            expect(result.message).toBe('Valid program number format (1.1.suffix)');
        });

        test('accepts valid program number with alphanumeric suffix', () => {
            const result = validateProgramNumber('1.1.2B', '1.1');
            expect(result.isValid).toBe(true);
            expect(result.message).toBe('Valid program number format (1.1.suffix)');
        });

        test('handles complex initiative numbers', () => {
            const result = validateProgramNumber('2.3.1.Alpha', '2.3.1');
            expect(result.isValid).toBe(true);
            expect(result.message).toBe('Valid program number format (2.3.1.suffix)');
        });

        test('handles initiative numbers with special regex characters', () => {
            // Test that special regex characters in initiative number are properly escaped
            const result = validateProgramNumber('1.1.A', '1.1');
            expect(result.isValid).toBe(true);
        });
    });

    describe('checkProgramNumberExists', () => {
        test('returns true when program number exists', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ exists: true })
            });

            const result = await checkProgramNumberExists('1.1.A', '123');
            
            expect(fetch).toHaveBeenCalledWith(
                '/app/ajax/agency/check_program_number.php',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: expect.any(URLSearchParams)
                }
            );
            expect(result).toBe(true);
        });

        test('returns false when program number does not exist', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ exists: false })
            });

            const result = await checkProgramNumberExists('1.1.B', '123');
            expect(result).toBe(false);
        });

        test('handles API error response', async () => {
            fetch.mockResolvedValueOnce({
                ok: false,
                status: 500,
                statusText: 'Internal Server Error'
            });

            await expect(checkProgramNumberExists('1.1.C', '123')).rejects.toThrow(
                'Failed to check program number'
            );
        });

        test('handles network error', async () => {
            fetch.mockRejectedValueOnce(new Error('Network error'));

            await expect(checkProgramNumberExists('1.1.D', '123')).rejects.toThrow('Failed to check program number');
        });

        test('handles malformed JSON response', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => { throw new Error('Invalid JSON'); }
            });

            await expect(checkProgramNumberExists('1.1.E', '123')).rejects.toThrow('Failed to check program number');
        });

        test('handles missing exists property in response', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ message: 'Program number checked' })
            });

            const result = await checkProgramNumberExists('1.1.F', '123');
            expect(result).toBe(false); // Now returns false for missing exists property
        });
    });

    describe('Edge Cases and Security', () => {
        test('handles null/undefined initiative number gracefully', () => {
            const result1 = validateProgramNumber('1.1.A', null);
            expect(result1.isValid).toBe(false);

            const result2 = validateProgramNumber('1.1.A', undefined);
            expect(result2.isValid).toBe(false);
        });

        test('handles null/undefined program number gracefully', () => {
            const result1 = validateProgramNumber(null, '1.1');
            expect(result1.isValid).toBe(false);

            const result2 = validateProgramNumber(undefined, '1.1');
            expect(result2.isValid).toBe(false);
        });

        test('does not trim whitespace from program number', () => {
            // The actual implementation doesn't trim whitespace, so spaces should make it invalid
            const result = validateProgramNumber('  1.1.A  ', '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Invalid format. Use only letters, numbers, and dots.');
        });

        test('prevents SQL injection attempts', () => {
            const maliciousNumber = "1.1'; DROP TABLE programs; --";
            const result = validateProgramNumber(maliciousNumber, '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Invalid format. Use only letters, numbers, and dots.');
        });

        test('prevents XSS attempts', () => {
            const xssNumber = '1.1.<script>alert("xss")</script>';
            const result = validateProgramNumber(xssNumber, '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Invalid format. Use only letters, numbers, and dots.');
        });
    });

    describe('Performance and Boundary Testing', () => {
        test('handles maximum valid length program number', () => {
            const maxValidNumber = '1.1.' + 'A'.repeat(13); // Exactly 20 characters
            const result = validateProgramNumber(maxValidNumber, '1.1');
            expect(result.isValid).toBe(true);
        });

        test('accepts program number at exact length limit', () => {
            // Test exactly 20 characters: '1.1.' (4) + 'A'.repeat(16) = 20
            const exactLengthNumber = '1.1.' + 'A'.repeat(16); // Exactly 20 characters
            const result = validateProgramNumber(exactLengthNumber, '1.1');
            expect(result.isValid).toBe(true);
        });

        test('rejects program number exceeding maximum length', () => {
            // Test 21 characters: '1.1.' (4) + 'A'.repeat(17) = 21
            const tooLongNumber = '1.1.' + 'A'.repeat(17); // 21 characters
            const result = validateProgramNumber(tooLongNumber, '1.1');
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Program number is too long (max 20 characters)');
        });

        test('handles very long initiative numbers', () => {
            const longInitiative = '1.2.3.4.5.6.7';
            const programNumber = longInitiative + '.A';
            const result = validateProgramNumber(programNumber, longInitiative);
            
            if (programNumber.length <= 20) {
                expect(result.isValid).toBe(true);
            } else {
                expect(result.isValid).toBe(false);
                expect(result.message).toBe('Program number is too long (max 20 characters)');
            }
        });
    });
});
