<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$success = $error = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "✅ Vehicle record saved successfully!";
}

$conn = new mysqli("localhost", "root", "", "roadtaxsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $platenumber = $_POST['platenumber'];
    $carname = $_POST['carname'];
    $owner = $_POST['owner'];
    $mother_name = $_POST['mother_name'];
    $phone = $_POST['phone'];
    $registration = $_POST['registration'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO vehiclemanagement (platenumber, carname, owner, mother_name, phone, registration_date, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $platenumber, $carname, $owner, $mother_name, $phone, $registration, $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        // ✅ Halkan saxid ayaa lagu sameeyay:
        header("Location: ../../dashboard/vehicle_management.php?success=1");
        exit;
    } else {
        $error = "❌ Error saving data: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Vehicle</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f9ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 15px;
            font-size: 20px;
        }
        label {
            display: block;
            margin: 8px 0 4px;
            font-weight: bold;
            font-size: 14px;
        }
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 9px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        input[type="submit"] {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Register Vehicle</h2>

    <?php if (!empty($success)) echo "<div class='message success'>$success</div>"; ?>
    <?php if (!empty($error)) echo "<div class='message error'>$error</div>"; ?>

    <form method="POST">
        <label for="platenumber">Plate Number</label>
        <input type="text" id="platenumber" name="platenumber" required>

        <label for="carname">Vehicle Type</label>
        <select id="carname" name="carname" required>
            <option value="">-- Select Type --</option>
            <?php
            $types = $conn->query("SELECT name FROM vehicle_types");
            while ($row = $types->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
            }
            ?>
        </select>

        <label for="owner">Owner Name</label>
        <input type="text" id="owner" name="owner" required>

        <label for="mother_name">Mother Name</label>
        <input type="text" id="mother_name" name="mother_name" required>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" required>

        <label for="registration">Registration Date</label>
        <input type="date" id="registration" name="registration" required>

        <input type="submit" value="Save">
    </form>
</div>
</body>
</html>
