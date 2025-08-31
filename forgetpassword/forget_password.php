<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';


include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_input = $_POST['email'];

    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email_input);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $user_id = $row['id'];
        $username = $row['username'];

        $token = bin2hex(random_bytes(32));

        // Update without expiry
        $conn->query("UPDATE users SET reset_token='$token', reset_token_expiry=NULL WHERE id=$user_id");

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'abdiwarsame22333@gmail.com'; // your email
            $mail->Password = 'kwff eaby ktwp ilgk'; // your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('your.email@gmail.com', 'Road Tax System');
            $mail->addAddress($email_input);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/roadtaxsystem/forgetpassword/reset_password.php?token=$token";
            $mail->Body = "Hello <b>$username</b>,<br><br>You requested a password reset.<br>
            Click below to reset your password:<br>
            <a href='$resetLink'>Reset Password</a><br><br>
            Regards,<br>Road Tax System";

            $mail->send();
            $message = "✅ Reset link sent to <b>$email_input</b> for user <b>$username</b>.";
        } catch (Exception $e) {
            $message = "❌ Failed to send email. Error: " . $mail->ErrorInfo;
        }
    } else {
        $message = "❌ Email address not found in system.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f4f4f4;
            margin: 0;
        }
        .forgot-box {
            background: white;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 0 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .forgot-box h2 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .forgot-box p {
            color: #555;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .forgot-box input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #007bff;
            border-radius: 25px;
        }
        .forgot-box button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            margin-top: 20px;
            cursor: pointer;
        }
        .forgot-box button:hover {
            background: #0056b3;
        }
        .message {
            margin-top: 20px;
            color: #007bff;
            font-size: 16px;
        }
        .back {
            margin-top: 15px;
            display: block;
            text-decoration: none;
            color: #007bff;
        }
        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<form method="POST" class="forgot-box">
    <h2>Forgot Password</h2>
    <p>Enter your <b>email address</b>. A reset link will be sent if found.</p>
    <input type="email" name="email" placeholder="Enter your Gmail" required>
    <button type="submit">Send Reset Link</button>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <a class="back" href="../login.php">← Back to Login</a>
</form>

</body>
</html>
