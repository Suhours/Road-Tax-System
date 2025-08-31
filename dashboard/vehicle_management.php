<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit;
}

$success = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "Vehicle record saved successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vehicle Management</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f9ff;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            text-align: center;
        }
        h2 {
            font-size: 28px;
            color: #007bff;
            margin-bottom: 10px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 6px;
            margin: 20px auto;
            width: fit-content;
            animation: fadein 0.6s ease;
        }
        .add-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin-top: 20px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
            transition: 0.3s ease;
        }
        .add-btn:hover {
            background-color: #0056b3;
        }
        p {
            font-size: 16px;
            color: #444;
            margin-top: 25px;
        }
        @keyframes fadein {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🚙 Vehicle Management</h2>

    <?php if (!empty($success)) echo "<div class='message success'>$success</div>"; ?>

    <a href="form" class="add-btn">➕ Add New Vehicle</a>

    <p>Manage and register new vehicles. Use the button above to start adding vehicle records.</p>
</div>

</body>
</html>
