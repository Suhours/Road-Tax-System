<?php
$email = isset($_GET['email']) ? $_GET['email'] : '';
if (empty($email)) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Waiting for Approval</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
        }

        h2 {
            margin-bottom: 20px;
            color: #007bff;
        }

        .spinner {
            border: 6px solid #eee;
            border-top: 6px solid #007bff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        p {
            font-size: 16px;
            color: #555;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = "check_status.php?email=<?= urlencode($email); ?>";
        }, 5000);
    </script>
</head>
<body>
    <div class="container">
        <h2>🔒 Waiting for Admin Approval</h2>
        <div class="spinner"></div>
        <p>Your password reset request is being reviewed.</p>
    </div>
</body>
</html>
