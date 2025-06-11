<?php
/**
 * Create Outcome Details - Admin
 * 
 * Interface for admin users to create and manage outcome details system-wide.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Add cache control headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$pageTitle = 'Create Outcome Details';

// Add CSS references for enhanced styling
$additionalStyles = [
    APP_URL . '/assets/css/components/forms.css',
    APP_URL . '/assets/css/components/buttons.css',
    APP_URL . '/assets/css/layout/dashboard.css'
];

// Handle JSON POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $items = $input['items'] ?? [];
    $detail_id = isset($input['detail_id']) ? (int)$input['detail_id'] : null;

    $errors = [];

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    
    if (empty($items)) {
        $errors[] = 'At least one value-description pair is required.';
    } else {
        foreach ($items as $index => $item) {
            $value = trim($item['value'] ?? '');
            $description = trim($item['description'] ?? '');
            
            if ($value === '') {
                $errors[] = "Value #" . ($index + 1) . " is required.";
            }
            if ($description === '') {
                $errors[] = "Description #" . ($index + 1) . " is required.";
            }
        }
    }

    if (!empty($errors)) {
        header('Content-Type: application/json');
        ob_end_clean();
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Get the selected layout type
    $layout_type = trim($input['layout_type'] ?? 'simple');
    
    // Prepare data for insertion or update using the new format
    $detail_name = $title;
    $detail_json = json_encode([
        'layout_type' => $layout_type,
        'items' => $items
    ]);    if ($detail_id === null) {
        // Insert new outcome detail
        // First check if an outcome detail with the same name already exists
        $check_stmt = $conn->prepare("SELECT detail_id FROM outcomes_details WHERE detail_name = ?");
        if (!$check_stmt) {
            $errors[] = 'Database error: ' . $conn->error;
        } else {
            $check_stmt->bind_param('s', $detail_name);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = 'An outcome detail with this name already exists. Please use a different name.';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // No duplicates found, proceed with insertion
                $stmt = $conn->prepare("INSERT INTO outcomes_details (detail_name, detail_json, is_draft) VALUES (?, ?, 0)");
                if ($stmt) {
                    $stmt->bind_param('ss', $detail_name, $detail_json);
                    if ($stmt->execute()) {
                        header('Content-Type: application/json');
                        ob_end_clean();
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Outcome detail created successfully.',
                            'action' => 'create',
                            'new_id' => $stmt->insert_id,
                            'title' => $detail_name,
                            'items' => $items
                        ]);
                        exit;
                    } else {
                        $errors[] = 'Database error: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $errors[] = 'Database error: ' . $conn->error;
                }
            }
        }
    } else {
        // Update existing outcome detail
        $stmt = $conn->prepare("UPDATE outcomes_details SET detail_name = ?, detail_json = ? WHERE detail_id = ?");
        if ($stmt) {
            $stmt->bind_param('ssi', $detail_name, $detail_json, $detail_id);
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                ob_end_clean();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Outcome detail updated successfully.',
                    'action' => 'update',
                    'updated_id' => $detail_id,
                    'title' => $detail_name,
                    'items' => $items
                ]);
                exit;
            } else {
                $errors[] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }

    header('Content-Type: application/json');
    ob_end_clean();
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Handle DELETE request for removing outcome details
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $detail_id = isset($input['detail_id']) ? (int)$input['detail_id'] : 0;
    
    if ($detail_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid detail ID.']);
        exit;
    }
      $stmt = $conn->prepare("DELETE FROM outcomes_details WHERE detail_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $detail_id);
        if ($stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Outcome detail deleted successfully.']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            exit;
        }
        $stmt->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
}

// Fetch existing outcome details for display
$result = $conn->query("SELECT detail_id, detail_name, detail_json, created_at FROM outcomes_details WHERE is_draft = 0 ORDER BY created_at DESC");
$detailsArray = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $jsonData = json_decode($row['detail_json'], true);
        
        // Handle both new and legacy format
        $items = [];
        $layout_type = 'simple'; // Default
        
        if (isset($jsonData['layout_type']) && isset($jsonData['items'])) {
            // New format
            $layout_type = $jsonData['layout_type'];
            $items = $jsonData['items'];
        } elseif (isset($jsonData['value']) && isset($jsonData['description'])) {
            // Legacy format - convert to new format
            $values = explode(';', $jsonData['value']);
            $descriptions = explode(';', $jsonData['description']);
            
            for ($i = 0; $i < count($values); $i++) {
                $items[] = [
                    'value' => $values[$i],
                    'description' => $descriptions[$i] ?? ''
                ];
            }
        }
        
        $detailsArray[] = [
            'id' => $row['detail_id'],
            'title' => $row['detail_name'],
            'layout_type' => $layout_type,
            'items' => $items,
            'created_at' => $row['created_at'],
            // Keep these fields for backward compatibility in the UI
            'value' => isset($jsonData['value']) ? $jsonData['value'] : implode(';', array_column($items, 'value')),
            'description' => isset($jsonData['description']) ? $jsonData['description'] : implode(';', array_column($items, 'description'))
        ];
    }
}

// Include header
require_once ROOT_PATH . 'app/views/layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Create Outcome Details',
    'subtitle' => 'Design and manage detailed KPI structures for outcome reporting system-wide',
    'variant' => 'white',
    'actions' => [
        [
            'text' => 'Back to Manage Outcomes',
            'url' => 'manage_outcomes.php',
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Alert Messages -->
    <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
    <div id="successContainer" class="alert alert-success" style="display: none;"></div>

    <!-- Form Section -->
    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Outcome Detail Configuration</h5>
            <small class="text-muted">Create structured KPI templates for outcome reporting</small>
        </div>
        <div class="card-body">
            <form id="metricDetailForm" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <small class="text-muted">Enter a unique name for this outcome detail configuration</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="layout_type" class="form-label">Layout Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="layout_type" name="layout_type" required>
                                <option value="simple">Simple (Default)</option>
                                <option value="detailed_list">Detailed List</option>
                                <option value="comparison">Comparison</option>
                            </select>
                            <small class="text-muted">Select the visual presentation style for this KPI</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Value-Description Pairs <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-3">Define the KPI metrics and their descriptions</small>
                    
                    <div id="itemsContainer">
                        <!-- Initial item will be added by JavaScript -->
                    </div>
                    
                    <button type="button" id="addItemBtn" class="btn btn-outline-success btn-sm mt-2">
                        <i class="fas fa-plus me-1"></i> Add Another Item
                    </button>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" id="resetFormBtn" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i> Reset Form
                    </button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Outcome Detail
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Details Section -->
    <div class="card admin-card">
        <div class="card-header">
            <h5 class="card-title mb-0">Existing Outcome Details</h5>
            <small class="text-muted">Manage your existing outcome detail configurations</small>
        </div>
        <div class="card-body">
            <div id="metricDetailsContainer">
                <?php if (empty($detailsArray)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No outcome details found.</p>
                        <p class="small text-muted">Create your first outcome detail using the form above.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($detailsArray as $detail): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 border-light shadow-sm">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0 text-primary">
                                                <i class="fas fa-chart-line me-1"></i>
                                                <?= htmlspecialchars($detail['title']) ?>
                                            </h6>
                                            <span class="badge bg-info"><?= ucfirst($detail['layout_type']) ?></span>
                                        </div>
                                        <small class="text-muted">Created: <?= date('M j, Y', strtotime($detail['created_at'])) ?></small>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($detail['items'])): ?>
                                            <div class="mb-3">
                                                <?php foreach ($detail['items'] as $index => $item): ?>
                                                    <div class="border-start border-primary ps-3 mb-2">
                                                        <strong class="text-primary"><?= htmlspecialchars($item['value']) ?></strong>
                                                        <p class="small text-muted mb-0"><?= htmlspecialchars($item['description']) ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-outline-primary btn-sm edit-detail-btn" 
                                                    data-detail-id="<?= $detail['id'] ?>"
                                                    data-title="<?= htmlspecialchars($detail['title']) ?>"
                                                    data-layout-type="<?= $detail['layout_type'] ?>"
                                                    data-items='<?= json_encode($detail['items']) ?>'>
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm delete-detail-btn"
                                                    data-detail-id="<?= $detail['id'] ?>"
                                                    data-title="<?= htmlspecialchars($detail['title']) ?>">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    let itemCounter = 0;
    let editingDetailId = null;

    const form = document.getElementById('metricDetailForm');
    const errorContainer = document.getElementById('errorContainer');
    const successContainer = document.getElementById('successContainer');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetFormBtn');
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('itemsContainer');

    function showAlert(message, type) {
        const container = type === 'error' ? errorContainer : successContainer;
        const otherContainer = type === 'error' ? successContainer : errorContainer;
        
        // Hide other container
        otherContainer.style.display = 'none';
        
        // Show current container
        container.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>${message}`;
        container.style.display = 'block';
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                container.style.display = 'none';
            }, 5000);
        }
        
        // Scroll to top to show the alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function addItem(value = '', description = '') {
        itemCounter++;
        const itemHtml = `
            <div class="item-container border rounded p-3 mb-3 bg-light" data-index="${itemCounter}">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Value</label>
                        <input type="text" class="form-control" id="value_${itemCounter}" 
                               placeholder="e.g., 85%" value="${value}" required>
                    </div>
                    <div class="col-md-7 mb-2">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="description_${itemCounter}" 
                               placeholder="e.g., Completion rate of conservation projects" value="${description}" required>
                    </div>
                    <div class="col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" 
                                onclick="removeItem(${itemCounter})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
    }

    window.removeItem = function(index) {
        const itemContainer = document.querySelector(`.item-container[data-index="${index}"]`);
        if (itemContainer) {
            itemContainer.remove();
        }
    };

    function resetForm() {
        form.reset();
        itemsContainer.innerHTML = '';
        itemCounter = 0;
        editingDetailId = null;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Create Outcome Detail';
        errorContainer.style.display = 'none';
        successContainer.style.display = 'none';
        addItem(); // Add initial empty item
    }

    // Initialize with one empty item
    addItem();

    // Event listeners
    addItemBtn.addEventListener('click', () => addItem());
    resetBtn.addEventListener('click', resetForm);

    // Edit detail functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-detail-btn')) {
            const btn = e.target.closest('.edit-detail-btn');
            const detailId = btn.dataset.detailId;
            const title = btn.dataset.title;
            const layoutType = btn.dataset.layoutType;
            const items = JSON.parse(btn.dataset.items);

            // Populate form
            document.getElementById('title').value = title;
            document.getElementById('layout_type').value = layoutType;
            
            // Clear and populate items
            itemsContainer.innerHTML = '';
            itemCounter = 0;
            items.forEach(item => addItem(item.value, item.description));
            
            editingDetailId = detailId;
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Update Outcome Detail';
            
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Delete detail functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-detail-btn')) {
            const btn = e.target.closest('.delete-detail-btn');
            const detailId = btn.dataset.detailId;
            const title = btn.dataset.title;

            if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
                fetch(window.location.href, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ detail_id: detailId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Reload page to update the list
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while deleting the outcome detail.', 'error');
                });
            }
        }
    });

    // Handle form submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errorContainer.style.display = 'none';
        successContainer.style.display = 'none';

        const title = form.title.value.trim();
        const layoutType = form.layout_type.value;
        const items = [];
        
        // Collect all items
        const itemContainers = document.querySelectorAll('.item-container');
        itemContainers.forEach(container => {
            const index = container.dataset.index;
            const value = document.getElementById(`value_${index}`).value.trim();
            const description = document.getElementById(`description_${index}`).value.trim();
            
            items.push({
                value: value,
                description: description
            });
        });

        const data = {
            title: title,
            layout_type: layoutType,
            items: items
        };

        if (editingDetailId) {
            data.detail_id = editingDetailId;
        }

        // Disable submit button during request
        submitBtn.disabled = true;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            if (data.success) {
                showAlert(data.message, 'success');
                resetForm();
                // Reload page to update the list
                setTimeout(() => window.location.reload(), 1500);
            } else {
                if (data.errors && Array.isArray(data.errors)) {
                    showAlert(data.errors.join('<br>'), 'error');
                } else {
                    showAlert('An error occurred while processing your request.', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            showAlert('An error occurred while processing your request.', 'error');
        });
    });
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
