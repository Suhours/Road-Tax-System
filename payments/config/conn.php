<?php


$conn = new mysqli("localhost","root","","road_tax");

if($conn->connect_error){
    echo $conn->error;
}else{
    // echo ("successfully connected");
}

?>