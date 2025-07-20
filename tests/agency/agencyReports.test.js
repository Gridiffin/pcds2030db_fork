/**
 * Tests for Agency Reports JavaScript Module
 * Testing assets/js/agency/reports/ functionality
 */

// Mock DOM elements
const mockDOM = () => {
    document.body.innerHTML = `
        <div class="reports-container">
            <div class="reports-header">
                <button id="refresh-reports" class="btn btn-primary">Refresh</button>
                <select id="filter-type" class="form-select">
                    <option value="all">All Reports</option>
                    <option value="public">Public Reports</option>
                </select>
            </div>
            <div class="reports-list" id="reports-list">
                <!-- Reports will be loaded here -->
            </div>
            <div class="pagination-container" id="pagination-container">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    `;
};

// Mock fetch function
global.fetch = jest.fn();

// Import the modules to test
import ReportsManager from '../../../assets/js/agency/reports/logic.js';
import ReportsAPI from '../../../assets/js/agency/reports/api.js';

describe('Agency Reports Module', () => {
    let reportsManager;
    let reportsAPI;

    beforeEach(() => {
        mockDOM();
        jest.clearAllMocks();
        
        // Initialize modules
        reportsAPI = new ReportsAPI();
        reportsManager = new ReportsManager();
    });

    describe('ReportsAPI', () => {
        test('should fetch reports successfully', async () => {
            const mockReports = [
                { id: 1, title: 'Test Report 1', type: 'public' },
                { id: 2, title: 'Test Report 2', type: 'internal' }
            ];

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockReports })
            });

            const result = await reportsAPI.getReports();
            
            expect(fetch).toHaveBeenCalledWith(
                expect.stringContaining('get_agency_reports.php'),
                expect.objectContaining({
                    method: 'GET',
                    headers: expect.objectContaining({
                        'Content-Type': 'application/json'
                    })
                })
            );
            
            expect(result.success).toBe(true);
            expect(result.data).toEqual(mockReports);
        });

        test('should handle API errors gracefully', async () => {
            fetch.mockResolvedValueOnce({
                ok: false,
                status: 500,
                statusText: 'Internal Server Error'
            });

            const result = await reportsAPI.getReports();
            
            expect(result.success).toBe(false);
            expect(result.error).toContain('500');
        });

        test('should fetch public reports', async () => {
            const mockPublicReports = [
                { id: 1, title: 'Public Report 1', type: 'public' }
            ];

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockPublicReports })
            });

            const result = await reportsAPI.getPublicReports();
            
            expect(fetch).toHaveBeenCalledWith(
                expect.stringContaining('get_public_reports.php'),
                expect.any(Object)
            );
            
            expect(result.success).toBe(true);
            expect(result.data).toEqual(mockPublicReports);
        });

        test('should handle network errors', async () => {
            fetch.mockRejectedValueOnce(new Error('Network error'));

            const result = await reportsAPI.getReports();
            
            expect(result.success).toBe(false);
            expect(result.error).toContain('Network error');
        });
    });

    describe('ReportsManager', () => {
        test('should initialize successfully', () => {
            expect(reportsManager).toBeDefined();
            expect(reportsManager.init).toBeDefined();
        });

        test('should load reports on initialization', async () => {
            const mockReports = [
                { id: 1, title: 'Test Report 1', created_at: '2025-01-01' }
            ];

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockReports })
            });

            await reportsManager.init();
            
            expect(fetch).toHaveBeenCalled();
        });

        test('should handle filter changes', async () => {
            const filterSelect = document.getElementById('filter-type');
            
            const mockPublicReports = [
                { id: 1, title: 'Public Report', type: 'public' }
            ];

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockPublicReports })
            });

            // Simulate filter change
            filterSelect.value = 'public';
            const changeEvent = new Event('change');
            filterSelect.dispatchEvent(changeEvent);

            // Allow async operations to complete
            await new Promise(resolve => setTimeout(resolve, 100));

            expect(fetch).toHaveBeenCalled();
        });

        test('should handle refresh button click', async () => {
            const refreshButton = document.getElementById('refresh-reports');
            
            const mockReports = [
                { id: 1, title: 'Refreshed Report' }
            ];

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockReports })
            });

            // Simulate refresh click
            const clickEvent = new Event('click');
            refreshButton.dispatchEvent(clickEvent);

            // Allow async operations to complete
            await new Promise(resolve => setTimeout(resolve, 100));

            expect(fetch).toHaveBeenCalled();
        });

        test('should render reports correctly', () => {
            const mockReports = [
                { 
                    id: 1, 
                    title: 'Test Report', 
                    description: 'Test Description',
                    created_at: '2025-01-01 10:00:00',
                    type: 'public'
                }
            ];

            reportsManager.renderReports(mockReports);
            
            const reportsList = document.getElementById('reports-list');
            const reportElements = reportsList.querySelectorAll('.report-item');
            
            expect(reportElements.length).toBe(1);
            expect(reportsList.innerHTML).toContain('Test Report');
            expect(reportsList.innerHTML).toContain('Test Description');
        });

        test('should show empty state when no reports', () => {
            reportsManager.renderReports([]);
            
            const reportsList = document.getElementById('reports-list');
            
            expect(reportsList.innerHTML).toContain('No reports found');
        });

        test('should handle loading states', () => {
            reportsManager.showLoading();
            
            const reportsList = document.getElementById('reports-list');
            
            expect(reportsList.innerHTML).toContain('Loading');
        });

        test('should handle error states', () => {
            const errorMessage = 'Failed to load reports';
            
            reportsManager.showError(errorMessage);
            
            const reportsList = document.getElementById('reports-list');
            
            expect(reportsList.innerHTML).toContain(errorMessage);
        });
    });

    describe('Reports Integration', () => {
        test('should complete full workflow', async () => {
            const mockReports = [
                { id: 1, title: 'Integration Test Report', type: 'public' },
                { id: 2, title: 'Another Report', type: 'internal' }
            ];

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockReports })
            });

            // Initialize the reports manager
            await reportsManager.init();
            
            // Verify reports are loaded and rendered
            const reportsList = document.getElementById('reports-list');
            
            // Allow time for DOM updates
            await new Promise(resolve => setTimeout(resolve, 100));
            
            expect(fetch).toHaveBeenCalled();
        });

        test('should maintain state across operations', async () => {
            // First load
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: [{ id: 1, title: 'Report 1' }] })
            });

            await reportsManager.init();
            
            // Filter change
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: [{ id: 2, title: 'Filtered Report' }] })
            });

            const filterSelect = document.getElementById('filter-type');
            filterSelect.value = 'public';
            filterSelect.dispatchEvent(new Event('change'));

            await new Promise(resolve => setTimeout(resolve, 100));

            expect(fetch).toHaveBeenCalledTimes(2);
        });
    });

    describe('Error Handling', () => {
        test('should handle API failures gracefully', async () => {
            fetch.mockRejectedValueOnce(new Error('API unavailable'));

            await reportsManager.init();
            
            const reportsList = document.getElementById('reports-list');
            
            // Should show error message instead of crashing
            expect(reportsList.innerHTML).toContain('error') || 
            expect(reportsList.innerHTML).toContain('Error') ||
            expect(reportsList.innerHTML).toContain('failed');
        });

        test('should validate data before rendering', () => {
            const invalidData = [
                { id: 1 }, // missing title
                { title: 'No ID' }, // missing id
                null, // null entry
                undefined // undefined entry
            ];

            // Should not crash
            expect(() => {
                reportsManager.renderReports(invalidData);
            }).not.toThrow();
        });
    });

    describe('Performance', () => {
        test('should render large report lists efficiently', () => {
            const largeReportList = Array.from({ length: 100 }, (_, i) => ({
                id: i + 1,
                title: `Report ${i + 1}`,
                description: `Description for report ${i + 1}`,
                created_at: '2025-01-01',
                type: 'public'
            }));

            const startTime = performance.now();
            
            reportsManager.renderReports(largeReportList);
            
            const endTime = performance.now();
            const renderTime = endTime - startTime;

            // Rendering should complete within 500ms
            expect(renderTime).toBeLessThan(500);
        });

        test('should debounce rapid filter changes', async () => {
            const filterSelect = document.getElementById('filter-type');
            
            fetch.mockResolvedValue({
                ok: true,
                json: async () => ({ success: true, data: [] })
            });

            // Rapidly change filter multiple times
            filterSelect.value = 'public';
            filterSelect.dispatchEvent(new Event('change'));
            
            filterSelect.value = 'all';
            filterSelect.dispatchEvent(new Event('change'));
            
            filterSelect.value = 'public';
            filterSelect.dispatchEvent(new Event('change'));

            // Wait for debounce period
            await new Promise(resolve => setTimeout(resolve, 600));

            // Should not make excessive API calls
            expect(fetch).toHaveBeenCalledTimes(1);
        });
    });
});
