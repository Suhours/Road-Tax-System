<?php
include '../db.php';

$email = $_POST['email'];

$check = $conn->query("SELECT * FROM users WHERE email = '$email'");
if ($check->num_rows > 0) {
    $conn->query("UPDATE tbl_users SET reset_requested = 1 WHERE email = '$email'");
    echo "<div style='padding:20px; background:#d4edda; color:#155724; font-family:Arial;'>Request sent successfully. Admin will approve it.</div>";
} else {
    echo "<div style='padding:20px; background:#f8d7da; color:#721c24; font-family:Arial;'>Email not found in system.</div>";
}
?>