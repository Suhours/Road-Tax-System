<?php
include '../db.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
$valid = false;

// Step 1: Token from URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $_SESSION['reset_user_id'] = $row['id'];
        $_SESSION['reset_token_checked'] = true;
        $valid = true;
    } else {
        $message = "⛔ Link expired or invalid. Please request a new one.";
    }
}

// Step 2: Check session
if (isset($_SESSION['reset_user_id']) && isset($_SESSION['reset_token_checked'])) {
    $valid = true;
}

// Step 3: Reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if ($new_pass !== $confirm) {
        $message = "❌ Passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $message = "❌ Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $update->bind_param("si", $hashed, $_SESSION['reset_user_id']);

        if ($update->execute()) {
            unset($_SESSION['reset_user_id'], $_SESSION['reset_token_checked']);
            $message = "✅ Password changed successfully. <a href='../login.php'>Login now</a>";
            $valid = false;
        } else {
            $message = "❌ Failed to update password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .password-wrapper {
            position: relative;
        }
        .toggle-eye {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        button {
            background: #007bff;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            margin-top: 15px;
            color: #007bff;
            font-weight: bold;
        }
        a {
            color: green;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>🔐 Reset Password</h2>
    <?php if ($valid): ?>
        <form method="POST">
            <div class="password-wrapper">
                <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                <span class="toggle-eye" onclick="toggle('new_password')">👁️</span>
            </div>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <span class="toggle-eye" onclick="toggle('confirm_password')">👁️</span>
            </div>
            <button type="submit">Save Password</button>
        </form>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>

<script>
function toggle(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
