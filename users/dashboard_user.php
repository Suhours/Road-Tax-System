<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    header("Location: ../login");
    exit;
}


include '../db.php';
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0f4f8;
    }

    .sidebar {
      width: 250px;
      position: fixed;
      height: 100%;
      background: #0d6efd;
      padding-top: 20px;
      color: white;
      z-index: 1000;
      transition: 0.3s;
    }

    .sidebar .logo {
      text-align: center;
    }

    .sidebar .logo img {
      width: 80px;
      border-radius: 50%;
      border: 2px solid white;
    }

    .sidebar h4 {
      margin-top: 10px;
      font-weight: bold;
    }

    .sidebar a {
      display: block;
      color: white;
      padding: 12px 20px;
      text-decoration: none;
      transition: 0.3s;
      font-weight: bold;
    }

    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.1);
      cursor: pointer;
    }

    .topbar {
      height: 60px;
      background: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-left: 250px;
    }

    .iframe-container {
      margin-left: 250px;
      height: calc(100vh - 60px);
    }

    iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    .profile-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      overflow: hidden;
      border: 2px solid #0d6efd;
      cursor: pointer;
    }

    .profile-icon img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #profileModal {
      display: none;
      position: fixed;
      top: 70px;
      right: 30px;
      width: 300px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.2);
      padding: 20px;
      z-index: 999;
    }

    @media (max-width: 768px) {
      .sidebar {
        left: -250px;
      }
      .sidebar.show {
        left: 0;
      }
      .topbar, .iframe-container {
        margin-left: 0;
      }
      .menu-btn {
        display: block;
        font-size: 24px;
        cursor: pointer;
      }
    }

    .menu-btn {
      display: none;
      font-weight: bold;
    }

    hr {
      border-color: rgba(255,255,255,0.3);
    }

    .small {
      font-size: 13px;
      color: #ddd;
    }
  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <div class="logo">
    <img src="img/logo2.png" alt="Logo">
    <h4>ROAD-TAX MS</h4>
    <p class="small">SSC-KHAATUMO MOF</p>
  </div>
  <hr>
  <a onclick="loadPage('welcome_user.php')">🏠 Dashboard</a>
  <?php foreach ($pages as $page): ?>
    <a onclick="loadPage('../<?= $page ?>')">
      &bull; <?= ucfirst(str_replace([".php", "_", "-"], ["", " ", " "], basename($page))) ?>
    </a>
  <?php endforeach; ?>
  <hr>
  <a href="../logout.php">🚪 Logout</a>
</div>

<div class="topbar">
  <span class="menu-btn" onclick="toggleSidebar()">☰</span>
  <div class="profile-icon" onclick="loadProfileModal()">
    <img src="../img/logo3.PNG" alt="Profile">
  </div>
</div>

<div class="iframe-container">
  <iframe id="mainFrame" src="welcome_user.php"></iframe>
</div>

<div id="profileModal">
  <div id="profileContent">Loading...</div>
  <button class="btn btn-sm btn-primary mt-3" onclick="closeProfileModal()">Close</button>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('show');
}

function loadPage(url) {
  document.getElementById('mainFrame').src = url;
}

function loadProfileModal() {
  document.getElementById('profileModal').style.display = 'block';
  fetch('user_profile_content.php')
    .then(res => res.text())
    .then(data => {
      document.getElementById('profileContent').innerHTML = data;
    });
}

function closeProfileModal() {
  document.getElementById('profileModal').style.display = 'none';
}
</script>

</body>
</html>
