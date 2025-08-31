<?php
include '../db.php';

if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $token = $_POST['token'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("ss", $username, $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {
        $_SESSION['reset_user_id'] = $user['id'];
        header("Location: reset_password.php");
        exit;
    } else {
        $message = "❌ Invalid username or expired token.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Token</title>
    <style>
        body {font-family: sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh;}
        .box {background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-width: 400px; width: 100%; text-align: center;}
        input, button {width: 100%; padding: 12px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc;}
        button {background: #007bff; color: white; font-weight: bold;}
        .message {margin-top: 10px; color: #007bff;}
    </style>
</head>
<body>
<div class="box">
    <h2>🔐 Verify Token</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="text" name="token" placeholder="Enter Token" required>
        <button type="submit">Verify</button>
    </form>
    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
</div>
</body>
</html>
