<?php 
// Add at the very top for error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db.php';
$success = $error = "";
$plate = $vehicle_type = $owner = $serial_number = "";
$amount = 0;
$user_id = $_SESSION['user_id'] ?? 0;

// Log function
function log_action($conn, $user_id, $action, $page, $details = '') {
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, page, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $page, $details);
    $stmt->execute(); $stmt->close();
}

// Success message
if (isset($_GET['success'])) {
  if ($_GET['success'] == 1) {
      $success = "✅ Receipt added successfully!";
  } elseif ($_GET['success'] == 2) {
      $success = "✅ Receipt updated successfully!";
  } elseif ($_GET['success'] == 3) {
      $success = "✅ Receipt deleted successfully!";
  }
}

// Table Search Logic
if (isset($_GET['table_search_plate']) || isset($_GET['table_search_owner'])) {
    $plate = $_GET['table_search_plate'] ?? '';
    $owner = $_GET['table_search_owner'] ?? '';

    $where_conditions = [];
    $params = [];
    $types = "";

    if ($plate) {
        $where_conditions[] = "r.plate_number LIKE ?";
        $params[] = "%$plate%";
        $types .= "s";
    }

    if ($owner) {
        $where_conditions[] = "r.owner LIKE ?";
        $params[] = "%$owner%";
        $types .= "s";
    }

    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        $sql = "SELECT r.*, c.center_name FROM tbl_reciept r 
                LEFT JOIN vehiclemanagement v ON r.plate_number = v.platenumber 
                LEFT JOIN centers c ON r.center_id = c.id
                $where_clause 
                ORDER BY r.id DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $receipts = $stmt->get_result();
    }
} else {
    // Default query without search
    $receipts = $conn->query("SELECT r.*, c.center_name FROM tbl_reciept r 
                             LEFT JOIN vehiclemanagement v ON r.plate_number = v.platenumber 
                             LEFT JOIN centers c ON r.center_id = c.id
                             ORDER BY r.id DESC");
}

// Form Search Logic
$show_search_form = true;
if (isset($_GET['form_search_plate'])) {
    $plate = $_GET['form_search_plate'] ?? '';

    if ($plate) {
        $stmt = $conn->prepare("SELECT SUM(amount) FROM tblgenerate WHERE platenumber = ?");
        if (!$stmt) {
            die("<div class='alert alert-danger'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>");
        }
        $stmt->bind_param("s", $plate);
        $stmt->execute();
        $stmt->bind_result($gen_amount);
        $stmt->fetch();
        $stmt->close();

        $stmt = $conn->prepare("SELECT SUM(amount) FROM tbl_reciept WHERE plate_number = ?");
        $stmt->bind_param("s", $plate);
        $stmt->execute();
        $stmt->bind_result($paid_amount);
        $stmt->fetch();
        $stmt->close();

        $gen_amount = $gen_amount ?? 0;
        $paid_amount = $paid_amount ?? 0;
        $amount = $gen_amount - $paid_amount;

        // First, let's get all columns from vehiclemanagement to find the correct column name
        $stmt = $conn->prepare("SELECT v.*, vt.stamp, vt.admin 
                                FROM vehiclemanagement v 
                                LEFT JOIN vehicle_types vt ON v.vehicletype = vt.name 
                                WHERE v.platenumber = ? LIMIT 1");

        if (!$stmt) {
            die("❌ SQL Error: " . $conn->error);
        }

        $stmt->bind_param("s", $plate);
        $stmt->execute();
        // Get all columns from the result set
        $result = $stmt->get_result();
        $vehicle_data = $result->fetch_assoc();
        
        if ($vehicle_data) {
            // Debug: Output all available columns
            // echo "<pre>Available columns: " . print_r(array_keys($vehicle_data), true) . "</pre>";
            
            // Map the columns we need
            $vehicle_type = $vehicle_data['vehicletype'] ?? '';
            $owner = $vehicle_data['owner'] ?? '';
            $stamp = $vehicle_data['stamp'] ?? '';
            $admin = $vehicle_data['admin'] ?? '';
            $vehicle_description = $vehicle_data['description'] ?? '';
            
            // Debug: Output all vehicle data
            error_log("Vehicle data: " . print_r($vehicle_data, true));
            
            // Get the serial number from the correct column name
            $serial_number = $vehicle_data['serial_no'] ?? '';
            error_log("Serial number: " . $serial_number);
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit_receipt'])) {
        $vehicle_type = $_POST['vehicle_type'];
        $plate_number = $_POST['plate_number'];
        $owner = $_POST['owner'];
        $amount = str_replace(',', '', $_POST['amount']);
        $duration = $_POST['duration'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'] ?: date('Y-m-d H:i:s');
        $serial_num = $_POST['serial_num'];
        $receipt_num = $_POST['receipt_num'];
        $center_id = isset($_POST['center_id']) ? intval($_POST['center_id']) : null;
        $stamp = $_POST['stamp'] ?? 0;
        $admin = $_POST['admin'] ?? 0;
        $error = "";

        $stmt = $conn->prepare("SELECT id FROM tbl_reciept WHERE receipt_num = ?");
        $stmt->bind_param("s", $receipt_num);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "❌ Receipt number already exists";
        }
        $stmt->close();

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO tbl_reciept 
                (vehicle_type, plate_number, owner, amount, duration, description, due_date, serial_num, receipt_num, center_id, stamp, admin) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("sssssssssidd", 
                $vehicle_type, $plate_number, $owner, $amount, $duration, $description, 
                $due_date, $serial_num, $receipt_num, $center_id, $stamp, $admin
            );

            if ($stmt->execute()) {
                log_action($conn, $user_id, 'Add', 'tbl_reciept', "Receipt added for plate: $plate_number");
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=1");
                exit;
            } else {
                $error = "❌ Error adding receipt";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['update_receipt'])) {
        $id = intval($_POST['id']);
        $vehicle_type = $_POST['vehicle_type'];
        $plate_number = $_POST['plate_number'];
        $owner = $_POST['owner'];
        $amount = $_POST['amount'];
        $duration = $_POST['duration'];
        $description = $_POST['description'];
        $serial_num = $_POST['serial_num'];
        $receipt_num = $_POST['receipt_num'];
        $due_date = $_POST['due_date'];
        $center_id = isset($_POST['center_id']) ? intval($_POST['center_id']) : null;
        $stamp = $_POST['stamp'] ?? 0;
        $admin = $_POST['admin'] ?? 0;

        $stmt = $conn->prepare("SELECT id FROM tbl_reciept WHERE receipt_num = ? AND id != ?");
        $stmt->bind_param("si", $receipt_num, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "❌ Receipt number already exists";
        }
        $stmt->close();

        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE tbl_reciept SET serial_num = ?, receipt_num = ?, due_date = ?, center_id = ?, stamp = ?, admin = ? WHERE id = ?");
            $stmt->bind_param("sssiidi", $serial_num, $receipt_num, $due_date, $center_id, $stamp, $admin, $id);
            if ($stmt->execute()) {
                log_action($conn, $user_id, 'Update', 'tbl_reciept', "Receipt updated for ID: $id");
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=2");
                exit;
            } else {
                $error = "❌ Error updating receipt";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['delete_receipt'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM tbl_reciept WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_action($conn, $user_id, 'Delete', 'tbl_reciept', "Receipt deleted ID: $id");
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=3");
            exit;
        } else {
            $error = "❌ Error deleting receipt";
        }
        $stmt->close();
    }
}

$edit_receipt = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM tbl_reciept WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_receipt = $result->fetch_assoc();
    $stmt->close();
}

$centers = $conn->query("SELECT id, center_name FROM centers ORDER BY center_name");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Receipt Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
  <style>
    :root {
      --primary-blue: #1a73e8;
      --light-blue: #e8f0fe;
      --white: #ffffff;
      --dark-blue: #0d47a1;
      --gray-bg: #f8f9fa;
    }
    
    body {
      background-color: var(--gray-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
    }
    
    .container {
      background-color: var(--white);
      border-radius: 0;
      padding: 25px;
      margin-bottom: 30px;
    }
    
    .header {
      color: var(--primary-blue);
      border-bottom: 2px solid var(--primary-blue);
      padding-bottom: 10px;
      margin-bottom: 20px;
    }
    
    .btn-primary {
      background-color: var(--primary-blue);
      border-color: var(--primary-blue);
    }
    
    .btn-primary:hover {
      background-color: var(--dark-blue);
      border-color: var(--dark-blue);
    }
    
    .table-responsive {
      border-radius: 0;
      overflow: auto;
      max-height: 60vh;
    }
    
    .table thead {
      background-color: var(--primary-blue);
      color: white;
    }
    
    .table th {
      font-weight: 500;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      position: sticky;
      top: 0;
    }
    
    .table-hover tbody tr:hover {
      background-color: var(--light-blue);
    }
    
    .badge {
      background-color: var(--primary-blue);
    }
    
    .amount-cell {
      font-weight: bold;
      color: #28a745;
    }
    
    .search-container {
      background-color: var(--light-blue);
      border-radius: 0;
      padding: 20px;
      margin-bottom: 25px;
      border: 1px solid #d1e3f8;
    }
    
    .form-control:focus {
      border-color: var(--primary-blue);
    }
    
    .action-btn {
      padding: 5px 10px;
      border-radius: 0;
      font-size: 0.85rem;
    }
    
    .edit-btn {
      color: var(--primary-blue);
      border: 1px solid var(--primary-blue);
    }
    
    .edit-btn:hover {
      background-color: var(--primary-blue);
      color: white;
    }
    
    .delete-btn {
      color: #dc3545;
      border: 1px solid #dc3545;
    }
    
    .delete-btn:hover {
      background-color: #dc3545;
      color: white;
    }
    
    .no-records {
      color: #6c757d;
      font-style: italic;
      padding: 20px;
      text-align: center;
    }
    
    .receipt-form {
      background-color: var(--light-blue);
      border-radius: 0;
      padding: 20px;
      margin-top: 20px;
    }
    
    .input-group-text {
      background-color: #d1e3f8;
      color: var(--primary-blue);
    }
    
    @media (max-width: 768px) {
      .container {
        padding: 15px;
      }
      
      .action-btn {
        padding: 3px 6px;
        font-size: 0.75rem;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4 header">
    <h3><i class="bi bi-receipt-cutoff me-2"></i>Receipt Management</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReceiptModal">
      <i class="bi bi-plus-circle me-1"></i> Add Receipt
    </button>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show text-center">
      <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show text-center">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Table Search Form -->
  <div class="search-container">
    <form method="GET" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Plate Number</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-car-front-fill"></i></span>
          <input type="text" name="table_search_plate" class="form-control" placeholder="Search plate number" 
                 value="<?= htmlspecialchars($_GET['table_search_plate'] ?? '') ?>">
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Owner</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
          <input type="text" name="table_search_owner" class="form-control" placeholder="Search owner name" 
                 value="<?= htmlspecialchars($_GET['table_search_owner'] ?? '') ?>">
        </div>
      </div>
      <div class="col-12">
        <button class="btn btn-primary w-100">
          <i class="bi bi-search me-1"></i> Search
        </button>
      </div>
    </form>
  </div>

  <!-- Receipts Table -->
  <div class="table-responsive" style="max-height:300px;overFlow:auto;">
    <table class="table table-hover table-bordered align-middle mb-0">
      <thead>
        <tr>
          <th><i class="bi bi-hash"></i></th>
          <th><i class=""></i> Serial</th>
          <th><i class=""></i> Plate</th>
          <th><i class=""></i> Owner</th>
          <th><i class=""></i> Vehicle Type</th>
          <th><i class=""></i> Center</th>
          <th><i class=""></i> Description</th>
          <th>RoadTax<br><small>(114526)</small></th>
          <th>Stamp<br><small>(115103)</small></th>
          <th>Admin<br><small>(142261)</small></th>
          <th><i class=""></i> Duration</th>
          <th><i class=""></i> Receipt </th> 
          <th><i class=""></i> Date</th>
          <th><i class=""></i> Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php $i = 1; if (isset($receipts) && $receipts instanceof mysqli_result && $receipts->num_rows > 0): while ($row = $receipts->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['serial_num']) ?></td>
            <td><span class="badge rounded-pill"><i class="bi bi-car-front me-1"></i><?= htmlspecialchars($row['plate_number']) ?></span></td>
            <td><?= htmlspecialchars($row['owner']) ?></td>
            <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
            <td><?= htmlspecialchars($row['center_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td class="amount-cell"><i class="bi bi-currency-dollar"></i><?= number_format($row['amount'], 2) ?></td>
            <td class="amount-cell"><i class="bi bi-currency-dollar"></i><?= number_format($row['stamp'] ?? 0, 2) ?></td>
            <td class="amount-cell"><i class="bi bi-currency-dollar"></i><?= number_format($row['admin'] ?? 0, 2) ?></td>
            <td><?= htmlspecialchars($row['duration']) ?></td>
            <td><?= htmlspecialchars($row['receipt_num']) ?></td>
            <td><i class=""></i><?= date('d M Y', strtotime($row['due_date'])) ?></td>
            <td>
              <div class="d-flex gap-2 justify-content-center">
                <button class="action-btn edit-btn btn-edit" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#editReceiptModal">
                  <i class="bi bi-pencil-square"></i> 
                </button>
                <button class="action-btn delete-btn btn-delete" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">
                  <i class="bi bi-trash-fill"></i> 
                </button>
              </div>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr>
            <td colspan="14" class="no-records">
              <i class="bi bi-database-exclamation me-2"></i>No records found
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Receipt Modal -->
<div class="modal" id="addReceiptModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-file-earmark-plus-fill me-2"></i>Add Receipt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Error Debug Section -->
        <?php if (isset(
            $php_errormsg) || isset($debug_error) || (isset($amount) && !is_numeric($amount)) || (isset($gen_amount) && !is_numeric($gen_amount)) || (isset($paid_amount) && !is_numeric($paid_amount))): ?>
          <div class="alert alert-danger">
            <strong>Debug Info:</strong><br>
            <?php if (isset($php_errormsg)) echo 'PHP Error: ' . htmlspecialchars($php_errormsg) . '<br>'; ?>
            <?php if (isset($debug_error)) echo 'Debug Error: ' . htmlspecialchars($debug_error) . '<br>'; ?>
            <?php if (isset($amount) && !is_numeric($amount)) echo 'Amount is not numeric: ' . htmlspecialchars(var_export($amount, true)) . '<br>'; ?>
            <?php if (isset($gen_amount) && !is_numeric($gen_amount)) echo 'gen_amount is not numeric: ' . htmlspecialchars(var_export($gen_amount, true)) . '<br>'; ?>
            <?php if (isset($paid_amount) && !is_numeric($paid_amount)) echo 'paid_amount is not numeric: ' . htmlspecialchars(var_export($paid_amount, true)) . '<br>'; ?>
          </div>
        <?php endif; ?>
        <!-- Search Form -->
        <form method="GET" class="row g-3 mb-4">
          <input type="hidden" name="form_search" value="1">
          <div class="col-md-12">
            <label class="form-label"><i class="bi bi-car-front-fill me-1"></i>Plate Number</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" name="form_search_plate" class="form-control" placeholder="Enter plate number" 
                     value="<?= htmlspecialchars($_GET['form_search_plate'] ?? '') ?>" required>
            </div>
          </div>
          <div class="col-12">
            <button class="btn btn-primary w-100">
              <i class="bi bi-search me-1"></i> Search Vehicle
            </button>
          </div>
        </form>

        <!-- Results Section -->
        <?php if (isset($_GET['form_search_plate'])): ?>
          <?php if (!empty($plate)): ?>
            <?php if ($gen_amount == 0): ?>
              <div class="alert alert-info text-center">
                <i class="bi bi-info-circle-fill me-2"></i>No charges have been generated for this vehicle.
              </div>
            <?php else: ?>
              <div class="receipt-form">
                <?php if ($amount > 0): ?>
                  <div class="alert alert-info">
                    <i class="bi bi-check-circle-fill me-2"></i>Vehicle found with outstanding amount.
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>No outstanding payments found.
                  </div>
                <?php endif; ?>
              
              <form method="POST" class="row g-3">
                <input type="hidden" name="plate_number" value="<?= $plate ?>">
                
                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-truck me-1"></i>Vehicle Type</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-car-front"></i></span>
                    <input type="text" name="vehicle_type" class="form-control" value="<?= $vehicle_type ?>" readonly>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-building me-1"></i>Center</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <select name="center_id" class="form-control" required>
                      <option value="">-- Select Center --</option>
                      <?php if ($centers) { while ($center = $centers->fetch_assoc()): ?>
                        <option value="<?= $center['id'] ?>"><?= htmlspecialchars($center['center_name']) ?></option>
                      <?php endwhile; } ?>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-receipt me-1"></i>Description</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                    <input type="text" name="description" class="form-control" value="<?= isset($vehicle_description) ? htmlspecialchars($vehicle_description) : '' ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-person-fill me-1"></i>Owner</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="owner" class="form-control" value="<?= $owner ?>" readonly>
                  </div>
                </div>
                

                <div class="col-md-4">
                  <label class="form-label"><i class="bi bi-cash-stack me-1"></i>Amount</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                    <input type="text" name="amount" class="form-control" value="<?= number_format($amount, 2) ?>" readonly>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label"><i class="bi bi-stamp me-1"></i>Stamp</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                    <input type="number" name="stamp" step="0.01" class="form-control" value="<?= $stamp ?? 0 ?>" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label"><i class="bi bi-person-shield me-1"></i>Admin</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                    <input type="number" name="admin" step="0.01" class="form-control" value="<?= $admin ?? 0 ?>" required>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-receipt me-1"></i>Duration</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                      <input type="hidden" name="duration" value="6 months">
                      <input type="text" class="form-control" value="6 months" readonly>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Due Date</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-clock"></i></span>
                      <input type="datetime-local" name="due_date" class="form-control" value="<?= date('Y-m-d\\TH:i') ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-upc-scan me-1"></i>Serial Number</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-123"></i></span>
                      <input type="text" name="serial_num" class="form-control" value="<?= htmlspecialchars($serial_number ?? '') ?>" required readonly>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-receipt me-1"></i>Receipt Number</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                      <input type="text" name="receipt_num" class="form-control" required>
                    </div>
                  </div>
                </div>
                
                <div class="col-12 mt-4">
                  <button class="btn btn-success w-100 py-2" name="submit_receipt">
                    <i class="bi bi-save-fill me-2"></i> Save Receipt
                  </button>
                </div>
              </form>
            </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="alert alert-warning text-center">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>Vehicle not found.
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Edit Receipt Modal -->
<div class="modal" id="editReceiptModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Receipt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if ($edit_receipt): ?>
          <form method="POST" class="row g-3">
            <input type="hidden" name="id" value="<?= $edit_receipt['id'] ?>">
            
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-truck me-1"></i>Vehicle Type</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-car-front"></i></span>
                <input type="text" class="form-control" value="<?= $edit_receipt['vehicle_type'] ?>" readonly>
              </div>
            </div>
            
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-car-front-fill me-1"></i>Plate Number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-123"></i></span>
                <input type="text" class="form-control" value="<?= $edit_receipt['plate_number'] ?>" readonly>
              </div>
            </div>
            
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-person-fill me-1"></i>Owner</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" value="<?= $edit_receipt['owner'] ?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-receipt me-1"></i>Description</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                <input type="text" name="description" class="form-control" value="<?= $edit_receipt['description'] ?>" required>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-cash-stack me-1"></i>Amount</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                <input type="text" class="form-control" value="<?= number_format($edit_receipt['amount'], 2) ?>" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-stamp me-1"></i>Stamp</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                <input type="number" name="stamp" step="0.01" class="form-control" value="<?= $edit_receipt['stamp'] ?? 0 ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-person-shield me-1"></i>Admin</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                <input type="number" name="admin" step="0.01" class="form-control" value="<?= $edit_receipt['admin'] ?? 0 ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-receipt me-1"></i>Duration</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                <input type="text" name="duration" class="form-control" value="<?= $edit_receipt['duration'] ?>" required>
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Due Date</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                <input type="datetime-local" name="due_date" class="form-control" 
                       value="<?= date('Y-m-d\TH:i', strtotime($edit_receipt['due_date'])) ?>">
              </div>
            </div>
            
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-upc-scan me-1"></i>Serial Number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-123"></i></span>
                <input type="text" name="serial_num" class="form-control" value="<?= $edit_receipt['serial_num'] ?>" required>
              </div>
            </div>
            
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-receipt me-1"></i>Receipt Number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                <input type="text" name="receipt_num" class="form-control" value="<?= $edit_receipt['receipt_num'] ?>" required>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-building me-1"></i>Center</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <select name="center_id" class="form-control" required>
                  <option value="">-- Select Center --</option>
                  <?php 
                  // Reset centers result set for reuse
                  $centers->data_seek(0);
                  while ($center = $centers->fetch_assoc()): 
                  ?>
                    <option value="<?= $center['id'] ?>" <?= ($edit_receipt['center_id'] == $center['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($center['center_name']) ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>
            
            <div class="col-12 mt-4">
              <button class="btn btn-success w-100 py-2" name="update_receipt">
                <i class="bi bi-save-fill me-2"></i> Update Receipt
              </button>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-danger text-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Receipt not found.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="delete_receipt" value="1">
        <input type="hidden" name="delete_id" id="delete_id">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-center"><i class="bi bi-question-circle-fill me-2"></i>Are you sure you want to delete this receipt? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-trash-fill me-1"></i> Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Delete button handler
  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
      document.getElementById('delete_id').value = this.getAttribute('data-id');
    });
  });
  
  // Edit button handler
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      window.location.href = `?edit_id=${id}`;
    });
  });
  
  // Auto focus search field when modal opens
  const addReceiptModal = document.getElementById('addReceiptModal');
  if (addReceiptModal) {
    addReceiptModal.addEventListener('shown.bs.modal', function() {
      const searchField = this.querySelector('input[name="form_search_plate"]');
      if (searchField) searchField.focus();
    });
  }
  
  // Show edit modal if edit_id is in URL
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit_id')) {
      const modal = new bootstrap.Modal(document.getElementById('editReceiptModal'));
      modal.show();
    }
    
    if (urlParams.has('form_search_plate')) {
      const modal = new bootstrap.Modal(document.getElementById('addReceiptModal'));
      modal.show();
    }
  });
</script>
</body>
</html>

<?php $conn->close(); ?>