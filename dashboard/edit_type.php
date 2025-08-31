<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login");
    exit;
}


include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = $error = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $amount = trim($_POST['amount']);
    $amount_type = trim($_POST['amount_type']);

    if (!empty($name) && is_numeric($amount) && !empty($amount_type)) {
        $stmt = $conn->prepare("UPDATE vehicle_types SET name=?, amount=?, amount_type=? WHERE id=?");
        $stmt->bind_param("sdsi", $name, $amount, $amount_type, $id);
        if ($stmt->execute()) {
            $success = "✅ Updated successfully!";
        } else {
            $error = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "❌ Please fill all fields correctly.";
    }
}

$data = $conn->query("SELECT * FROM vehicle_types WHERE id=$id")->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Vehicle Type</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f9ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 380px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border: none;
        }
        .message {
            text-align: center;
            font-weight: bold;
        }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="box">
    <h2>Edit Vehicle Type</h2>
    <?php if ($success) echo "<div class='message success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='message error'>$error</div>"; ?>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" required>
        <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($data['amount']) ?>" required>
        <input type="text" name="amount_type" value="<?= htmlspecialchars($data['amount_type']) ?>" required>
        <button type="submit">Update</button>
    </form>
</div>
</body>
</html>
