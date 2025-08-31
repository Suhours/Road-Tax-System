<?php
include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? '';
if (!$id) {
    die("Invalid ID");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $vehicletype = $_POST['vehicletype'];
    $platenumber = $_POST['platenumber'];
    $amount = $_POST['amount'];
    $due_date = $_POST['due_date'];

    $stmt = $conn->prepare("UPDATE tblgenerate SET fullname=?, vehicletype=?, platenumber=?, amount=?, due_date=? WHERE id=?");
    $stmt->bind_param("sssssi", $fullname, $vehicletype, $platenumber, $amount, $due_date, $id);
    $stmt->execute();

    header("Location: generate_report.php");
    exit;
}

$result = $conn->query("SELECT * FROM tblgenerate WHERE id = $id");
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vehicle Payment</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 30px; }
        .form-box {
            max-width: 600px; margin: auto; background: white; padding: 25px;
            border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        input, select {
            width: 100%; padding: 10px; margin-bottom: 15px;
            border: 1px solid #ccc; border-radius: 6px;
        }
        button {
            background: #007bff; color: white; padding: 10px 20px;
            border: none; border-radius: 6px; cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Edit Payment Record</h2>
        <form method="POST">
            <input type="text" name="fullname" value="<?= $data['fullname'] ?>" required>
            <input type="text" name="vehicletype" value="<?= $data['vehicletype'] ?>" required>
            <input type="text" name="platenumber" value="<?= $data['platenumber'] ?>" required>
            <input type="number" step="0.01" name="amount" value="<?= $data['amount'] ?>" required>
            <input type="date" name="due_date" value="<?= $data['due_date'] ?>" required>
            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
