<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload'; // Isku xidh PHPMailer

session_start();

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];


include '../db.php';;
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $otp = rand(100000, 999999);
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $otp, $email);
        $stmt->execute();

        // Send OTP using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'YOUR_GMAIL@gmail.com'; 
            $mail->Password = 'YOUR_APP_PASSWORD'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('YOUR_GMAIL@gmail.com', 'RoadTax System');
            $mail->addAddress($email);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP Code is: $otp";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            header("Location: verify_code.php");
            exit;
        } catch (Exception $e) {
            $error = "❌ Failed to send email.";
        }
    } else {
        $error = "❌ This email is not registered.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- HTML DESIGN -->
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>/* same CSS as before */</style>
</head>
<body>
<div class="box">
    <h2>🔐 Forgot Password</h2>
    <?php if ($error) echo "<div class='message'>$error</div>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <input type="submit" value="Send Code">
    </form>
</div>
</body>
</html>
