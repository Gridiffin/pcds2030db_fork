/**
 * Dashboard Integration Tests
 * Tests the main AgencyDashboard class and component interactions
 */

import AgencyDashboard from '../../assets/js/agency/dashboard/dashboard.js';

// Mock all the component classes
jest.mock('../../assets/js/agency/dashboard/chart.js', () => ({
    DashboardChart: jest.fn().mockImplementation(() => ({
        initialize: jest.fn(),
        destroy: jest.fn(),
        updateChart: jest.fn(),
        refreshData: jest.fn()
    }))
}));

jest.mock('../../assets/js/agency/dashboard/logic.js', () => ({
    DashboardLogic: jest.fn().mockImplementation(() => ({
        initialize: jest.fn(),
        destroy: jest.fn(),
        updateStatCards: jest.fn(),
        loadAjaxData: jest.fn()
    }))
}));

jest.mock('../../assets/js/agency/dashboard/initiatives.js', () => ({
    InitiativeCarousel: jest.fn().mockImplementation(() => ({
        initialize: jest.fn(),
        destroy: jest.fn(),
        refresh: jest.fn(),
        goToSlide: jest.fn()
    }))
}));

jest.mock('../../assets/js/agency/dashboard/programs.js', () => ({
    ProgramsTable: jest.fn().mockImplementation(() => ({
        initialize: jest.fn(),
        destroy: jest.fn(),
        loadPrograms: jest.fn(),
        refresh: jest.fn()
    }))
}));

// Import the mocked components
import { DashboardChart } from '../../assets/js/agency/dashboard/chart.js';
import { DashboardLogic } from '../../assets/js/agency/dashboard/logic.js';
import { InitiativeCarousel } from '../../assets/js/agency/dashboard/initiatives.js';
import { ProgramsTable } from '../../assets/js/agency/dashboard/programs.js';

describe('AgencyDashboard Integration', () => {
    let dashboard;

    beforeEach(() => {
        // Reset DOM
        document.body.innerHTML = `
            <div class="dashboard-container">
                <div class="stat-cards">
                    <div class="stat-card" id="totalPrograms">
                        <span class="stat-number">0</span>
                        <span class="stat-label">Total Programs</span>
                    </div>
                </div>
                <div id="programRatingChart"></div>
                <div id="programCarouselCard"></div>
                <div class="programs-section">
                    <table id="programsTable"></table>
                </div>
            </div>
        `;

        // Clear mock calls
        jest.clearAllMocks();
    });

    afterEach(() => {
        if (dashboard) {
            dashboard.destroy();
        }
    });

    describe('Initialization', () => {
        test('should initialize all components', () => {
            dashboard = new AgencyDashboard();
            
            expect(DashboardChart).toHaveBeenCalledTimes(1);
            expect(DashboardLogic).toHaveBeenCalledTimes(1);
            expect(InitiativeCarousel).toHaveBeenCalledTimes(1);
            expect(ProgramsTable).toHaveBeenCalledTimes(1);
        });

        test('should call initialize on all components', () => {
            dashboard = new AgencyDashboard();
            
            expect(dashboard.chart.initialize).toHaveBeenCalled();
            expect(dashboard.logic.initialize).toHaveBeenCalled();
            expect(dashboard.carousel.initialize).toHaveBeenCalled();
            expect(dashboard.programsTable.initialize).toHaveBeenCalled();
        });

        test('should handle missing DOM elements gracefully', () => {
            // Remove required elements
            document.body.innerHTML = '<div>Empty dashboard</div>';
            
            expect(() => {
                dashboard = new AgencyDashboard();
            }).not.toThrow();
        });

        test('should store component references', () => {
            dashboard = new AgencyDashboard();
            
            expect(dashboard.chart).toBeInstanceOf(DashboardChart);
            expect(dashboard.logic).toBeInstanceOf(DashboardLogic);
            expect(dashboard.carousel).toBeInstanceOf(InitiativeCarousel);
            expect(dashboard.programsTable).toBeInstanceOf(ProgramsTable);
        });
    });

    describe('Component Interaction', () => {
        beforeEach(() => {
            dashboard = new AgencyDashboard();
        });

        test('should refresh all components', () => {
            dashboard.refreshAll();
            
            expect(dashboard.chart.refreshData).toHaveBeenCalled();
            expect(dashboard.logic.loadAjaxData).toHaveBeenCalled();
            expect(dashboard.carousel.refresh).toHaveBeenCalled();
            expect(dashboard.programsTable.refresh).toHaveBeenCalled();
        });

        test('should update chart when data changes', () => {
            const newData = { labels: ['Q1', 'Q2'], data: [10, 20] };
            
            dashboard.updateChart(newData);
            
            expect(dashboard.chart.updateChart).toHaveBeenCalledWith(newData);
        });

        test('should update stat cards', () => {
            const stats = { totalPrograms: 25, activePrograms: 18 };
            
            dashboard.updateStats(stats);
            
            expect(dashboard.logic.updateStatCards).toHaveBeenCalledWith(stats);
        });

        test('should handle component communication', () => {
            // Simulate program table triggering chart update
            const spy = jest.spyOn(dashboard, 'updateChart');
            
            // Mock event emission
            dashboard.handleProgramsUpdate([{ id: 1, rating: 5 }, { id: 2, rating: 3 }]);
            
            expect(spy).toHaveBeenCalled();
        });
    });

    describe('Data Loading', () => {
        beforeEach(() => {
            dashboard = new AgencyDashboard();
        });

        test('should load initial data for all components', () => {
            dashboard.loadInitialData();
            
            expect(dashboard.logic.loadAjaxData).toHaveBeenCalled();
            expect(dashboard.programsTable.loadPrograms).toHaveBeenCalled();
        });

        test('should handle data loading errors gracefully', () => {
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            
            // Mock component error
            dashboard.logic.loadAjaxData.mockImplementation(() => {
                throw new Error('Network error');
            });
            
            expect(() => {
                dashboard.loadInitialData();
            }).not.toThrow();
            
            consoleSpy.mockRestore();
        });

        test('should coordinate loading states', () => {
            const mockShowLoader = jest.fn();
            const mockHideLoader = jest.fn();
            
            dashboard.showLoader = mockShowLoader;
            dashboard.hideLoader = mockHideLoader;
            
            dashboard.loadInitialData();
            
            expect(mockShowLoader).toHaveBeenCalled();
            // hideLoader would be called after async operations complete
        });
    });

    describe('Event Handling', () => {
        beforeEach(() => {
            dashboard = new AgencyDashboard();
        });

        test('should handle window resize', () => {
            const spy = jest.spyOn(dashboard.chart, 'updateChart');
            
            window.dispatchEvent(new Event('resize'));
            
            // Chart should be updated on resize
            expect(spy).toHaveBeenCalled();
        });

        test('should handle visibility change', () => {
            const spy = jest.spyOn(dashboard, 'refreshAll');
            
            // Mock document becoming visible
            Object.defineProperty(document, 'hidden', { value: false, configurable: true });
            document.dispatchEvent(new Event('visibilitychange'));
            
            expect(spy).toHaveBeenCalled();
        });

        test('should handle custom dashboard events', () => {
            const spy = jest.spyOn(dashboard.programsTable, 'loadPrograms');
            
            // Emit custom event
            const event = new CustomEvent('dashboard:refresh-programs');
            document.dispatchEvent(event);
            
            expect(spy).toHaveBeenCalled();
        });
    });

    describe('Performance Optimization', () => {
        beforeEach(() => {
            dashboard = new AgencyDashboard();
        });

        test('should debounce rapid refresh calls', () => {
            const spy = jest.spyOn(dashboard.logic, 'loadAjaxData');
            
            // Rapid calls
            dashboard.refreshAll();
            dashboard.refreshAll();
            dashboard.refreshAll();
            
            // Should be debounced to single call
            expect(spy).toHaveBeenCalledTimes(1);
        });

        test('should implement lazy loading for heavy components', () => {
            // Chart should not initialize until needed
            expect(dashboard.chart.initialize).toHaveBeenCalled();
            
            // But complex operations should be deferred
            expect(dashboard.chart.refreshData).not.toHaveBeenCalled();
        });

        test('should cleanup event listeners properly', () => {
            const removeEventListenerSpy = jest.spyOn(window, 'removeEventListener');
            
            dashboard.destroy();
            
            expect(removeEventListenerSpy).toHaveBeenCalled();
            removeEventListenerSpy.mockRestore();
        });
    });

    describe('Error Handling and Recovery', () => {
        beforeEach(() => {
            dashboard = new AgencyDashboard();
        });

        test('should handle component initialization failures', () => {
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            
            // Mock component failure
            dashboard.chart.initialize.mockImplementation(() => {
                throw new Error('Chart initialization failed');
            });
            
            expect(() => {
                dashboard.initializeComponents();
            }).not.toThrow();
            
            consoleSpy.mockRestore();
        });

        test('should provide fallback when components fail', () => {
            // If chart fails, should still show basic dashboard
            dashboard.chart = null;
            
            expect(() => {
                dashboard.refreshAll();
            }).not.toThrow();
        });

        test('should log component errors for debugging', () => {
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            
            dashboard.handleComponentError('chart', new Error('Test error'));
            
            expect(consoleSpy).toHaveBeenCalledWith(
                'Dashboard component error:',
                'chart',
                expect.any(Error)
            );
            
            consoleSpy.mockRestore();
        });
    });

    describe('State Management', () => {
        beforeEach(() => {
            dashboard = new AgencyDashboard();
        });

        test('should maintain dashboard state', () => {
            const state = {
                activeTab: 'programs',
                selectedPeriod: '2024-Q1',
                filters: { status: 'active' }
            };
            
            dashboard.setState(state);
            
            expect(dashboard.getState()).toEqual(state);
        });

        test('should sync state across components', () => {
            const state = { selectedPeriod: '2024-Q2' };
            dashboard.setState(state);
            
            // Components should receive state updates
            expect(dashboard.chart.updateChart).toHaveBeenCalled();
            expect(dashboard.programsTable.refresh).toHaveBeenCalled();
        });

        test('should persist state to localStorage', () => {
            const localStorageSpy = jest.spyOn(Storage.prototype, 'setItem');
            
            dashboard.setState({ selectedPeriod: '2024-Q1' });
            
            expect(localStorageSpy).toHaveBeenCalledWith(
                'dashboard_state',
                expect.any(String)
            );
            
            localStorageSpy.mockRestore();
        });
    });

    describe('Cleanup', () => {
        test('should destroy all components', () => {
            dashboard = new AgencyDashboard();
            
            dashboard.destroy();
            
            expect(dashboard.chart.destroy).toHaveBeenCalled();
            expect(dashboard.logic.destroy).toHaveBeenCalled();
            expect(dashboard.carousel.destroy).toHaveBeenCalled();
            expect(dashboard.programsTable.destroy).toHaveBeenCalled();
        });

        test('should clear all references', () => {
            dashboard = new AgencyDashboard();
            
            dashboard.destroy();
            
            expect(dashboard.chart).toBeNull();
            expect(dashboard.logic).toBeNull();
            expect(dashboard.carousel).toBeNull();
            expect(dashboard.programsTable).toBeNull();
        });

        test('should handle multiple destroy calls', () => {
            dashboard = new AgencyDashboard();
            
            expect(() => {
                dashboard.destroy();
                dashboard.destroy(); // Should not throw
            }).not.toThrow();
        });
    });
});
