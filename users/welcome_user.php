<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    echo "<div style='padding:20px;color:red;font-family:sans-serif;'>Access denied.</div>";
    exit;
}
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            box-sizing: border-box;
        }
        .main {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 50px 80px;
        }
        .left {
            flex: 1;
        }
        .left h1 {
            font-size: 36px;
            color: #007bff;
            margin-bottom: 15px;
        }
        .left p {
            font-size: 15px;
            color: #333;
            max-width: 500px;
            line-height: 1.6;
        }
        .right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .right img {
            width: 450px;
            max-width: 100%;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        }
        .footer {
            height: 60px;
            background: #f1f5f9;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 40px;
            border-top: 1px solid #ddd;
        }
        .footer a {
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
            font-size: 14px;
            transition: 0.3s;
        }
        .footer a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .main {
                flex-direction: column-reverse;
                text-align: center;
                padding: 30px;
            }
            .right img {
                width: 70%;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="main">
        <div class="left">
            <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
            <p>
                You're now inside your personal dashboard. Use the sidebar on the left to explore your tools and stay productive. Everything you need is just a click away.
            </p>
        </div>
        <div class="right">
            <img src="img/user.png" alt="Welcome Image">
        </div>
    </div>
    <div class="footer">
        <a href="../logout.php">🚪 Logout</a>
    </div>
</div>
</body>
</html>
