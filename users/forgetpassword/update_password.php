<?php
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");

$email = $_POST['email'];
$new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

// Hubi in email-ka la helay iyo in reset uu yahay approved (2)
$check = $conn->query("SELECT reset_requested FROM users WHERE email = '$email'");
$row = $check->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Update Result</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f9ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="box <?php echo ($row && $row['reset_requested'] == 2) ? 'success' : 'error'; ?>">
        <?php
        if ($row && $row['reset_requested'] == 2) {
            // Update password and reset the reset request status
            $conn->query("UPDATE users SET password = '$new_password', reset_requested = 0 WHERE email = '$email'");
            echo "<h2>✅ Password Updated</h2><p>Your password has been changed successfully.</p>";
        } else {
            echo "<h2>❌ Update Failed</h2><p>Reset not approved or email is incorrect.</p>";
        }
        ?>
        <a href="reset_password.php">🔙 Go Back</a>
    </div>
</body>
</html>
