/**
 * Form Validation Tests
 * Tests for program creation form validation
 */

import { validateDateFormat, validateDateRange, validateProgramName } from '../../../assets/js/agency/programs/formValidation.js';

describe('Program Form Validation', () => {
    describe('validateDateFormat', () => {
        test('accepts valid date format', () => {
            expect(validateDateFormat('2025-01-01')).toBe(true);
        });

        test('rejects invalid date format', () => {
            expect(validateDateFormat('01/01/2025')).toBe(false);
            expect(validateDateFormat('2025-1-1')).toBe(false);
            expect(validateDateFormat('2025.01.01')).toBe(false);
        });

        test('accepts empty date as valid', () => {
            expect(validateDateFormat('')).toBe(true);
            expect(validateDateFormat(null)).toBe(true);
            expect(validateDateFormat(undefined)).toBe(true);
        });
    });

    describe('validateDateRange', () => {
        test('accepts valid date range', () => {
            expect(validateDateRange('2025-01-01', '2025-12-31')).toBe(true);
            expect(validateDateRange('2025-01-01', '2025-01-01')).toBe(true); // Same day
        });

        test('rejects invalid date range', () => {
            expect(validateDateRange('2025-12-31', '2025-01-01')).toBe(false);
        });

        test('accepts partial or empty dates', () => {
            expect(validateDateRange('', '2025-12-31')).toBe(true);
            expect(validateDateRange('2025-01-01', '')).toBe(true);
            expect(validateDateRange('', '')).toBe(true);
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
    });
}); 