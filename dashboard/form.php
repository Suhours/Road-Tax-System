<?php 
session_start();

include '../db.php';


$success = $error = "";
function log_action($conn, $user_id, $action, $page, $details = '') {
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, page, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $page, $details);
    $stmt->execute();
    $stmt->close();
}

// Excel Import functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['import_vehicles'])) {
    if (isset($_FILES['xlsx_file']) && $_FILES['xlsx_file']['error'] == 0) {
        $file_name = $_FILES['xlsx_file']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        if ($file_ext == 'xlsx') {
            require '../vendor/autoload.php'; // Adjust path if needed
            
            try {
                $file_tmp = $_FILES['xlsx_file']['tmp_name'];
                
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($file_tmp);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                // Skip header row
                array_shift($rows);
                
                $imported_count = 0;
                
                foreach ($rows as $row) {
                    // Expecting: plate, chesis, serial, owner, vehicletype, description, model, color, cylinder, manufacture, center, registration_date
                    if (count($row) >= 12) {
                        $platenumber = trim($row[0]);
                        $chesis_no = trim($row[1]);
                        $serial_no = trim($row[2]);
                        $owner = trim($row[3]);
                        $vehicletype = trim($row[4]);
                        $description = trim($row[5]);
                        $model = trim($row[6]);
                        $color = trim($row[7]);
                        $cylinder = trim($row[8]);
                        $manufacture = trim($row[9]);
                        $center = trim($row[10]);
                        $registration = trim($row[11]);
                        // Basic validation
                        if (!empty($platenumber) && !empty($owner)) {
                            $stmt = $conn->prepare("INSERT INTO vehiclemanagement (platenumber, chesis_no, serial_no, owner, vehicletype, description, model, color, cylinder, manufacture, center, registration_date) 
                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                            $stmt->bind_param("sssssssssss", $platenumber, $chesis_no, $serial_no, $owner, $vehicletype, $description, $model, $color, $cylinder, $manufacture, $center);
                            if ($stmt->execute()) {
                                $imported_count++;
                            }
                            $stmt->close();
                        }
                    }
                }
                $success = "✅ Successfully imported $imported_count vehicles!";
            } catch (Exception $e) {
                $error = "❌ Error reading Excel file: " . $e->getMessage();
            }
        } else {
            $error = "❌ Only Excel (.xlsx) files are allowed.";
        }
    } else {
        $error = "❌ Please select a valid Excel file.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_vehicle'])) {
    $platenumber   = $_POST['platenumber'];
    $chesis_no     = $_POST['chesis_no'];
    $serial_no     = $_POST['serial_no'];
    $owner         = $_POST['owner'];
    $vehicletype   = $_POST['vehicletype'];
    $description   = $_POST['description'];
    $model         = $_POST['model'];
    $color         = $_POST['color'];
    $cylinder      = $_POST['cylinder'];
    $manufacture   = $_POST['manufacture'];
    $center        = $_POST['center'];

    if (!preg_match("/^[a-zA-Z\s]+$/", $owner)) {
        $error = "❌ Owner name must contain only letters.";
    } else {
        // Check for duplicate plate number
        $checkStmt = $conn->prepare("SELECT id FROM vehiclemanagement WHERE platenumber = ? LIMIT 1");
        $checkStmt->bind_param("s", $platenumber);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "❌ This plate number is already registered.";
            $checkStmt->close();
        } else {
            $checkStmt->close();
            // Check for duplicate serial_no
            $serialCheck = $conn->prepare("SELECT id FROM vehiclemanagement WHERE serial_no = ? LIMIT 1");
            $serialCheck->bind_param("s", $serial_no);
            $serialCheck->execute();
            $serialCheck->store_result();
            if ($serialCheck->num_rows > 0) {
                $error = "❌ This serial number is already registered.";
                $serialCheck->close();
            } else {
                $serialCheck->close();
                $stmt = $conn->prepare("INSERT INTO vehiclemanagement 
                    (platenumber, chesis_no, serial_no, owner, vehicletype, description, model, color, cylinder, manufacture, center, registration_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssssssss", $platenumber, $chesis_no, $serial_no, $owner, $vehicletype, $description, $model, $color, $cylinder, $manufacture, $center);

                if ($stmt->execute()) {
                    $success = "<i class='bi bi-check-circle-fill text-success'></i> Vehicle registered successfully!";
                    $stmt->close();
                } else {
                    $error = "❌ Error saving vehicle: " . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }
}




// Edit vehicle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_vehicle'])) {
    $id = $_POST['vehicle_id'];
    $platenumber   = $_POST['platenumber'];
    $chesis_no     = $_POST['chesis_no'];
    $serial_no     = $_POST['serial_no'];
    $owner         = $_POST['owner'];
    $vehicletype   = $_POST['vehicletype'];
    $description   = $_POST['description'];
    $model         = $_POST['model'];
    $color         = $_POST['color'];
    $cylinder      = $_POST['cylinder'];
    $manufacture   = $_POST['manufacture'];
    $center        = $_POST['center'];
    // Check for duplicate platenumber (exclude current vehicle)
    $plateCheck = $conn->prepare("SELECT id FROM vehiclemanagement WHERE platenumber = ? AND id != ? LIMIT 1");
    $plateCheck->bind_param("si", $platenumber, $id);
    $plateCheck->execute();
    $plateCheck->store_result();
    if ($plateCheck->num_rows > 0) {
        $success = '';
        $error = "❌ This plate number is already registered to another vehicle.";
        $plateCheck->close();
    } else {
        $plateCheck->close();
        // Check for duplicate serial_no (exclude current vehicle)
        $serialCheck = $conn->prepare("SELECT id FROM vehiclemanagement WHERE serial_no = ? AND id != ? LIMIT 1");
        $serialCheck->bind_param("si", $serial_no, $id);
        $serialCheck->execute();
        $serialCheck->store_result();
        if ($serialCheck->num_rows > 0) {
            $success = '';
            $error = "❌ This serial number is already registered to another vehicle.";
            $serialCheck->close();
        } else {
            $serialCheck->close();
            $stmt = $conn->prepare("UPDATE vehiclemanagement SET platenumber=?, chesis_no=?, serial_no=?, owner=?, vehicletype=?, description=?, model=?, color=?, cylinder=?, manufacture=?, center=? WHERE id=?");
            $stmt->bind_param("sssssssssssi", $platenumber, $chesis_no, $serial_no, $owner, $vehicletype, $description, $model, $color, $cylinder, $manufacture, $center, $id);
            $success = $stmt->execute() ? "<i class='bi bi-check-circle-fill text-success'></i> Vehicle updated successfully!" : "Error updating vehicle.";
            $stmt->close();
        }
    }
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_vehicle'])) {
    $id = $_POST['vehicle_id'];
    $stmt = $conn->prepare("DELETE FROM vehiclemanagement WHERE id=?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute() ? "Vehicle deleted successfully!" : "Error deleting vehicle.";
    $stmt->close();
}

// Handle Quick Add Center
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quick_add_center'])) {
    $new_center_name = trim($_POST['new_center_name']);
    if (!empty($new_center_name)) {
        // Check if center already exists
        $checkStmt = $conn->prepare("SELECT id FROM centers WHERE center_name = ? LIMIT 1");
        $checkStmt->bind_param("s", $new_center_name);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $error = "❌ Center '$new_center_name' already exists.";
        } else {
            $checkStmt->close();
            
            $stmt = $conn->prepare("INSERT INTO centers (center_name) VALUES (?)");
            $stmt->bind_param("s", $new_center_name);
            if ($stmt->execute()) {
                $success = "✅ Center '$new_center_name' added successfully!";
                // Redirect to refresh the page and update dropdowns
                header("Location: " . $_SERVER['PHP_SELF'] . "?new_center=" . urlencode($new_center_name));
                exit;
            } else {
                $error = "❌ Error adding center: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error = "❌ Center name is required.";
    }
}

// Fetch vehicles
$result = $conn->query("SELECT * FROM vehiclemanagement ORDER BY id DESC");
$plate_number = $conn->query("SELECT platenumber FROM vehiclemanagement");
$types = $conn->query("SELECT name FROM vehicle_types");
$owner = $conn->query("SELECT owner FROM vehiclemanagement");
$registration = $conn->query("SELECT registration_date FROM vehiclemanagement");



// Report data
$carname_result = $conn->query("SELECT DISTINCT vehicletype FROM vehiclemanagement ORDER BY vehicletype");
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$car_filter = isset($_GET['vehicletype']) ? $conn->real_escape_string($_GET['vehicletype']) : "";
$month_filter = isset($_GET['month']) ? $conn->real_escape_string($_GET['month']) : "";

$where = [];
if (!empty($search)) {
    $where[] = "platenumber LIKE '%$search%'";
}
if (!empty($car_filter)) {
    $where[] = "vehicletype = '$car_filter'";
}
if (!empty($month_filter)) {
    $where[] = "MONTH(registration_date) = '$month_filter'";
}
$where_clause = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT * FROM vehiclemanagement $where_clause ORDER BY id ASC";
$result = $conn->query($sql);
$total = $result->num_rows;

// For dropdown inside modal
$types = $conn->query("SELECT name FROM vehicle_types");
$centers = $conn->query("SELECT center_name FROM centers ORDER BY center_name");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vehicle Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>

  html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
  }

  body {
      display: flex;
      font-family: sans-serif;
  }

  .sidebar {
      width: 250px;
      background-color: #007bff;
      color: #fff;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      overflow-y: auto;
      padding: 20px;
  }

  .main-content {
      margin-left: 250px;
      padding: 20px;
      height: 100vh;
      overflow-y: auto;
      width: calc(100% - 250px);
      background-color: #f8f9fa;
  }
      .table-responsive table {
        font-size: 14px;
    }

    .table-responsive td,
    .table-responsive th {
        white-space: nowrap;
    }

    .table-responsive {
        max-height: 60vh;
        overflow-y: auto;
    }
    
</style>

</head>

<body class="bg-light">

<div class="container-fluid py-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <!--<h2 class="text-center mb-0">🚗 Vehicle Reports & Registration</h2>-->
        </div>
        
        <div class="card-body">
            <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <!-- Filter and Action Buttons -->
            <div class="card mb-4">
                <div class="card-body">
                <h2 class="text-center mb-4 fw-bold text-primary">Vehicle Management</h2>

                    <div class="row g-2">
                        <div class="col-md-9">
                            <form class="row g-2" method="GET">
                                <div class="col-sm-6 col-md-4">
                                    <input class="form-control" type="text" name="search" placeholder="Search by Plate Number" value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <select class="form-select" name="vehicletype">
                                        <option value="">Filter by Vehicle Type</option>
                                        <?php while ($row = $carname_result->fetch_assoc()): ?>
                                            <option value="<?= $row['vehicletype'] ?>" <?= $car_filter == $row['vehicletype'] ? 'selected' : '' ?>><?= $row['vehicletype'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <select class="form-select" name="month">
                                        <option value="">Filter by Month</option>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?= $m ?>" <?= $month_filter == $m ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-sm-6 col-md-2 d-grid">
                                    <button class="btn btn-primary">
                                        <i class="bi bi-filter"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3 d-flex justify-content-end gap-2 flex-wrap">
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="bi bi-upload"></i> Import
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registerModal">
                                <i class="bi bi-plus"></i> Register
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Plate #</th>
                                    <th>Chesis #</th>
                                    <th>Serial #</th>
                                    <th>Owner</th>
                                    <th>Vehicle Type</th>
                                    <th>Description</th>
                                    <th>Model</th>
                                    <th>Color</th>
                                    <th>Cylinder</th>
                                    <th>Manufacture</th>
                                    <th>Center</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['platenumber']) ?></td>
                                        <td><?= htmlspecialchars($row['chesis_no'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['serial_no'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['owner']) ?></td>
                                        <td><?= htmlspecialchars($row['vehicletype']) ?></td>
                                        <td><?= htmlspecialchars($row['description']) ?></td>
                                        <td><?= htmlspecialchars($row['model']) ?></td>
                                        <td><?= htmlspecialchars($row['color']) ?></td>
                                        <td><?= htmlspecialchars($row['cylinder']) ?></td>
                                        <td><?= htmlspecialchars($row['manufacture']) ?></td>
                                        <td><?= htmlspecialchars($row['center']) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['registration_date']))) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning btn-edit"
                                                data-id="<?= $row['id'] ?>"
                                                data-plate="<?= $row['platenumber'] ?>"
                                                data-chesis_no="<?= htmlspecialchars($row['chesis_no'] ?? '') ?>"
                                                data-serial_no="<?= htmlspecialchars($row['serial_no'] ?? '') ?>"
                                                data-owner="<?= $row['owner'] ?>"
                                                data-type="<?= $row['vehicletype'] ?>"
                                                data-description="<?= $row['description'] ?>"
                                                data-model="<?= $row['model'] ?>"
                                                data-color="<?= $row['color'] ?>"
                                                data-cylinder="<?= $row['cylinder'] ?>"
                                                data-manufacture="<?= $row['manufacture'] ?>"
                                                data-center="<?= $row['center'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button class="btn btn-sm btn-danger btn-delete"
                                                data-id="<?= $row['id'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer bg-light">
            <small class="text-muted">Total records: <?= $total ?></small>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="registerModal"  tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" class="modal-content needs-validation" novalidate>
    <input type="hidden" name="register_vehicle" value="1">
      <div class="modal-header">
        <h5 class="modal-title text-primary" id="registerModalLabel">Register New Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light p-3 rounded-3" >
        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <input type="text" name="platenumber" id="platenumber" class="form-control" required placeholder="Plate Number">
          </div>
          <div class="col-md-6 mb-2">
            <select name="vehicletype" class="form-select" required>
              <option value="">Select Type</option>
              <?php while ($row = $types->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['name']) ?>"><?= htmlspecialchars($row['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="owner" id="owner" class="form-control" required placeholder="Owner Name">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="model" id="model" class="form-control" required placeholder="Vehicle Model">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="chesis_no" id="chesis_no" class="form-control" required placeholder="Chesis Number">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="serial_no" id="serial_no" class="form-control" required placeholder="Serial Number">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="color" id="color" class="form-control" required placeholder="Color">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="cylinder" id="cylinder" class="form-control" required placeholder="Cylinder">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="manufacture" id="manufacture" class="form-control" required placeholder="Manufacture">
          </div>
          <div class="col-md-6 mb-2">
            <div class="input-group">
              <select name="center" class="form-select" required>
                <option value="">Select Center</option>
                <?php while ($row = $centers->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($row['center_name']) ?>"><?= htmlspecialchars($row['center_name']) ?></option>
                <?php endwhile; ?>
              </select>
              <!-- <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#quickAddCenterModal">
                <i class="bi bi-plus"></i>
              </button> -->
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="description" class="form-control" required placeholder="Description">
          </div>
        </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x"></i>Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save"></i> Save Vehicle
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ✅ Client-side validation script -->
<script>
  document.addEventListener("DOMContentLoaded", () => {
    // Bootstrap validation
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();

    // Owner name: Only letters
    document.getElementById('owner').addEventListener('input', function () {
      const pattern = /^[A-Za-z\s]+$/;
      if (!pattern.test(this.value)) {
        this.classList.add('is-invalid');
      } else {
        this.classList.remove('is-invalid');
      }
    });

    // Plate number check via AJAX
    document.getElementById('platenumber').addEventListener('blur', function () {
      const plate = this.value.trim();
      if (!plate) return;
      fetch('check_plate.php?plate=' + encodeURIComponent(plate))
        .then(res => res.json())
        .then(data => {
          if (data.exists) {
            this.classList.add('is-invalid');
            var plateError = document.getElementById('plate-error');
            if (plateError) plateError.style.display = 'block';
          } else {
            this.classList.remove('is-invalid');
            var plateError = document.getElementById('plate-error');
            if (plateError) plateError.style.display = 'none';
          }
        });
    });
  });
</script>



<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" >
    <form method="POST" class="modal-content">
      <input type="hidden" name="edit_vehicle" value="1">
      <input type="hidden" name="vehicle_id" id="edit_id">
      <div class="modal-header">
        <h5 class="modal-title text-warning" id="editModalLabel">Edit Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light p-3 rounded-3">
        
        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <input type="text" name="platenumber" id="edit_plate" class="form-control" required placeholder="Plate Number">
          </div>
          <div class="col-md-6 mb-2">
            <select name="vehicletype" id="edit_type" class="form-select" required>
              <?php
              $types2 = $conn->query("SELECT name FROM vehicle_types");
              while ($t = $types2->fetch_assoc()):
              ?>
                  <option value="<?= htmlspecialchars($t['name']) ?>"><?= htmlspecialchars($t['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="owner" id="edit_owner" class="form-control" required placeholder="Owner Name">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="model" id="edit_model" class="form-control" required placeholder="Vehicle Model">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="chesis_no" id="edit_chesis_no" class="form-control" required placeholder="Chesis Number">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="serial_no" id="edit_serial_no" class="form-control" required placeholder="Serial Number">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="color" id="edit_color" class="form-control" required placeholder="Color">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="cylinder" id="edit_cylinder" class="form-control" required placeholder="Cylinder">
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="manufacture" id="edit_manufacture" class="form-control" required placeholder="Manufacture">
          </div>
          <div class="col-md-6 mb-2">
            <select name="center" id="edit_center" class="form-select" required>
              <option value="">Select Center</option>
              <?php
              $centers2 = $conn->query("SELECT center_name FROM centers ORDER BY center_name");
              while ($c = $centers2->fetch_assoc()):
              ?>
                  <option value="<?= htmlspecialchars($c['center_name']) ?>"><?= htmlspecialchars($c['center_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <input type="text" name="description" id="edit_description" class="form-control" required placeholder="Description">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="delete_vehicle" value="1">
      <input type="hidden" name="vehicle_id" id="delete_id">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this vehicle?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
      </div>
    </form>
  </div>
</div>


<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title text-info" id="importModalLabel">Import Vehicles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="import_vehicles" value="1">
        <div class="mb-3">
            <label class="form-label">Select Excel File</label>
            <input type="file" name="xlsx_file" class="form-control" accept=".xlsx" required>
        </div>
        <div class="alert alert-info">
            <p class="mb-1"><strong>Excel Format:</strong></p>
            <p class="small mb-0">First row should be header row. Columns should be in this order:</p>
            <p class="small mb-0">1. Plate Number, 2. Vehicle Type, 3. Owner, 4. Model, 5. Registration Date (YYYY-MM-DD)</p>
        </div>
      </div>
      <div class="modal-footer">
         <a href="xlsx-template.php" class="btn btn-outline-secondary" download>
            <i class="bi bi-download"></i> Download Template</a> 
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-cloud-upload"></i> Import</button>
      </div>
    </form>
  </div>
</div>

<!-- Quick Add Center Modal -->
<!-- <div class="modal fade" id="quickAddCenterModal" tabindex="-1" aria-labelledby="quickAddCenterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-success" id="quickAddCenterModalLabel">
          <i class="bi bi-building-add"></i> Quick Add Center
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="quick_add_center" value="1">
        <div class="mb-3">
          <label class="form-label">Center Name</label>
          <input type="text" name="new_center_name" class="form-control" required placeholder="Enter center name">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">
          <i class="bi bi-plus"></i> Add Center
        </button>
      </div>
    </form>
  </div>
</div> -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-select newly added center
    const urlParams = new URLSearchParams(window.location.search);
    const newCenter = urlParams.get('new_center');
    if (newCenter) {
        const centerSelect = document.querySelector('select[name="center"]');
        if (centerSelect) {
            centerSelect.value = newCenter;
        }
    }
    
    // Edit button click handler
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const plate = this.getAttribute('data-plate');
            const chesis_no = this.getAttribute('data-chesis_no');
            const serial_no = this.getAttribute('data-serial_no');
            const owner = this.getAttribute('data-owner');
            const type = this.getAttribute('data-type');
            const description = this.getAttribute('data-description');
            const model = this.getAttribute('data-model');
            const color = this.getAttribute('data-color');
            const cylinder = this.getAttribute('data-cylinder');
            const manufacture = this.getAttribute('data-manufacture');
            const center = this.getAttribute('data-center');
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_plate').value = plate;
            document.getElementById('edit_chesis_no').value = chesis_no;
            document.getElementById('edit_serial_no').value = serial_no;
            document.getElementById('edit_owner').value = owner;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_model').value = model;
            document.getElementById('edit_color').value = color;
            document.getElementById('edit_cylinder').value = cylinder;
            document.getElementById('edit_manufacture').value = manufacture;
            document.getElementById('edit_center').value = center;
        });
    });

    // Delete button click handler
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            document.getElementById('delete_id').value = id;
        });
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>

