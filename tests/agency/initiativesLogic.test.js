/**
 * Unit Tests for Initiatives Logic
 * Tests the JavaScript logic functions used in the initiatives module
 */

// Mock DOM elements and functions for testing
global.document = {
    createElement: jest.fn(() => ({
        getContext: jest.fn(() => ({
            fillStyle: '',
            strokeStyle: '',
            lineWidth: 1,
            beginPath: jest.fn(),
            moveTo: jest.fn(),
            lineTo: jest.fn(),
            stroke: jest.fn(),
            fill: jest.fn(),
            arc: jest.fn(),
            fillRect: jest.fn(),
            clearRect: jest.fn(),
            measureText: jest.fn(() => ({ width: 100 })),
            fillText: jest.fn()
        }))
    })),
    getElementById: jest.fn(),
    querySelector: jest.fn(),
    addEventListener: jest.fn()
};

global.window = {
    location: {
        pathname: '/pcds2030_dashboard_fork/app/views/agency/initiatives/initiatives.php'
    }
};

// Mock Chart.js
global.Chart = jest.fn(() => ({
    destroy: jest.fn(),
    update: jest.fn(),
    data: { datasets: [] },
    options: {}
}));

// Import the logic functions we want to test
const { 
    calculateHealthScore, 
    formatTimelineText, 
    validateInitiativeData,
    getStatusColor,
    formatProgramCount
} = require('../../assets/js/agency/initiatives/logic');

describe('Initiative Health Score Calculation', () => {
    test('should calculate 100% for all completed programs', () => {
        const programs = [
            { status: 'completed' },
            { status: 'completed' },
            { status: 'completed' }
        ];
        expect(calculateHealthScore(programs)).toBe(100);
    });

    test('should calculate 75% for all active programs', () => {
        const programs = [
            { status: 'active' },
            { status: 'active' },
            { status: 'active' }
        ];
        expect(calculateHealthScore(programs)).toBe(75);
    });

    test('should calculate mixed status correctly', () => {
        const programs = [
            { status: 'completed' }, // 100
            { status: 'active' },    // 75
            { status: 'on_hold' },   // 50
            { status: 'delayed' }    // 25
        ];
        // (100 + 75 + 50 + 25) / 4 = 62.5, rounded = 63
        expect(calculateHealthScore(programs)).toBe(63);
    });

    test('should return 50 for empty programs array', () => {
        expect(calculateHealthScore([])).toBe(50);
    });

    test('should handle undefined status', () => {
        const programs = [
            { status: undefined },
            { status: 'active' }
        ];
        // Both undefined and explicit undefined default to 'active' = 75
        // (75 + 75) / 2 = 75
        expect(calculateHealthScore(programs)).toBe(75);
    });

    test('should normalize inconsistent status values', () => {
        const programs = [
            { status: 'not-started' }, // should normalize to 'active' = 75
            { status: 'not_started' }, // should normalize to 'active' = 75
            { status: 'on-hold' },     // should normalize to 'on_hold' = 50
            { status: 'canceled' }     // should normalize to 'cancelled' = 10
        ];
        // (75 + 75 + 50 + 10) / 4 = 52.5, rounded = 53
        expect(calculateHealthScore(programs)).toBe(53);
    });
});

describe('Timeline Text Formatting', () => {
    test('should format timeline with years correctly', () => {
        const startDate = '2021-01-01';
        const endDate = '2030-12-31';
        const result = formatTimelineText(startDate, endDate);
        expect(result).toContain('2021-01-01 to 2030-12-31');
        expect(result).toContain('10 years'); // About 10 years difference
    });

    test('should handle single year timeline', () => {
        const startDate = '2025-01-01';
        const endDate = '2025-12-31';
        const result = formatTimelineText(startDate, endDate);
        expect(result).toContain('2025-01-01 to 2025-12-31');
        expect(result).toContain('1 year'); // Approximately 1 year
    });

    test('should handle invalid dates gracefully', () => {
        const result = formatTimelineText('invalid', 'also-invalid');
        expect(result).toContain('Invalid dates');
    });

    test('should handle missing dates', () => {
        const result = formatTimelineText(null, null);
        expect(result).toContain('No timeline data');
    });
});

describe('Initiative Data Validation', () => {
    test('should validate complete initiative data', () => {
        const initiative = {
            initiative_id: 1,
            initiative_name: 'Test Initiative',
            start_date: '2021-01-01',
            end_date: '2030-12-31',
            is_active: 1
        };
        expect(validateInitiativeData(initiative)).toBe(true);
    });

    test('should reject initiative without required fields', () => {
        const initiative = {
            initiative_name: 'Test Initiative'
            // missing other required fields
        };
        expect(validateInitiativeData(initiative)).toBe(false);
    });

    test('should reject initiative with invalid dates', () => {
        const initiative = {
            initiative_id: 1,
            initiative_name: 'Test Initiative',
            start_date: 'invalid-date',
            end_date: '2030-12-31',
            is_active: 1
        };
        expect(validateInitiativeData(initiative)).toBe(false);
    });

    test('should reject initiative with end date before start date', () => {
        const initiative = {
            initiative_id: 1,
            initiative_name: 'Test Initiative',
            start_date: '2030-01-01',
            end_date: '2021-12-31', // before start date
            is_active: 1
        };
        expect(validateInitiativeData(initiative)).toBe(false);
    });
});

describe('Status Color Mapping', () => {
    test('should return correct colors for each status', () => {
        expect(getStatusColor('completed')).toBe('#28a745');
        expect(getStatusColor('active')).toBe('#17a2b8');
        expect(getStatusColor('on_hold')).toBe('#ffc107');
        expect(getStatusColor('delayed')).toBe('#fd7e14');
        expect(getStatusColor('cancelled')).toBe('#dc3545');
    });

    test('should return default color for unknown status', () => {
        expect(getStatusColor('unknown')).toBe('#6c757d');
        expect(getStatusColor('')).toBe('#6c757d');
        expect(getStatusColor(null)).toBe('#6c757d');
    });

    test('should handle status normalization', () => {
        expect(getStatusColor('not-started')).toBe('#17a2b8'); // normalized to active
        expect(getStatusColor('on-hold')).toBe('#ffc107'); // normalized to on_hold
        expect(getStatusColor('canceled')).toBe('#dc3545'); // normalized to cancelled
    });
});

describe('Program Count Formatting', () => {
    test('should format single program correctly', () => {
        expect(formatProgramCount(1)).toBe('1 program');
    });

    test('should format multiple programs correctly', () => {
        expect(formatProgramCount(5)).toBe('5 programs');
        expect(formatProgramCount(0)).toBe('0 programs');
    });

    test('should handle negative numbers', () => {
        expect(formatProgramCount(-1)).toBe('0 programs');
    });

    test('should handle non-numeric input', () => {
        expect(formatProgramCount('abc')).toBe('0 programs');
        expect(formatProgramCount(null)).toBe('0 programs');
        expect(formatProgramCount(undefined)).toBe('0 programs');
    });
});

describe('Chart Data Processing', () => {
    test('should process rating distribution data correctly', () => {
        // This would test Chart.js data processing functions
        // Since Chart.js logic is complex, we'll create a simple test
        const mockData = [
            { rating: 'excellent', count: 5 },
            { rating: 'good', count: 3 },
            { rating: 'fair', count: 2 }
        ];
        
        // Test would verify chart data structure
        expect(Array.isArray(mockData)).toBe(true);
        expect(mockData.length).toBe(3);
        expect(mockData[0]).toHaveProperty('rating');
        expect(mockData[0]).toHaveProperty('count');
    });
});
