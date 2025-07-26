<?php
/**
 * Reporting Periods Content
 * Simple, clean implementation that loads periods directly
 */

// Include database connection
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';

// Get all periods ordered by year (desc) and period number
$stmt = $conn->prepare("SELECT * FROM reporting_periods ORDER BY year DESC, period_number ASC");
$stmt->execute();
$periods_result = $stmt->get_result();

$periods = [];
while ($row = $periods_result->fetch_assoc()) {
    $periods[] = $row;
}

// Group periods by year for better display
$periods_by_year = [];
foreach ($periods as $period) {
    $periods_by_year[$period['year']][] = $period;
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Reporting Periods Management</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                        <i class="fas fa-plus me-1"></i> Add New Period
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($periods)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No reporting periods found. Add a new period to get started.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="periodsAccordion">
                            <?php foreach ($periods_by_year as $year => $year_periods): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?php echo array_keys($periods_by_year)[0] != $year ? 'collapsed' : ''; ?>" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#year<?php echo $year; ?>" 
                                                aria-expanded="<?php echo array_keys($periods_by_year)[0] == $year ? 'true' : 'false'; ?>">
                                            <span class="fw-bold"><?php echo $year; ?></span>
                                            <span class="ms-2 badge rounded-pill bg-secondary"><?php echo count($year_periods); ?> periods</span>
                                        </button>
                                    </h2>
                                    <div id="year<?php echo $year; ?>" 
                                         class="accordion-collapse collapse <?php echo array_keys($periods_by_year)[0] == $year ? 'show' : ''; ?>" 
                                         data-bs-parent="#periodsAccordion">
                                        <div class="accordion-body p-0">
                                            <table class="table table-striped table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Period</th>
                                                        <th>Date Range</th>
                                                        <th>Status</th>
                                                        <th class="text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($year_periods as $period): ?>
                                                        <tr>
                                                            <td>
                                                                <?php
                                                                if ($period['period_type'] == 'quarter') {
                                                                    echo "Q" . $period['period_number'];
                                                                } elseif ($period['period_type'] == 'half') {
                                                                    echo "Half Yearly " . $period['period_number'];
                                                                } else {
                                                                    echo "Yearly " . $period['period_number'];
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php echo date('M j, Y', strtotime($period['start_date'])); ?> - 
                                                                <?php echo date('M j, Y', strtotime($period['end_date'])); ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $period['status'] == 'open' ? 'success' : 'danger'; ?>">
                                                                    <?php echo ucfirst($period['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-primary edit-period-btn" 
                                                                            data-period-id="<?php echo $period['period_id']; ?>"
                                                                            data-year="<?php echo $period['year']; ?>"
                                                                            data-period-type="<?php echo $period['period_type']; ?>"
                                                                            data-period-number="<?php echo $period['period_number']; ?>"
                                                                            data-start-date="<?php echo $period['start_date']; ?>"
                                                                            data-end-date="<?php echo $period['end_date']; ?>"
                                                                            data-status="<?php echo $period['status']; ?>">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-<?php echo $period['status'] == 'open' ? 'warning' : 'success'; ?> toggle-status-btn"
                                                                            data-period-id="<?php echo $period['period_id']; ?>"
                                                                            data-current-status="<?php echo $period['status']; ?>">
                                                                        <?php if ($period['status'] == 'open'): ?>
                                                                            <i class="fas fa-lock"></i>
                                                                        <?php else: ?>
                                                                            <i class="fas fa-lock-open"></i>
                                                                        <?php endif; ?>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger delete-period-btn" 
                                                                            data-period-id="<?php echo $period['period_id']; ?>">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Period Modal -->
<div class="modal fade" id="addPeriodModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Reporting Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="periodForm">
                <div class="modal-body">
                    <input type="hidden" id="periodId" name="period_id">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="periodType" class="form-label">Period Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="periodType" name="period_type" required>
                                <option value="">Select Type</option>
                                <option value="quarter">Quarter</option>
                                <option value="half">Half Yearly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="periodNumber" class="form-label">Period Number <span class="text-danger">*</span></label>
                            <select class="form-select" id="periodNumber" name="period_number" required>
                                <option value="">Select Number</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="year" name="year" 
                                   min="2020" max="2030" required>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="closed" selected>Closed</option>
                            <option value="open">Open</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Ensure modal is centered properly */
.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100vh - 1rem);
    margin: 0.5rem auto;
}

.modal-dialog-centered::before {
    display: block;
    height: calc(100vh - 1rem);
    content: "";
}

.modal-dialog-centered .modal-content {
    margin: auto;
}

/* Ensure modal backdrop covers full screen */
.modal {
    padding-left: 0 !important;
}

.modal-open {
    padding-right: 0 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
        // Period type change handler
        const periodType = document.getElementById('periodType');
        if (periodType) {
            periodType.addEventListener('change', function() {
                const periodNumber = document.getElementById('periodNumber');
        const type = this.value;
        
        // Clear existing options
        periodNumber.innerHTML = '<option value="">Select Number</option>';
        
        if (type === 'quarter') {
            for (let i = 1; i <= 4; i++) {
                periodNumber.innerHTML += `<option value="${i}">${i}</option>`;
            }
        } else if (type === 'half') {
            for (let i = 1; i <= 2; i++) {
                periodNumber.innerHTML += `<option value="${i}">${i}</option>`;
            }
        } else if (type === 'yearly') {
            periodNumber.innerHTML += '<option value="1">1</option>';
        }
        
        updateDates();
            });
        }
    
        // Period number and year change handler
        const periodNumber = document.getElementById('periodNumber');
        const year = document.getElementById('year');
        if (periodNumber) {
            periodNumber.addEventListener('change', updateDates);
        }
        if (year) {
            year.addEventListener('change', updateDates);
        }
    
        function updateDates() {
            const type = document.getElementById('periodType').value;
            const number = parseInt(document.getElementById('periodNumber').value);
            const year = parseInt(document.getElementById('year').value);
            
            if (!type || !number || !year) return;
            
            let startDate, endDate;
            
            if (type === 'quarter') {
                switch (number) {
                    case 1:
                        startDate = `${year}-01-01`;
                        endDate = `${year}-03-31`;
                        break;
                    case 2:
                        startDate = `${year}-04-01`;
                        endDate = `${year}-06-30`;
                        break;
                    case 3:
                        startDate = `${year}-07-01`;
                        endDate = `${year}-09-30`;
                        break;
                    case 4:
                        startDate = `${year}-10-01`;
                        endDate = `${year}-12-31`;
                        break;
                }
            } else if (type === 'half') {
                switch (number) {
                    case 1:
                        startDate = `${year}-01-01`;
                        endDate = `${year}-06-30`;
                        break;
                    case 2:
                        startDate = `${year}-07-01`;
                        endDate = `${year}-12-31`;
                        break;
                }
            } else if (type === 'yearly') {
                startDate = `${year}-01-01`;
                endDate = `${year}-12-31`;
            }
            
            if (startDate && endDate) {
                document.getElementById('startDate').value = startDate;
                document.getElementById('endDate').value = endDate;
            }
        }
    
    // Form submission
    const periodForm = document.getElementById('periodForm');
    if (periodForm) {
        periodForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEdit = document.getElementById('periodId').value !== '';
            const url = `${window.APP_URL}/app/ajax/${isEdit ? 'update_period.php' : 'add_period.php'}`;
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save period'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the period: ' + error.message);
            });
        });
    }
    
    // Edit button handler
    document.querySelectorAll('.edit-period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            
            document.getElementById('modalTitle').textContent = 'Edit Reporting Period';
            document.getElementById('periodId').value = data.periodId;
            document.getElementById('periodType').value = data.periodType;
            
            // Trigger period type change to populate numbers
            document.getElementById('periodType').dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                document.getElementById('periodNumber').value = data.periodNumber;
                document.getElementById('year').value = data.year;
                document.getElementById('startDate').value = data.startDate;
                document.getElementById('endDate').value = data.endDate;
                document.getElementById('status').value = data.status;
            }, 100);
            
            const modal = new bootstrap.Modal(document.getElementById('addPeriodModal'));
            modal.show();
        });
    });
    
    // Toggle status handler
    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const periodId = this.dataset.periodId;
            const currentStatus = this.dataset.currentStatus;
            const newStatus = currentStatus === 'open' ? 'closed' : 'open';
            
            if (confirm(`Are you sure you want to ${newStatus === 'open' ? 'open' : 'close'} this period?`)) {
                fetch(`${window.APP_URL}/app/ajax/toggle_period_status.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `period_id=${periodId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update status'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating status');
                });
            }
        });
    });
    
    // Delete handler
    document.querySelectorAll('.delete-period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const periodId = this.dataset.periodId;
            
            if (confirm('Are you sure you want to delete this period? This action cannot be undone.')) {
                fetch(`${window.APP_URL}/app/ajax/delete_period.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `period_id=${periodId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete period'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting period');
                });
            }
        });
    });
    
    // Reset modal when hidden
    document.getElementById('addPeriodModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('modalTitle').textContent = 'Add New Reporting Period';
        document.getElementById('periodForm').reset();
        document.getElementById('periodId').value = '';
        document.getElementById('periodNumber').innerHTML = '<option value="">Select Number</option>';
    });
    
});
</script>