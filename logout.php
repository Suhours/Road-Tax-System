<?php
session_start();
// ❌ Ma isticmaaleyno require_once haddii function-ka aan hoos ku qoreyno

include 'db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Ku qor function-ka halkan haddii aadan import garaynin
function log_action($conn, $user_id, $action, $page, $details = '') {
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, page, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $page, $details);
    $stmt->execute();
    $stmt->close();
}

$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    log_action($conn, $user_id, 'Logout', 'logout.php', 'User logged out');
}

session_destroy();
header("Location: login.php");
exit;
