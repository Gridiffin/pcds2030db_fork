/**
 * Dashboard Programs Table Component Unit Tests
 * Tests the programs table functionality
 */

import { ProgramsTable } from '../../assets/js/agency/dashboard/programs.js';

// Mock AJAX functions
global.$ = {
    post: jest.fn(),
    get: jest.fn()
};

describe('ProgramsTable', () => {
    let programsTable;
    let mockPrograms;

    beforeEach(() => {
        // Sample program data
        mockPrograms = [
            { id: 1, program_number: 'P001', title: 'Forest Conservation', status: 'active', progress: 75 },
            { id: 2, program_number: 'P002', title: 'Timber Management', status: 'draft', progress: 50 },
            { id: 3, program_number: 'P003', title: 'Wildlife Protection', status: 'completed', progress: 100 }
        ];

        // Reset DOM
        document.body.innerHTML = `
            <div class="programs-section">
                <div class="table-controls">
                    <input type="text" id="programSearchInput" placeholder="Search programs...">
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                        <option value="completed">Completed</option>
                    </select>
                    <select id="sortSelect">
                        <option value="title">Title</option>
                        <option value="status">Status</option>
                        <option value="progress">Progress</option>
                    </select>
                    <button id="sortOrderBtn">↑</button>
                    <button id="refreshProgramsBtn">Refresh</button>
                </div>
                <div id="programsTableContainer">
                    <table id="programsTable">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <button id="prevPage">Previous</button>
                    <span id="pageInfo">Page 1 of 1</span>
                    <button id="nextPage">Next</button>
                </div>
                <div id="loadingSpinner" style="display: none;">Loading...</div>
            </div>
        `;

        // Reset AJAX mocks
        $.post.mockClear();
        $.get.mockClear();
    });

    afterEach(() => {
        if (programsTable) {
            programsTable.destroy();
        }
    });

    describe('Initialization', () => {
        test('should initialize with default settings', () => {
            programsTable = new ProgramsTable();
            
            expect(programsTable.currentPage).toBe(1);
            expect(programsTable.itemsPerPage).toBe(10);
            expect(programsTable.sortBy).toBe('title');
            expect(programsTable.sortOrder).toBe('asc');
        });

        test('should load programs on initialization', () => {
            programsTable = new ProgramsTable();
            
            expect($.post).toHaveBeenCalledWith(
                expect.stringContaining('get_program_submissions_list.php'),
                expect.objectContaining({
                    page: 1,
                    limit: 10,
                    sort_by: 'title',
                    sort_order: 'asc'
                }),
                expect.any(Function),
                'json'
            );
        });

        test('should setup event listeners', () => {
            programsTable = new ProgramsTable();
            
            // Test search input event
            const searchInput = document.getElementById('programSearchInput');
            const spy = jest.spyOn(programsTable, 'handleSearch');
            
            searchInput.value = 'test';
            searchInput.dispatchEvent(new Event('input'));
            
            expect(spy).toHaveBeenCalled();
        });
    });

    describe('Data Loading', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
        });

        test('should load programs successfully', () => {
            const mockResponse = {
                success: true,
                programs: mockPrograms,
                total: 3,
                totalPages: 1
            };

            // Mock successful AJAX response
            $.post.mockImplementation((url, data, callback) => {
                callback(mockResponse);
            });

            programsTable.loadPrograms();

            expect(programsTable.allPrograms).toEqual(mockPrograms);
            expect(programsTable.totalPages).toBe(1);
        });

        test('should handle loading error', () => {
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();
            const mockResponse = {
                success: false,
                message: 'Database error'
            };

            $.post.mockImplementation((url, data, callback) => {
                callback(mockResponse);
            });

            programsTable.loadPrograms();

            expect(consoleSpy).toHaveBeenCalledWith('Failed to load programs:', 'Database error');
            consoleSpy.mockRestore();
        });

        test('should handle network error', () => {
            const consoleSpy = jest.spyOn(console, 'error').mockImplementation();

            $.post.mockImplementation((url, data, callback, dataType) => {
                // Simulate network error
                throw new Error('Network error');
            });

            programsTable.loadPrograms();

            expect(consoleSpy).toHaveBeenCalledWith('Error loading programs:', expect.any(Error));
            consoleSpy.mockRestore();
        });

        test('should show loading spinner during load', () => {
            programsTable = new ProgramsTable();
            const spinner = document.getElementById('loadingSpinner');
            
            // Mock delayed response
            $.post.mockImplementation((url, data, callback) => {
                expect(spinner.style.display).toBe('block');
                setTimeout(() => callback({ success: true, programs: [], total: 0 }), 100);
            });
            
            programsTable.loadPrograms();
        });
    });

    describe('Table Rendering', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
            programsTable.allPrograms = mockPrograms;
        });

        test('should render programs table correctly', () => {
            programsTable.renderTable(mockPrograms);
            
            const tbody = document.querySelector('#programsTable tbody');
            const rows = tbody.querySelectorAll('tr');
            
            expect(rows.length).toBe(3);
            expect(rows[0].textContent).toContain('P001');
            expect(rows[0].textContent).toContain('Forest Conservation');
        });

        test('should show empty state when no programs', () => {
            programsTable.renderTable([]);
            
            const tbody = document.querySelector('#programsTable tbody');
            const rows = tbody.querySelectorAll('tr');
            
            expect(rows.length).toBe(1);
            expect(rows[0].textContent).toContain('No programs found');
        });

        test('should render status badges correctly', () => {
            programsTable.renderTable([mockPrograms[0]]);
            
            const statusCell = document.querySelector('.status-badge');
            expect(statusCell.classList.contains('status-active')).toBe(true);
        });

        test('should render progress bars', () => {
            programsTable.renderTable([mockPrograms[0]]);
            
            const progressBar = document.querySelector('.progress-bar');
            expect(progressBar.style.width).toBe('75%');
        });

        test('should handle missing progress data', () => {
            const programWithoutProgress = { ...mockPrograms[0], progress: null };
            programsTable.renderTable([programWithoutProgress]);
            
            const progressBar = document.querySelector('.progress-bar');
            expect(progressBar.style.width).toBe('0%');
        });
    });

    describe('Search Functionality', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
            programsTable.allPrograms = mockPrograms;
        });

        test('should filter programs by search term', () => {
            const searchInput = document.getElementById('programSearchInput');
            searchInput.value = 'Forest';
            
            programsTable.handleSearch();
            
            expect(programsTable.filteredPrograms.length).toBe(1);
            expect(programsTable.filteredPrograms[0].title).toBe('Forest Conservation');
        });

        test('should search in program number', () => {
            const searchInput = document.getElementById('programSearchInput');
            searchInput.value = 'P002';
            
            programsTable.handleSearch();
            
            expect(programsTable.filteredPrograms.length).toBe(1);
            expect(programsTable.filteredPrograms[0].program_number).toBe('P002');
        });

        test('should be case insensitive', () => {
            const searchInput = document.getElementById('programSearchInput');
            searchInput.value = 'TIMBER';
            
            programsTable.handleSearch();
            
            expect(programsTable.filteredPrograms.length).toBe(1);
            expect(programsTable.filteredPrograms[0].title).toBe('Timber Management');
        });

        test('should clear search results when input is empty', () => {
            const searchInput = document.getElementById('programSearchInput');
            searchInput.value = '';
            
            programsTable.handleSearch();
            
            expect(programsTable.filteredPrograms).toEqual(mockPrograms);
        });
    });

    describe('Status Filtering', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
            programsTable.allPrograms = mockPrograms;
        });

        test('should filter by status', () => {
            const statusFilter = document.getElementById('statusFilter');
            statusFilter.value = 'active';
            
            programsTable.applyFilters();
            
            expect(programsTable.filteredPrograms.length).toBe(1);
            expect(programsTable.filteredPrograms[0].status).toBe('active');
        });

        test('should show all programs when no status selected', () => {
            const statusFilter = document.getElementById('statusFilter');
            statusFilter.value = '';
            
            programsTable.applyFilters();
            
            expect(programsTable.filteredPrograms.length).toBe(3);
        });

        test('should handle unknown status', () => {
            const statusFilter = document.getElementById('statusFilter');
            statusFilter.value = 'unknown';
            
            programsTable.applyFilters();
            
            expect(programsTable.filteredPrograms.length).toBe(0);
        });
    });

    describe('Sorting', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
            programsTable.allPrograms = mockPrograms;
            programsTable.filteredPrograms = [...mockPrograms];
        });

        test('should sort by title ascending', () => {
            programsTable.sortBy = 'title';
            programsTable.sortOrder = 'asc';
            
            programsTable.applySorting();
            
            expect(programsTable.filteredPrograms[0].title).toBe('Forest Conservation');
            expect(programsTable.filteredPrograms[1].title).toBe('Timber Management');
        });

        test('should sort by title descending', () => {
            programsTable.sortBy = 'title';
            programsTable.sortOrder = 'desc';
            
            programsTable.applySorting();
            
            expect(programsTable.filteredPrograms[0].title).toBe('Wildlife Protection');
            expect(programsTable.filteredPrograms[1].title).toBe('Timber Management');
        });

        test('should sort by progress', () => {
            programsTable.sortBy = 'progress';
            programsTable.sortOrder = 'desc';
            
            programsTable.applySorting();
            
            expect(programsTable.filteredPrograms[0].progress).toBe(100);
            expect(programsTable.filteredPrograms[1].progress).toBe(75);
        });

        test('should toggle sort order on button click', () => {
            const sortBtn = document.getElementById('sortOrderBtn');
            expect(programsTable.sortOrder).toBe('asc');
            
            sortBtn.click();
            expect(programsTable.sortOrder).toBe('desc');
            expect(sortBtn.textContent).toBe('↓');
            
            sortBtn.click();
            expect(programsTable.sortOrder).toBe('asc');
            expect(sortBtn.textContent).toBe('↑');
        });
    });

    describe('Pagination', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
            programsTable.itemsPerPage = 2; // Small page size for testing
            
            // Create more programs for pagination
            const morePrograms = [];
            for (let i = 1; i <= 5; i++) {
                morePrograms.push({
                    id: i,
                    program_number: `P00${i}`,
                    title: `Program ${i}`,
                    status: 'active',
                    progress: i * 20
                });
            }
            programsTable.allPrograms = morePrograms;
            programsTable.filteredPrograms = [...morePrograms];
        });

        test('should calculate correct number of pages', () => {
            programsTable.updatePagination();
            
            expect(programsTable.totalPages).toBe(3); // 5 items / 2 per page = 3 pages
        });

        test('should show correct page items', () => {
            programsTable.currentPage = 1;
            const pageItems = programsTable.getCurrentPageItems();
            
            expect(pageItems.length).toBe(2);
            expect(pageItems[0].program_number).toBe('P001');
            expect(pageItems[1].program_number).toBe('P002');
        });

        test('should navigate to next page', () => {
            programsTable.currentPage = 1;
            programsTable.totalPages = 3;
            
            const nextBtn = document.getElementById('nextPage');
            nextBtn.click();
            
            expect(programsTable.currentPage).toBe(2);
        });

        test('should navigate to previous page', () => {
            programsTable.currentPage = 2;
            
            const prevBtn = document.getElementById('prevPage');
            prevBtn.click();
            
            expect(programsTable.currentPage).toBe(1);
        });

        test('should disable next button on last page', () => {
            programsTable.currentPage = 3;
            programsTable.totalPages = 3;
            programsTable.updatePagination();
            
            const nextBtn = document.getElementById('nextPage');
            expect(nextBtn.disabled).toBe(true);
        });

        test('should disable previous button on first page', () => {
            programsTable.currentPage = 1;
            programsTable.updatePagination();
            
            const prevBtn = document.getElementById('prevPage');
            expect(prevBtn.disabled).toBe(true);
        });

        test('should update page info display', () => {
            programsTable.currentPage = 2;
            programsTable.totalPages = 3;
            programsTable.updatePagination();
            
            const pageInfo = document.getElementById('pageInfo');
            expect(pageInfo.textContent).toBe('Page 2 of 3');
        });
    });

    describe('Program Actions', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
        });

        test('should generate view program link', () => {
            const program = mockPrograms[0];
            const link = programsTable.generateViewLink(program);
            
            expect(link).toContain('page=agency_program_view');
            expect(link).toContain('id=1');
        });

        test('should generate edit program link', () => {
            const program = mockPrograms[0];
            const link = programsTable.generateEditLink(program);
            
            expect(link).toContain('page=agency_programs_edit');
            expect(link).toContain('id=1');
        });

        test('should handle programs without ID', () => {
            const programWithoutId = { ...mockPrograms[0] };
            delete programWithoutId.id;
            
            const viewLink = programsTable.generateViewLink(programWithoutId);
            const editLink = programsTable.generateEditLink(programWithoutId);
            
            expect(viewLink).toBe('#');
            expect(editLink).toBe('#');
        });
    });

    describe('Refresh Functionality', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
        });

        test('should refresh programs list on button click', () => {
            const spy = jest.spyOn(programsTable, 'loadPrograms');
            
            const refreshBtn = document.getElementById('refreshProgramsBtn');
            refreshBtn.click();
            
            expect(spy).toHaveBeenCalled();
        });

        test('should reset to first page on refresh', () => {
            programsTable.currentPage = 3;
            
            const refreshBtn = document.getElementById('refreshProgramsBtn');
            refreshBtn.click();
            
            expect(programsTable.currentPage).toBe(1);
        });
    });

    describe('Error Handling', () => {
        beforeEach(() => {
            programsTable = new ProgramsTable();
        });

        test('should handle missing DOM elements gracefully', () => {
            // Remove some elements
            document.getElementById('programSearchInput').remove();
            
            expect(() => {
                programsTable.setupEventListeners();
            }).not.toThrow();
        });

        test('should handle malformed program data', () => {
            const malformedPrograms = [
                { id: 1 }, // Missing required fields
                null,
                undefined,
                { id: 2, title: 'Valid Program' }
            ];
            
            expect(() => {
                programsTable.renderTable(malformedPrograms);
            }).not.toThrow();
            
            // Should still render valid programs
            const rows = document.querySelectorAll('#programsTable tbody tr');
            expect(rows.length).toBeGreaterThan(0);
        });
    });

    describe('Cleanup', () => {
        test('should clean up event listeners on destroy', () => {
            programsTable = new ProgramsTable();
            
            const removeEventListenerSpy = jest.spyOn(document, 'removeEventListener');
            
            programsTable.destroy();
            
            expect(removeEventListenerSpy).toHaveBeenCalled();
            removeEventListenerSpy.mockRestore();
        });
    });
});
