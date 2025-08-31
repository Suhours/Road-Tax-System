<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "PHP is working!<br>";

include 'db.php'; // Fiiro gaar ah: halkan 'db.php' waa file-ka jira isla directory-ga

echo "db.php included!<br>";

if (!$conn || $conn->connect_error) {
    die("❌ DB Error: " . $conn->connect_error);
}

echo "✅ DB connected!";
?>
