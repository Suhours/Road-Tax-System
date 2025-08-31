<?php
header("Content-type: application/json");
include '../config/conn.php';




function payment_registration($conn){
    extract($_POST);
    $data = array();

    // Wacida stored procedure-ka make_payment
    $payment_date = date('Y-m-d'); // ama POST ahaan u keen
    $amount = $_POST['amount'];  // hubi inuu amount imanayo
    $query = "CALL make_vehicle_payment('$number_plate','$amount','$description')";

    $result = $conn->query($query);
    if($result){
        $data = array("status" => true, "data" => "Successfully payment Recorded"); 
    }else{
        $data = array("status" => false, "data" => $conn->error);
    }

    echo json_encode($data);
}



function get_vehicle_info($conn) {
    if (!isset($_POST['number_plate']) || empty(trim($_POST['number_plate']))) {
        echo json_encode([
            "status" => false,
            "data" => "Fadlan geli number plate-ka"
        ]);
        return;
    }
    $number_plate = trim($_POST['number_plate']);
    $stmt = $conn->prepare(
        "SELECT 
                GREATEST(
                    ((YEAR(CURDATE()) - YEAR(vb.last_payment_date)) * 4 +
                    (QUARTER(CURDATE()) - QUARTER(vb.last_payment_date))) * vt.amount_per_three_month
                    -
                    IFNULL((
                        SELECT SUM(pr.amount_paid)
                        FROM payment_record pr
                        WHERE pr.number_plate = v.number_plate
                    ), 0),
                0) AS unpaid_balance
            FROM vehicles v
            JOIN vehicle_balance vb ON v.number_plate = vb.number_plate
            JOIN vehicle_type vt ON vt.id = v.vehicle_type_id
            WHERE v.number_plate = ?
            LIMIT 1;"
            );

    if (!$stmt) {
        echo json_encode([
            "status" => false,
            "data" => "Error in preparing statement"
        ]);
        return;
    }
    $stmt->bind_param("s", $number_plate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "status" => true,
            "data" => $row
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "data" => "Ma jiro gaadhi ku jira number plate-kan"
        ]);
    }

    $stmt->close();
}


function load_payment_records($conn) {
    $data = array();
    $array_data = array();
    $keyword = isset($_POST['keyword']) ? $conn->real_escape_string($_POST['keyword']) : '';

    // Base query with JOIN
    $query = "SELECT pr.*, v.number_plate 
              FROM payment_record pr 
              JOIN vehicles v ON pr.number_plate = v.number_plate";

    // Add WHERE clause if keyword exists
    if (!empty($keyword)) {
        // Use LIKE for partial matching or = for exact matching
        $query .= " WHERE pr.number_plate LIKE '%$keyword%'";
    }

    // Optional: Sort by most recent payments
    $query .= " ORDER BY pr.payment_date DESC";

    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $array_data[] = $row;
        }
        $data = array("status" => true, "data" => $array_data);
    } else {
        $data = array("status" => false, "data" => "Query Error: " . $conn->error);
    }

    echo json_encode($data);
}








// function fetch_payment_info($conn) {
//     extract($_POST);
//     $data = array();
//     $query = "SELECT * FROM `tax_payments` where `id` = '$id'";
//     $result = $conn->query($query);
    
//     if($result) {
//         $row = $result->fetch_assoc();
//         $data = array("status" => true, "data" => $row);
//     } else {
//         $data = array("status" => false, "data" => $conn->error);
//     }
    
//     echo json_encode($data);
// }

// function update_payment($conn) {
//     extract($_POST);
//     $data = array();
//     $query = "UPDATE `tax_payments` SET `number_plate`='$number_plate',`amount`='$amount',`status`='$status',`reciept_number`='$reciept_number' WHERE `id` = '$id'";
//     $result = $conn->query($query);
    
//     if($result) {
//         $data = array("status" => true, "data" => "Successfully updated");
//     } else {
//         $data = array("status" => false, "data" => $conn->error);
//     }
    
//     echo json_encode($data);
// }

// function load_payment_info($conn) {
//     $data = array();
//     $array_data = array();
//     $keyword = isset($_POST['keyword']) ? $conn->real_escape_string($_POST['keyword']) : '';
    
//     $query = "SELECT p.*, v.owner_name, vt.type as vehicle_type, v.id as vehicle_id
//               FROM tax_payments p
//               JOIN vehicles v ON p.vehicle_id = v.id
//               JOIN vehicle_types vt ON v.vehicle_type_id = vt.id";
    
//     if (!empty($keyword)) {
//         $query .= " WHERE p.number_plate LIKE '%$keyword%' OR v.owner_name LIKE '%$keyword%'";
//     }
    
//     $query .= " ORDER BY p.payment_date DESC";
    
//     $result = $conn->query($query);
//     if($result) {
//         while($row = $result->fetch_assoc()) {
//             $array_data[] = $row;
//         }
//         $data = array("status" => true, "data" => $array_data);
//     } else {
//         $data = array("status" => false, "data" => $conn->error);
//     }
    
//     echo json_encode($data);
// }



function delete_payment($conn) {
    extract($_POST);
    $data = array();
    $query = "DELETE FROM `tax_payments` where `id` = '$id'";
    $result = $conn->query($query);
    
    if($result) {
        $data = array("status" => true, "data" => "Successfully deleted");
    } else {
        $data = array("status" => false, "data" => $conn->error);
    }
    
    echo json_encode($data);
}

if(isset($_POST['action'])) {
    $action = $_POST['action'];
    $action($conn);
} else {
    echo json_encode(array("status" => false, "data" => "Action required"));
}
?>