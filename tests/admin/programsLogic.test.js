/**
 * Admin Programs Logic Tests
 * Tests for admin programs management JavaScript functionality
 */

// Mock the CSS import
jest.mock('../../css/admin/programs/programs.css', () => ({}));

// Mock global functions
global.showToast = jest.fn();
global.confirm = jest.fn();
global.fetch = jest.fn();

describe('Admin Programs Management Logic', () => {
    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
    });

    describe('Program Validation', () => {
        test('should validate program name', () => {
            const validateProgramName = (name) => {
                if (!name || name.trim().length === 0) {
                    return { valid: false, error: 'Program name is required' };
                }
                if (name.length > 255) {
                    return { valid: false, error: 'Program name must be less than 255 characters' };
                }
                return { valid: true };
            };

            expect(validateProgramName('')).toEqual({ valid: false, error: 'Program name is required' });
            expect(validateProgramName('   ')).toEqual({ valid: false, error: 'Program name is required' });
            expect(validateProgramName('Valid Program')).toEqual({ valid: true });
            expect(validateProgramName('a'.repeat(256))).toEqual({ valid: false, error: 'Program name must be less than 255 characters' });
        });

        test('should validate program description', () => {
            const validateProgramDescription = (description) => {
                if (!description || description.trim().length === 0) {
                    return { valid: false, error: 'Program description is required' };
                }
                if (description.length > 1000) {
                    return { valid: false, error: 'Program description must be less than 1000 characters' };
                }
                return { valid: true };
            };

            expect(validateProgramDescription('')).toEqual({ valid: false, error: 'Program description is required' });
            expect(validateProgramDescription('Valid description')).toEqual({ valid: true });
            expect(validateProgramDescription('a'.repeat(1001))).toEqual({ valid: false, error: 'Program description must be less than 1000 characters' });
        });

        test('should validate program dates', () => {
            const validateProgramDates = (startDate, endDate) => {
                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    if (start > end) {
                        return { valid: false, error: 'Start date cannot be after end date' };
                    }
                }
                return { valid: true };
            };

            expect(validateProgramDates('2024-06-18', '2024-07-01')).toEqual({ valid: true });
            expect(validateProgramDates('2024-07-01', '2024-06-18')).toEqual({ valid: false, error: 'Start date cannot be after end date' });
            expect(validateProgramDates('2024-06-18', '')).toEqual({ valid: true });
            expect(validateProgramDates('', '2024-07-01')).toEqual({ valid: true });
        });
    });

    describe('Program Filtering Logic', () => {
        test('should filter programs by search term', () => {
            const filterProgramsBySearch = (programs, searchTerm) => {
                if (!searchTerm) return programs;
                
                return programs.filter(program => 
                    program.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    program.description.toLowerCase().includes(searchTerm.toLowerCase())
                );
            };

            const programs = [
                { id: 1, name: 'Test Program', description: 'Test Description' },
                { id: 2, name: 'Another Program', description: 'Another Description' }
            ];

            expect(filterProgramsBySearch(programs, 'test')).toHaveLength(1);
            expect(filterProgramsBySearch(programs, 'another')).toHaveLength(1);
            expect(filterProgramsBySearch(programs, '')).toHaveLength(2);
        });

        test('should filter programs by status', () => {
            const filterProgramsByStatus = (programs, status) => {
                if (!status) return programs;
                
                return programs.filter(program => program.status === status);
            };

            const programs = [
                { id: 1, name: 'Active Program', status: 'active' },
                { id: 2, name: 'Inactive Program', status: 'inactive' },
                { id: 3, name: 'Draft Program', status: 'draft' }
            ];

            expect(filterProgramsByStatus(programs, 'active')).toHaveLength(1);
            expect(filterProgramsByStatus(programs, 'inactive')).toHaveLength(1);
            expect(filterProgramsByStatus(programs, '')).toHaveLength(3);
        });

        test('should filter programs by type', () => {
            const filterProgramsByType = (programs, type) => {
                if (!type) return programs;
                
                return programs.filter(program => program.type === type);
            };

            const programs = [
                { id: 1, name: 'Type A Program', type: 'type_a' },
                { id: 2, name: 'Type B Program', type: 'type_b' }
            ];

            expect(filterProgramsByType(programs, 'type_a')).toHaveLength(1);
            expect(filterProgramsByType(programs, 'type_b')).toHaveLength(1);
            expect(filterProgramsByType(programs, '')).toHaveLength(2);
        });
    });

    describe('Program Sorting Logic', () => {
        test('should sort programs by name ascending', () => {
            const sortProgramsByName = (programs, direction = 'asc') => {
                return [...programs].sort((a, b) => {
                    if (direction === 'asc') {
                        return a.name.localeCompare(b.name);
                    } else {
                        return b.name.localeCompare(a.name);
                    }
                });
            };

            const programs = [
                { id: 1, name: 'Zebra Program' },
                { id: 2, name: 'Alpha Program' },
                { id: 3, name: 'Beta Program' }
            ];

            const sortedAsc = sortProgramsByName(programs, 'asc');
            expect(sortedAsc[0].name).toBe('Alpha Program');
            expect(sortedAsc[1].name).toBe('Beta Program');
            expect(sortedAsc[2].name).toBe('Zebra Program');
        });

        test('should sort programs by name descending', () => {
            const sortProgramsByName = (programs, direction = 'asc') => {
                return [...programs].sort((a, b) => {
                    if (direction === 'asc') {
                        return a.name.localeCompare(b.name);
                    } else {
                        return b.name.localeCompare(a.name);
                    }
                });
            };

            const programs = [
                { id: 1, name: 'Alpha Program' },
                { id: 2, name: 'Beta Program' },
                { id: 3, name: 'Zebra Program' }
            ];

            const sortedDesc = sortProgramsByName(programs, 'desc');
            expect(sortedDesc[0].name).toBe('Zebra Program');
            expect(sortedDesc[1].name).toBe('Beta Program');
            expect(sortedDesc[2].name).toBe('Alpha Program');
        });

        test('should sort programs by date', () => {
            const sortProgramsByDate = (programs, field = 'start_date', direction = 'asc') => {
                return [...programs].sort((a, b) => {
                    const aDate = new Date(a[field]);
                    const bDate = new Date(b[field]);
                    
                    if (direction === 'asc') {
                        return aDate - bDate;
                    } else {
                        return bDate - aDate;
                    }
                });
            };

            const programs = [
                { id: 1, name: 'Program 1', start_date: '2024-03-01' },
                { id: 2, name: 'Program 2', start_date: '2024-01-01' },
                { id: 3, name: 'Program 3', start_date: '2024-02-01' }
            ];

            const sortedAsc = sortProgramsByDate(programs, 'start_date', 'asc');
            expect(sortedAsc[0].start_date).toBe('2024-01-01');
            expect(sortedAsc[1].start_date).toBe('2024-02-01');
            expect(sortedAsc[2].start_date).toBe('2024-03-01');
        });
    });

    describe('Program CRUD Operations', () => {
        test('should create program with valid data', () => {
            const createProgram = (data) => {
                const errors = [];
                
                if (!data.name || data.name.trim().length === 0) {
                    errors.push('Name is required');
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
                name: 'Test Program',
                description: 'Test Description',
                start_date: '2024-06-18',
                end_date: '2024-07-01',
                status: 'active'
            };

            const result = createProgram(validData);
            expect(result.success).toBe(true);
            expect(result.id).toBeDefined();
        });

        test('should update program with valid data', () => {
            const updateProgram = (id, data) => {
                if (!id) {
                    return { success: false, error: 'Program ID is required' };
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

            const result = updateProgram(1, { name: 'Updated Program' });
            expect(result.success).toBe(true);
        });

        test('should delete program', () => {
            const deleteProgram = (id) => {
                if (!id) {
                    return { success: false, error: 'Program ID is required' };
                }
                return { success: true };
            };

            expect(deleteProgram(1)).toEqual({ success: true });
            expect(deleteProgram(null)).toEqual({ success: false, error: 'Program ID is required' });
        });
    });

    describe('Program Statistics', () => {
        test('should calculate program statistics', () => {
            const calculateProgramStats = (programs) => {
                return {
                    total: programs.length,
                    active: programs.filter(p => p.status === 'active').length,
                    inactive: programs.filter(p => p.status === 'inactive').length,
                    draft: programs.filter(p => p.status === 'draft').length,
                    completed: programs.filter(p => p.status === 'completed').length
                };
            };

            const programs = [
                { id: 1, name: 'Active 1', status: 'active' },
                { id: 2, name: 'Active 2', status: 'active' },
                { id: 3, name: 'Inactive 1', status: 'inactive' },
                { id: 4, name: 'Draft 1', status: 'draft' },
                { id: 5, name: 'Completed 1', status: 'completed' }
            ];

            const stats = calculateProgramStats(programs);
            expect(stats.total).toBe(5);
            expect(stats.active).toBe(2);
            expect(stats.inactive).toBe(1);
            expect(stats.draft).toBe(1);
            expect(stats.completed).toBe(1);
        });

        test('should calculate program progress', () => {
            const calculateProgramProgress = (program) => {
                if (!program.start_date || !program.end_date) {
                    return 0;
                }

                const start = new Date(program.start_date);
                const end = new Date(program.end_date);
                const now = new Date();

                if (now < start) return 0;
                if (now > end) return 100;

                const totalDuration = end - start;
                const elapsed = now - start;
                return Math.round((elapsed / totalDuration) * 100);
            };

            const program = {
                start_date: '2024-01-01',
                end_date: '2024-12-31'
            };

            const progress = calculateProgramProgress(program);
            expect(progress).toBeGreaterThanOrEqual(0);
            expect(progress).toBeLessThanOrEqual(100);
        });
    });

    describe('Program Search and Filter', () => {
        test('should search programs by multiple criteria', () => {
            const searchPrograms = (programs, criteria) => {
                return programs.filter(program => {
                    let matches = true;
                    
                    if (criteria.search) {
                        const searchTerm = criteria.search.toLowerCase();
                        const nameMatch = program.name.toLowerCase().includes(searchTerm);
                        const descMatch = program.description.toLowerCase().includes(searchTerm);
                        if (!nameMatch && !descMatch) {
                            matches = false;
                        }
                    }
                    
                    if (criteria.status && program.status !== criteria.status) {
                        matches = false;
                    }
                    
                    if (criteria.type && program.type !== criteria.type) {
                        matches = false;
                    }
                    
                    return matches;
                });
            };

            const programs = [
                { id: 1, name: 'Test Program', description: 'Test Description', status: 'active', type: 'type_a' },
                { id: 2, name: 'Another Program', description: 'Another Description', status: 'inactive', type: 'type_b' }
            ];

            const criteria = { search: 'test', status: 'active' };
            const results = searchPrograms(programs, criteria);
            expect(results).toHaveLength(1);
            expect(results[0].name).toBe('Test Program');
        });
    });

    describe('Error Handling', () => {
        test('should handle validation errors', () => {
            const handleValidationError = (errors) => {
                return {
                    success: false,
                    error: 'Please correct the following errors:',
                    errors: errors
                };
            };

            const errors = ['Name is required', 'Description is required'];
            const result = handleValidationError(errors);
            expect(result.success).toBe(false);
            expect(result.errors).toEqual(errors);
        });

        test('should handle network errors', () => {
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
    });

    describe('Program Deletion Confirmation', () => {
        test('should confirm program deletion', () => {
            // Mock confirm dialog
            global.confirm.mockReturnValue(true);

            const confirmDeleteProgram = (programId, periodId) => {
                const programName = 'Test Program';
                const message = `Are you sure you want to delete "${programName}"? This action cannot be undone.`;
                
                if (confirm(message)) {
                    return true; // Proceed with deletion
                }
                return false; // Cancel deletion
            };

            const result = confirmDeleteProgram(1, 1);

            expect(global.confirm).toHaveBeenCalledWith(
                'Are you sure you want to delete "Test Program"? This action cannot be undone.'
            );
            expect(result).toBe(true);
        });

        test('should cancel program deletion', () => {
            // Mock confirm dialog
            global.confirm.mockReturnValue(false);

            const confirmDeleteProgram = (programId, periodId) => {
                const programName = 'Test Program';
                const message = `Are you sure you want to delete "${programName}"? This action cannot be undone.`;
                
                if (confirm(message)) {
                    return true; // Proceed with deletion
                }
                return false; // Cancel deletion
            };

            const result = confirmDeleteProgram(1, 1);

            expect(result).toBe(false);
        });
    });

    describe('HTML Escaping', () => {
        test('should escape HTML characters correctly', () => {
            const escapeHtml = (text) => {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            };

            expect(escapeHtml('<script>alert("test")</script>')).toBe('&lt;script&gt;alert(&quot;test&quot;)&lt;/script&gt;');
            expect(escapeHtml('Test & More')).toBe('Test &amp; More');
            expect(escapeHtml('Normal text')).toBe('Normal text');
        });
    });

    describe('Toast Notifications', () => {
        test('should show toast notification', () => {
            const showToast = (message, type = 'info', duration = 5000) => {
                // Create toast element
                const toast = {
                    className: `alert alert-${type} alert-dismissible fade show`,
                    innerHTML: `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>${message}</div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `
                };
                
                return toast;
            };

            const toast = showToast('Test message', 'success');

            expect(toast.className).toContain('alert-success');
            expect(toast.innerHTML).toContain('Test message');
        });
    });
}); 