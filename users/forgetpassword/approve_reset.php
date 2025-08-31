<?php
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");
$id = $_GET['id'];

$conn->query("UPDATE users SET reset_requested = 2 WHERE id = $id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Approved</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #e1f5fe);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message-box {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .message-box a:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h2>✅ Request Approved!</h2>
        <p>The password reset request has been approved successfully.</p>
        <a href="reset_requests.php">🔙 Back to Requests</a>
    </div>
</body>
</html>
