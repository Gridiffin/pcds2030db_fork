<?php
// Start session and include necessary files
session_start();
require_once '../../includes/db_connect.php'; // Adjust path as needed

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Additional scripts needed for dashboard
$additionalScripts = [
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/dashboard.js'
];

// Handle JSON POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    // Read the raw input
    $input = json_decode(file_get_contents('php://input'), true);

    $value = trim($input['value'] ?? '');
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');

    $errors = [];

    if ($value === '') {
        $errors[] = 'Value is required.';
    }
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }

    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Prepare data for insertion
    $detail_name = $title;
    $detail_json = json_encode([
        'value' => $value,
        'description' => $description
    ]);

    // Insert into metrics_details table
    $stmt = $conn->prepare("INSERT INTO metrics_details (detail_name, detail_json, is_draft) VALUES (?, ?, 0)");
    if ($stmt) {
        $stmt->bind_param('ss', $detail_name, $detail_json);
        if ($stmt->execute()) {
            header('Content-Type: application/json');
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
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('metricDetailForm');
            const errorContainer = document.getElementById('errorContainer');
            const successContainer = document.getElementById('successContainer');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                errorContainer.innerHTML = '';
                successContainer.innerHTML = '';

                const data = {
                    value: form.value.value.trim(),
                    title: form.title.value.trim(),
                    description: form.description.value.trim()
                };

                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        successContainer.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                        form.reset();
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
                .catch(() => {
                    errorContainer.textContent = 'Failed to submit data. Please try again.';
                    errorContainer.className = 'alert alert-danger';
                });
            });
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1>Create Metric Detail</h1>
        <div id="errorContainer"></div>
        <div id="successContainer"></div>
        <div style="display: flex; gap: 40px; align-items: flex-start;">
            <form id="metricDetailForm" method="post" action="" style="flex: 1; min-width: 300px;">
                <div class="mb-3">
                    <label for="value" class="form-label">Value</label>
                    <input type="text" class="form-control" id="value" name="value" required>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="<?php echo APP_URL; ?>/views/agency/submit_metrics.php" class="btn btn-secondary ms-2">Cancel</a>
            </form>
            <div style="flex: 1; min-width: 300px; border: 1px solid #ccc; border-radius: 8px; padding: 20px; background-color: #f9f9f9; color: #333; font-size: 0.9rem; line-height: 1.4;">
                <h2>Guide</h2>
                <p>The <strong>Values</strong> can be separated with a <strong>semicolon (;)</strong></p>
                <p>The <strong>Title</strong> should be descriptive and concise.</p>
                <p>The <strong>Description</strong> can include additional details or context. You can separate the description for values with a <strong>semicolon (;)</strong> too!</p>
            </div>
        </div>

        <div class="mt-5">
            <h2>Created Metric Details</h2>
            <div id="metricDetailsContainer"></div>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const metricDetails = <?php
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
            echo json_encode($detailsArray);
        ?>;

        // Function to render metric details with infographic style side by side
        function renderMetricDetails() {
            const container = document.getElementById('metricDetailsContainer');
            container.innerHTML = ''; // Clear existing content
            if (metricDetails.length === 0) {
                container.innerHTML = '<p>No metric details found.</p>';
                return;
            }

        // Create a list container for metric details
        const listContainer = document.createElement('ul');
        listContainer.style.listStyleType = 'none';
        listContainer.style.padding = '0';
        listContainer.style.display = 'block';
        listContainer.style.gap = '20px';

        metricDetails.forEach((detail) => {
            const listItem = document.createElement('li');
            listItem.style.border = '1px solid #ccc';
            listItem.style.borderRadius = '8px';
            listItem.style.padding = '20px';
            listItem.style.boxSizing = 'border-box';
            listItem.style.minWidth = '250px';
            listItem.style.marginBottom = '20px';

            // Create a flex container for the detail content: left box for title, right box for value and description stacked
            const detailFlex = document.createElement('div');
            detailFlex.style.display = 'flex';
            detailFlex.style.gap = '15px';

            // Left box for title
            const titleBox = document.createElement('div');
            titleBox.style.flex = '1';
            titleBox.style.display = 'flex';
            titleBox.style.alignItems = 'center';

            const title = document.createElement('h3');
            title.textContent = detail.title;
            title.style.fontWeight = 'bold';
            title.style.margin = '0';
            titleBox.appendChild(title);

            // Right box for value and description stacked vertically
            const rightBox = document.createElement('div');
            rightBox.style.flex = '2';

            // Split values by semicolon and trim
            const values = detail.value.split(';').map(v => v.trim());

            if (values.length === 1) {
                // If only one value, place description next to value horizontally
                rightBox.style.display = 'flex';
                rightBox.style.alignItems = 'center';
                rightBox.style.gap = '15px';

                const valueDiv = document.createElement('div');
                valueDiv.textContent = values[0];
                valueDiv.style.color = '#007bff'; // Bootstrap primary blue
                valueDiv.style.fontWeight = 'bold';
                valueDiv.style.fontSize = '2rem';

                const descriptionDiv = document.createElement('div');
                descriptionDiv.textContent = detail.description;
                descriptionDiv.style.color = '#000';
                descriptionDiv.style.fontSize = '1rem';

                rightBox.appendChild(valueDiv);
                rightBox.appendChild(descriptionDiv);
            } else {
                // Multiple values and descriptions, display in grid
                rightBox.style.display = 'grid';
                rightBox.style.gridTemplateColumns = `repeat(${values.length}, 1fr)`;
                rightBox.style.gridTemplateRows = 'auto auto';
                rightBox.style.gap = '10px';

                // Split descriptions by semicolon and trim
                const descriptions = detail.description.split(';').map(d => d.trim());

                // Add values in first row
                values.forEach(val => {
                    const valDiv = document.createElement('div');
                    valDiv.textContent = val;
                    valDiv.style.color = '#007bff'; // Bootstrap primary blue
                    valDiv.style.fontWeight = 'bold';
                    valDiv.style.fontSize = '2rem';
                    rightBox.appendChild(valDiv);
                });

                // Add descriptions in second row
                descriptions.forEach(desc => {
                    const descDiv = document.createElement('div');
                    descDiv.textContent = desc;
                    descDiv.style.color = '#000';
                    descDiv.style.fontSize = '1rem';
                    rightBox.appendChild(descDiv);
                });
            }

            detailFlex.appendChild(titleBox);
            detailFlex.appendChild(rightBox);

            listItem.appendChild(detailFlex);

            listContainer.appendChild(listItem);
        });

        container.appendChild(listContainer);
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderMetricDetails();
        });
    </script>
<?php require_once '../layouts/footer.php'; ?>
