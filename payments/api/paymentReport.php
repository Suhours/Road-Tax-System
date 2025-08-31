<?php

header("Content-type: application/json");
include '../config/conn.php';









function load_payments_report($conn) {
    $data = array();
    $array_data = array();

    $keyword = isset($_POST['keyword']) ? mysqli_real_escape_string($conn, $_POST['keyword']) : '';

    $where = "";
    if (!empty($keyword)) {
        $where = "WHERE 
            v.number_plate LIKE '%$keyword%' OR 
            v.owner_name LIKE '%$keyword%' OR 
            v.owner_phone LIKE '%$keyword%' OR
            vt.type LIKE '%$keyword%'";
    }
    
    $query = "SELECT 
                    v.number_plate AS NumberPlate,
                    v.owner_name AS OwnerName,
                    v.owner_phone AS OwnerPhone,
                    vt.type AS VehicleType,
                    vt.amount_per_three_month AS TaxPerQuarter,
                    
                    -- Quarter-kii ugu dambeeyay ee uu bixiyay
                    vb.last_payment_date AS LastPaymentDate,
                    
                    -- Tirada quarterada laga bilaabo last_payment_date ilaa maanta
                    (
                    (YEAR(CURDATE()) - YEAR(vb.last_payment_date)) * 4 +
                    (QUARTER(CURDATE()) - QUARTER(vb.last_payment_date))
                    ) AS QuartersDue,

                    -- Cashuurta guud ee laga rabo ilaa hadda
                    (
                    (YEAR(CURDATE()) - YEAR(vb.last_payment_date)) * 4 +
                    (QUARTER(CURDATE()) - QUARTER(vb.last_payment_date))
                    ) * vt.amount_per_three_month AS TotalTaxDue,

                    -- Wadarta lacagta uu bixiyay
                    IFNULL((
                        SELECT SUM(pr.amount_paid)
                        FROM payment_record pr
                        WHERE pr.number_plate = v.number_plate
                    ), 0) AS TotalPaid,

                    -- Lacagta aan wali la bixin
                    GREATEST(
                    (
                        (YEAR(CURDATE()) - YEAR(vb.last_payment_date)) * 4 +
                        (QUARTER(CURDATE()) - QUARTER(vb.last_payment_date))
                    ) * vt.amount_per_three_month
                    -
                    IFNULL((
                        SELECT SUM(pr.amount_paid)
                        FROM payment_record pr
                        WHERE pr.number_plate = v.number_plate
                    ), 0),
                    0
                    ) AS UnpaidBalance

                FROM vehicles v
                JOIN vehicle_balance vb ON v.number_plate = vb.number_plate
                JOIN vehicle_type vt ON vt.id = v.vehicle_type_id
                ORDER BY v.number_plate;";

    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $array_data[] = $row;
        }
        $data = array("status" => true, "data" => $array_data);
    } else {
        $data = array("status" => false, "data" => $conn->error);
    }

    echo json_encode($data);
}


function Get_vehicle_report($conn){

    extract($_POST);
    $data = array();
    $array_data = array();
    $query = "CALL   Get_student_report('$from','$to')";
    $result = $conn->query($query);

    if($result){
        while($row = $result->fetch_assoc()){
            $array_data [] = $row;
        }
        $data = array("status" => true, "data" => $array_data);
    }else{
        $data =array("status" => false, "data" => $conn->error);
    }
 echo json_encode($data);

}


function get_vehicle_by_date($conn){

    extract($_POST);
    $data = array();
    $array_Data  =  array();
    $query = "CALL `vehicle_statement` ('$from','$to')";
    $result = $conn->query($query);

    if($result){
        while($row = $result->fetch_assoc()){
            $array_Data [] = $row;

        }

        $data = array("status" => true, "data" => $array_Data);
    }else{
        $data = array("status" => false, "data" => $conn->error);
    }
    echo json_encode($data);
}




if(isset($_POST['action'])){
    $action = $_POST['action'];

    $action($conn);
}else{
    echo json_encode(array("status" => false,"data" => "action required..."));
}

?>