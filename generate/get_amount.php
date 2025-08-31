<?php
include '../db.php';

$type = $_GET['type'] ?? '';
$response = ['amount' => '', 'amount_type' => ''];

if ($type) {
    $stmt = $conn->prepare("SELECT amount, amount_type FROM vehicle_types WHERE name = ? AND amount_type = '6' LIMIT 1");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $response = $row;
    }
}

echo json_encode($response);
?>
