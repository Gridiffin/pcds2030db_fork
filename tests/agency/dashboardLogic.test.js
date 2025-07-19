/**
 * Dashboard Logic Component Unit Tests  
 * Tests dashboard interactions, AJAX calls, and general functionality
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

// Import the component
import { DashboardLogic } from '../../assets/js/agency/dashboard/logic.js';

describe('DashboardLogic', () => {
    let logicComponent;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
        localStorageMock.clear();
        
        // Reset DOM
        document.body.innerHTML = `
            <button id="refreshDashboard">Refresh</button>
            <button id="refreshPage">Legacy Refresh</button>
            <input type="checkbox" id="includeAssignedToggle">
            <div class="bento-card primary">
                <div class="display-4">10</div>
            </div>
            <div class="bento-card success">
                <div class="display-4">8</div>
            </div>
            <div class="bento-card warning">
                <div class="display-4">2</div>
            </div>
            <div class="bento-card info">
                <div class="display-4">0</div>
            </div>
        `;
        
        // Mock window.location.reload
        delete window.location;
        window.location = { reload: jest.fn() };
    });

    afterEach(() => {
        // Clean up
        if (logicComponent) {
            jest.clearAllTimers();
        }
    });

    describe('Initialization', () => {
        test('should initialize event listeners successfully', () => {
            logicComponent = new DashboardLogic();
            
            const refreshButton = document.getElementById('refreshDashboard');
            const toggle = document.getElementById('includeAssignedToggle');
            
            expect(refreshButton).toBeTruthy();
            expect(toggle).toBeTruthy();
        });

        test('should load saved toggle preference from localStorage', () => {
            localStorageMock.setItem('includeAssignedPrograms', 'true');
            
            logicComponent = new DashboardLogic();
            
            const toggle = document.getElementById('includeAssignedToggle');
            expect(toggle.checked).toBe(true);
        });

        test('should default toggle to false when no preference saved', () => {
            logicComponent = new DashboardLogic();
            
            const toggle = document.getElementById('includeAssignedToggle');
            expect(toggle.checked).toBe(false);
        });
    });

    describe('Refresh Functionality', () => {
        beforeEach(() => {
            jest.useFakeTimers();
            logicComponent = new DashboardLogic();
        });

        afterEach(() => {
            jest.useRealTimers();
        });

        test('should handle refresh button click', () => {
            const refreshButton = document.getElementById('refreshDashboard');
            
            refreshButton.click();
            
            expect(refreshButton.classList.contains('loading')).toBe(true);
            expect(refreshButton.disabled).toBe(true);
            expect(refreshButton.innerHTML).toContain('Refreshing...');
            
            // Fast-forward timers
            jest.advanceTimersByTime(500);
            
            expect(window.location.reload).toHaveBeenCalled();
        });

        test('should handle legacy refresh button', () => {
            const legacyButton = document.getElementById('refreshPage');
            
            legacyButton.click();
            
            expect(legacyButton.classList.contains('loading')).toBe(true);
            expect(legacyButton.disabled).toBe(true);
            
            jest.advanceTimersByTime(500);
            expect(window.location.reload).toHaveBeenCalled();
        });
    });

    describe('Assigned Toggle Functionality', () => {
        beforeEach(() => {
            // Mock successful fetch response
            fetch.mockResolvedValue({
                json: () => Promise.resolve({
                    success: true,
                    stats: { total: 15, 'on-track': 10, delayed: 3, completed: 2 },
                    chart_data: { labels: ['Test'], data: [1] },
                    recent_updates: []
                })
            });
            
            logicComponent = new DashboardLogic();
        });

        test('should save toggle preference to localStorage', () => {
            const toggle = document.getElementById('includeAssignedToggle');
            
            toggle.checked = true;
            toggle.dispatchEvent(new Event('change'));
            
            expect(localStorageMock.setItem).toHaveBeenCalledWith('includeAssignedPrograms', 'true');
        });

        test('should make AJAX request when toggle changes', async () => {
            const toggle = document.getElementById('includeAssignedToggle');
            
            toggle.checked = true;
            toggle.dispatchEvent(new Event('change'));
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(fetch).toHaveBeenCalledWith('ajax/agency_dashboard_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    period_id: null,
                    include_assigned: true
                })
            });
        });

        test('should update stats cards with new data', async () => {
            const mockData = {
                success: true,
                stats: { total: 20, 'on-track': 15, delayed: 3, completed: 2 }
            };
            
            fetch.mockResolvedValue({
                json: () => Promise.resolve(mockData)
            });
            
            const toggle = document.getElementById('includeAssignedToggle');
            toggle.checked = true;
            toggle.dispatchEvent(new Event('change'));
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            const totalElement = document.querySelector('.bento-card.primary .display-4');
            expect(totalElement.textContent).toBe('20');
        });

        test('should handle AJAX error gracefully', async () => {
            fetch.mockRejectedValue(new Error('Network error'));
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            
            const toggle = document.getElementById('includeAssignedToggle');
            toggle.checked = true;
            toggle.dispatchEvent(new Event('change'));
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(consoleSpy).toHaveBeenCalledWith('❌ AJAX error:', expect.any(Error));
            consoleSpy.mockRestore();
        });
    });

    describe('Stats Card Updates', () => {
        beforeEach(() => {
            logicComponent = new DashboardLogic();
        });

        test('should update all stat cards correctly', () => {
            const stats = {
                total: 25,
                'on-track': 20,
                delayed: 3,
                completed: 2
            };
            
            logicComponent.updateStatsCards(stats);
            
            const elements = document.querySelectorAll('.display-4');
            expect(elements[0].textContent).toBe('25'); // total
            expect(elements[1].textContent).toBe('20'); // on-track
            expect(elements[2].textContent).toBe('3');  // delayed
            expect(elements[3].textContent).toBe('2');  // completed
        });

        test('should calculate percentages correctly', () => {
            document.body.innerHTML += `
                <div class="opacity-75">50% of total</div>
                <div class="opacity-75">25% of total</div>
                <div class="opacity-75">25% of total</div>
            `;
            
            const stats = {
                total: 100,
                'on-track': 80,
                delayed: 15,
                completed: 5
            };
            
            logicComponent = new DashboardLogic();
            logicComponent.updateStatsCards(stats);
            
            const percentageElements = document.querySelectorAll('.opacity-75');
            expect(percentageElements[0].textContent).toBe('80% of total');
            expect(percentageElements[1].textContent).toBe('15% of total');
            expect(percentageElements[2].textContent).toBe('5% of total');
        });

        test('should handle zero total gracefully', () => {
            const stats = {
                total: 0,
                'on-track': 0,
                delayed: 0,
                completed: 0
            };
            
            logicComponent.updateStatsCards(stats);
            
            const elements = document.querySelectorAll('.display-4');
            expect(elements[0].textContent).toBe('0');
        });
    });

    describe('Card Interactions', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <div class="bento-card" style="transform: translateY(0);">Card 1</div>
                <div class="bento-card" style="transform: translateY(0);">Card 2</div>
            `;
            logicComponent = new DashboardLogic();
        });

        test('should add hover effects to bento cards', () => {
            const cards = document.querySelectorAll('.bento-card');
            
            // Simulate mouseenter
            cards[0].dispatchEvent(new Event('mouseenter'));
            expect(cards[0].style.transform).toBe('translateY(-4px)');
            
            // Simulate mouseleave
            cards[0].dispatchEvent(new Event('mouseleave'));
            expect(cards[0].style.transform).toBe('translateY(0px)');
        });
    });

    describe('Loading States', () => {
        beforeEach(() => {
            logicComponent = new DashboardLogic();
        });

        test('should show loading state on stat cards', () => {
            logicComponent.showLoadingState();
            
            const elements = document.querySelectorAll('.display-4');
            elements.forEach(element => {
                expect(element.style.opacity).toBe('0.5');
            });
        });

        test('should hide loading state', () => {
            // First show loading
            logicComponent.showLoadingState();
            
            // Then hide it
            logicComponent.hideLoadingState();
            
            const elements = document.querySelectorAll('.display-4');
            elements.forEach(element => {
                expect(element.style.opacity).toBe('1');
            });
        });
    });

    describe('Refresh Method', () => {
        test('should refresh with current toggle state', () => {
            const toggle = document.getElementById('includeAssignedToggle');
            toggle.checked = true;
            
            fetch.mockResolvedValue({
                json: () => Promise.resolve({ success: true, stats: {}, chart_data: {}, recent_updates: [] })
            });
            
            logicComponent = new DashboardLogic();
            logicComponent.refresh();
            
            expect(fetch).toHaveBeenCalledWith('ajax/agency_dashboard_data.php', 
                expect.objectContaining({
                    body: expect.stringContaining('"include_assigned":true')
                })
            );
        });
    });
});
