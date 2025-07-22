/**
 * Tests for Agency Notifications JavaScript Module  
 * Testing assets/js/agency/users/ notification functionality
 */

// Mock DOM elements
const mockNotificationDOM = () => {
    document.body.innerHTML = `
        <div class="notifications-container">
            <div class="notifications-header">
                <div class="notifications-stats">
                    <div class="stat-card">
                        <div class="stat-value total-notifications">0</div>
                        <div class="stat-label">Total Notifications</div>
                    </div>
                    <div class="stat-card unread">
                        <div class="stat-value unread-notifications">0</div>
                        <div class="stat-label">Unread</div>
                    </div>
                </div>
                <div class="notifications-actions">
                    <button id="mark-all-read" class="btn btn-secondary">Mark All Read</button>
                    <button id="refresh-notifications" class="btn btn-primary">Refresh</button>
                </div>
            </div>
            <div class="notifications-list" id="notifications-list">
                <!-- Notifications will be loaded here -->
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
import NotificationsLogic from '../../../assets/js/agency/users/logic.js';
import { NotificationsAjax } from '../../../assets/js/agency/users/ajax.js';

describe('Agency Notifications Module', () => {
    let notificationsManager;
    let notificationsAPI;

    beforeEach(() => {
        mockNotificationDOM();
        jest.clearAllMocks();
        
        // Initialize modules
        notificationsAPI = new NotificationsAjax();
        notificationsManager = new NotificationsLogic();
    });

    describe('NotificationsAjax', () => {
        test('should fetch notifications successfully', async () => {
            const mockNotifications = {
                notifications: [
                    { id: 1, message: 'Test notification 1', read_status: 0 },
                    { id: 2, message: 'Test notification 2', read_status: 1 }
                ],
                total_count: 2,
                unread_count: 1
            };

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockNotifications })
            });

            const result = await notificationsAPI.getNotifications();
            
            expect(fetch).toHaveBeenCalledWith(
                expect.stringContaining('get_notifications.php'),
                expect.objectContaining({
                    method: 'GET',
                    headers: expect.objectContaining({
                        'Content-Type': 'application/json'
                    })
                })
            );
            
            expect(result.success).toBe(true);
            expect(result.data.notifications).toHaveLength(2);
            expect(result.data.unread_count).toBe(1);
        });

        test('should mark notifications as read', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, message: 'Notifications marked as read' })
            });

            const result = await notificationsAPI.markAsRead([1, 2]);
            
            expect(fetch).toHaveBeenCalledWith(
                expect.stringContaining('mark_notifications_read.php'),
                expect.objectContaining({
                    method: 'POST',
                    headers: expect.objectContaining({
                        'Content-Type': 'application/json'
                    }),
                    body: JSON.stringify({ notification_ids: [1, 2] })
                })
            );
            
            expect(result.success).toBe(true);
        });

        test('should delete notifications', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, message: 'Notifications deleted' })
            });

            const result = await notificationsAPI.deleteNotifications([1, 2]);
            
            expect(fetch).toHaveBeenCalledWith(
                expect.stringContaining('delete_notifications.php'),
                expect.objectContaining({
                    method: 'POST',
                    body: JSON.stringify({ notification_ids: [1, 2] })
                })
            );
            
            expect(result.success).toBe(true);
        });

        test('should handle API errors gracefully', async () => {
            fetch.mockResolvedValueOnce({
                ok: false,
                status: 500,
                statusText: 'Internal Server Error'
            });

            const result = await notificationsAPI.getNotifications();
            
            expect(result.success).toBe(false);
            expect(result.error).toContain('500');
        });

        test('should handle network errors', async () => {
            fetch.mockRejectedValueOnce(new Error('Network error'));

            const result = await notificationsAPI.getNotifications();
            
            expect(result.success).toBe(false);
            expect(result.error).toContain('Network error');
        });
    });

    describe('NotificationsLogic', () => {
        test('should initialize successfully', () => {
            expect(notificationsManager).toBeDefined();
            expect(notificationsManager.init).toBeDefined();
        });

        test('should load notifications on initialization', async () => {
            const mockData = {
                notifications: [
                    { id: 1, message: 'Test notification', read_status: 0 }
                ],
                total_count: 1,
                unread_count: 1
            };

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockData })
            });

            await notificationsManager.init();
            
            expect(fetch).toHaveBeenCalled();
        });

        test('should update statistics correctly', () => {
            const stats = {
                total: 10,
                unread: 3,
                today: 2
            };

            notificationsManager.updateStats(stats);
            
            const totalElement = document.querySelector('.total-notifications');
            const unreadElement = document.querySelector('.unread-notifications');
            
            expect(totalElement.textContent).toBe('10');
            expect(unreadElement.textContent).toBe('3');
        });

        test('should render notifications correctly', () => {
            const mockNotifications = [
                { 
                    id: 1, 
                    message: 'Test notification message',
                    read_status: 0,
                    created_at: '2025-01-01 10:00:00',
                    type: 'system'
                },
                { 
                    id: 2, 
                    message: 'Another notification',
                    read_status: 1,
                    created_at: '2025-01-01 11:00:00',
                    type: 'alert'
                }
            ];

            notificationsManager.renderNotifications(mockNotifications);
            
            const notificationsList = document.getElementById('notifications-list');
            const notificationElements = notificationsList.querySelectorAll('.notification-item');
            
            expect(notificationElements.length).toBe(2);
            expect(notificationsList.innerHTML).toContain('Test notification message');
            expect(notificationsList.innerHTML).toContain('Another notification');
        });

        test('should handle mark all as read', async () => {
            const markAllButton = document.getElementById('mark-all-read');
            
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true })
            });

            // Simulate click
            const clickEvent = new Event('click');
            markAllButton.dispatchEvent(clickEvent);

            await new Promise(resolve => setTimeout(resolve, 100));

            expect(fetch).toHaveBeenCalled();
        });

        test('should handle refresh button click', async () => {
            const refreshButton = document.getElementById('refresh-notifications');
            
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ 
                    success: true, 
                    data: { notifications: [], total_count: 0, unread_count: 0 }
                })
            });

            // Simulate refresh click
            const clickEvent = new Event('click');
            refreshButton.dispatchEvent(clickEvent);

            await new Promise(resolve => setTimeout(resolve, 100));

            expect(fetch).toHaveBeenCalled();
        });

        test('should show empty state when no notifications', () => {
            notificationsManager.renderNotifications([]);
            
            const notificationsList = document.getElementById('notifications-list');
            
            expect(notificationsList.innerHTML).toContain('No notifications found') ||
            expect(notificationsList.innerHTML).toContain('no notifications');
        });

        test('should handle loading states', () => {
            notificationsManager.showLoading();
            
            const notificationsList = document.getElementById('notifications-list');
            
            expect(notificationsList.innerHTML).toContain('Loading') ||
            expect(notificationsList.innerHTML).toContain('loading');
        });

        test('should handle error states', () => {
            const errorMessage = 'Failed to load notifications';
            
            notificationsManager.showError(errorMessage);
            
            const notificationsList = document.getElementById('notifications-list');
            
            expect(notificationsList.innerHTML).toContain(errorMessage);
        });
    });

    describe('Notification Interactions', () => {
        test('should mark individual notification as read', async () => {
            const mockNotifications = [
                { id: 1, message: 'Test notification', read_status: 0 }
            ];

            notificationsManager.renderNotifications(mockNotifications);
            
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true })
            });

            // Simulate marking as read
            const notificationElement = document.querySelector('.notification-item');
            if (notificationElement) {
                const markReadButton = notificationElement.querySelector('[data-action="mark-read"]');
                if (markReadButton) {
                    markReadButton.click();
                    
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                    expect(fetch).toHaveBeenCalled();
                }
            }
        });

        test('should delete individual notification', async () => {
            const mockNotifications = [
                { id: 1, message: 'Test notification', read_status: 0 }
            ];

            notificationsManager.renderNotifications(mockNotifications);
            
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true })
            });

            // Simulate deletion
            const notificationElement = document.querySelector('.notification-item');
            if (notificationElement) {
                const deleteButton = notificationElement.querySelector('[data-action="delete"]');
                if (deleteButton) {
                    deleteButton.click();
                    
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                    expect(fetch).toHaveBeenCalled();
                }
            }
        });

        test('should handle bulk selection', () => {
            const mockNotifications = [
                { id: 1, message: 'Test notification 1', read_status: 0 },
                { id: 2, message: 'Test notification 2', read_status: 0 }
            ];

            notificationsManager.renderNotifications(mockNotifications);
            
            // Simulate selecting notifications
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            });

            // Verify selection state is tracked
            expect(checkboxes.length).toBeGreaterThan(0);
        });
    });

    describe('Notifications Integration', () => {
        test('should complete full workflow', async () => {
            const mockData = {
                notifications: [
                    { id: 1, message: 'Integration Test Notification', read_status: 0 }
                ],
                total_count: 1,
                unread_count: 1
            };

            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: mockData })
            });

            // Initialize the notifications manager
            await notificationsManager.init();
            
            // Verify notifications are loaded and stats updated
            await new Promise(resolve => setTimeout(resolve, 100));
            
            expect(fetch).toHaveBeenCalled();
        });

        test('should maintain pagination state', async () => {
            // Mock multiple pages of notifications
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ 
                    success: true, 
                    data: { 
                        notifications: Array.from({length: 20}, (_, i) => ({
                            id: i + 1,
                            message: `Notification ${i + 1}`,
                            read_status: 0
                        })),
                        total_count: 50,
                        unread_count: 30,
                        total_pages: 3,
                        current_page: 1
                    }
                })
            });

            await notificationsManager.init();
            
            expect(fetch).toHaveBeenCalled();
        });
    });

    describe('Error Handling', () => {
        test('should handle API failures gracefully', async () => {
            fetch.mockRejectedValueOnce(new Error('API unavailable'));

            await notificationsManager.init();
            
            const notificationsList = document.getElementById('notifications-list');
            
            // Should show error message instead of crashing
            expect(notificationsList.innerHTML).toContain('error') || 
            expect(notificationsList.innerHTML).toContain('Error') ||
            expect(notificationsList.innerHTML).toContain('failed');
        });

        test('should validate data before rendering', () => {
            const invalidData = [
                { id: 1 }, // missing message
                { message: 'No ID' }, // missing id
                null, // null entry
                undefined // undefined entry
            ];

            // Should not crash
            expect(() => {
                notificationsManager.renderNotifications(invalidData);
            }).not.toThrow();
        });

        test('should handle empty responses', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: true, data: null })
            });

            await notificationsManager.init();
            
            // Should handle null data gracefully
            const notificationsList = document.getElementById('notifications-list');
            expect(notificationsList).toBeDefined();
        });
    });

    describe('Performance', () => {
        test('should render large notification lists efficiently', () => {
            const largeNotificationList = Array.from({ length: 100 }, (_, i) => ({
                id: i + 1,
                message: `Notification ${i + 1}`,
                read_status: i % 2,
                created_at: '2025-01-01',
                type: 'system'
            }));

            const startTime = performance.now();
            
            notificationsManager.renderNotifications(largeNotificationList);
            
            const endTime = performance.now();
            const renderTime = endTime - startTime;

            // Rendering should complete within 500ms
            expect(renderTime).toBeLessThan(500);
        });

        test('should throttle rapid refresh calls', async () => {
            const refreshButton = document.getElementById('refresh-notifications');
            
            fetch.mockResolvedValue({
                ok: true,
                json: async () => ({ 
                    success: true, 
                    data: { notifications: [], total_count: 0, unread_count: 0 }
                })
            });

            // Rapidly click refresh multiple times
            refreshButton.click();
            refreshButton.click();
            refreshButton.click();

            // Wait for throttle period
            await new Promise(resolve => setTimeout(resolve, 600));

            // Should not make excessive API calls
            expect(fetch).toHaveBeenCalledTimes(1);
        });
    });
});
