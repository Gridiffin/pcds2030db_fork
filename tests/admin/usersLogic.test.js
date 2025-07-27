/**
 * User Management Logic Unit Tests
 * Tests for admin user management functionality
 */

// Mock DOM elements and functions
document.body.innerHTML = `
    <div id="userModal">
        <form id="userForm">
            <input type="text" id="username" name="username" required>
            <input type="email" id="email" name="email" required>
            <input type="password" id="password" name="password" required>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="agency">Agency</option>
                <option value="focal">Focal</option>
            </select>
            <select id="agency" name="agency">
                <option value="">Select Agency</option>
                <option value="1">Agency 1</option>
                <option value="2">Agency 2</option>
            </select>
            <button type="submit">Save User</button>
        </form>
    </div>
    <div id="userTable">
        <input type="text" id="userSearch" placeholder="Search users...">
        <select id="roleFilter">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="agency">Agency</option>
            <option value="focal">Focal</option>
        </select>
    </div>
`;

// Mock Bootstrap
global.bootstrap = {
    Tooltip: jest.fn()
};

describe('User Management Logic', () => {
    let mockForm;
    let mockSearchInput;
    let mockRoleFilter;
    let mockAgencySelect;

    beforeEach(() => {
        // Reset DOM
        document.body.innerHTML = `
            <div id="userModal">
                <form id="userForm">
                    <input type="text" id="username" name="username" required>
                    <input type="email" id="email" name="email" required>
                    <input type="password" id="password" name="password" required>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="agency">Agency</option>
                        <option value="focal">Focal</option>
                    </select>
                    <select id="agency" name="agency">
                        <option value="">Select Agency</option>
                        <option value="1">Agency 1</option>
                        <option value="2">Agency 2</option>
                    </select>
                    <button type="submit">Save User</button>
                </form>
            </div>
            <div id="userTable">
                <input type="text" id="userSearch" placeholder="Search users...">
                <select id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="agency">Agency</option>
                    <option value="focal">Focal</option>
                </select>
            </div>
        `;

        mockForm = document.getElementById('userForm');
        mockSearchInput = document.getElementById('userSearch');
        mockRoleFilter = document.getElementById('roleFilter');
        mockAgencySelect = document.getElementById('agency');
    });

    describe('User Validation Functions', () => {
        test('should validate username correctly', () => {
            const validateUsername = (username) => {
                if (!username || username.trim() === '') {
                    return { valid: false, message: 'Username is required' };
                }
                if (username.length < 3) {
                    return { valid: false, message: 'Username must be at least 3 characters' };
                }
                if (username.length > 50) {
                    return { valid: false, message: 'Username must be less than 50 characters' };
                }
                if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    return { valid: false, message: 'Username can only contain letters, numbers, and underscores' };
                }
                return { valid: true, message: 'Username is valid' };
            };

            expect(validateUsername('')).toEqual({ valid: false, message: 'Username is required' });
            expect(validateUsername('ab')).toEqual({ valid: false, message: 'Username must be at least 3 characters' });
            expect(validateUsername('a'.repeat(51))).toEqual({ valid: false, message: 'Username must be less than 50 characters' });
            expect(validateUsername('user@name')).toEqual({ valid: false, message: 'Username can only contain letters, numbers, and underscores' });
            expect(validateUsername('valid_user123')).toEqual({ valid: true, message: 'Username is valid' });
        });

        test('should validate email correctly', () => {
            const validateEmail = (email) => {
                if (!email || email.trim() === '') {
                    return { valid: false, message: 'Email is required' };
                }
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    return { valid: false, message: 'Please enter a valid email address' };
                }
                return { valid: true, message: 'Email is valid' };
            };

            expect(validateEmail('')).toEqual({ valid: false, message: 'Email is required' });
            expect(validateEmail('invalid-email')).toEqual({ valid: false, message: 'Please enter a valid email address' });
            expect(validateEmail('user@domain')).toEqual({ valid: false, message: 'Please enter a valid email address' });
            expect(validateEmail('user@domain.com')).toEqual({ valid: true, message: 'Email is valid' });
        });

        test('should validate password correctly', () => {
            const validatePassword = (password) => {
                if (!password || password.trim() === '') {
                    return { valid: false, message: 'Password is required' };
                }
                if (password.length < 8) {
                    return { valid: false, message: 'Password must be at least 8 characters' };
                }
                if (!/(?=.*[a-z])/.test(password)) {
                    return { valid: false, message: 'Password must contain at least one lowercase letter' };
                }
                if (!/(?=.*[A-Z])/.test(password)) {
                    return { valid: false, message: 'Password must contain at least one uppercase letter' };
                }
                if (!/(?=.*\d)/.test(password)) {
                    return { valid: false, message: 'Password must contain at least one number' };
                }
                return { valid: true, message: 'Password is valid' };
            };

            expect(validatePassword('')).toEqual({ valid: false, message: 'Password is required' });
            expect(validatePassword('short')).toEqual({ valid: false, message: 'Password must be at least 8 characters' });
            expect(validatePassword('nouppercase123')).toEqual({ valid: false, message: 'Password must contain at least one uppercase letter' });
            expect(validatePassword('NOLOWERCASE123')).toEqual({ valid: false, message: 'Password must contain at least one lowercase letter' });
            expect(validatePassword('NoNumbers')).toEqual({ valid: false, message: 'Password must contain at least one number' });
            expect(validatePassword('ValidPass123')).toEqual({ valid: true, message: 'Password is valid' });
        });

        test('should validate role correctly', () => {
            const validateRole = (role) => {
                const validRoles = ['admin', 'agency', 'focal'];
                if (!role || role.trim() === '') {
                    return { valid: false, message: 'Role is required' };
                }
                if (!validRoles.includes(role)) {
                    return { valid: false, message: 'Please select a valid role' };
                }
                return { valid: true, message: 'Role is valid' };
            };

            expect(validateRole('')).toEqual({ valid: false, message: 'Role is required' });
            expect(validateRole('invalid')).toEqual({ valid: false, message: 'Please select a valid role' });
            expect(validateRole('admin')).toEqual({ valid: true, message: 'Role is valid' });
            expect(validateRole('agency')).toEqual({ valid: true, message: 'Role is valid' });
            expect(validateRole('focal')).toEqual({ valid: true, message: 'Role is valid' });
        });

        test('should validate agency selection for agency/focal roles', () => {
            const validateAgency = (role, agency) => {
                if (role === 'agency' || role === 'focal') {
                    if (!agency || agency.trim() === '') {
                        return { valid: false, message: 'Agency is required for this role' };
                    }
                }
                return { valid: true, message: 'Agency is valid' };
            };

            expect(validateAgency('agency', '')).toEqual({ valid: false, message: 'Agency is required for this role' });
            expect(validateAgency('focal', '')).toEqual({ valid: false, message: 'Agency is required for this role' });
            expect(validateAgency('admin', '')).toEqual({ valid: true, message: 'Agency is valid' });
            expect(validateAgency('agency', '1')).toEqual({ valid: true, message: 'Agency is valid' });
        });

        test('should validate complete user form', () => {
            const validateUserForm = (formData) => {
                const errors = [];
                
                const usernameValidation = validateUsername(formData.username);
                if (!usernameValidation.valid) errors.push(usernameValidation.message);
                
                const emailValidation = validateEmail(formData.email);
                if (!emailValidation.valid) errors.push(emailValidation.message);
                
                const passwordValidation = validatePassword(formData.password);
                if (!passwordValidation.valid) errors.push(passwordValidation.message);
                
                const roleValidation = validateRole(formData.role);
                if (!roleValidation.valid) errors.push(roleValidation.message);
                
                const agencyValidation = validateAgency(formData.role, formData.agency);
                if (!agencyValidation.valid) errors.push(agencyValidation.message);
                
                return {
                    valid: errors.length === 0,
                    errors: errors
                };
            };

            const validateUsername = (username) => {
                if (!username || username.trim() === '') {
                    return { valid: false, message: 'Username is required' };
                }
                return { valid: true };
            };

            const validateEmail = (email) => {
                if (!email || email.trim() === '') {
                    return { valid: false, message: 'Email is required' };
                }
                return { valid: true };
            };

            const validatePassword = (password) => {
                if (!password || password.trim() === '') {
                    return { valid: false, message: 'Password is required' };
                }
                return { valid: true };
            };

            const validateRole = (role) => {
                if (!role || role.trim() === '') {
                    return { valid: false, message: 'Role is required' };
                }
                return { valid: true };
            };

            const validateAgency = (role, agency) => {
                if (role === 'agency' || role === 'focal') {
                    if (!agency || agency.trim() === '') {
                        return { valid: false, message: 'Agency is required for this role' };
                    }
                }
                return { valid: true };
            };

            const validFormData = {
                username: 'testuser',
                email: 'test@example.com',
                password: 'ValidPass123',
                role: 'admin',
                agency: ''
            };

            const invalidFormData = {
                username: '',
                email: 'invalid-email',
                password: 'short',
                role: '',
                agency: ''
            };

            expect(validateUserForm(validFormData)).toEqual({ valid: true, errors: [] });
            expect(validateUserForm(invalidFormData).valid).toBe(false);
            expect(validateUserForm(invalidFormData).errors.length).toBeGreaterThan(0);
        });
    });

    describe('User CRUD Operations', () => {
        test('should create user successfully', () => {
            const createUser = (userData) => {
                return new Promise((resolve, reject) => {
                    // Simulate API call
                    setTimeout(() => {
                        if (userData.username && userData.email && userData.password && userData.role) {
                            resolve({
                                success: true,
                                user: {
                                    id: Math.floor(Math.random() * 1000),
                                    ...userData,
                                    created_at: new Date().toISOString()
                                }
                            });
                        } else {
                            reject(new Error('Invalid user data'));
                        }
                    }, 100);
                });
            };

            const userData = {
                username: 'newuser',
                email: 'newuser@example.com',
                password: 'ValidPass123',
                role: 'agency',
                agency: '1'
            };

            return expect(createUser(userData)).resolves.toMatchObject({
                success: true,
                user: expect.objectContaining({
                    username: 'newuser',
                    email: 'newuser@example.com',
                    role: 'agency'
                })
            });
        });

        test('should handle user creation error', () => {
            const createUser = (userData) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (!userData.username) {
                            reject(new Error('Username is required'));
                        } else {
                            resolve({ success: true, user: userData });
                        }
                    }, 100);
                });
            };

            const invalidUserData = {
                email: 'test@example.com',
                password: 'ValidPass123',
                role: 'admin'
            };

            return expect(createUser(invalidUserData)).rejects.toThrow('Username is required');
        });

        test('should update user successfully', () => {
            const updateUser = (userId, userData) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (userId && userData) {
                            resolve({
                                success: true,
                                user: {
                                    id: userId,
                                    ...userData,
                                    updated_at: new Date().toISOString()
                                }
                            });
                        } else {
                            reject(new Error('Invalid user data'));
                        }
                    }, 100);
                });
            };

            const userId = 123;
            const updateData = {
                email: 'updated@example.com',
                role: 'focal',
                agency: '2'
            };

            return expect(updateUser(userId, updateData)).resolves.toMatchObject({
                success: true,
                user: expect.objectContaining({
                    id: 123,
                    email: 'updated@example.com',
                    role: 'focal'
                })
            });
        });

        test('should delete user successfully', () => {
            const deleteUser = (userId) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (userId) {
                            resolve({
                                success: true,
                                message: 'User deleted successfully'
                            });
                        } else {
                            reject(new Error('User ID is required'));
                        }
                    }, 100);
                });
            };

            return expect(deleteUser(123)).resolves.toMatchObject({
                success: true,
                message: 'User deleted successfully'
            });
        });

        test('should get user by ID', () => {
            const getUserById = (userId) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (userId) {
                            resolve({
                                success: true,
                                user: {
                                    id: userId,
                                    username: 'testuser',
                                    email: 'test@example.com',
                                    role: 'admin',
                                    created_at: '2024-01-01T00:00:00Z'
                                }
                            });
                        } else {
                            reject(new Error('User not found'));
                        }
                    }, 100);
                });
            };

            return expect(getUserById(123)).resolves.toMatchObject({
                success: true,
                user: expect.objectContaining({
                    id: 123,
                    username: 'testuser',
                    email: 'test@example.com'
                })
            });
        });
    });

    describe('User Role Management', () => {
        test('should assign role to user', () => {
            const assignRole = (userId, role) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        const validRoles = ['admin', 'agency', 'focal'];
                        if (!validRoles.includes(role)) {
                            reject(new Error('Invalid role'));
                        } else {
                            resolve({
                                success: true,
                                user: {
                                    id: userId,
                                    role: role,
                                    updated_at: new Date().toISOString()
                                }
                            });
                        }
                    }, 100);
                });
            };

            return expect(assignRole(123, 'agency')).resolves.toMatchObject({
                success: true,
                user: expect.objectContaining({
                    id: 123,
                    role: 'agency'
                })
            });
        });

        test('should handle invalid role assignment', () => {
            const assignRole = (userId, role) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        const validRoles = ['admin', 'agency', 'focal'];
                        if (!validRoles.includes(role)) {
                            reject(new Error('Invalid role'));
                        } else {
                            resolve({ success: true });
                        }
                    }, 100);
                });
            };

            return expect(assignRole(123, 'invalid_role')).rejects.toThrow('Invalid role');
        });

        test('should get users by role', () => {
            const getUsersByRole = (role) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockUsers = [
                            { id: 1, username: 'admin1', role: 'admin' },
                            { id: 2, username: 'admin2', role: 'admin' },
                            { id: 3, username: 'agency1', role: 'agency' },
                            { id: 4, username: 'focal1', role: 'focal' }
                        ];

                        const filteredUsers = mockUsers.filter(user => user.role === role);
                        resolve({
                            success: true,
                            users: filteredUsers,
                            count: filteredUsers.length
                        });
                    }, 100);
                });
            };

            return expect(getUsersByRole('admin')).resolves.toMatchObject({
                success: true,
                users: expect.arrayContaining([
                    expect.objectContaining({ role: 'admin' }),
                    expect.objectContaining({ role: 'admin' })
                ]),
                count: 2
            });
        });

        test('should check user permissions', () => {
            const checkUserPermissions = (userId, permission) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const userPermissions = {
                            1: ['read', 'write', 'delete'], // admin
                            2: ['read', 'write'], // agency
                            3: ['read'] // focal
                        };

                        const permissions = userPermissions[userId] || [];
                        resolve({
                            success: true,
                            hasPermission: permissions.includes(permission),
                            permissions: permissions
                        });
                    }, 100);
                });
            };

            return expect(checkUserPermissions(1, 'delete')).resolves.toMatchObject({
                success: true,
                hasPermission: true,
                permissions: expect.arrayContaining(['delete'])
            });
        });
    });

    describe('User Search and Filtering', () => {
        test('should search users by username', () => {
            const searchUsers = (query) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockUsers = [
                            { id: 1, username: 'admin_user', email: 'admin@example.com' },
                            { id: 2, username: 'agency_user', email: 'agency@example.com' },
                            { id: 3, username: 'focal_user', email: 'focal@example.com' }
                        ];

                        const filteredUsers = mockUsers.filter(user => 
                            user.username.toLowerCase().includes(query.toLowerCase()) ||
                            user.email.toLowerCase().includes(query.toLowerCase())
                        );

                        resolve({
                            success: true,
                            users: filteredUsers,
                            count: filteredUsers.length
                        });
                    }, 100);
                });
            };

            return expect(searchUsers('admin')).resolves.toMatchObject({
                success: true,
                users: expect.arrayContaining([
                    expect.objectContaining({ username: 'admin_user' })
                ]),
                count: 1
            });
        });

        test('should filter users by role', () => {
            const filterUsersByRole = (role) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockUsers = [
                            { id: 1, username: 'admin1', role: 'admin' },
                            { id: 2, username: 'admin2', role: 'admin' },
                            { id: 3, username: 'agency1', role: 'agency' },
                            { id: 4, username: 'focal1', role: 'focal' }
                        ];

                        const filteredUsers = role ? mockUsers.filter(user => user.role === role) : mockUsers;

                        resolve({
                            success: true,
                            users: filteredUsers,
                            count: filteredUsers.length
                        });
                    }, 100);
                });
            };

            return expect(filterUsersByRole('agency')).resolves.toMatchObject({
                success: true,
                users: expect.arrayContaining([
                    expect.objectContaining({ role: 'agency' })
                ]),
                count: 1
            });
        });

        test('should filter users by agency', () => {
            const filterUsersByAgency = (agencyId) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockUsers = [
                            { id: 1, username: 'user1', agency: '1' },
                            { id: 2, username: 'user2', agency: '1' },
                            { id: 3, username: 'user3', agency: '2' },
                            { id: 4, username: 'user4', agency: null }
                        ];

                        const filteredUsers = agencyId ? mockUsers.filter(user => user.agency === agencyId) : mockUsers;

                        resolve({
                            success: true,
                            users: filteredUsers,
                            count: filteredUsers.length
                        });
                    }, 100);
                });
            };

            return expect(filterUsersByAgency('1')).resolves.toMatchObject({
                success: true,
                users: expect.arrayContaining([
                    expect.objectContaining({ agency: '1' }),
                    expect.objectContaining({ agency: '1' })
                ]),
                count: 2
            });
        });

        test('should combine search and filters', () => {
            const searchAndFilterUsers = (query, role, agencyId) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockUsers = [
                            { id: 1, username: 'admin_user1', role: 'admin', agency: null },
                            { id: 2, username: 'agency_user1', role: 'agency', agency: '1' },
                            { id: 3, username: 'agency_user2', role: 'agency', agency: '2' },
                            { id: 4, username: 'focal_user1', role: 'focal', agency: '1' }
                        ];

                        let filteredUsers = mockUsers;

                        // Apply search filter
                        if (query) {
                            filteredUsers = filteredUsers.filter(user => 
                                user.username.toLowerCase().includes(query.toLowerCase())
                            );
                        }

                        // Apply role filter
                        if (role) {
                            filteredUsers = filteredUsers.filter(user => user.role === role);
                        }

                        // Apply agency filter
                        if (agencyId) {
                            filteredUsers = filteredUsers.filter(user => user.agency === agencyId);
                        }

                        resolve({
                            success: true,
                            users: filteredUsers,
                            count: filteredUsers.length
                        });
                    }, 100);
                });
            };

            return expect(searchAndFilterUsers('agency', 'agency', '1')).resolves.toMatchObject({
                success: true,
                users: expect.arrayContaining([
                    expect.objectContaining({ 
                        username: 'agency_user1', 
                        role: 'agency', 
                        agency: '1' 
                    })
                ]),
                count: 1
            });
        });
    });

    describe('User Form Handling', () => {
        test('should toggle password visibility', () => {
            const togglePasswordVisibility = (inputElement, toggleButton) => {
                if (inputElement.type === 'password') {
                    inputElement.type = 'text';
                    toggleButton.innerHTML = '<i class="far fa-eye-slash"></i>';
                } else {
                    inputElement.type = 'password';
                    toggleButton.innerHTML = '<i class="far fa-eye"></i>';
                }
                return inputElement.type;
            };

            const passwordInput = document.createElement('input');
            passwordInput.type = 'password';
            const toggleButton = document.createElement('button');

            expect(togglePasswordVisibility(passwordInput, toggleButton)).toBe('text');
            expect(toggleButton.innerHTML).toBe('<i class="far fa-eye-slash"></i>');

            expect(togglePasswordVisibility(passwordInput, toggleButton)).toBe('password');
            expect(toggleButton.innerHTML).toBe('<i class="far fa-eye"></i>');
        });

        test('should toggle agency fields based on role', () => {
            const toggleAgencyFields = (role, agencyFields) => {
                if (role === 'agency' || role === 'focal') {
                    agencyFields.style.display = 'block';
                    agencyFields.required = true;
                } else {
                    agencyFields.style.display = 'none';
                    agencyFields.required = false;
                    agencyFields.value = '';
                }
                return {
                    display: agencyFields.style.display,
                    required: agencyFields.required
                };
            };

            const agencyFields = document.createElement('select');

            // Test agency role
            const agencyResult = toggleAgencyFields('agency', agencyFields);
            expect(agencyResult.display).toBe('block');
            expect(agencyResult.required).toBe(true);

            // Test admin role
            const adminResult = toggleAgencyFields('admin', agencyFields);
            expect(adminResult.display).toBe('none');
            expect(adminResult.required).toBe(false);
        });

        test('should handle form submission', () => {
            const handleFormSubmission = (formData) => {
                return new Promise((resolve, reject) => {
                    // Validate form data
                    const errors = [];
                    if (!formData.username) errors.push('Username is required');
                    if (!formData.email) errors.push('Email is required');
                    if (!formData.password) errors.push('Password is required');
                    if (!formData.role) errors.push('Role is required');

                    if (errors.length > 0) {
                        reject({ success: false, errors });
                    } else {
                        // Simulate successful submission
                        resolve({
                            success: true,
                            message: 'User created successfully',
                            user: {
                                id: Math.floor(Math.random() * 1000),
                                ...formData,
                                created_at: new Date().toISOString()
                            }
                        });
                    }
                });
            };

            const validFormData = {
                username: 'testuser',
                email: 'test@example.com',
                password: 'ValidPass123',
                role: 'admin'
            };

            const invalidFormData = {
                username: '',
                email: 'test@example.com',
                password: 'ValidPass123',
                role: 'admin'
            };

            return Promise.all([
                expect(handleFormSubmission(validFormData)).resolves.toMatchObject({
                    success: true,
                    message: 'User created successfully'
                }),
                expect(handleFormSubmission(invalidFormData)).rejects.toMatchObject({
                    success: false,
                    errors: expect.arrayContaining(['Username is required'])
                })
            ]);
        });
    });

    describe('User Error Handling', () => {
        test('should handle network errors gracefully', () => {
            const handleNetworkError = (error) => {
                return {
                    success: false,
                    error: error.message || 'Network error occurred',
                    timestamp: new Date().toISOString(),
                    retryable: true
                };
            };

            const networkError = new Error('Failed to fetch');
            const result = handleNetworkError(networkError);

            expect(result.success).toBe(false);
            expect(result.error).toBe('Failed to fetch');
            expect(result.retryable).toBe(true);
            expect(result.timestamp).toBeDefined();
        });

        test('should handle validation errors', () => {
            const handleValidationError = (errors) => {
                return {
                    success: false,
                    type: 'validation',
                    errors: errors,
                    message: 'Please correct the following errors: ' + errors.join(', ')
                };
            };

            const validationErrors = ['Username is required', 'Email is invalid'];
            const result = handleValidationError(validationErrors);

            expect(result.success).toBe(false);
            expect(result.type).toBe('validation');
            expect(result.errors).toEqual(validationErrors);
            expect(result.message).toContain('Please correct the following errors');
        });

        test('should handle permission errors', () => {
            const handlePermissionError = (action, userId) => {
                return {
                    success: false,
                    type: 'permission',
                    message: `You don't have permission to ${action} user ${userId}`,
                    action: action,
                    userId: userId
                };
            };

            const result = handlePermissionError('delete', 123);

            expect(result.success).toBe(false);
            expect(result.type).toBe('permission');
            expect(result.message).toBe("You don't have permission to delete user 123");
            expect(result.action).toBe('delete');
            expect(result.userId).toBe(123);
        });
    });
}); 