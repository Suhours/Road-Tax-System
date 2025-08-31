<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit;
}


include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing vehicle info
$sql = "SELECT * FROM vehiclemanagement WHERE id = $id";
$result = $conn->query($sql);
$vehicle = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platenumber = $conn->real_escape_string($_POST['platenumber']);
    $carname = $conn->real_escape_string($_POST['carname']);
    $owner = $conn->real_escape_string($_POST['owner']);
    $registration_date = $conn->real_escape_string($_POST['registration_date']);

    $updateQuery = "UPDATE vehiclemanagement SET platenumber='$platenumber', carname='$carname', owner='$owner', registration_date='$registration_date' WHERE id=$id";
    
    if ($conn->query($updateQuery)) {
        header("Location: reports");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vehicle</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background-color: #f4f9ff; }
        form { background: white; padding: 20px; border-radius: 10px; width: 400px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="text"], input[type="date"] {
            width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center; color:#007bff;">Edit Vehicle Information</h2>
    <form method="POST">
        <label>Plate Number</label>
        <input type="text" name="platenumber" value="<?= htmlspecialchars($vehicle['platenumber']) ?>" required>

        <label>Car Name</label>
        <input type="text" name="carname" value="<?= htmlspecialchars($vehicle['carname']) ?>" required>

        <label>Owner</label>
        <input type="text" name="owner" value="<?= htmlspecialchars($vehicle['owner']) ?>" required>

        <label>Registration Date</label>
        <input type="date" name="registration_date" value="<?= htmlspecialchars($vehicle['registration_date']) ?>" required>

        <input type="submit" value="Update Vehicle">
    </form>
</body>
</html>

<?php $conn->close(); ?>
