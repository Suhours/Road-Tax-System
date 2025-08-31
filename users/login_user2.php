<?php
session_start();
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password']) && $user['username'] === 'user2') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard_user2.php");
        exit;
    } else {
        $error = "Login failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User2 Login</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('img/login-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .login-box img {
            width: 80px;
            margin-bottom: 20px;
        }
        .login-box h2 {
            color: #007bff;
            margin-bottom: 30px;
        }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 2px solid #007bff;
            border-radius: 25px;
            outline: none;
        }
        .login-box button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
        }
        .login-box button:hover {
            background: #0056b3;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <form method="POST" class="login-box">
        <img src="img/logo.png" alt="Logo">
        <h2>User 2 Login</h2>
        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
