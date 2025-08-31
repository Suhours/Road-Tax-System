<?php
session_start();

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT u.id FROM users u JOIN password_resets r ON u.id = r.user_id WHERE u.username = '$username' AND r.status = 'approved'");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE users SET password = '$new_pass' WHERE username = '$username'");
        $conn->query("DELETE FROM password_resets WHERE user_id = (SELECT id FROM users WHERE username = '$username')");
        echo "✅ Password updated!";
    } else {
        echo "❌ Not approved or no request found.";
    }
}
?>
<form method="post">
    <input name="username" placeholder="Your Username" required>
    <input name="password" placeholder="New Password" required type="password">
    <button type="submit">Reset Password</button>
</form>
