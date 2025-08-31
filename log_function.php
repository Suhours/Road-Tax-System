<?php
function log_action($conn, $user_id, $action, $page, $details = '') {
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, page, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $page, $details);
    $stmt->execute();
    $stmt->close();
}
?>
