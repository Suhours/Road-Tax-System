<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


include '../db.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $token = bin2hex(random_bytes(16));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // Send email with token link
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // ama mail server-kaaga
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your_email@gmail.com'; // BADDEL
            $mail->Password   = 'your_email_password';  // BADDEL
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_email@gmail.com', 'Road Tax System');
            $mail->addAddress($email);

            $link = "http://localhost/roadtaxsystem/reset_password.php?token=$token";
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body    = "Click the link below to reset your password:<br><br>
                              <a href='$link'>$link</a><br><br>
                              This link will expire in 1 hour.";

            $mail->send();
            $message = "✅ Reset link sent successfully to your email.";
        } catch (Exception $e) {
            $message = "❌ Email sending failed. Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "❌ Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Send Reset Link</title>
  <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f0f2f5;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .box {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }
    input, button {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    button {
        background-color: #007bff;
        color: white;
        font-weight: bold;
        cursor: pointer;
        border: none;
    }
    button:hover {
        background-color: #0056b3;
    }
    .message {
        margin-top: 15px;
        color: green;
    }
  </style>
</head>
<body>
<div class="box">
    <h2>📨 Send Reset Link</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Link</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>
</body>
</html>
