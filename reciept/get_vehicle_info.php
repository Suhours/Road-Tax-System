<?php
header('Content-Type: application/json');
session_start();
include '../db.php';

$response = [
    'success' => false,
    'serial_num' => '',
    'vehicle_type' => '',
    'owner' => ''
];

if (isset($_GET['plate_number']) && !empty($_GET['plate_number'])) {
    $plate_number = trim($_GET['plate_number']);
    
    // Query to get vehicle information
    $stmt = $conn->prepare("SELECT serialnumber, vehicletype, owner FROM vehiclemanagement WHERE platenumber = ? LIMIT 1");
    
    if ($stmt) {
        $stmt->bind_param("s", $plate_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $vehicle = $result->fetch_assoc();
            $response['success'] = true;
            $response['serial_num'] = $vehicle['serialnumber'] ?? '';
            $response['vehicle_type'] = $vehicle['vehicletype'] ?? '';
            $response['owner'] = $vehicle['owner'] ?? '';
        }
        
        $stmt->close();
    } else {
        $response['error'] = 'Database error: ' . $conn->error;
    }
} else {
    $response['error'] = 'Plate number is required';
}

echo json_encode($response);
?>
