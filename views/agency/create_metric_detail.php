<?php
// Start session and include necessary files
session_start();
require_once '../../includes/db_connect.php'; // Adjust path as needed

// Initialize variables for form values and errors
$value = '';
$title = '';
$description = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $value = trim($_POST['value'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($value === '') {
        $errors[] = 'Value is required.';
    }
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }

    // If no errors, process the form (e.g., save to database)
    if (empty($errors)) {
        // Example: Insert into a metrics_details table (adjust table and columns as needed)
        $stmt = $conn->prepare("INSERT INTO metric_details (value, title, description, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sssi', $value, $title, $description, $_SESSION['user_id']);
            if ($stmt->execute()) {
                // Redirect or show success message
                header('Location: submit_metrics.php?success=1');
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
</head>
<body>
    <div class="container mt-5">
        <h1>Create Metric Detail</h1>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="value" class="form-label">Value</label>
                <input type="text" class="form-control" id="value" name="value" value="<?php echo htmlspecialchars($value); ?>" required>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="<?php echo APP_URL; ?>/views/agency/submit_metrics.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</body>
</html>
