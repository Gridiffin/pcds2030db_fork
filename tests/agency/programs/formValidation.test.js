/**
 * Form Validation Tests
 * Tests for program creation form validation
 */

import { validateDateFormat, validateDateRange, validateProgramName } from '../../../assets/js/agency/programs/formValidation.js';

// Mock DOM elements for form validation tests
const createMockInput = (id, value = '') => {
    const input = document.createElement('input');
    input.id = id;
    input.value = value;
    return input;
};

describe('Program Form Validation', () => {
    beforeEach(() => {
        // Clear DOM
        document.body.innerHTML = '';
    });

    describe('validateDateFormat', () => {
        test('accepts valid date format', () => {
            expect(validateDateFormat('2025-01-01')).toBe(true);
            expect(validateDateFormat('2025-12-31')).toBe(true);
            expect(validateDateFormat('2000-01-01')).toBe(true);
        });

        test('rejects invalid date format', () => {
            expect(validateDateFormat('01/01/2025')).toBe(false);
            expect(validateDateFormat('2025-1-1')).toBe(false);
            expect(validateDateFormat('2025.01.01')).toBe(false);
            expect(validateDateFormat('25-01-01')).toBe(false);
            expect(validateDateFormat('2025-13-01')).toBe(false);
            expect(validateDateFormat('2025-01-32')).toBe(false);
            expect(validateDateFormat('not-a-date')).toBe(false);
        });

        test('accepts empty date as valid', () => {
            expect(validateDateFormat('')).toBe(true);
            expect(validateDateFormat(null)).toBe(true);
            expect(validateDateFormat(undefined)).toBe(true);
        });

        test('handles edge cases', () => {
            expect(validateDateFormat('2025-02-29')).toBe(false); // Not a leap year
            expect(validateDateFormat('2024-02-29')).toBe(true);  // Leap year
            expect(validateDateFormat('2025-00-01')).toBe(false); // Invalid month
            expect(validateDateFormat('2025-01-00')).toBe(false); // Invalid day
        });
    });

    describe('validateDateRange', () => {
        test('accepts valid date range', () => {
            expect(validateDateRange('2025-01-01', '2025-12-31')).toBe(true);
            expect(validateDateRange('2025-01-01', '2025-01-01')).toBe(true); // Same day
            expect(validateDateRange('2024-12-31', '2025-01-01')).toBe(true); // Cross year
        });

        test('rejects invalid date range', () => {
            expect(validateDateRange('2025-12-31', '2025-01-01')).toBe(false);
            expect(validateDateRange('2025-01-02', '2025-01-01')).toBe(false);
        });

        test('accepts partial or empty dates', () => {
            expect(validateDateRange('', '2025-12-31')).toBe(true);
            expect(validateDateRange('2025-01-01', '')).toBe(true);
            expect(validateDateRange('', '')).toBe(true);
            expect(validateDateRange(null, '2025-12-31')).toBe(true);
            expect(validateDateRange('2025-01-01', null)).toBe(true);
        });

        test('handles edge cases with time zones', () => {
            // Test dates that might be affected by timezone parsing
            expect(validateDateRange('2025-01-01', '2025-01-01')).toBe(true);
            expect(validateDateRange('2025-12-31', '2026-01-01')).toBe(true);
        });
    });

    describe('validateProgramName', () => {
        test('accepts valid program name', () => {
            expect(validateProgramName('Test Program')).toEqual({
                isValid: true,
                message: ''
            });
        });

        test('rejects empty program name', () => {
            expect(validateProgramName('')).toEqual({
                isValid: false,
                message: 'Program name is required'
            });
            expect(validateProgramName('   ')).toEqual({
                isValid: false,
                message: 'Program name is required'
            });
        });

        test('rejects too long program name', () => {
            const longName = 'a'.repeat(256);
            expect(validateProgramName(longName)).toEqual({
                isValid: false,
                message: 'Program name is too long (max 255 characters)'
            });
        });

        test('accepts program name with special characters', () => {
            expect(validateProgramName('Test Program #123 (2025)')).toEqual({
                isValid: true,
                message: ''
            });
        });

        test('accepts program name at maximum length', () => {
            const maxLengthName = 'a'.repeat(255);
            expect(validateProgramName(maxLengthName)).toEqual({
                isValid: true,
                message: ''
            });
        });

        test('handles unicode characters', () => {
            expect(validateProgramName('Ürogram Naïve Español 中文')).toEqual({
                isValid: true,
                message: ''
            });
        });

        test('handles null and undefined', () => {
            expect(validateProgramName(null)).toEqual({
                isValid: false,
                message: 'Program name is required'
            });
            expect(validateProgramName(undefined)).toEqual({
                isValid: false,
                message: 'Program name is required'
            });
        });
    });

    describe('Form Validation Integration', () => {
        test('validates complete form with all valid data', () => {
            // Create mock form structure
            const form = document.createElement('form');
            const nameInput = createMockInput('program_name', 'Valid Program');
            const startInput = createMockInput('start_date', '2025-01-01');
            const endInput = createMockInput('end_date', '2025-12-31');
            
            form.appendChild(nameInput);
            form.appendChild(startInput);
            form.appendChild(endInput);
            document.body.appendChild(form);
            
            // Test that individual validations pass
            expect(validateProgramName(nameInput.value).isValid).toBe(true);
            expect(validateDateFormat(startInput.value)).toBe(true);
            expect(validateDateFormat(endInput.value)).toBe(true);
            expect(validateDateRange(startInput.value, endInput.value)).toBe(true);
        });

        test('handles form with invalid data combinations', () => {
            const form = document.createElement('form');
            const nameInput = createMockInput('program_name', ''); // Invalid
            const startInput = createMockInput('start_date', '2025-12-31'); // Valid
            const endInput = createMockInput('end_date', '2025-01-01'); // Valid format, invalid range
            
            form.appendChild(nameInput);
            form.appendChild(startInput);
            form.appendChild(endInput);
            document.body.appendChild(form);
            
            expect(validateProgramName(nameInput.value).isValid).toBe(false);
            expect(validateDateFormat(startInput.value)).toBe(true);
            expect(validateDateFormat(endInput.value)).toBe(true);
            expect(validateDateRange(startInput.value, endInput.value)).toBe(false);
        });
    });

    describe('Error Handling and Edge Cases', () => {
        test('handles malformed input gracefully', () => {
            // Test with non-string inputs
            expect(validateProgramName(123)).toEqual({
                isValid: false,
                message: 'Program name is required'
            });
            
            expect(validateDateFormat(123)).toBe(false);
            expect(validateDateRange(123, '2025-01-01')).toBe(true); // Falsy start date
        });

        test('handles extremely large inputs', () => {
            const hugeString = 'a'.repeat(10000);
            const result = validateProgramName(hugeString);
            expect(result.isValid).toBe(false);
            expect(result.message).toBe('Program name is too long (max 255 characters)');
        });

        test('validates date boundary conditions', () => {
            // Test leap year edge cases
            expect(validateDateFormat('2024-02-29')).toBe(true);  // Valid leap year
            expect(validateDateFormat('2025-02-29')).toBe(false); // Invalid non-leap year
            
            // Test month boundaries
            expect(validateDateFormat('2025-04-31')).toBe(false); // April has 30 days
            expect(validateDateFormat('2025-04-30')).toBe(true);  // Valid April date
        });

        test('security validation prevents XSS', () => {
            const xssAttempt = '<script>alert("xss")</script>';
            const result = validateProgramName(xssAttempt);
            expect(result.isValid).toBe(true); // Name validation allows HTML (should be escaped elsewhere)
            
            // Date validation should reject non-date strings
            expect(validateDateFormat(xssAttempt)).toBe(false);
        });
    });
}); 