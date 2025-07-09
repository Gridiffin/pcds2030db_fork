document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period_id');
    const targetsContainer = document.getElementById('targets-container');
    const addTargetBtn = document.getElementById('add-target-btn');
    // Highlight open periods
    Array.from(periodSelect.options).forEach(option => {
        if (option.dataset.status === 'open') {
            option.classList.add('text-success', 'fw-bold');
        }
    });
    // Target management
    let targetCounter = 0;
    const addNewTarget = () => {
        targetCounter++;
        const targetEntry = document.createElement('div');
        targetEntry.className = 'target-entry border rounded p-2 mb-2 position-relative';
        targetEntry.innerHTML = `
            <button type="button" class="btn-close remove-target" aria-label="Remove target" style="position: absolute; top: 5px; right: 5px;"></button>
            <div class="mb-2">
                <label class="form-label small">Target ${targetCounter}</label>
                <textarea class="form-control form-control-sm" name="target_text[]" rows="2" placeholder="Define a measurable target" required></textarea>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm" name="target_number[]" placeholder="Target Number">
                </div>
                <div class="col-6">
                    <select class="form-select form-select-sm" name="target_status[]">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <textarea class="form-control form-control-sm" name="target_status_description[]" rows="1" placeholder="Status description"></textarea>
            </div>
        `;
        targetsContainer.appendChild(targetEntry);
        // Add remove functionality
        const removeBtn = targetEntry.querySelector('.remove-target');
        removeBtn.addEventListener('click', () => {
            targetEntry.remove();
            updateTargetNumbers();
        });
    };
    const updateTargetNumbers = () => {
        const targets = targetsContainer.querySelectorAll('.target-entry');
        targets.forEach((target, index) => {
            const label = target.querySelector('label');
            if (label) {
                label.textContent = `Target ${index + 1}`;
            }
        });
        targetCounter = targets.length;
    };
    addTargetBtn.addEventListener('click', addNewTarget);
    // Add one target by default
    addNewTarget();
    // Auto-generate target numbers if not provided
    // (Optional: implement if needed)
}); 