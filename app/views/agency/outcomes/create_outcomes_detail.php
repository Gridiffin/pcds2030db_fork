<?php
/**
 * Create Metric Detail
 * 
 * Interface for agency users to create metric details.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

ob_start();
session_start();
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';

// Add cache control headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$additionalScripts = [
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/dashboard.js'
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
    }    // Get the selected layout type
    $layout_type = trim($input['layout_type'] ?? 'simple');
    
    // Prepare data for insertion or update using the new format
    $detail_name = $title;
    $detail_json = json_encode([
        'layout_type' => $layout_type,
        'items' => $items
    ]);    if ($detail_id === null) {        // Insert new outcome detail
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
    } else {        // Update existing outcome detail
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

// Fetch existing outcome details for display
$result = $conn->query("SELECT detail_id, detail_name, detail_json FROM outcomes_details WHERE is_draft = 0 ORDER BY created_at DESC");
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
            // Keep these fields for backward compatibility in the UI
            'value' => isset($jsonData['value']) ? $jsonData['value'] : implode(';', array_column($items, 'value')),
            'description' => isset($jsonData['description']) ? $jsonData['description'] : implode(';', array_column($items, 'description'))
        ];
    }
}
?>

<?php
// Header configuration
$header_config = [
    'title' => 'Create Outcome Detail',
    'subtitle' => 'Design and manage detailed KPI structures for outcome reporting',
    'variant' => 'white',    'actions' => [
        [
            'text' => 'Back to Outcomes',
            'url' => APP_URL . '/app/views/agency/outcomes/submit_outcomes.php',
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];
require_once '../../layouts/page_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Outcome Detail</title>
    <link href="<?php echo APP_URL; ?>/assets/css/main.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/forms.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/buttons.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/navigation.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/dashboard.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/custom/agency.css" rel="stylesheet">
</head>
<body>
    <div class="main-content">
        <div class="container-fluid p-4">
            <!-- Alert Messages -->
            <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
            <div id="successContainer" class="alert alert-success" style="display: none;"></div>

            <!-- Form Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create Outcome Detail</h5>
                    <small class="text-muted">Define the structure and components of your outcome metrics</small>
                </div>
                <div class="card-body">
                    <form id="metricDetailForm">
                        <div class="mb-3">
                            <label for="title" class="form-label">Detail Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="layout_type" class="form-label">Layout Type</label>
                            <select class="form-select" id="layout_type" name="layout_type">
                                <option value="simple">Simple</option>
                                <option value="detailed">Detailed</option>
                            </select>
                        </div>

                        <div id="itemsContainer">
                            <!-- Dynamic item fields will be injected here -->
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" onclick="addItem()">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-save me-1"></i> Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Helper function to escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Function to add a new item field
        function addItem(value = '', description = '') {
            const container = document.getElementById('itemsContainer');
            const index = container.children.length; // New index based on current children count
            
            const newItem = document.createElement('div');
            newItem.className = 'item-container border rounded p-3 mb-3 position-relative';
            newItem.dataset.index = index;
            newItem.innerHTML = `
                <span class="remove-item position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 25px; height: 25px; cursor: pointer; margin: 5px;" onclick="removeItem(this)">×</span>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="value_${index}" class="form-label">Value</label>
                            <input type="text" class="form-control" id="value_${index}" name="value_${index}" required value="${escapeHtml(value)}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description_${index}" class="form-label">Description</label>
                            <textarea class="form-control" id="description_${index}" name="description_${index}" rows="3" required>${escapeHtml(description)}</textarea>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
        }

        // Function to remove an item field
        function removeItem(button) {
            const container = document.getElementById('itemsContainer');
            container.removeChild(button.closest('.item-container'));
            
            // Reindex items
            container.querySelectorAll('.item-container').forEach((item, index) => {
                item.dataset.index = index;
                item.querySelector('input, textarea').id = `value_${index}, description_${index}`;
            });
        }

        // Function to show alert messages
        function showAlert(message, type) {
            const container = document.getElementById(`${type}Container`);
            if (container) {
                container.textContent = message;
                container.style.display = 'block';
                setTimeout(() => {
                    container.style.display = 'none';
                }, 5000);
            } else {
                console.error(`Alert container not found for type: ${type}`);
            }
        }

        // Handle form submission
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('metricDetailForm');
            const errorContainer = document.getElementById('errorContainer');
            const successContainer = document.getElementById('successContainer');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                errorContainer.style.display = 'none';
                successContainer.style.display = 'none';

                const title = form.title.value.trim();
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
                    items: items
                };

                // Only include detail_id if we're editing
                if (editingDetailId !== null) {
                    data.detail_id = editingDetailId;
                }

                // Disable the submit button to prevent duplicate submissions
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
.then(result => {
    if (result.success) {
        showAlert(result.message, 'success');
        
        // Reset editingDetailId before reload
        editingDetailId = null;

        // Reload the page after create or update action
        window.location.href = window.location.href;
    } else {
        if (result.errors && result.errors.length > 0) {
            const ul = document.createElement('ul');
            result.errors.forEach(function (error) {
                const li = document.createElement('li');
                li.textContent = error;
                ul.appendChild(li);
            });
            errorContainer.innerHTML = '';
            errorContainer.appendChild(ul);
            errorContainer.style.display = 'block';
        } else {
            showAlert('An unknown error occurred.', 'error');
        }
    }
})
                .catch((error) => {
                    console.error('Fetch error:', error);
                    showAlert('Failed to submit data. Please try again.', 'error');
                })                .finally(() => {
                    // Re-enable the submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = editingDetailId ? '<i class="fas fa-save me-1"></i> Update' : '<i class="fas fa-save me-1"></i> Create';
                });
            });
        });
    </script>
<?php require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php'; ?>



