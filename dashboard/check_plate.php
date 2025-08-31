<?php
// 👉 Set response type to JSON
header('Content-Type: application/json');

// ✅ DB Connection
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");
if ($conn->connect_error) {
    echo json_encode(['exists' => false, 'error' => 'Database connection failed']);
    exit;
}

// ✅ Get the plate number from the GET request
$plate = $_GET['plate'] ?? '';
$response = ['exists' => false];

if (!empty($plate)) {
    $stmt = $conn->prepare("SELECT id FROM vehiclemanagement WHERE platenumber = ? LIMIT 1");
    $stmt->bind_param("s", $plate);
    $stmt->execute();
    $stmt->store_result();
    $response['exists'] = $stmt->num_rows > 0;
    $stmt->close();
}

$conn->close();
echo json_encode($response);
exit;
