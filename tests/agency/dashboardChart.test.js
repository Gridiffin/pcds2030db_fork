/**
 * Dashboard Chart Component Unit Tests
 * Tests the Chart.js component for program rating distribution
 */

// Mock Chart.js constructor
const mockChart = {
    destroy: jest.fn(),
    update: jest.fn(),
    data: { datasets: [{ data: [] }], labels: [] }
};

global.Chart = jest.fn(() => mockChart);

// Import the component
import { DashboardChart } from '../../assets/js/agency/dashboard/chart.js';

describe('DashboardChart', () => {
    let chartComponent;
    let mockCanvas;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
        
        // Create mock canvas element
        mockCanvas = document.createElement('canvas');
        mockCanvas.id = 'programRatingChart';
        document.body.appendChild(mockCanvas);
        
        // Mock Chart.js constructor and static methods
        global.Chart = jest.fn().mockImplementation(() => ({
            update: jest.fn(),
            destroy: jest.fn(),
            data: { datasets: [{ data: [] }] },
            options: {}
        }));
        
        global.Chart.register = jest.fn();
        global.Chart.defaults = {
            plugins: {
                legend: {},
                tooltip: {}
            }
        };
        
        // Setup mock chart data
        global.programRatingChartData = {
            labels: ['On Track', 'Delayed', 'Completed', 'Not Started'],
            data: [15, 5, 8, 2]
        };
    });

    afterEach(() => {
        // Clean up DOM
        document.body.innerHTML = '';
        delete global.programRatingChartData;
    });

    describe('Initialization', () => {
        test('should create chart instance successfully', () => {
            chartComponent = new DashboardChart();
            
            expect(Chart).toHaveBeenCalledWith(
                mockCanvas,
                expect.objectContaining({
                    type: 'doughnut',
                    data: expect.objectContaining({
                        labels: ['On Track', 'Delayed', 'Completed', 'Not Started'],
                        datasets: expect.arrayContaining([
                            expect.objectContaining({
                                data: [15, 5, 8, 2],
                                backgroundColor: ['#ffc107', '#dc3545', '#28a745', '#6c757d']
                            })
                        ])
                    })
                })
            );
        });

        test('should handle missing canvas element gracefully', () => {
            document.body.innerHTML = '';
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            
            chartComponent = new DashboardChart();
            
            expect(consoleSpy).toHaveBeenCalledWith('❌ Chart canvas not found');
            expect(Chart).not.toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });

        test('should handle missing chart data gracefully', () => {
            delete global.programRatingChartData;
            const consoleSpy = jest.spyOn(console, 'warn').mockImplementation();
            
            chartComponent = new DashboardChart();
            
            expect(consoleSpy).toHaveBeenCalledWith('⚠️ No chart data found');
            expect(Chart).not.toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });

        test('should handle Chart.js not loaded', () => {
            delete global.Chart;
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            
            chartComponent = new DashboardChart();
            
            expect(consoleSpy).toHaveBeenCalledWith('❌ Chart.js not loaded');
            
            consoleSpy.mockRestore();
        });
    });

    describe('Chart Configuration', () => {
        beforeEach(() => {
            chartComponent = new DashboardChart();
        });

        test('should configure doughnut chart with correct options', () => {
            const chartConfig = Chart.mock.calls[0][1];
            
            expect(chartConfig.type).toBe('doughnut');
            expect(chartConfig.options.responsive).toBe(true);
            expect(chartConfig.options.maintainAspectRatio).toBe(false);
            expect(chartConfig.options.cutout).toBe('70%');
        });

        test('should have proper legend configuration', () => {
            const chartConfig = Chart.mock.calls[0][1];
            const legendConfig = chartConfig.options.plugins.legend;
            
            expect(legendConfig.display).toBe(true);
            expect(legendConfig.position).toBe('bottom');
            expect(legendConfig.labels.usePointStyle).toBe(true);
        });

        test('should have custom tooltip configuration', () => {
            const chartConfig = Chart.mock.calls[0][1];
            const tooltipConfig = chartConfig.options.plugins.tooltip;
            
            expect(tooltipConfig.callbacks.label).toBeDefined();
            
            // Test tooltip callback
            const mockContext = {
                label: 'On Track',
                raw: 15,
                dataset: { data: [15, 5, 8, 2] }
            };
            
            const result = tooltipConfig.callbacks.label(mockContext);
            expect(result).toBe('On Track: 15 (50%)');
        });
    });

    describe('Chart Updates', () => {
        beforeEach(() => {
            chartComponent = new DashboardChart();
        });

        test('should update chart with new data', () => {
            const newData = {
                labels: ['New Label 1', 'New Label 2'],
                data: [10, 20]
            };
            
            chartComponent.update(newData);
            
            expect(mockChart.data.datasets[0].data).toEqual([10, 20]);
            expect(mockChart.data.labels).toEqual(['New Label 1', 'New Label 2']);
            expect(mockChart.update).toHaveBeenCalledWith('active');
        });

        test('should handle update with no chart instance', () => {
            chartComponent.chart = null;
            const consoleSpy = jest.spyOn(console, 'log').mockImplementation();
            
            chartComponent.update({ data: [1, 2, 3] });
            
            expect(mockChart.update).not.toHaveBeenCalled();
            consoleSpy.mockRestore();
        });

        test('should refresh chart by recreating', () => {
            const destroySpy = jest.spyOn(chartComponent, 'destroy');
            const createSpy = jest.spyOn(chartComponent, 'createChart');
            
            chartComponent.refresh();
            
            expect(createSpy).toHaveBeenCalled();
        });
    });

    describe('Error Handling', () => {
        test('should show error message when chart creation fails', () => {
            Chart.mockImplementation(() => {
                throw new Error('Chart creation failed');
            });
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            chartComponent = new DashboardChart();
            
            expect(consoleSpy).toHaveBeenCalledWith('❌ Error creating chart:', expect.any(Error));
            expect(mockCanvas.parentElement.innerHTML).toContain('chart-error');
            
            consoleSpy.mockRestore();
        });

        test('should show loading state', () => {
            chartComponent = new DashboardChart();
            chartComponent.showLoading();
            
            expect(mockCanvas.parentElement.innerHTML).toContain('chart-loading');
            expect(mockCanvas.parentElement.innerHTML).toContain('Loading chart...');
        });
    });

    describe('Cleanup', () => {
        test('should destroy chart instance on destroy', () => {
            chartComponent = new DashboardChart();
            chartComponent.destroy();
            
            expect(mockChart.destroy).toHaveBeenCalled();
            expect(chartComponent.chart).toBeNull();
        });
    });
});
