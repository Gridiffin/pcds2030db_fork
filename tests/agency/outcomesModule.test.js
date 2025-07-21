/**
 * Outcomes Module Unit Tests
 * Tests the main OutcomesModule class functionality
 */

// Mock fetch globally
global.fetch = jest.fn();

// Mock localStorage
const localStorageMock = (() => {
    let store = {};
    return {
        getItem: jest.fn(key => store[key] || null),
        setItem: jest.fn((key, value) => { store[key] = value.toString(); }),
        removeItem: jest.fn(key => { delete store[key]; }),
        clear: jest.fn(() => { store = {}; })
    };
})();

global.localStorage = localStorageMock;

// Mock DOM elements
const mockElement = {
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    querySelector: jest.fn(),
    querySelectorAll: jest.fn(() => []),
    classList: {
        add: jest.fn(),
        remove: jest.fn(),
        contains: jest.fn(() => false)
    },
    style: {},
    innerHTML: '',
    textContent: ''
};

global.document = {
    querySelector: jest.fn(() => mockElement),
    querySelectorAll: jest.fn(() => [mockElement]),
    createElement: jest.fn(() => mockElement),
    body: mockElement,
    addEventListener: jest.fn(),
    readyState: 'complete'
};

global.window = {
    location: { pathname: '/index.php?page=agency_outcomes_submit' },
    addEventListener: jest.fn(),
    alert: jest.fn(),
    console: { log: jest.fn(), error: jest.fn() }
};

// Import components to test
const OutcomesModule = require('../../assets/js/agency/outcomes/outcomes.js').default;

describe('OutcomesModule', () => {
    let outcomesModule;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
        
        // Reset DOM mocks
        global.document.querySelector.mockReturnValue(mockElement);
        global.document.querySelectorAll.mockReturnValue([mockElement]);
        
        // Create new instance
        outcomesModule = new OutcomesModule();
    });

    afterEach(() => {
        if (outcomesModule && typeof outcomesModule.cleanup === 'function') {
            outcomesModule.cleanup();
        }
    });

    describe('Initialization', () => {
        test('should create OutcomesModule instance correctly', () => {
            expect(outcomesModule).toBeDefined();
            expect(outcomesModule.moduleName).toBe('outcomes');
        });

        test('should initialize common functionality', () => {
            const initCommonSpy = jest.spyOn(outcomesModule, 'initCommon');
            
            outcomesModule.init();
            
            expect(initCommonSpy).toHaveBeenCalled();
        });

        test('should detect page type correctly', () => {
            // Test submit page detection
            global.window.location.pathname = '/index.php?page=agency_outcomes_submit';
            expect(outcomesModule.getPageType()).toBe('submit');
            
            // Test view page detection
            global.window.location.pathname = '/index.php?page=agency_outcomes_view';
            expect(outcomesModule.getPageType()).toBe('view');
            
            // Test edit page detection
            global.window.location.pathname = '/index.php?page=agency_outcomes_edit';
            expect(outcomesModule.getPageType()).toBe('edit');
        });
    });

    describe('Page-specific Initialization', () => {
        test('should initialize submit page correctly', () => {
            global.window.location.pathname = '/index.php?page=agency_outcomes_submit';
            
            const initSubmitPageSpy = jest.spyOn(outcomesModule, 'initSubmitPage');
            
            outcomesModule.init();
            
            expect(initSubmitPageSpy).toHaveBeenCalled();
        });

        test('should initialize view page correctly', () => {
            global.window.location.pathname = '/index.php?page=agency_outcomes_view';
            
            const initViewPageSpy = jest.spyOn(outcomesModule, 'initViewPage');
            
            outcomesModule.init();
            
            expect(initViewPageSpy).toHaveBeenCalled();
        });

        test('should handle unknown page types gracefully', () => {
            global.window.location.pathname = '/index.php?page=unknown_page';
            
            expect(() => {
                outcomesModule.init();
            }).not.toThrow();
        });
    });

    describe('Common Functionality', () => {
        test('should initialize tooltips on common init', () => {
            // Mock tooltip elements
            const tooltipElements = [mockElement, mockElement];
            global.document.querySelectorAll.mockReturnValue(tooltipElements);
            
            outcomesModule.initCommon();
            
            expect(global.document.querySelectorAll).toHaveBeenCalledWith('[data-bs-toggle="tooltip"]');
        });

        test('should setup global event listeners', () => {
            outcomesModule.initCommon();
            
            expect(global.document.addEventListener).toHaveBeenCalled();
        });
    });

    describe('Module Management', () => {
        test('should create and manage sub-modules correctly', () => {
            outcomesModule.initSubmitPage();
            
            expect(outcomesModule.submitModule).toBeDefined();
        });

        test('should cleanup modules on destroy', () => {
            outcomesModule.initSubmitPage();
            
            const cleanupSpy = jest.fn();
            outcomesModule.submitModule = { cleanup: cleanupSpy };
            
            outcomesModule.cleanup();
            
            expect(cleanupSpy).toHaveBeenCalled();
        });
    });

    describe('Error Handling', () => {
        test('should handle initialization errors gracefully', () => {
            // Mock an error during initialization
            jest.spyOn(outcomesModule, 'initCommon').mockImplementation(() => {
                throw new Error('Test error');
            });
            
            expect(() => {
                outcomesModule.init();
            }).not.toThrow();
        });

        test('should log errors properly', () => {
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            // Trigger an error condition
            jest.spyOn(outcomesModule, 'initCommon').mockImplementation(() => {
                throw new Error('Test error');
            });
            
            outcomesModule.init();
            
            expect(consoleSpy).toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });
    });

    describe('Utility Methods', () => {
        test('should format dates correctly', () => {
            const testDate = '2024-01-15';
            const formatted = outcomesModule.formatDate(testDate);
            
            expect(formatted).toBeDefined();
            expect(typeof formatted).toBe('string');
        });

        test('should show alerts correctly', () => {
            const alertSpy = jest.spyOn(window, 'alert');
            
            outcomesModule.showAlert('Test message', 'success');
            
            // Should not throw and should handle the alert properly
            expect(() => {
                outcomesModule.showAlert('Test message', 'success');
            }).not.toThrow();
        });
    });
});
