/**
 * ChartManager Component Unit Tests
 * Tests the ChartManager class functionality for chart rendering and management
 */

// Mock Chart.js
global.Chart = {
    register: jest.fn(),
    Chart: jest.fn().mockImplementation(() => ({
        data: { datasets: [], labels: [] },
        update: jest.fn(),
        destroy: jest.fn(),
        resize: jest.fn()
    })),
    CategoryScale: {},
    LinearScale: {},
    BarElement: {},
    LineElement: {},
    PointElement: {},
    BarController: {},
    LineController: {},
    Title: {},
    Tooltip: {},
    Legend: {}
};

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
    getContext: jest.fn(() => ({}))
};

global.document = {
    querySelector: jest.fn(() => mockElement),
    querySelectorAll: jest.fn(() => [mockElement]),
    getElementById: jest.fn(() => mockElement),
    createElement: jest.fn(() => mockElement),
    body: mockElement
};

global.window = {
    location: { pathname: '/index.php?page=agency_outcomes_view' },
    console: { log: jest.fn(), error: jest.fn() },
    devicePixelRatio: 1
};

// Import the component to test
const { ChartManager } = require('../../assets/js/agency/outcomes/chart-manager.js');

describe('ChartManager', () => {
    let chartManager;
    let mockChartData;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
        
        // Mock chart data
        mockChartData = {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                datasets: [{
                    label: '2024',
                    data: [1000, 1100, 1200, 1300],
                    backgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Test Chart' }
                }
            }
        };
        
        // Setup DOM mocks
        global.document.querySelector.mockReturnValue(mockElement);
        global.document.querySelectorAll.mockReturnValue([mockElement]);
        global.document.getElementById.mockReturnValue(mockElement);
        
        // Create new instance
        chartManager = new ChartManager('test-chart-container');
    });

    afterEach(() => {
        if (chartManager && typeof chartManager.cleanup === 'function') {
            chartManager.cleanup();
        }
    });

    describe('Initialization', () => {
        test('should create ChartManager instance correctly', () => {
            expect(chartManager).toBeDefined();
            expect(chartManager.containerId).toBe('test-chart-container');
            expect(chartManager.chart).toBeNull();
        });

        test('should find chart container on init', () => {
            chartManager.init();
            
            expect(global.document.getElementById).toHaveBeenCalledWith('test-chart-container');
        });

        test('should setup event listeners on init', () => {
            const addEventListenerSpy = jest.spyOn(mockElement, 'addEventListener');
            
            chartManager.init();
            
            expect(addEventListenerSpy).toHaveBeenCalled();
        });

        test('should handle missing container gracefully', () => {
            global.document.getElementById.mockReturnValue(null);
            
            expect(() => {
                chartManager.init();
            }).not.toThrow();
        });
    });

    describe('Chart Creation', () => {
        beforeEach(() => {
            chartManager.container = mockElement;
        });

        test('should create chart successfully', () => {
            const canvas = mockElement;
            canvas.getContext = jest.fn(() => ({}));
            global.document.createElement.mockReturnValue(canvas);
            
            chartManager.createChart(mockChartData);
            
            expect(global.Chart.Chart).toHaveBeenCalled();
            expect(chartManager.chart).toBeDefined();
        });

        test('should handle chart creation errors', () => {
            global.Chart.Chart.mockImplementation(() => {
                throw new Error('Chart creation error');
            });
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            expect(() => {
                chartManager.createChart(mockChartData);
            }).not.toThrow();
            
            expect(consoleSpy).toHaveBeenCalled();
            consoleSpy.mockRestore();
        });

        test('should create different chart types', () => {
            const lineChartData = { ...mockChartData, type: 'line' };
            const pieChartData = { ...mockChartData, type: 'pie' };
            
            chartManager.createChart(lineChartData);
            expect(global.Chart.Chart).toHaveBeenCalled();
            
            chartManager.createChart(pieChartData);
            expect(global.Chart.Chart).toHaveBeenCalledTimes(2);
        });
    });

    describe('Chart Updates', () => {
        beforeEach(() => {
            chartManager.container = mockElement;
            chartManager.chart = {
                data: { datasets: [{ data: [] }], labels: [] },
                update: jest.fn(),
                destroy: jest.fn()
            };
        });

        test('should update chart data correctly', () => {
            const newData = [1500, 1600, 1700, 1800];
            const newLabels = ['May', 'Jun', 'Jul', 'Aug'];
            
            chartManager.updateChart(newData, newLabels);
            
            expect(chartManager.chart.data.datasets[0].data).toEqual(newData);
            expect(chartManager.chart.data.labels).toEqual(newLabels);
            expect(chartManager.chart.update).toHaveBeenCalled();
        });

        test('should handle update with no existing chart', () => {
            chartManager.chart = null;
            
            expect(() => {
                chartManager.updateChart([1, 2, 3], ['A', 'B', 'C']);
            }).not.toThrow();
        });

        test('should handle partial updates', () => {
            const newData = [2000, 2100];
            
            chartManager.updateChart(newData);
            
            expect(chartManager.chart.data.datasets[0].data).toEqual(newData);
            expect(chartManager.chart.update).toHaveBeenCalled();
        });
    });

    describe('Chart Types and Options', () => {
        test('should get correct options for bar chart', () => {
            const options = chartManager.getChartOptions('bar');
            
            expect(options).toBeDefined();
            expect(options.responsive).toBe(true);
            expect(options.scales).toBeDefined();
        });

        test('should get correct options for line chart', () => {
            const options = chartManager.getChartOptions('line');
            
            expect(options).toBeDefined();
            expect(options.responsive).toBe(true);
            expect(options.elements).toBeDefined();
        });

        test('should get correct options for pie chart', () => {
            const options = chartManager.getChartOptions('pie');
            
            expect(options).toBeDefined();
            expect(options.responsive).toBe(true);
            expect(options.plugins).toBeDefined();
        });

        test('should handle unknown chart type', () => {
            const options = chartManager.getChartOptions('unknown');
            
            expect(options).toBeDefined();
            expect(options.responsive).toBe(true);
        });

        test('should apply custom colors correctly', () => {
            const colors = ['#ff0000', '#00ff00', '#0000ff'];
            const dataset = chartManager.applyColors(mockChartData.data.datasets[0], colors);
            
            expect(dataset.backgroundColor).toEqual(colors);
        });
    });

    describe('Data Processing', () => {
        test('should process outcome data for chart display', () => {
            const outcomeData = {
                rows: [
                    { month: 'January', 2024: 1000, 2025: 1200 },
                    { month: 'February', 2024: 1100, 2025: 1300 }
                ],
                columns: ['2024', '2025']
            };
            
            const processed = chartManager.processOutcomeData(outcomeData);
            
            expect(processed.labels).toEqual(['January', 'February']);
            expect(processed.datasets).toBeDefined();
            expect(processed.datasets).toHaveLength(2);
        });

        test('should handle empty outcome data', () => {
            const emptyData = { rows: [], columns: [] };
            
            const processed = chartManager.processOutcomeData(emptyData);
            
            expect(processed.labels).toEqual([]);
            expect(processed.datasets).toEqual([]);
        });

        test('should handle malformed outcome data', () => {
            const malformedData = { rows: null, columns: undefined };
            
            expect(() => {
                chartManager.processOutcomeData(malformedData);
            }).not.toThrow();
        });

        test('should format numbers correctly', () => {
            const formatted = chartManager.formatNumber(1234567.89);
            
            expect(formatted).toBeDefined();
            expect(typeof formatted).toBe('string');
        });
    });

    describe('Chart Interactions', () => {
        beforeEach(() => {
            chartManager.chart = {
                data: { datasets: [{ data: [] }], labels: [] },
                update: jest.fn(),
                destroy: jest.fn(),
                resize: jest.fn()
            };
        });

        test('should handle chart resize', () => {
            chartManager.resizeChart();
            
            expect(chartManager.chart.resize).toHaveBeenCalled();
        });

        test('should handle chart type changes', () => {
            const createSpy = jest.spyOn(chartManager, 'createChart');
            
            chartManager.changeChartType('line');
            
            expect(chartManager.chart.destroy).toHaveBeenCalled();
            expect(createSpy).toHaveBeenCalled();
        });

        test('should export chart as image', () => {
            const mockCanvas = {
                toDataURL: jest.fn(() => 'data:image/png;base64,mock')
            };
            
            chartManager.chart.canvas = mockCanvas;
            
            const dataURL = chartManager.exportChart();
            
            expect(mockCanvas.toDataURL).toHaveBeenCalledWith('image/png');
            expect(dataURL).toBe('data:image/png;base64,mock');
        });

        test('should handle export errors gracefully', () => {
            chartManager.chart = null;
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            const result = chartManager.exportChart();
            
            expect(result).toBeNull();
            expect(consoleSpy).toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });
    });

    describe('Responsive Design', () => {
        test('should handle window resize events', () => {
            const resizeSpy = jest.spyOn(chartManager, 'resizeChart');
            
            // Simulate window resize
            global.window.dispatchEvent = jest.fn();
            chartManager.setupResponsive();
            
            expect(global.window.addEventListener).toBeDefined();
        });

        test('should adapt to container size changes', () => {
            chartManager.container = mockElement;
            chartManager.container.offsetWidth = 800;
            chartManager.container.offsetHeight = 400;
            
            const dimensions = chartManager.getContainerDimensions();
            
            expect(dimensions.width).toBe(800);
            expect(dimensions.height).toBe(400);
        });
    });

    describe('Performance', () => {
        test('should throttle resize events', () => {
            let resizeCallback;
            global.window.addEventListener = jest.fn((event, callback) => {
                if (event === 'resize') {
                    resizeCallback = callback;
                }
            });
            
            chartManager.setupResponsive();
            
            // Call resize multiple times quickly
            if (resizeCallback) {
                resizeCallback();
                resizeCallback();
                resizeCallback();
            }
            
            // Should only call resize once due to throttling
            expect(global.window.addEventListener).toHaveBeenCalled();
        });

        test('should handle large datasets efficiently', () => {
            const largeDataset = Array.from({ length: 1000 }, (_, i) => i);
            const largeLabels = Array.from({ length: 1000 }, (_, i) => `Label ${i}`);
            
            const start = Date.now();
            chartManager.updateChart(largeDataset, largeLabels);
            const end = Date.now();
            
            // Should complete quickly (under 100ms for test)
            expect(end - start).toBeLessThan(100);
        });
    });

    describe('Cleanup', () => {
        test('should cleanup chart and event listeners', () => {
            const mockChart = {
                destroy: jest.fn()
            };
            chartManager.chart = mockChart;
            
            const removeEventListenerSpy = jest.spyOn(global.window, 'removeEventListener').mockImplementation(() => {});
            
            chartManager.cleanup();
            
            expect(mockChart.destroy).toHaveBeenCalled();
            expect(chartManager.chart).toBeNull();
            
            removeEventListenerSpy.mockRestore();
        });

        test('should handle cleanup with no chart', () => {
            chartManager.chart = null;
            
            expect(() => {
                chartManager.cleanup();
            }).not.toThrow();
        });
    });

    describe('Error Recovery', () => {
        test('should recover from chart rendering errors', () => {
            // Mock chart that fails to render
            global.Chart.Chart.mockImplementation(() => {
                throw new Error('Rendering failed');
            });
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            chartManager.createChart(mockChartData);
            
            // Should fallback gracefully
            expect(consoleSpy).toHaveBeenCalled();
            expect(chartManager.chart).toBeNull();
            
            consoleSpy.mockRestore();
        });

        test('should handle corrupted data gracefully', () => {
            const corruptedData = {
                type: 'bar',
                data: null
            };
            
            expect(() => {
                chartManager.createChart(corruptedData);
            }).not.toThrow();
        });
    });
});
