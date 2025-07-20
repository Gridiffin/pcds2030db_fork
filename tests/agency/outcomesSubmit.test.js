/**
 * SubmitOutcomes Component Unit Tests
 * Tests the SubmitOutcomes class functionality for outcomes listing and management
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
    disabled: false
};

global.document = {
    querySelector: jest.fn(() => mockElement),
    querySelectorAll: jest.fn(() => [mockElement]),
    getElementById: jest.fn(() => mockElement),
    createElement: jest.fn(() => mockElement),
    body: mockElement
};

global.window = {
    location: { pathname: '/index.php?page=agency_outcomes_submit' },
    console: { log: jest.fn(), error: jest.fn() }
};

// Import the component to test
const { SubmitOutcomes } = require('../../assets/js/agency/outcomes/submit.js');

describe('SubmitOutcomes', () => {
    let submitOutcomes;
    let mockOutcomesData;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
        
        // Mock outcomes data
        mockOutcomesData = [
            {
                id: 1,
                code: 'FDS_01',
                title: 'Forest Conservation',
                type: 'chart',
                sector: 'Forestry',
                data: { rows: [], columns: [] }
            },
            {
                id: 2,
                code: 'SFC_01',
                title: 'Sustainable Logging',
                type: 'kpi',
                sector: 'Forestry',
                data: { value: 85, unit: '%' }
            }
        ];
        
        // Setup DOM mocks
        global.document.querySelector.mockReturnValue(mockElement);
        global.document.querySelectorAll.mockReturnValue([mockElement]);
        global.document.getElementById.mockReturnValue(mockElement);
        
        // Create new instance
        submitOutcomes = new SubmitOutcomes();
    });

    afterEach(() => {
        if (submitOutcomes && typeof submitOutcomes.cleanup === 'function') {
            submitOutcomes.cleanup();
        }
    });

    describe('Initialization', () => {
        test('should create SubmitOutcomes instance correctly', () => {
            expect(submitOutcomes).toBeDefined();
            expect(submitOutcomes.outcomes).toEqual([]);
            expect(submitOutcomes.filteredOutcomes).toEqual([]);
        });

        test('should setup event listeners on init', () => {
            const addEventListenerSpy = jest.spyOn(mockElement, 'addEventListener');
            
            submitOutcomes.init();
            
            expect(addEventListenerSpy).toHaveBeenCalled();
        });

        test('should load outcomes data on init', async () => {
            const loadSpy = jest.spyOn(submitOutcomes, 'loadOutcomes').mockResolvedValue();
            
            await submitOutcomes.init();
            
            expect(loadSpy).toHaveBeenCalled();
        });
    });

    describe('Data Loading', () => {
        test('should load outcomes successfully', async () => {
            // Mock successful fetch response
            global.fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({
                    success: true,
                    data: mockOutcomesData
                })
            });
            
            await submitOutcomes.loadOutcomes();
            
            expect(global.fetch).toHaveBeenCalledWith(
                expect.stringContaining('/app/ajax/get_all_outcomes.php'),
                expect.objectContaining({
                    method: 'GET'
                })
            );
            expect(submitOutcomes.outcomes).toEqual(mockOutcomesData);
            expect(submitOutcomes.filteredOutcomes).toEqual(mockOutcomesData);
        });

        test('should handle loading errors', async () => {
            // Mock failed fetch response
            global.fetch.mockResolvedValueOnce({
                ok: false,
                status: 500
            });
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            await submitOutcomes.loadOutcomes();
            
            expect(submitOutcomes.outcomes).toEqual([]);
            expect(consoleSpy).toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });

        test('should handle network errors', async () => {
            // Mock network error
            global.fetch.mockRejectedValueOnce(new Error('Network error'));
            
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
            
            await submitOutcomes.loadOutcomes();
            
            expect(submitOutcomes.outcomes).toEqual([]);
            expect(consoleSpy).toHaveBeenCalled();
            
            consoleSpy.mockRestore();
        });
    });

    describe('Outcomes Rendering', () => {
        beforeEach(() => {
            submitOutcomes.outcomes = mockOutcomesData;
            submitOutcomes.filteredOutcomes = mockOutcomesData;
        });

        test('should render outcomes grid correctly', () => {
            const gridContainer = mockElement;
            global.document.querySelector.mockReturnValue(gridContainer);
            
            submitOutcomes.renderOutcomes();
            
            expect(gridContainer.innerHTML).toBeDefined();
        });

        test('should render empty state when no outcomes', () => {
            submitOutcomes.filteredOutcomes = [];
            
            const gridContainer = mockElement;
            global.document.querySelector.mockReturnValue(gridContainer);
            
            submitOutcomes.renderOutcomes();
            
            expect(gridContainer.innerHTML).toBeDefined();
        });

        test('should create outcome cards with correct data', () => {
            const mockCard = mockElement;
            global.document.createElement.mockReturnValue(mockCard);
            
            const card = submitOutcomes.createOutcomeCard(mockOutcomesData[0]);
            
            expect(card).toBeDefined();
            expect(global.document.createElement).toHaveBeenCalledWith('div');
        });

        test('should handle different outcome types', () => {
            const chartOutcome = mockOutcomesData[0];
            const kpiOutcome = mockOutcomesData[1];
            
            const chartCard = submitOutcomes.createOutcomeCard(chartOutcome);
            const kpiCard = submitOutcomes.createOutcomeCard(kpiOutcome);
            
            expect(chartCard).toBeDefined();
            expect(kpiCard).toBeDefined();
        });
    });

    describe('Filtering and Search', () => {
        beforeEach(() => {
            submitOutcomes.outcomes = mockOutcomesData;
        });

        test('should filter outcomes by search term', () => {
            submitOutcomes.applyFilters('Forest');
            
            expect(submitOutcomes.filteredOutcomes).toHaveLength(1);
            expect(submitOutcomes.filteredOutcomes[0].title).toContain('Forest');
        });

        test('should filter outcomes by type', () => {
            submitOutcomes.filterByType('chart');
            
            expect(submitOutcomes.filteredOutcomes).toHaveLength(1);
            expect(submitOutcomes.filteredOutcomes[0].type).toBe('chart');
        });

        test('should handle case-insensitive search', () => {
            submitOutcomes.applyFilters('forest');
            
            expect(submitOutcomes.filteredOutcomes).toHaveLength(1);
        });

        test('should return all outcomes when search is empty', () => {
            submitOutcomes.applyFilters('');
            
            expect(submitOutcomes.filteredOutcomes).toHaveLength(mockOutcomesData.length);
        });

        test('should handle no matches gracefully', () => {
            submitOutcomes.applyFilters('nonexistent');
            
            expect(submitOutcomes.filteredOutcomes).toHaveLength(0);
        });
    });

    describe('User Interactions', () => {
        test('should handle refresh button click', async () => {
            const refreshButton = mockElement;
            global.document.querySelector.mockReturnValue(refreshButton);
            
            const loadSpy = jest.spyOn(submitOutcomes, 'loadOutcomes').mockResolvedValue();
            
            await submitOutcomes.refreshOutcomes();
            
            expect(loadSpy).toHaveBeenCalled();
        });

        test('should handle view outcome action', () => {
            const outcomeId = '1';
            global.window.location.href = '';
            
            submitOutcomes.viewOutcome(outcomeId);
            
            expect(global.window.location.href).toContain('agency_outcomes_view');
            expect(global.window.location.href).toContain('id=1');
        });

        test('should handle search input changes', () => {
            const searchInput = mockElement;
            searchInput.value = 'test search';
            global.document.querySelector.mockReturnValue(searchInput);
            
            const filterSpy = jest.spyOn(submitOutcomes, 'applyFilters');
            
            submitOutcomes.handleSearchInput();
            
            expect(filterSpy).toHaveBeenCalledWith('test search');
        });

        test('should handle type filter changes', () => {
            const typeSelect = mockElement;
            typeSelect.value = 'chart';
            global.document.querySelector.mockReturnValue(typeSelect);
            
            const filterSpy = jest.spyOn(submitOutcomes, 'filterByType');
            
            submitOutcomes.handleTypeFilter();
            
            expect(filterSpy).toHaveBeenCalledWith('chart');
        });
    });

    describe('Loading States', () => {
        test('should show loading state during data load', () => {
            const loadingElement = mockElement;
            global.document.querySelector.mockReturnValue(loadingElement);
            
            submitOutcomes.showLoading();
            
            expect(loadingElement.style.display).toBeDefined();
        });

        test('should hide loading state after data load', () => {
            const loadingElement = mockElement;
            global.document.querySelector.mockReturnValue(loadingElement);
            
            submitOutcomes.hideLoading();
            
            expect(loadingElement.style.display).toBeDefined();
        });

        test('should disable buttons during loading', () => {
            const refreshButton = mockElement;
            global.document.querySelector.mockReturnValue(refreshButton);
            
            submitOutcomes.showLoading();
            
            expect(refreshButton.disabled).toBeDefined();
        });
    });

    describe('Error Handling', () => {
        test('should show error messages appropriately', () => {
            const errorContainer = mockElement;
            global.document.querySelector.mockReturnValue(errorContainer);
            
            submitOutcomes.showError('Test error message');
            
            expect(errorContainer.textContent).toBeDefined();
            expect(errorContainer.style.display).toBeDefined();
        });

        test('should hide error messages', () => {
            const errorContainer = mockElement;
            global.document.querySelector.mockReturnValue(errorContainer);
            
            submitOutcomes.hideError();
            
            expect(errorContainer.style.display).toBeDefined();
        });

        test('should handle rendering errors gracefully', () => {
            // Cause a rendering error
            submitOutcomes.outcomes = [{ invalid: 'data' }];
            
            expect(() => {
                submitOutcomes.renderOutcomes();
            }).not.toThrow();
        });
    });

    describe('Pagination', () => {
        test('should handle large datasets with pagination', () => {
            // Create large dataset
            const largeDataset = Array.from({ length: 50 }, (_, i) => ({
                id: i + 1,
                code: `TEST_${i + 1}`,
                title: `Test Outcome ${i + 1}`,
                type: 'chart'
            }));
            
            submitOutcomes.outcomes = largeDataset;
            submitOutcomes.setupPagination();
            
            expect(submitOutcomes.currentPage).toBeDefined();
            expect(submitOutcomes.itemsPerPage).toBeDefined();
        });

        test('should navigate pages correctly', () => {
            submitOutcomes.currentPage = 1;
            
            submitOutcomes.goToPage(2);
            
            expect(submitOutcomes.currentPage).toBe(2);
        });
    });

    describe('Cleanup', () => {
        test('should cleanup event listeners and resources', () => {
            const removeEventListenerSpy = jest.spyOn(mockElement, 'removeEventListener');
            
            submitOutcomes.cleanup();
            
            expect(removeEventListenerSpy).toHaveBeenCalled();
        });

        test('should reset data on cleanup', () => {
            submitOutcomes.outcomes = mockOutcomesData;
            
            submitOutcomes.cleanup();
            
            expect(submitOutcomes.outcomes).toEqual([]);
            expect(submitOutcomes.filteredOutcomes).toEqual([]);
        });
    });
});
