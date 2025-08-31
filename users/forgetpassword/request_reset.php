<?php
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    // Check user+email match
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // Mark reset_requested = 1 using prepared statement
        $update = $conn->prepare("UPDATE users SET reset_requested = 1 WHERE username = ?");
        $update->bind_param("s", $username);
        $update->execute();
        $update->close();
    }

    // Always go to waiting page
    header("Location: waiting_approval.php?email=" . urlencode($email));
    exit;
}
?>
