<?php
session_start();
if ($_SESSION['role'] !== 'Admin') {
    header("Location: ../login");
    exit;
}


include '../db.php';
$resets = $conn->query("SELECT id, username FROM users WHERE reset_token = 'pending'");

if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $conn->query("UPDATE users SET reset_token = 'approved' WHERE id = $id");
    header("Location: approve_resets");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Approve Reset Requests</title></head>
<body>
    <h2>Pending Reset Requests</h2>
    <ul>
        <?php while ($row = $resets->fetch_assoc()): ?>
            <li><?= $row['username'] ?> - <a href="?approve=<?= $row['id'] ?>">✅ Approve</a></li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
