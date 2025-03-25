/**
 * User management functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Toggle agency fields based on selected role
    const roleSelect = document.getElementById('role');
    const editRoleSelect = document.getElementById('edit_role');
    
    // For Add User modal
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            toggleAgencyFields(this.value, '.agency-field');
        });
        
        // Initial toggle based on default selection
        toggleAgencyFields(roleSelect.value, '.agency-field');
    }
    
    // For Edit User modal
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', function() {
            toggleAgencyFields(this.value, '.edit-agency-field');
        });
    }
    
    // Handle Edit User modal data population
    const editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Get data from button
            const userId = button.getAttribute('data-user-id');
            const username = button.getAttribute('data-username');
            const role = button.getAttribute('data-role');
            const agency = button.getAttribute('data-agency');
            const sectorId = button.getAttribute('data-sector');
            
            // Set form values
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_agency_name').value = agency || '';
            document.getElementById('edit_sector_id').value = sectorId || '';
            
            // Toggle agency fields based on role
            toggleAgencyFields(role, '.edit-agency-field');
        });
    }
    
    // Handle Deactivate User modal data population
    const deactivateUserModal = document.getElementById('deactivateUserModal');
    if (deactivateUserModal) {
        deactivateUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Get data from button
            const userId = button.getAttribute('data-user-id');
            const username = button.getAttribute('data-username');
            
            // Set form values
            document.getElementById('deactivate_user_id').value = userId;
            document.getElementById('deactivate_username').textContent = username;
        });
    }
    
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            
            // Toggle password visibility
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i class="far fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="far fa-eye"></i>';
            }
        });
    });
    
    // Password validation
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        const passwordHint = document.querySelector('.password-hint');
        
        passwordInput.addEventListener('input', function() {
            const value = this.value;
            
            if (value.length > 0 && value.length < 8) {
                passwordHint.textContent = `Password must be at least 8 characters (${value.length}/8)`;
                passwordHint.classList.remove('text-muted');
                passwordHint.classList.add('text-danger');
            } else if (value.length >= 8) {
                passwordHint.textContent = 'Password meets minimum length requirement';
                passwordHint.classList.remove('text-danger');
                passwordHint.classList.add('text-success');
            } else {
                passwordHint.textContent = 'Password should be at least 8 characters';
                passwordHint.classList.remove('text-danger', 'text-success');
                passwordHint.classList.add('text-muted');
            }
        });
        
        // Form validation
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            if (passwordInput.value.length < 8) {
                e.preventDefault();
                passwordHint.textContent = 'Password must be at least 8 characters';
                passwordHint.classList.add('text-danger');
                passwordInput.focus();
            }
        });
    }
    
    // Function to toggle agency fields based on role
    function toggleAgencyFields(role, fieldsSelector) {
        document.querySelectorAll(fieldsSelector).forEach(field => {
            const inputs = field.querySelectorAll('input, select');
            
            if (role === 'admin') {
                field.style.display = 'none';
                inputs.forEach(input => input.removeAttribute('required'));
            } else {
                field.style.display = 'block';
                inputs.forEach(input => input.setAttribute('required', ''));
            }
        });
    }
    
    // Fix for focus issues in modals
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input:not([type=hidden]), select');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        });
    });
});
