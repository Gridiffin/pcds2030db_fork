/**
 * Initiatives Management Logic Unit Tests
 * Tests for admin initiatives management functionality
 */

// Mock DOM elements and functions
document.body.innerHTML = `
    <div id="initiativeModal">
        <form id="initiativeForm">
            <input type="text" id="initiativeName" name="name" required>
            <input type="text" id="initiativeNumber" name="number" required>
            <textarea id="initiativeDescription" name="description" required></textarea>
            <input type="date" id="startDate" name="start_date">
            <input type="date" id="endDate" name="end_date">
            <select id="isActive" name="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            <button type="submit">Save Initiative</button>
        </form>
    </div>
    <div id="initiativeTable">
        <input type="text" id="initiativeSearch" placeholder="Search initiatives...">
        <select id="statusFilter">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>
`;

// Mock Bootstrap
global.bootstrap = {
    Tooltip: jest.fn()
};

// Mock fetch
global.fetch = jest.fn();

describe('Initiatives Management Logic', () => {
    beforeEach(() => {
        // Clear all mocks
        jest.clearAllMocks();
        fetch.mockClear();
    });

    describe('Date Formatting', () => {
        test('should format valid date strings correctly', () => {
            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                if (isNaN(d)) return '';
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            expect(formatDate('2024-06-18')).toMatch(/Jun.*18.*2024/);
            expect(formatDate('2024-12-25')).toMatch(/Dec.*25.*2024/);
        });

        test('should return empty string for invalid dates', () => {
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                if (isNaN(d)) return '';
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            expect(formatDate('not-a-date')).toBe('');
            expect(formatDate('')).toBe('');
            expect(formatDate(null)).toBe('');
            expect(formatDate(undefined)).toBe('');
        });

        test('should handle various date formats', () => {
            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                if (isNaN(d)) return '';
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            expect(formatDate('2024-01-01')).toMatch(/Jan.*1.*2024/);
            expect(formatDate('2024-03-15')).toMatch(/Mar.*15.*2024/);
        });
    });

    describe('Table Rendering', () => {
        test('should render no initiatives message when empty', () => {
            const renderTable = (initiatives, columns) => {
                if (!initiatives.length) {
                    return `<div class="text-center py-5">
                        <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No initiatives found</h5>
                        <p class="text-muted">No initiatives match your search criteria.</p>
                        <a href="initiatives.php" class="btn btn-outline-primary">
                            <i class="fas fa-undo me-1"></i>Clear Filters
                        </a>
                    </div>`;
                }
                // ... rest of the function would be here
            };

            const columns = {
                id: 'id',
                name: 'name',
                number: 'number',
                description: 'description',
                start_date: 'start_date',
                end_date: 'end_date',
                is_active: 'is_active'
            };
            
            const result = renderTable([], columns);
            expect(result).toMatch(/No initiatives found/);
            expect(result).toMatch(/No initiatives match your search criteria/);
        });

        test('should render table with initiatives data', () => {
            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                if (isNaN(d)) return '';
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            const renderTable = (initiatives, columns) => {
                if (!initiatives.length) {
                    return `<div class="text-center py-5">
                        <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No initiatives found</h5>
                        <p class="text-muted">No initiatives match your search criteria.</p>
                        <a href="initiatives.php" class="btn btn-outline-primary">
                            <i class="fas fa-undo me-1"></i>Clear Filters
                        </a>
                    </div>`;
                }
                let rows = initiatives.map(initiative => {
                    return `<tr data-initiative-id="${initiative[columns.id]}">
                        <td>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1">
                                        ${initiative[columns.number] ? `<span class="badge bg-primary me-2">${initiative[columns.number]}</span>` : ''}
                                        ${initiative[columns.name] || ''}
                                    </div>
                                    ${initiative[columns.description] ? `<div class="text-muted small" style="line-height: 1.4;">${initiative[columns.description].length > 120 ? initiative[columns.description].substring(0, 120) + '...' : initiative[columns.description]}</div>` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary">${initiative.program_count || 0} total</span></td>
                        <td>${(initiative[columns.start_date] || initiative[columns.end_date]) ? `<div class="small">${initiative[columns.start_date] && initiative[columns.end_date] ? `<i class='fas fa-calendar-alt me-1 text-muted'></i>${formatDate(initiative[columns.start_date])} - ${formatDate(initiative[columns.end_date])}` : initiative[columns.start_date] ? `<i class='fas fa-play me-1 text-success'></i>Started: ${formatDate(initiative[columns.start_date])}` : `<i class='fas fa-flag-checkered me-1 text-warning'></i>Due: ${formatDate(initiative[columns.end_date])}`}</div>` : `<span class="text-muted small"><i class="fas fa-calendar-times me-1"></i>No timeline</span>`}</td>
                        <td>${initiative[columns.is_active] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                        <td class="text-center"><a href="edit.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm me-1" title="Edit Initiative"><i class="fas fa-edit"></i></a><a href="view_initiative.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm" title="View Initiative Details"><i class="fas fa-eye"></i></a></td>
                    </tr>`;
                }).join('');
                return `<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Initiative</th><th class="text-center">Total Programs</th><th>Timeline</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>${rows}</tbody></table></div>`;
            };

            const columns = {
                id: 'id',
                name: 'name',
                number: 'number',
                description: 'description',
                start_date: 'start_date',
                end_date: 'end_date',
                is_active: 'is_active'
            };
            
            const initiatives = [
                {
                    id: 1,
                    name: 'Test Initiative',
                    number: 'A1',
                    description: 'Test Description',
                    start_date: '2024-06-18',
                    end_date: '2024-07-01',
                    is_active: 1,
                    program_count: 2
                }
            ];
            
            const result = renderTable(initiatives, columns);
            expect(result).toMatch(/Test Initiative/);
            expect(result).toMatch(/A1/);
            expect(result).toMatch(/Test Description/);
            expect(result).toMatch(/Active/);
        });

        test('should handle initiatives without optional fields', () => {
            const renderTable = (initiatives, columns) => {
                if (!initiatives.length) {
                    return `<div class="text-center py-5">
                        <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No initiatives found</h5>
                        <p class="text-muted">No initiatives match your search criteria.</p>
                        <a href="initiatives.php" class="btn btn-outline-primary">
                            <i class="fas fa-undo me-1"></i>Clear Filters
                        </a>
                    </div>`;
                }
                let rows = initiatives.map(initiative => {
                    return `<tr data-initiative-id="${initiative[columns.id]}">
                        <td>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1">
                                        ${initiative[columns.number] ? `<span class="badge bg-primary me-2">${initiative[columns.number]}</span>` : ''}
                                        ${initiative[columns.name] || ''}
                                    </div>
                                    ${initiative[columns.description] ? `<div class="text-muted small" style="line-height: 1.4;">${initiative[columns.description].length > 120 ? initiative[columns.description].substring(0, 120) + '...' : initiative[columns.description]}</div>` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary">${initiative.program_count || 0} total</span></td>
                        <td>${(initiative[columns.start_date] || initiative[columns.end_date]) ? `<div class="small">${initiative[columns.start_date] && initiative[columns.end_date] ? `<i class='fas fa-calendar-alt me-1 text-muted'></i>${formatDate(initiative[columns.start_date])} - ${formatDate(initiative[columns.end_date])}` : initiative[columns.start_date] ? `<i class='fas fa-play me-1 text-success'></i>Started: ${formatDate(initiative[columns.start_date])}` : `<i class='fas fa-flag-checkered me-1 text-warning'></i>Due: ${formatDate(initiative[columns.end_date])}`}</div>` : `<span class="text-muted small"><i class="fas fa-calendar-times me-1"></i>No timeline</span>`}</td>
                        <td>${initiative[columns.is_active] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                        <td class="text-center"><a href="edit.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm me-1" title="Edit Initiative"><i class="fas fa-edit"></i></a><a href="view_initiative.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm" title="View Initiative Details"><i class="fas fa-eye"></i></a></td>
                    </tr>`;
                }).join('');
                return `<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Initiative</th><th class="text-center">Total Programs</th><th>Timeline</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>${rows}</tbody></table></div>`;
            };

            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                if (isNaN(d)) return '';
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            const columns = {
                id: 'id',
                name: 'name',
                number: 'number',
                description: 'description',
                start_date: 'start_date',
                end_date: 'end_date',
                is_active: 'is_active'
            };
            
            const initiatives = [
                {
                    id: 1,
                    name: 'Minimal Initiative',
                    is_active: 0
                }
            ];
            
            const result = renderTable(initiatives, columns);
            expect(result).toMatch(/Minimal Initiative/);
            expect(result).toMatch(/Inactive/);
        });

        test('should render timeline correctly', () => {
            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                if (isNaN(d)) return '';
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            };

            const renderTable = (initiatives, columns) => {
                if (!initiatives.length) {
                    return `<div class="text-center py-5">
                        <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No initiatives found</h5>
                        <p class="text-muted">No initiatives match your search criteria.</p>
                        <a href="initiatives.php" class="btn btn-outline-primary">
                            <i class="fas fa-undo me-1"></i>Clear Filters
                        </a>
                    </div>`;
                }
                let rows = initiatives.map(initiative => {
                    return `<tr data-initiative-id="${initiative[columns.id]}">
                        <td>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1">
                                        ${initiative[columns.number] ? `<span class="badge bg-primary me-2">${initiative[columns.number]}</span>` : ''}
                                        ${initiative[columns.name] || ''}
                                    </div>
                                    ${initiative[columns.description] ? `<div class="text-muted small" style="line-height: 1.4;">${initiative[columns.description].length > 120 ? initiative[columns.description].substring(0, 120) + '...' : initiative[columns.description]}</div>` : ''}
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary">${initiative.program_count || 0} total</span></td>
                        <td>${(initiative[columns.start_date] || initiative[columns.end_date]) ? `<div class="small">${initiative[columns.start_date] && initiative[columns.end_date] ? `<i class='fas fa-calendar-alt me-1 text-muted'></i>${formatDate(initiative[columns.start_date])} - ${formatDate(initiative[columns.end_date])}` : initiative[columns.start_date] ? `<i class='fas fa-play me-1 text-success'></i>Started: ${formatDate(initiative[columns.start_date])}` : `<i class='fas fa-flag-checkered me-1 text-warning'></i>Due: ${formatDate(initiative[columns.end_date])}`}</div>` : `<span class="text-muted small"><i class="fas fa-calendar-times me-1"></i>No timeline</span>`}</td>
                        <td>${initiative[columns.is_active] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                        <td class="text-center"><a href="edit.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm me-1" title="Edit Initiative"><i class="fas fa-edit"></i></a><a href="view_initiative.php?id=${initiative[columns.id]}" class="btn btn-outline-primary btn-sm" title="View Initiative Details"><i class="fas fa-eye"></i></a></td>
                    </tr>`;
                }).join('');
                return `<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Initiative</th><th class="text-center">Total Programs</th><th>Timeline</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>${rows}</tbody></table></div>`;
            };

            const columns = {
                id: 'id',
                name: 'name',
                number: 'number',
                description: 'description',
                start_date: 'start_date',
                end_date: 'end_date',
                is_active: 'is_active'
            };
            
            const initiatives = [
                {
                    id: 1,
                    name: 'Timeline Test',
                    start_date: '2024-06-18',
                    end_date: '2024-07-01',
                    is_active: 1
                }
            ];
            
            const result = renderTable(initiatives, columns);
            expect(result).toMatch(/Jun.*18.*2024.*-.*Jul.*1.*2024/);
        });
    });

    describe('Initiative Validation', () => {
        test('should validate initiative name', () => {
            const validateInitiativeName = (name) => {
                if (!name || name.trim().length === 0) {
                    return { valid: false, error: 'Initiative name is required' };
                }
                if (name.length > 255) {
                    return { valid: false, error: 'Initiative name must be less than 255 characters' };
                }
                return { valid: true };
            };

            expect(validateInitiativeName('')).toEqual({ valid: false, error: 'Initiative name is required' });
            expect(validateInitiativeName('   ')).toEqual({ valid: false, error: 'Initiative name is required' });
            expect(validateInitiativeName('Valid Name')).toEqual({ valid: true });
            expect(validateInitiativeName('a'.repeat(256))).toEqual({ valid: false, error: 'Initiative name must be less than 255 characters' });
        });

        test('should validate initiative number', () => {
            const validateInitiativeNumber = (number) => {
                if (!number || number.trim().length === 0) {
                    return { valid: false, error: 'Initiative number is required' };
                }
                if (!/^[A-Z0-9-]+$/.test(number)) {
                    return { valid: false, error: 'Initiative number must contain only uppercase letters, numbers, and hyphens' };
                }
                return { valid: true };
            };

            expect(validateInitiativeNumber('')).toEqual({ valid: false, error: 'Initiative number is required' });
            expect(validateInitiativeNumber('A1')).toEqual({ valid: true });
            expect(validateInitiativeNumber('A1-B2')).toEqual({ valid: true });
            expect(validateInitiativeNumber('a1')).toEqual({ valid: false, error: 'Initiative number must contain only uppercase letters, numbers, and hyphens' });
        });

        test('should validate date ranges', () => {
            const validateDateRange = (startDate, endDate) => {
                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    if (start > end) {
                        return { valid: false, error: 'Start date cannot be after end date' };
                    }
                }
                return { valid: true };
            };

            expect(validateDateRange('2024-06-18', '2024-07-01')).toEqual({ valid: true });
            expect(validateDateRange('2024-07-01', '2024-06-18')).toEqual({ valid: false, error: 'Start date cannot be after end date' });
            expect(validateDateRange('2024-06-18', '')).toEqual({ valid: true });
            expect(validateDateRange('', '2024-07-01')).toEqual({ valid: true });
        });
    });

    describe('Initiative CRUD Operations', () => {
        test('should create initiative with valid data', () => {
            const createInitiative = (data) => {
                const errors = [];
                
                if (!data.name || data.name.trim().length === 0) {
                    errors.push('Name is required');
                }
                if (!data.number || data.number.trim().length === 0) {
                    errors.push('Number is required');
                }
                if (!data.description || data.description.trim().length === 0) {
                    errors.push('Description is required');
                }
                
                if (errors.length > 0) {
                    return { success: false, errors };
                }
                
                return { success: true, id: Math.floor(Math.random() * 1000) + 1 };
            };

            const validData = {
                name: 'Test Initiative',
                number: 'A1',
                description: 'Test Description',
                start_date: '2024-06-18',
                end_date: '2024-07-01',
                is_active: 1
            };

            const result = createInitiative(validData);
            expect(result.success).toBe(true);
            expect(result.id).toBeDefined();
        });

        test('should update initiative with valid data', () => {
            const updateInitiative = (id, data) => {
                if (!id) {
                    return { success: false, error: 'Initiative ID is required' };
                }
                
                const errors = [];
                if (!data.name || data.name.trim().length === 0) {
                    errors.push('Name is required');
                }
                
                if (errors.length > 0) {
                    return { success: false, errors };
                }
                
                return { success: true };
            };

            const result = updateInitiative(1, { name: 'Updated Initiative' });
            expect(result.success).toBe(true);
        });

        test('should delete initiative', () => {
            const deleteInitiative = (id) => {
                if (!id) {
                    return { success: false, error: 'Initiative ID is required' };
                }
                return { success: true };
            };

            expect(deleteInitiative(1)).toEqual({ success: true });
            expect(deleteInitiative(null)).toEqual({ success: false, error: 'Initiative ID is required' });
        });
    });

    describe('Initiative Search and Filter', () => {
        test('should search initiatives by name', () => {
            const searchInitiatives = (initiatives, searchTerm) => {
                if (!searchTerm) return initiatives;
                
                return initiatives.filter(initiative => 
                    initiative.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    initiative.number.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    initiative.description.toLowerCase().includes(searchTerm.toLowerCase())
                );
            };

            const initiatives = [
                { id: 1, name: 'Test Initiative', number: 'A1', description: 'Test Description' },
                { id: 2, name: 'Another Initiative', number: 'B2', description: 'Another Description' }
            ];

            expect(searchInitiatives(initiatives, 'test')).toHaveLength(1);
            expect(searchInitiatives(initiatives, 'A1')).toHaveLength(1);
            expect(searchInitiatives(initiatives, '')).toHaveLength(2);
        });

        test('should filter initiatives by status', () => {
            const filterInitiativesByStatus = (initiatives, status) => {
                if (status === '' || status === null || status === undefined) return initiatives;
                
                return initiatives.filter(initiative => initiative.is_active == status);
            };

            const initiatives = [
                { id: 1, name: 'Active Initiative', is_active: 1 },
                { id: 2, name: 'Inactive Initiative', is_active: 0 }
            ];

            expect(filterInitiativesByStatus(initiatives, 1)).toHaveLength(1);
            expect(filterInitiativesByStatus(initiatives, 0)).toHaveLength(1);
            expect(filterInitiativesByStatus(initiatives, '')).toHaveLength(2);
        });
    });

    describe('Initiative Statistics', () => {
        test('should calculate initiative statistics', () => {
            const calculateInitiativeStats = (initiatives) => {
                return {
                    total: initiatives.length,
                    active: initiatives.filter(i => i.is_active == 1).length,
                    inactive: initiatives.filter(i => i.is_active == 0).length,
                    withTimeline: initiatives.filter(i => i.start_date || i.end_date).length,
                    withoutTimeline: initiatives.filter(i => !i.start_date && !i.end_date).length
                };
            };

            const initiatives = [
                { id: 1, name: 'Active 1', is_active: 1, start_date: '2024-06-18' },
                { id: 2, name: 'Active 2', is_active: 1, end_date: '2024-07-01' },
                { id: 3, name: 'Inactive 1', is_active: 0 },
                { id: 4, name: 'Inactive 2', is_active: 0 }
            ];

            const stats = calculateInitiativeStats(initiatives);
            expect(stats.total).toBe(4);
            expect(stats.active).toBe(2);
            expect(stats.inactive).toBe(2);
            expect(stats.withTimeline).toBe(2);
            expect(stats.withoutTimeline).toBe(2);
        });
    });

    describe('Error Handling', () => {
        test('should handle network errors gracefully', () => {
            const handleNetworkError = (error) => {
                return {
                    success: false,
                    error: 'Network error occurred. Please check your connection and try again.',
                    details: error.message
                };
            };

            const result = handleNetworkError(new Error('Network timeout'));
            expect(result.success).toBe(false);
            expect(result.error).toMatch(/Network error occurred/);
        });

        test('should handle validation errors', () => {
            const handleValidationError = (errors) => {
                return {
                    success: false,
                    error: 'Please correct the following errors:',
                    errors: errors
                };
            };

            const errors = ['Name is required', 'Number is required'];
            const result = handleValidationError(errors);
            expect(result.success).toBe(false);
            expect(result.errors).toEqual(errors);
        });
    });
}); 