<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit;
}


include '../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT * FROM password_resets WHERE user_id = $user_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO password_resets (user_id, status) VALUES ($user_id, 'pending')");
        $message = "✅ Request sent. Please wait for admin approval.";
    } else {
        $message = "⏳ You have already requested reset.";
    }
}
?>
<form method="post">
    <button type="submit">Request Password Reset</button>
</form>
<?php if (!empty($message)) echo "<p>$message</p>"; ?>
