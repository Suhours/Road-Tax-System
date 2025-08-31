<?php
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");

$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($email == '') {
    echo "Email not provided.";
    exit;
}

$result = $conn->query("SELECT reset_requested FROM users WHERE email = '$email'");
$row = $result->fetch_assoc();

if ($row) {
    if ($row['reset_requested'] == 2) {
        // ✅ Approved: go to password reset
        header("Location: reset_password.php?email=" . urlencode($email));
        exit;
    } elseif ($row['reset_requested'] == 0) {
        // ❌ Rejected: go back to login with rejected message
        header("Location: login.php?rejected=1");
        exit;
    } else {
        // ⏳ Still pending: stay on waiting
        header("Location: waiting_approval.php?email=" . urlencode($email));
        exit;
    }
} else {
    echo "Invalid request.";
}
?>
