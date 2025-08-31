<?php
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");
$id = $_GET['id'];

// Codsiga laga noqday
$conn->query("UPDATE users SET reset_requested = 0 WHERE id = $id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Rejected</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #ffe6e6, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message-box {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .message-box h2 {
            margin-bottom: 10px;
        }
        .message-box a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .message-box a:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h2>❌ Request Rejected</h2>
        <p>The password reset request has been rejected successfully.</p>
        <a href="reset_requests.php">🔙 Back to Requests</a>
    </div>
</body>
</html>
