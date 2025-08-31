<?php
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");

$email = $_GET['email'] ?? '';
$username = '';
$old_password_hash = '';

if ($email) {
    $res = $conn->query("SELECT username, password FROM users WHERE email = '$email'");
    if ($res && $res->num_rows > 0) {
        $data = $res->fetch_assoc();
        $username = $data['username'];
        $old_password_hash = $data['password'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #dfe9f3, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .info {
            font-size: 14px;
            background: #f0f0f0;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #444;
        }
        input[type="password"] {
            width: 90%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>🔐 Reset Your Password</h2>
        <?php if ($username): ?>
            <div class="info">
                <strong>Username:</strong> <?php echo htmlspecialchars($username); ?><br>
                <strong>Old Password Hash:</strong><br> <?php echo $old_password_hash; ?>
            </div>
            <form method="POST" action="update_password.php">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="password" name="new_password" placeholder="New Password" required><br>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
                <button type="submit">Update Password</button>
            </form>
        <?php else: ?>
            <p style="color:red;">Invalid or missing email address.</p>
        <?php endif; ?>
    </div>
</body>
</html>
