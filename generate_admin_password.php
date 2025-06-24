<?php
// Generate proper password hash for admin account
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\nSQL to update admin password:\n";
echo "UPDATE users SET password = '$hash' WHERE username = 'admin';\n";
?>
