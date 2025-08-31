<?php
include '../db.php';

$plate = $phone = "";
$data = [];
$message = "";

// Search logic
if ($_SERVER["REQUEST_METHOD"] === "GET" && (isset($_GET['plate']) || isset($_GET['phone']))) {
    $plate = $_GET['plate'] ?? '';
    $phone = $_GET['phone'] ?? '';

    if ($phone && !$plate) {
        $stmt = $conn->prepare("SELECT platenumber FROM vehiclemanagement WHERE phone = ? LIMIT 1");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->bind_result($found_plate);
        $stmt->fetch();
        $stmt->close();
        $plate = $found_plate ?? '';
    }

    if ($plate) {
        $data['plate'] = $plate;

        $stmt = $conn->prepare("SELECT owner, carname, phone FROM vehiclemanagement WHERE platenumber = ? LIMIT 1");
        $stmt->bind_param("s", $plate);
        $stmt->execute();
        $stmt->bind_result($data['owner'], $data['type'], $data['phone']);
        $stmt->fetch();
        $stmt->close();

        $stmt = $conn->prepare("SELECT SUM(amount) FROM tblgenerate WHERE platenumber = ?");
        $stmt->bind_param("s", $plate);
        $stmt->execute();
        $stmt->bind_result($generated);
        $stmt->fetch();
        $stmt->close();

        $stmt = $conn->prepare("SELECT SUM(amount) FROM tbl_reciept WHERE plate_number = ?");
        $stmt->bind_param("s", $plate);
        $stmt->execute();
        $stmt->bind_result($paid);
        $stmt->fetch();
        $stmt->close();

        $data['amount_due'] = ($generated ?? 0) - ($paid ?? 0);
    }
}

// Save ONLY to tblgenerate
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['charge'])) {
    $plate = $_POST['plate'];
    $owner = $_POST['owner'];
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $now = date("Y-m-d H:i:s");

    $stmt2 = $conn->prepare("INSERT INTO tblgenerate (vehicletype, platenumber, fullname, amount, due_date, status) VALUES (?, ?, ?, ?, ?, 'completed')");
    $stmt2->bind_param("sssds", $type, $plate, $owner, $amount, $now);

    if ($stmt2->execute()) {
        $message = "✅ Amount successfully saved to tblgenerate!";
        $data = [];
    } else {
        $message = "❌ Error: " . $stmt2->error;
    }
    $stmt2->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Generate One by One</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eaf6ff;
            padding: 40px;
        }
        .container {
            max-width: 720px;
            background: white;
            padding: 35px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 25px;
        }
        input[type="text"], input[type="number"] {
            padding: 12px;
            width: 90%;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        .data-box {
            margin-top: 20px;
            text-align: left;
        }
        .data-box p {
            margin: 5px 0;
            font-size: 16px;
        }
        .message {
            padding: 10px;
            background: #d4edda;
            color: #155724;
            margin-top: 15px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>💳 Charge Vehicle One by One</h2>

    <form method="GET">
        <input type="text" name="plate" placeholder="Search by Plate Number" value="<?= htmlspecialchars($plate) ?>">
        <input type="text" name="phone" placeholder="Or Search by Phone" value="<?= htmlspecialchars($phone) ?>">
        <input type="submit" value="Search">
    </form>

    <?php if (!empty($data['plate'])): ?>
        <div class="data-box">
            <p><strong>Plate Number:</strong> <?= htmlspecialchars($data['plate']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($data['phone']) ?></p>
            <p><strong>Owner:</strong> <?= htmlspecialchars($data['owner']) ?></p>
            <p><strong>Vehicle Type:</strong> <?= htmlspecialchars($data['type']) ?></p>
            <p><strong>Amount Due:</strong> $<?= number_format($data['amount_due'], 2) ?></p>
        </div>

        <form method="POST">
            <input type="hidden" name="plate" value="<?= htmlspecialchars($data['plate']) ?>">
            <input type="hidden" name="owner" value="<?= htmlspecialchars($data['owner']) ?>">
            <input type="hidden" name="type" value="<?= htmlspecialchars($data['type']) ?>">
            <input type="number" name="amount" step="0.01" placeholder="Enter amount to charge" required>
            <input type="submit" name="charge" value="Charge Now">
        </form>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>

</body>
</html>
