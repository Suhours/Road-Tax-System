<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized");
}


include '../db.php';
$resets = $conn->query("SELECT r.id, u.username FROM password_resets r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending'");

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE password_resets SET status = 'approved' WHERE id = $id");
    echo "✅ Approved.";
}
?>

<h2>Pending Reset Requests</h2>
<ul>
<?php while ($row = $resets->fetch_assoc()): ?>
    <li><?= $row['username'] ?> - <a href="?approve=<?= $row['id'] ?>">Approve</a></li>
<?php endwhile; ?>
</ul>
