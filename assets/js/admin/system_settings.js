// assets/js/admin/system_settings.js
// Handles UI logic for the System Settings page (admin)

document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.getElementById('outcomeCreationToggle');
    if (toggleSwitch) {
        toggleSwitch.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.textContent = 'Enabled - Outcome Creation Allowed';
            } else {
                label.textContent = 'Disabled - Outcome Creation Restricted';
            }
        });
    }
}); 