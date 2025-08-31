<?php
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");

// Hel user-yada codsaday password reset (reset_requested = 1)
$result = $conn->query("SELECT id, username, email FROM users WHERE reset_requested = 1");

// Success message
$success = isset($_GET['success']) && $_GET['success'] == 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Requests</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #e1f5fe);
            margin: 0;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #00796b;
            margin-bottom: 30px;
        }
        .alert {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .card {
            background: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .card strong {
            font-size: 16px;
            color: #333;
        }
        .card small {
            display: block;
            color: #555;
            margin-top: 5px;
        }
        .actions {
            margin-top: 15px;
        }
        .actions a {
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            margin-right: 10px;
            display: inline-block;
        }
        .approve {
            background-color: #28a745;
            color: white;
        }
        .reject {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Requests</h2>

        <?php if ($success): ?>
            <div class="alert">
                ✅ Password reset request has been approved successfully!
            </div>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card">
                <strong><?php echo $row['username']; ?></strong>
                <small><?php echo $row['email']; ?></small>
                <div class="actions">
                    <a class="approve" href="approve_reset.php?id=<?php echo $row['id']; ?>">Approve</a>
                    <a class="reject" href="reject_reset.php?id=<?php echo $row['id']; ?>">Reject</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
