<div class="sidebar">
    <h2>ROAD-TAX MS</h2>
    <a href="dashboard">🏠 Dashboard</a>

    <!-- Dropdown for Vehicle Management -->
    <div class="dropdown-btn" onclick="toggleDropdown('vehicleDropdown')">🚗 Vehicle Management ▾</div>
    <div class="dropdown-container" id="vehicleDropdown" style="display: none;">
        <a href="form">➕ Register Vehicle</a>
    </div>

    <!-- Dropdown for Payment Recording -->
    <div class="dropdown-btn" onclick="toggleDropdown('paymentDropdown')">💰 Payment Recording ▾</div>
    <div class="dropdown-container" id="paymentDropdown" style="display: none;">
        <a href="../generate/generate_payment">➕ Generate Payment</a>
        <a href="../generate/generate_report">📊 Generate Report</a>
        <a href="../reciept/reciept_payment">➕ Receipt Payment</a>
        <a href="../reciept/reciept_report">📊 Receipt Report</a>
        <a href="Vehiclestatement">📑 Vehicle Statement</a>
    </div>

    <a href="reports">📄 Reports</a>
    <a href="register_user">🧑 Add Register</a>
    <a href="settings">⚙️ Settings</a>
    <a href="logout">🚪 Logout</a>
</div>

<script>
    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }
</script>
