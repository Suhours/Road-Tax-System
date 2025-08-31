<?php
include '../db.php';
$id = $_GET['id'] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plate_number = $_POST['plate_number'];
    $owner = $_POST['owner'];
    $vehicle_type = $_POST['vehicle_type'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tbl_reciept SET plate_number=?, owner=?, vehicle_type=?, amount=?, status=? WHERE id=?");
    $stmt->bind_param("sssdsi", $plate_number, $owner, $vehicle_type, $amount, $status, $id);
    if ($stmt->execute()) {
        header("Location: reciept_report.php?updated=1");
        exit;
    }
}

$stmt = $conn->prepare("SELECT * FROM tbl_reciept WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Receipt</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f9ff;
            padding: 40px;
        }
        form {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 15px;
        }
        input[type="submit"] {
            background: #007bff;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        a {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<form method="POST">
    <h2>Edit Receipt</h2>

    <input type="text" name="plate_number" value="<?= htmlspecialchars($data['plate_number']) ?>" required>
    <input type="text" name="owner" value="<?= htmlspecialchars($data['owner']) ?>" required>
    <input type="text" name="vehicle_type" value="<?= htmlspecialchars($data['vehicle_type']) ?>" required>
    <input type="number" step="0.01" name="amount" value="<?= $data['amount'] ?>" required>

    <select name="status" required>
        <option value="On Time" <?= $data['status'] === 'On Time' ? 'selected' : '' ?>>On Time</option>
        <option value="Overdue" <?= $data['status'] === 'Overdue' ? 'selected' : '' ?>>Overdue</option>
    </select>

    <div class="col-md-6">
      <label class="form-label"><i class="bi bi-receipt me-1"></i>Duration</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
        <input type="hidden" name="duration" value="6 months">
        <input type="text" class="form-control" value="6 months" readonly>
      </div>
    </div>

    <input type="submit" value="Update Receipt">
    <a href="reciept_report.php">← Back to Report</a>
</form>

</body>
</html>
