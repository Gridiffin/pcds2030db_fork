<?php
/**
 * Initial setup script
 * 
 * Creates default sectors and user accounts for testing.
 * WARNING: Run this script only once to initialize the database.
 */

// Include necessary files
require_once 'config/config.php';
require_once 'includes/db_connect.php';

// Disable error reporting for production
// error_reporting(0);

echo '<h1>PCDS2030 Dashboard - Initial Setup</h1>';

// Check if setup has already been run
$check_query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($check_query);
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    die('<p>Setup has already been run. For security reasons, this script has been disabled.</p>
         <p>To run setup again, please clear the database first.</p>
         <p><a href="login.php">Go to Login</a></p>');
}

// Create sectors
echo '<h2>Creating sectors...</h2>';

$sectors = [
    ['Forestry', 'Forestry sector including timber and forest resources'],
    ['Land', 'Land development and management'],
    ['Environment', 'Environmental protection and management'],
    ['Natural Resources', 'Management of natural resources'],
    ['Urban Development', 'Urban planning and development']
];

foreach ($sectors as $sector) {
    $stmt = $conn->prepare("INSERT INTO sectors (sector_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $sector[0], $sector[1]);
    $stmt->execute();
    echo "<p>Created sector: {$sector[0]}</p>";
}

// Create users
echo '<h2>Creating users...</h2>';

// Admin user
$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT); // Change in production!
$admin_agency = 'Ministry of Natural Resources and Urban Development';
$admin_role = 'admin';

$stmt = $conn->prepare("INSERT INTO users (username, password, agency_name, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $admin_username, $admin_password, $admin_agency, $admin_role);
$stmt->execute();
echo "<p>Created admin user: {$admin_username} (Password: admin123)</p>";

// Agency users (one for each sector)
$agencies = [
    ['forestry_dept', 'Forestry Department', 1], // Forestry sector
    ['land_survey', 'Land and Survey Department', 2], // Land sector
    ['nreb', 'Natural Resources and Environment Board', 3], // Environment sector
    ['sfc', 'Sarawak Forestry Corporation', 1], // Forestry sector
    ['lcda', 'Land Custody and Development Authority', 2] // Land sector
];

foreach ($agencies as $agency) {
    $username = $agency[0];
    $agency_name = $agency[1];
    $sector_id = $agency[2];
    $password = password_hash('agency123', PASSWORD_DEFAULT); // Change in production!
    $role = 'agency';
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, agency_name, role, sector_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $password, $agency_name, $role, $sector_id);
    $stmt->execute();
    echo "<p>Created agency user: {$username} (Password: agency123)</p>";
}

// Success message
echo '<div style="margin-top: 20px; padding: 10px; background-color: #d4edda; color: #155724; border-radius: 5px;">
    <h3>Setup completed successfully!</h3>
    <p>You can now log in with the following credentials:</p>
    <ul>
        <li><strong>Admin:</strong> Username: admin, Password: admin123</li>
        <li><strong>Agency:</strong> Username: forestry_dept, Password: agency123</li>
        <li><strong>Agency:</strong> Username: land_survey, Password: agency123</li>
        <li><strong>Agency:</strong> Username: nreb, Password: agency123</li>
        <li><strong>Agency:</strong> Username: sfc, Password: agency123</li>
        <li><strong>Agency:</strong> Username: lcda, Password: agency123</li>
    </ul>
    <p><a href="login.php" style="color: #155724; text-decoration: underline;">Go to Login Page</a></p>
    <p><strong>Important:</strong> For security reasons, delete this file after setup or restrict access to it.</p>
</div>';
?>
