<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['code'];
    $email = $_SESSION['reset_email'];

    $conn = new mysqli("localhost", "root", "", "roadtaxsystem");
    $stmt = $conn->prepare("SELECT reset_token FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if ($row && $entered_code == $row['reset_token']) {
        header("Location: reset_password");
        exit;
    } else {
        $error = "❌ Invalid code.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head><title>Verify OTP</title><style>/* same CSS */</style></head>
<body>
<div class="box">
    <h2>✅ Enter OTP Code</h2>
    <?php if ($error) echo "<div class='message'>$error</div>"; ?>
    <form method="POST">
        <input type="text" name="code" placeholder="Enter OTP" required>
        <input type="submit" value="Verify Code">
    </form>
</div>
</body>
</html>
