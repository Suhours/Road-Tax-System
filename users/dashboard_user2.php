<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 4) {
    header("Location: ../login");
    exit;
}

$conn = new mysqli("localhost", "root", "", "roadtaxsystem");
$user_id = $_SESSION['user_id'];

$sql = "SELECT page_name FROM tbl_user_pages WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$pages = [];
while ($row = $result->fetch_assoc()) {
    $pages[] = $row['page_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User2 Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4ff;
            display: flex;
        }
        .sidebar {
            width: 240px;
            height: 100vh;
            background: #007bff;
            color: white;
            padding-top: 30px;
            position: fixed;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            animation: slideInLeft 0.5s ease-out;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 22px;
            font-weight: bold;
        }
        .sidebar a {
            display: block;
            padding: 14px 25px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #0056b3;
            padding-left: 30px;
        }
        .content {
            margin-left: 240px;
            padding: 60px;
            animation: fadeIn 0.5s ease-in;
        }
        .content h1 {
            color: #007bff;
            font-size: 30px;
        }
        .content p {
            font-size: 17px;
            color: #555;
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>User2 Panel</h2>
    <?php foreach ($pages as $page): ?>
        <a href="<?php echo $page; ?>">
            <?php echo ucfirst(str_replace([".php", "_", "-"], ["", " ", " "], basename($page))); ?>
        </a>
    <?php endforeach; ?>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="content">
    <h1>Welcome, User2</h1>
    <p>You can access the pages listed in your sidebar. Please choose a section to continue.</p>
</div>

</body>
</html>
