<?php
ob_start();
session_start();
require_once '../../includes/db_connect.php';
require_once '../layouts/header.php';
require_once '../layouts/agency_nav.php';

$additionalScripts = [
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/dashboard.js'
];

// Handle JSON POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $items = $input['items'] ?? [];

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

    // Convert items to the original format (value1;value2 and description1;description2)
    $valueString = implode(';', array_column($items, 'value'));
    $descriptionString = implode(';', array_column($items, 'description'));

    // Prepare data for insertion in original format
    $detail_name = $title;
    $detail_json = json_encode([
        'value' => $valueString,
        'description' => $descriptionString
    ]);

    // Insert into metrics_details table
    $stmt = $conn->prepare("INSERT INTO metrics_details (detail_name, detail_json, is_draft) VALUES (?, ?, 0)");
    if ($stmt) {
        $stmt->bind_param('ss', $detail_name, $detail_json);
        if ($stmt->execute()) {
            header('Content-Type: application/json');
            ob_end_clean();
            echo json_encode(['success' => true, 'message' => 'Metric detail created successfully.']);
            exit;
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $errors[] = 'Database error: ' . $conn->error;
    }

    header('Content-Type: application/json');
    ob_end_clean();
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Fetch existing metric details for display (in original format)
$result = $conn->query("SELECT detail_name, detail_json FROM metrics_details WHERE is_draft = 0 ORDER BY created_at DESC");
$detailsArray = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $jsonData = json_decode($row['detail_json'], true);
        $detailsArray[] = [
            'title' => $row['detail_name'],
            'value' => $jsonData['value'] ?? '',
            'description' => $jsonData['description'] ?? ''
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Metric Detail</title>
    <link href="<?php echo APP_URL; ?>/assets/css/main.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/forms.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/components/buttons.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/navigation.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/layout/dashboard.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/custom/agency.css" rel="stylesheet">
    <style>
        .item-container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .remove-item {
            float: right;
            color: #dc3545;
            cursor: pointer;
            font-weight: bold;
        }
        .add-item-btn {
            margin-bottom: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Create Metric Detail</h1>
        <div id="errorContainer"></div>
        <div id="successContainer"></div>
        
        <form id="metricDetailForm" method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            
            <div id="itemsContainer">
                <div class="item-container" data-index="0">
                    <span class="remove-item" onclick="removeItem(this)">×</span>
                    <div class="mb-3">
                        <label for="value_0" class="form-label">Value</label>
                        <input type="text" class="form-control" id="value_0" name="value_0" required>
                    </div>
                    <div class="mb-3">
                        <label for="description_0" class="form-label">Description</label>
                        <textarea class="form-control" id="description_0" name="description_0" rows="3" required></textarea>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-secondary add-item-btn" onclick="addItem()">+ Add Another Value</button>
            
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="<?php echo APP_URL; ?>/views/agency/submit_metrics.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>

        <div class="mt-5">
            <h2>Created Metric Details</h2>
            <div id="metricDetailsContainer">
                <?php if (empty($detailsArray)): ?>
                    <p>No metric details found.</p>
                <?php else: ?>
                    <ul style="list-style-type: none; padding: 0; display: block; gap: 20px;">
                        <?php foreach ($detailsArray as $detail): ?>
                            <li style="border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-sizing: border-box; min-width: 250px; margin-bottom: 20px;">
                                <div style="display: flex; gap: 15px;">
                                    <div style="flex: 1; display: flex; align-items: center;">
                                        <h3 style="font-weight: bold; margin: 0;"><?= htmlspecialchars($detail['title']) ?></h3>
                                    </div>
                                    <div style="flex: 2;">
                                        <?php
                                        // Split values by semicolon and trim
                                        $values = explode(';', $detail['value']);
                                        $descriptions = explode(';', $detail['description']);
                                        
                                        if (count($values) === 1): ?>
                                            <div style="display: flex; align-items: center; gap: 15px;">
                                                <div style="color: #007bff; font-weight: bold; font-size: 2rem;"><?= htmlspecialchars($values[0]) ?></div>
                                                <div style="color: #000; font-size: 1rem;"><?= htmlspecialchars($descriptions[0] ?? '') ?></div>
                                            </div>
                                        <?php else: ?>
                                            <div style="display: grid; grid-template-columns: repeat(<?= count($values) ?>, 1fr); grid-template-rows: auto auto; gap: 10px;">
                                                <?php foreach ($values as $val): ?>
                                                    <div style="color: #007bff; font-weight: bold; font-size: 2rem;"><?= htmlspecialchars($val) ?></div>
                                                <?php endforeach; ?>
                                                <?php foreach ($descriptions as $desc): ?>
                                                    <div style="color: #000; font-size: 1rem;"><?= htmlspecialchars($desc) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Function to add a new item
        function addItem() {
            const container = document.getElementById('itemsContainer');
            const itemCount = container.children.length;
            const newIndex = itemCount;
            
            const newItem = document.createElement('div');
            newItem.className = 'item-container';
            newItem.dataset.index = newIndex;
            
            newItem.innerHTML = `
                <span class="remove-item" onclick="removeItem(this)">×</span>
                <div class="mb-3">
                    <label for="value_${newIndex}" class="form-label">Value</label>
                    <input type="text" class="form-control" id="value_${newIndex}" name="value_${newIndex}" required>
                </div>
                <div class="mb-3">
                    <label for="description_${newIndex}" class="form-label">Description</label>
                    <textarea class="form-control" id="description_${newIndex}" name="description_${newIndex}" rows="3" required></textarea>
                </div>
            `;
            
            container.appendChild(newItem);
        }
        
        // Function to remove an item
        function removeItem(element) {
            const container = document.getElementById('itemsContainer');
            if (container.children.length > 1) {
                element.parentElement.remove();
                // Reindex remaining items
                Array.from(container.children).forEach((child, index) => {
                    child.dataset.index = index;
                    const valueInput = child.querySelector('input[type="text"]');
                    const descriptionInput = child.querySelector('textarea');
                    valueInput.id = `value_${index}`;
                    valueInput.name = `value_${index}`;
                    descriptionInput.id = `description_${index}`;
                    descriptionInput.name = `description_${index}`;
                });
            } else {
                alert('You need at least one value-description pair.');
            }
        }
        
        // Handle form submission
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('metricDetailForm');
            const errorContainer = document.getElementById('errorContainer');
            const successContainer = document.getElementById('successContainer');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                errorContainer.innerHTML = '';
                successContainer.innerHTML = '';

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
                        successContainer.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                        form.reset();
                        // Reset to one item
                        const container = document.getElementById('itemsContainer');
                        container.innerHTML = '';
                        addItem(); // Add initial item
                        
                        // Reload the page to show updated metrics
                        location.reload();
                    } else {
                        if (result.errors && result.errors.length > 0) {
                            const ul = document.createElement('ul');
                            result.errors.forEach(function (error) {
                                const li = document.createElement('li');
                                li.textContent = error;
                                ul.appendChild(li);
                            });
                            errorContainer.appendChild(ul);
                            errorContainer.className = 'alert alert-danger';
                        } else {
                            errorContainer.textContent = 'An unknown error occurred.';
                            errorContainer.className = 'alert alert-danger';
                        }
                    }
                })
                .catch((error) => {
                    console.error('Fetch error:', error);
                    errorContainer.textContent = 'Failed to submit data. Please try again.';
                    errorContainer.className = 'alert alert-danger';
                });
            });
        });
    </script>
<?php require_once '../layouts/footer.php'; ?>