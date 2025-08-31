<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $code = rand(100000, 999999); // 6-digit code
        $stmt2 = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt2->bind_param("ss", $code, $email);
        $stmt2->execute();
k
        // Send email
        $subject = "Reset Code";
        $message = "Your reset code is: $code";
        $headers = "From: no-reply@roadtaxsystem.com";

        if (mail($email, $subject, $message, $headers)) {
            $_SESSION['reset_email'] = $email;
            header("Location: verify_code.php");
            exit;
        } else {
            $error = "❌ Failed to send email. Make sure mail() is configured.";
        }
    } else {
        $error = "❌ Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Reset Code</title>
    <style>
        body { font-family: Arial; background: #eef4ff; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); width: 350px; text-align: center; }
        input { padding: 10px; width: 100%; margin: 15px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { padding: 10px; background: #007bff; color: white; border: none; width: 100%; border-radius: 5px; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="box">
    <h2>🔐 Send Reset Code</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <button type="submit">Send Code</button>
    </form>
</div>
</body>
</html>
