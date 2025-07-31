<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing includes step by step...<br><br>";

try {
    echo "1. Loading config...<br>";
    require_once __DIR__ . '/app/config/config.php';
    echo "✅ Config loaded<br>";

    echo "2. Loading db_connect...<br>";
    require_once __DIR__ . '/app/lib/db_connect.php';
    echo "✅ DB connect loaded<br>";

    echo "3. Loading functions...<br>";
    require_once __DIR__ . '/app/lib/functions.php';
    echo "✅ Functions loaded<br>";

    echo "4. Loading session...<br>";
    require_once __DIR__ . '/app/lib/session.php';
    echo "✅ Session loaded<br>";

    echo "5. Loading admin_functions...<br>";
    require_once __DIR__ . '/app/lib/admin_functions.php';
    echo "✅ Admin functions loaded<br>";

    echo "<br>All includes successful! The issue is elsewhere.<br>";

} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (ParseError $e) {
    echo "❌ PARSE ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<br>Testing session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
?>