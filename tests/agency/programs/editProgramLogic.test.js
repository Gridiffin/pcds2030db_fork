/**
 * Edit Program Logic Tests
 * Tests for program editing functionality including status management
 */

import { initEditProgram } from '../../../assets/js/agency/programs/editProgramLogic.js';

// Mock Bootstrap
global.bootstrap = {
    Modal: jest.fn().mockImplementation(() => ({
        show: jest.fn(),
        hide: jest.fn()
    }))
};

// Mock global fetch
global.fetch = jest.fn();

// Mock window object
global.window = {
    PCDS_VARS: {
        programId: '123',
        APP_URL: 'http://localhost/pcds2030_dashboard_fork'
    },
    showToast: jest.fn(),
    confirm: jest.fn()
};

// Mock console methods
global.console = {
    ...console,
    error: jest.fn(),
    warn: jest.fn()
};

describe('Edit Program Logic', () => {
    beforeEach(() => {
        // Clear DOM
        document.body.innerHTML = '';
        
        // Reset all mocks
        fetch.mockClear();
        window.showToast.mockClear();
        window.confirm.mockClear();
        console.error.mockClear();
        console.warn.mockClear();
        bootstrap.Modal.mockClear();
    });

    describe('Initialization', () => {
        test('initializes successfully with valid PCDS_VARS', () => {
            // Create required DOM elements
            createBasicDOM();
            
            expect(() => initEditProgram()).not.toThrow();
        });

        test('logs error when PCDS_VARS is missing', () => {
            const originalPCDS = window.PCDS_VARS;
            delete window.PCDS_VARS;
            
            initEditProgram();
            
            expect(console.error).toHaveBeenCalledWith('PCDS_VARS (programId, APP_URL) not found on window object.');
            
            window.PCDS_VARS = originalPCDS;
        });

        test('logs error when programId is missing', () => {
            window.PCDS_VARS.programId = undefined;
            
            initEditProgram();
            
            expect(console.error).toHaveBeenCalledWith('PCDS_VARS (programId, APP_URL) not found on window object.');
            
            window.PCDS_VARS.programId = '123';
        });

        test('handles missing DOM elements gracefully', () => {
            // Don't create DOM elements - should not crash
            expect(() => initEditProgram()).not.toThrow();
        });
    });

    describe('Form Logic', () => {
        beforeEach(() => {
            createFormDOM();
        });

        test('enables program number input when initiative is selected', () => {
            const initiativeSelect = document.getElementById('initiative_id');
            const programNumberInput = document.getElementById('program_number');
            
            // Initialize the logic
            initEditProgram();
            
            // Select an initiative
            initiativeSelect.value = '1';
            initiativeSelect.dispatchEvent(new Event('change'));
            
            expect(programNumberInput.disabled).toBe(false);
            expect(programNumberInput.placeholder).toBe('Enter program number');
        });

        test('disables program number input when no initiative selected', () => {
            const initiativeSelect = document.getElementById('initiative_id');
            const programNumberInput = document.getElementById('program_number');
            
            initEditProgram();
            
            // No initiative selected
            initiativeSelect.value = '';
            initiativeSelect.dispatchEvent(new Event('change'));
            
            expect(programNumberInput.disabled).toBe(true);
            expect(programNumberInput.placeholder).toBe('Select initiative first');
        });

        test('validates program number format on input', () => {
            const programNumberInput = document.getElementById('program_number');
            const validationMessage = document.getElementById('validation-message');
            
            initEditProgram();
            
            // Valid input
            programNumberInput.value = '1.1.A';
            programNumberInput.dispatchEvent(new Event('input'));
            
            expect(validationMessage.className).toBe('text-success');
            expect(validationMessage.textContent).toBe('Valid format');
        });

        test('shows error for invalid program number format', () => {
            const programNumberInput = document.getElementById('program_number');
            const validationMessage = document.getElementById('validation-message');
            
            initEditProgram();
            
            // Invalid input
            programNumberInput.value = '1.1@invalid';
            programNumberInput.dispatchEvent(new Event('input'));
            
            expect(validationMessage.className).toBe('text-danger');
            expect(validationMessage.textContent).toBe('Invalid format. Use only letters, numbers, and dots.');
        });

        test('toggles user selection section based on restrict editors checkbox', () => {
            const restrictEditorsToggle = document.getElementById('restrict_editors');
            const userSelectionSection = document.getElementById('userSelectionSection');
            
            initEditProgram();
            
            // Check the toggle
            restrictEditorsToggle.checked = true;
            restrictEditorsToggle.dispatchEvent(new Event('change'));
            
            expect(userSelectionSection.style.display).toBe('block');
            
            // Uncheck the toggle
            restrictEditorsToggle.checked = false;
            restrictEditorsToggle.dispatchEvent(new Event('change'));
            
            expect(userSelectionSection.style.display).toBe('none');
        });

        test('updates final number preview on input', () => {
            const programNumberInput = document.getElementById('program_number');
            const finalNumberPreview = document.getElementById('final-number-preview');
            
            initEditProgram();
            
            // Enter a number
            programNumberInput.value = '1.2.3';
            programNumberInput.dispatchEvent(new Event('input'));
            
            expect(finalNumberPreview.textContent).toBe('1.2.3');
            
            // Clear the number
            programNumberInput.value = '';
            programNumberInput.dispatchEvent(new Event('input'));
            
            expect(finalNumberPreview.textContent).toBe('Will be generated automatically');
        });
    });

    describe('Status Management', () => {
        beforeEach(() => {
            createStatusDOM();
            
            // Mock successful fetch responses
            fetch.mockResolvedValue({
                ok: true,
                json: async () => ({
                    status: 'active',
                    hold_point: null
                })
            });
        });

        test('renders status badge correctly', async () => {
            const statusBadge = document.getElementById('program-status-badge');
            
            initEditProgram();
            
            // Wait for async operations
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(statusBadge.innerHTML).toContain('Active');
            expect(statusBadge.className).toContain('bg-success');
        });

        test('handles unknown status gracefully', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({
                    status: 'unknown_status'
                })
            });
            
            const statusBadge = document.getElementById('program-status-badge');
            
            initEditProgram();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(statusBadge.innerHTML).toContain('Unknown Status');
            expect(statusBadge.className).toContain('bg-secondary');
        });

        test('shows hold point fields when status is on_hold', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({
                    status: 'on_hold',
                    hold_point: {
                        reason: 'Budget constraints',
                        remarks: 'Waiting for approval'
                    }
                })
            });
            
            const holdSection = document.getElementById('holdPointManagementSection');
            const holdReason = document.getElementById('hold_reason');
            const holdRemarks = document.getElementById('hold_remarks');
            
            initEditProgram();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(holdSection.style.display).toBe('');
            expect(holdReason.value).toBe('Budget constraints');
            expect(holdRemarks.value).toBe('Waiting for approval');
        });

        test('hides hold point fields when status is not on_hold', async () => {
            const holdSection = document.getElementById('holdPointManagementSection');
            
            initEditProgram();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(holdSection.style.display).toBe('none');
        });

        test('opens edit status modal on edit button click', async () => {
            const editBtn = document.getElementById('edit-status-btn');
            const mockModal = { show: jest.fn() };
            bootstrap.Modal.mockReturnValue(mockModal);
            
            initEditProgram();
            
            editBtn.click();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(fetch).toHaveBeenCalledWith(
                'http://localhost/pcds2030_dashboard_fork/app/api/program_status.php?action=status&program_id=123'
            );
            expect(mockModal.show).toHaveBeenCalled();
        });

        test('opens status history modal on history button click', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({
                    status_history: [
                        { status: 'active', changed_by: '1', changed_at: '2025-01-01', remarks: 'Started' }
                    ],
                    hold_points: []
                })
            });
            
            const historyBtn = document.getElementById('view-status-history-btn');
            const mockModal = { show: jest.fn() };
            bootstrap.Modal.mockReturnValue(mockModal);
            
            initEditProgram();
            
            historyBtn.click();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(fetch).toHaveBeenCalledWith(
                'http://localhost/pcds2030_dashboard_fork/app/api/program_status.php?action=status_history&program_id=123'
            );
            expect(mockModal.show).toHaveBeenCalled();
        });
    });

    describe('Hold Point Management', () => {
        beforeEach(() => {
            createHoldPointDOM();
            
            fetch.mockResolvedValue({
                ok: true,
                json: async () => ({ success: true })
            });
        });

        test('updates hold point with valid data', async () => {
            const updateBtn = document.getElementById('updateHoldPointBtn');
            const holdReason = document.getElementById('hold_reason');
            const holdRemarks = document.getElementById('hold_remarks');
            
            holdReason.value = 'Budget approval pending';
            holdRemarks.value = 'Waiting for Q2 budget';
            
            initEditProgram();
            
            updateBtn.click();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(fetch).toHaveBeenCalledWith(
                'http://localhost/pcds2030_dashboard_fork/app/api/program_status.php',
                expect.objectContaining({
                    method: 'POST',
                    body: expect.any(FormData)
                })
            );
            expect(window.showToast).toHaveBeenCalledWith('Success', 'Hold point updated.', 'success');
        });

        test('shows validation error for empty hold reason', () => {
            const updateBtn = document.getElementById('updateHoldPointBtn');
            const holdReason = document.getElementById('hold_reason');
            
            holdReason.value = '   '; // Only whitespace
            
            initEditProgram();
            
            updateBtn.click();
            
            expect(window.showToast).toHaveBeenCalledWith('Validation Error', 'Hold reason is required.', 'warning');
            expect(fetch).not.toHaveBeenCalled();
        });

        test('ends hold point with confirmation', async () => {
            window.confirm.mockReturnValue(true);
            
            const endBtn = document.getElementById('endHoldPointBtn');
            
            initEditProgram();
            
            endBtn.click();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(window.confirm).toHaveBeenCalledWith('Are you sure you want to end this hold point? The program status will become "Active".');
            expect(fetch).toHaveBeenCalled();
            expect(window.showToast).toHaveBeenCalledWith('Success', 'Hold point ended.', 'success');
        });

        test('cancels end hold point without confirmation', () => {
            window.confirm.mockReturnValue(false);
            
            const endBtn = document.getElementById('endHoldPointBtn');
            
            initEditProgram();
            
            endBtn.click();
            
            expect(window.confirm).toHaveBeenCalled();
            expect(fetch).not.toHaveBeenCalled();
        });

        test('handles API errors gracefully', async () => {
            fetch.mockResolvedValueOnce({
                ok: true,
                json: async () => ({ success: false, error: 'Database error' })
            });
            
            const updateBtn = document.getElementById('updateHoldPointBtn');
            const holdReason = document.getElementById('hold_reason');
            
            holdReason.value = 'Test reason';
            
            initEditProgram();
            
            updateBtn.click();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(window.showToast).toHaveBeenCalledWith('Error', 'Database error', 'danger');
        });
    });

    describe('Error Handling', () => {
        beforeEach(() => {
            createBasicDOM();
        });

        test('handles fetch errors gracefully', async () => {
            fetch.mockRejectedValue(new Error('Network error'));
            
            const editBtn = document.getElementById('edit-status-btn');
            
            initEditProgram();
            
            editBtn.click();
            
            await new Promise(resolve => setTimeout(resolve, 0));
            
            expect(console.error).toHaveBeenCalledWith('Failed to load status:', expect.any(Error));
        });

        test('falls back to alert when showToast is not available', () => {
            delete window.showToast;
            global.alert = jest.fn();
            
            const updateBtn = document.getElementById('updateHoldPointBtn');
            const holdReason = document.getElementById('hold_reason');
            
            holdReason.value = '   '; // Empty reason to trigger validation
            
            initEditProgram();
            
            updateBtn.click();
            
            expect(console.warn).toHaveBeenCalledWith('showToast function not found. Implement or include a toast library.');
            expect(global.alert).toHaveBeenCalledWith('Validation Error: Hold reason is required.');
            
            // Restore showToast
            window.showToast = jest.fn();
        });
    });
});

// Helper functions to create DOM elements for testing

function createBasicDOM() {
    document.body.innerHTML = `
        <div id="program-status-badge"></div>
        <button id="edit-status-btn">Edit Status</button>
        <button id="view-status-history-btn">View History</button>
        <div id="editStatusModal">
            <div id="edit-status-modal-body"></div>
        </div>
        <div id="statusHistoryModal">
            <div id="status-history-modal-body"></div>
        </div>
    `;
}

function createFormDOM() {
    document.body.innerHTML = `
        <select id="initiative_id">
            <option value="">Select Initiative</option>
            <option value="1">Initiative 1</option>
        </select>
        <input type="text" id="program_number" />
        <div id="number-help-text"></div>
        <div id="final-number-display" style="display: none;">
            <span id="final-number-preview"></span>
        </div>
        <div id="number-validation" style="display: none;">
            <span id="validation-message"></span>
        </div>
        <input type="checkbox" id="restrict_editors" />
        <div id="userSelectionSection" style="display: none;"></div>
    `;
}

function createStatusDOM() {
    createBasicDOM();
    document.body.innerHTML += `
        <div id="holdPointManagementSection" style="display: none;">
            <input type="text" id="hold_reason" />
            <textarea id="hold_remarks"></textarea>
        </div>
    `;
}

function createHoldPointDOM() {
    createStatusDOM();
    document.body.innerHTML += `
        <button id="updateHoldPointBtn">Update Hold Point</button>
        <button id="endHoldPointBtn">End Hold Point</button>
    `;
}
