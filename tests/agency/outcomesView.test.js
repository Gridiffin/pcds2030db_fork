/**
 * ViewOutcome Component Unit Tests
 * Tests the ViewOutcome class functionality for displaying outcome details
 */

// Mock fetch globally
global.fetch = jest.fn();

// Mock DOM elements
const mockElement = {
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    querySelector: jest.fn(),
    querySelectorAll: jest.fn(() => []),
    classList: {
        add: jest.fn(),
        remove: jest.fn(),
        contains: jest.fn(() => false),
        toggle: jest.fn()
    },
    style: { display: '' },
    innerHTML: '',
    textContent: '',
    value: '',
    checked: false
};

global.document = {
    querySelector: jest.fn(() => mockElement),
    querySelectorAll: jest.fn(() => [mockElement]),
    getElementById: jest.fn(() => mockElement),
    createElement: jest.fn(() => mockElement),
    body: mockElement
};

global.window = {
    location: { pathname: '/index.php?page=agency_outcomes_view&id=1' },
    console: { log: jest.fn(), error: jest.fn() }
};

// Import the component to test
const { ViewOutcome } = require('../../assets/js/agency/outcomes/view.js');

describe('ViewOutcome', () => {
    let viewOutcome;
    let mockOutcomeData;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
        
        // Mock outcome data
        mockOutcomeData = {
            id: 1,
            code: 'FDS_01',
            title: 'Forest Conservation',
            type: 'chart',
            data: {
                rows: [
                    { month: 'January', 2024: 1000, 2025: 1200 },
                    { month: 'February', 2024: 1100, 2025: 1300 }
                ],
                columns: ['2024', '2025']
            }
        };
        
        // Setup DOM mocks
        global.document.querySelector.mockReturnValue(mockElement);
        global.document.querySelectorAll.mockReturnValue([mockElement]);
        global.document.getElementById.mockReturnValue(mockElement);
        
        // Create new instance
        viewOutcome = new ViewOutcome();
    });

    afterEach(() => {
        if (viewOutcome && typeof viewOutcome.cleanup === 'function') {
            viewOutcome.cleanup();
        }
    });

    describe('Initialization', () => {
        test('should create ViewOutcome instance correctly', () => {
            expect(viewOutcome).toBeDefined();
            expect(viewOutcome.outcomeId).toBeDefined();
            expect(viewOutcome.outcomeData).toBeNull();
        });

        test('should extract outcome ID from URL', () => {
            global.window.location.search = '?page=agency_outcomes_view&id=123';
            
            const newInstance = new ViewOutcome();
            
            expect(newInstance.outcomeId).toBe('123');
        });

        test('should setup event listeners on init', () => {
            const addEventListenerSpy = jest.spyOn(mockElement, 'addEventListener');
            
            viewOutcome.init();
            
            expect(addEventListenerSpy).toHaveBeenCalled();
        });
    });

    describe('Data Loading', () => {
        test('should load outcome data successfully', async () => {
            // Mock successful fetch response
            global.fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({
                    success: true,
                    data: mockOutcomeData
                })
            });
            
            await viewOutcome.loadOutcomeData();
            
            expect(global.fetch).toHaveBeenCalledWith(
                expect.stringContaining('/app/ajax/get_outcome_data.php'),
                expect.objectContaining({
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                })
            );
            expect(viewOutcome.outcomeData).toEqual(mockOutcomeData);
        });

        test('should handle data loading errors', async () => {
            // Mock failed fetch response
            global.fetch.mockResolvedValueOnce({
                ok: false,
                status: 404
            });
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            await viewOutcome.loadOutcomeData();
            
            expect(viewOutcome.outcomeData).toBeNull();
            expect(consoleSpy).toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });

        test('should handle network errors', async () => {
            // Mock network error
            global.fetch.mockRejectedValueOnce(new Error('Network error'));
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            await viewOutcome.loadOutcomeData();
            
            expect(viewOutcome.outcomeData).toBeNull();
            expect(consoleSpy).toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });
    });

    describe('Data Rendering', () => {
        beforeEach(() => {
            viewOutcome.outcomeData = mockOutcomeData;
        });

        test('should render chart data correctly', () => {
            const chartContainer = mockElement;
            global.document.querySelector.mockReturnValue(chartContainer);
            
            viewOutcome.renderOutcomeData();
            
            expect(chartContainer.innerHTML).toBeDefined();
        });

        test('should render KPI data correctly', () => {
            viewOutcome.outcomeData.type = 'kpi';
            viewOutcome.outcomeData.data = {
                value: 85,
                unit: '%',
                target: 90
            };
            
            const kpiContainer = mockElement;
            global.document.querySelector.mockReturnValue(kpiContainer);
            
            viewOutcome.renderOutcomeData();
            
            expect(kpiContainer.innerHTML).toBeDefined();
        });

        test('should handle empty data gracefully', () => {
            viewOutcome.outcomeData.data = null;
            
            expect(() => {
                viewOutcome.renderOutcomeData();
            }).not.toThrow();
        });

        test('should show loading state during render', () => {
            const loadingElement = mockElement;
            global.document.querySelector.mockReturnValue(loadingElement);
            
            viewOutcome.showLoading();
            
            expect(loadingElement.style.display).toBeDefined();
        });

        test('should hide loading state after render', () => {
            const loadingElement = mockElement;
            global.document.querySelector.mockReturnValue(loadingElement);
            
            viewOutcome.hideLoading();
            
            expect(loadingElement.style.display).toBeDefined();
        });
    });

    describe('Chart Management', () => {
        beforeEach(() => {
            viewOutcome.outcomeData = mockOutcomeData;
        });

        test('should create chart instance correctly', () => {
            const chartCanvas = mockElement;
            chartCanvas.getContext = jest.fn(() => ({}));
            global.document.querySelector.mockReturnValue(chartCanvas);
            
            viewOutcome.createChart();
            
            expect(chartCanvas.getContext).toHaveBeenCalledWith('2d');
        });

        test('should handle chart creation errors', () => {
            const chartCanvas = mockElement;
            chartCanvas.getContext = jest.fn(() => {
                throw new Error('Canvas error');
            });
            global.document.querySelector.mockReturnValue(chartCanvas);
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            expect(() => {
                viewOutcome.createChart();
            }).not.toThrow();
            
            expect(consoleSpy).toHaveBeenCalled();
            consoleSpy.mockRestore();
        });

        test('should update chart data correctly', () => {
            // Mock existing chart
            viewOutcome.chart = {
                data: { datasets: [{ data: [] }] },
                update: jest.fn()
            };
            
            const newData = [1500, 1600];
            viewOutcome.updateChartData(newData);
            
            expect(viewOutcome.chart.data.datasets[0].data).toEqual(newData);
            expect(viewOutcome.chart.update).toHaveBeenCalled();
        });
    });

    describe('User Interactions', () => {
        test('should handle data export correctly', () => {
            viewOutcome.outcomeData = mockOutcomeData;
            
            // Mock download functionality
            const mockLink = {
                href: '',
                download: '',
                click: jest.fn()
            };
            global.document.createElement.mockReturnValue(mockLink);
            
            viewOutcome.exportData('csv');
            
            expect(mockLink.click).toHaveBeenCalled();
        });

        test('should handle print functionality', () => {
            global.window.print = jest.fn();
            
            viewOutcome.printOutcome();
            
            expect(global.window.print).toHaveBeenCalled();
        });

        test('should handle refresh button click', async () => {
            const refreshButton = mockElement;
            global.document.querySelector.mockReturnValue(refreshButton);
            
            const loadDataSpy = jest.spyOn(viewOutcome, 'loadOutcomeData').mockResolvedValue();
            
            await viewOutcome.refreshData();
            
            expect(loadDataSpy).toHaveBeenCalled();
        });
    });

    describe('Error Handling', () => {
        test('should show error messages appropriately', () => {
            const errorContainer = mockElement;
            global.document.querySelector.mockReturnValue(errorContainer);
            
            viewOutcome.showError('Test error message');
            
            expect(errorContainer.textContent).toBeDefined();
            expect(errorContainer.style.display).toBeDefined();
        });

        test('should hide error messages', () => {
            const errorContainer = mockElement;
            global.document.querySelector.mockReturnValue(errorContainer);
            
            viewOutcome.hideError();
            
            expect(errorContainer.style.display).toBeDefined();
        });

        test('should handle missing outcome ID', () => {
            viewOutcome.outcomeId = null;
            
            expect(() => {
                viewOutcome.init();
            }).not.toThrow();
        });
    });

    describe('Cleanup', () => {
        test('should cleanup event listeners and resources', () => {
            const removeEventListenerSpy = jest.spyOn(mockElement, 'removeEventListener');
            
            viewOutcome.cleanup();
            
            expect(removeEventListenerSpy).toHaveBeenCalled();
        });

        test('should destroy chart instance on cleanup', () => {
            viewOutcome.chart = {
                destroy: jest.fn()
            };
            
            viewOutcome.cleanup();
            
            expect(viewOutcome.chart.destroy).toHaveBeenCalled();
            expect(viewOutcome.chart).toBeNull();
        });
    });
});
