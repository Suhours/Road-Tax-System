<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login");
    exit;
}

include '../db.php';

$success = $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    if (strlen($password) < 8) {
        $error = "❌ Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $conn = new mysqli("localhost", "root", "", "roadtaxsystem");
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed, $role);

        if ($stmt->execute()) {
            $success = "✅ User registered successfully!";
        } else {
            $error = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to right, #eaf6ff, #f4f9ff);
        padding: 50px;
        margin: 0;
    }
    form {
        background: white;
        max-width: 450px;
        margin: auto;
        padding: 35px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #007bff;
        margin-bottom: 25px;
    }
    input, select {
        width: 100%;
        padding: 12px;
        margin: 12px 0 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    input:focus, select:focus {
        border-color: #007bff;
        box-shadow: 0 0 6px rgba(0,123,255,0.25);
        outline: none;
    }
    input[type="submit"] {
        background: #007bff;
        color: white;
        cursor: pointer;
        border: none;
        font-weight: bold;
        transition: background 0.3s;
    }
    input[type="submit"]:hover {
        background: #0056b3;
    }
    .msg {
        text-align: center;
        font-weight: bold;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .success {
        background: #e6ffee;
        color: #1a7f2b;
        border: 1px solid #b2e2c4;
    }
    .error {
        background: #ffe6e6;
        color: #b30000;
        border: 1px solid #f5b2b2;
    }
    </style>
</head>
<body>

<form method="POST">
    <h2>Add New User</h2>

    <?php if ($success) echo "<p class='msg success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='msg error'>$error</p>"; ?>

    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password (min 8 characters)" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    
    <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="Admin">Admin</option>
        <option value="User">User</option>
    </select>

    <input type="submit" value="Register">
</form>

</body>
</html>
