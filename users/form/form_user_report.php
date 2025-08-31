<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "roadtaxsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT platenumber, carname, owner, mother_name, phone, registration_date FROM vehiclemanagement WHERE user_id = $user_id");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Vehicle Report</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f9ff;
            padding: 40px;
        }
        h2 {
            text-align: center;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 14px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #eef2f7;
        }
    </style>
</head>
<body>
    <h2>🚗 My Registered Vehicles</h2>
    <table>
        <tr>
            <th>Plate Number</th>
            <th>Vehicle Type</th>
            <th>Owner</th>
            <th>Mother Name</th>
            <th>Phone</th>
            <th>Registration Date</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['platenumber']) . "</td>
                        <td>" . htmlspecialchars($row['carname']) . "</td>
                        <td>" . htmlspecialchars($row['owner']) . "</td>
                        <td>" . htmlspecialchars($row['mother_name']) . "</td>
                        <td>" . htmlspecialchars($row['phone']) . "</td>
                        <td>" . htmlspecialchars($row['registration_date']) . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>No vehicles found.</td></tr>";
        }
        ?>
    </table>
</body>
</html>
