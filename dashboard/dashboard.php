<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit;
}

// Get current page URL
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard_home.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome To Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="img/logo2.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
  body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4f9ff;
  }

  .sidebar {
    width: 250px;
    background-color: #007bff;
    position: fixed;
    height: 100%;
    color: white;
    padding-top: 30px;
    overflow-y: auto;
  }

  .sidebar a {
    padding: 14px 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    text-decoration: none;
    font-weight: 500;
    font-size: 15px;
    border-left: 4px solid transparent;
  }

  .sidebar a:hover {
    background-color: #3399ff;
    border-left: 4px solid #fff;
    cursor: pointer;
  }

  .sidebar a.active {
    background-color: #3399ff;
    border-left: 4px solid #fff;
  }

  .dropdown-container {
    display: none;
    background-color: #3399ff;
  }

  .dropdown-container a {
    padding-left: 45px;
    font-weight: normal;
    font-size: 14px;
  }

  .dropdown-container a.active {
    background-color: rgba(255,255,255,0.3);
    font-weight: 500;
  }

  .main {
    margin-left: 250px;
  }

  iframe {
    width: 100%;
    height: 100vh;
    border: none;
  }

  .logo-box {
    text-align: center;
    margin-bottom: 30px;
  }

  .logo-box img {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    border: 2px solid white;
  }

  .logo-box div {
    margin-top: 5px;
    font-size: 15px;
  }

  .menu-toggle {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    font-size: 26px;
    color: #007bff;
    background: white;
    border-radius: 0;
    padding: 4px 10px;
    cursor: pointer;
    z-index: 1001;
  }

  @media (max-width: 768px) {
    .sidebar {
      left: -250px;
      top: 0;
      z-index: 1000;
    }
    .sidebar.active {
      left: 0;
    }
    .main {
      margin-left: 0;
    }
    .menu-toggle {
      display: block;
    }
  }
</style>

</head>
<body>

<div class="menu-toggle" onclick="toggleMenu()">☰</div>

<div class="sidebar" id="sidebar">
  <div class="logo-box">
    <img src="img/logo2.png" alt="Logo">
    <div><b>ROAD-TAX MS</b></div>
    <div style="font-size: 12px;">SSC-KHAATUMO MOF</div>
  </div>

  <a onclick="loadPage('dashboard_home')" class="<?php echo $current_page == 'dashboard_home.php' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a onclick="loadPage('form')" class="<?php echo $current_page == 'form' ? 'active' : ''; ?>"><i class="bi bi-truck"></i> Vehicle Management</a>

  <a onclick="toggleDropdown('paymentDropdown')" class="<?php echo (strpos($current_page, 'generate_payment') !== false || strpos($current_page, 'reciept_payment') !== false) ? 'active' : ''; ?>"><i class="bi bi-cash-stack"></i> Payment Recording</a>
  <div class="dropdown-container" id="paymentDropdown" style="<?php echo (strpos($current_page, 'generate_payment') !== false || strpos($current_page, 'reciept_payment') !== false) ? 'display: block;' : ''; ?>">
    <a onclick="loadPage('../generate/generate_payment')" class="<?php echo strpos($current_page, 'generate_payment') !== false ? 'active' : ''; ?>">Generate Payment</a>
    <a onclick="loadPage('../generate/generate_all')" class="<?php echo strpos($current_page, 'generate_all') !== false ? 'active' : ''; ?>">generate All</a>
    <a onclick="loadPage('../reciept/reciept_payment')" class="<?php echo strpos($current_page, 'reciept_payment') !== false ? 'active' : ''; ?>">Receipt Payment</a>
  </div>

  <a onclick="toggleDropdown('reportDropdown')" class="<?php echo (strpos($current_page, 'reports') !== false || strpos($current_page, 'generate_report') !== false || strpos($current_page, 'reciept_report') !== false) ? 'active' : ''; ?>"><i class="bi bi-bar-chart-fill"></i> Reports</a>
  <div class="dropdown-container" id="reportDropdown" style="<?php echo (strpos($current_page, 'reports') !== false || strpos($current_page, 'generate_report') !== false || strpos($current_page, 'reciept_report') !== false) ? 'display: block;' : ''; ?>">
    <a onclick="loadPage('reports')" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">Report Vehicle</a>
    <a onclick="loadPage('../generate/generate_report')" class="<?php echo strpos($current_page, 'generate_report') !== false ? 'active' : ''; ?>">Generate Report</a>
    <a onclick="loadPage('../reciept/reciept_report')" class="<?php echo strpos($current_page, 'reciept_report') !== false ? 'active' : ''; ?>">Receipt Report</a>
  </div>

  <a onclick="loadPage('Vehiclestatement.php')" class="<?php echo $current_page == 'Vehiclestatement.php' ? 'active' : ''; ?>"><i class="bi bi-file-earmark-text"></i> Vehicle Statement</a>
  
  <a onclick="toggleDropdown('settingsDropdown')" class="<?php echo (strpos($current_page, 'settings') !== false || strpos($current_page, 'manage_users') !== false || strpos($current_page, 'audit_log') !== false) ? 'active' : ''; ?>"><i class="bi bi-gear"></i> Settings</a>
<div class="dropdown-container" id="settingsDropdown" style="<?php echo (strpos($current_page, 'settings') !== false || strpos($current_page, 'manage_users') !== false || strpos($current_page, 'audit_log') !== false) ? 'display: block;' : ''; ?>">
  <a onclick="loadPage('settings.php')" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">Role</a>
  <a onclick="loadPage('manage_users.php')" class="<?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">Manage Users</a>
  <a onclick="loadPage('../audit_log.php')" class="<?php echo strpos($current_page, 'audit_log') !== false ? 'active' : ''; ?>">Audit Log</a>
</div>


  <a href="../logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main">
  <iframe id="contentFrame" src="<?php echo $current_page; ?>"></iframe>
</div>

<script>
function toggleDropdown(id) {
  const dropdowns = document.getElementsByClassName("dropdown-container");
  for (let i = 0; i < dropdowns.length; i++) {
    if (dropdowns[i].id !== id) {
      dropdowns[i].style.display = "none";
    }
  }
  const el = document.getElementById(id);
  el.style.display = (el.style.display === "block") ? "none" : "block";
  
  // Store dropdown state in localStorage
  localStorage.setItem(id, el.style.display);
}

function loadPage(page) {
  document.getElementById('contentFrame').src = page;
  localStorage.setItem('lastPage', page);
  
  // Update URL without reloading the page
  history.pushState(null, null, '?page=' + page);
  
  // Highlight the active link
  const links = document.querySelectorAll('.sidebar a');
  links.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(page)) {
      link.classList.add('active');
    }
  });
  
  // Highlight parent dropdown if this is a subpage
  if (page.includes('generate_payment') || page.includes('reciept_payment')) {
    document.querySelector('a[onclick="toggleDropdown(\'paymentDropdown\')"]').classList.add('active');
    document.getElementById('paymentDropdown').style.display = 'block';
  } else if (page.includes('reports') || page.includes('generate_report') || page.includes('reciept_report')) {
    document.querySelector('a[onclick="toggleDropdown(\'reportDropdown\')"]').classList.add('active');
    document.getElementById('reportDropdown').style.display = 'block';
  } else if (page.includes('settings') || page.includes('manage_users') || page.includes('audit_log')) {
    document.querySelector('a[onclick="toggleDropdown(\'settingsDropdown\')"]').classList.add('active');
    document.getElementById('settingsDropdown').style.display = 'block';
  }
}

function toggleMenu() {
  document.getElementById("sidebar").classList.toggle("active");
}

window.onload = function() {
  // Load last page from localStorage
  const saved = localStorage.getItem('lastPage') || 'dashboard_home.php';
  document.getElementById('contentFrame').src = saved;
  
  // Restore dropdown states
  const dropdownIds = ['paymentDropdown', 'reportDropdown', 'settingsDropdown'];
  dropdownIds.forEach(id => {
    const state = localStorage.getItem(id);
    if (state) {
      document.getElementById(id).style.display = state;
    }
  });
  
  // Highlight current page
  const currentPage = new URL(window.location.href).searchParams.get('page') || saved;
  loadPage(currentPage);
};

// Handle browser back/forward buttons
window.onpopstate = function() {
  const currentPage = new URL(window.location.href).searchParams.get('page') || 'dashboard_home.php';
  loadPage(currentPage);
};
</script>

</body>
</html>