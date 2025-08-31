<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// SAVE ONE BY ONE CHARGE
$response = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charge'])) {
    $plate = $_POST['plate'];
    $owner = $_POST['owner'];
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $now = date("Y-m-d H:i:s");
    $duration_label = '6 bilood';
    $status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO tblgenerate (vehicletype, platenumber, fullname, amount, due_date, status, amount_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsss", $type, $plate, $owner, $amount, $now, $status, $duration_label);
    if ($stmt->execute()) $response = "✅ Vehicle $plate charged successfully.";
    else $response = "❌ Error: " . $stmt->error;
}

// BULK GENERATE PAYMENT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_payment'])) {
    $vehicletype = $_POST['vehicletype'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $due_date = $_POST['due_date'] ?? date("Y-m-d H:i:s");

    if ($vehicletype && $duration) {
        $vehicles = $vehicletype === "all"
            ? $conn->query("SELECT * FROM vehiclemanagement")
            : $conn->query("SELECT * FROM vehiclemanagement WHERE vehicletype = '$vehicletype'");

        $inserted = 0;

        while ($v = $vehicles->fetch_assoc()) {
            $plate = $v['platenumber'];
            $owner = $v['owner'];
            $raw_type = $v['vehicletype'];
            $lookup_type = strtolower(trim($raw_type));

            // Get 6-month charge
            $stmt = $conn->prepare("SELECT amount FROM vehicle_types WHERE name = ? AND amount_type = '6'");
            $stmt->bind_param("s", $lookup_type);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $base_amount = floatval(str_replace(',', '', $row['amount'])); // Ensure it's numeric
                $final_amount = $base_amount;
                $duration_label = '6 bilood';
                $insert = $conn->prepare("INSERT INTO tblgenerate (fullname, vehicletype, platenumber, amount, amount_type, due_date)
                                          VALUES (?, ?, ?, ?, ?, ?)");
                $insert->bind_param("sssdss", $owner, $raw_type, $plate, $final_amount, $duration_label, $due_date);
                if ($insert->execute()) $inserted++;
            }
        }

        $response .= "✅ $inserted vehicle(s) charged in bulk.";
    }
}

// HANDLE AJAX SEARCH
if (isset($_GET['ajax']) && $_GET['ajax'] == "1") {
    $plate = $_GET['plate'] ?? '';
    $data = [];
    if ($plate) {
        $stmt = $conn->prepare("SELECT owner, vehicletype FROM vehiclemanagement WHERE platenumber = ? LIMIT 1");
        if (!$stmt) {
            echo json_encode(['error' => 'Prepare failed: ' . $conn->error, 'step' => 'plate-prepare']);
            exit;
        }
        $stmt->bind_param("s", $plate);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Execute failed: ' . $stmt->error, 'step' => 'plate-execute']);
            exit;
        }
        $stmt->bind_result($owner, $type);
        $found = $stmt->fetch();
        $stmt->close();
        if ($found) {
            // Get generated and paid amounts
            $stmt = $conn->prepare("SELECT SUM(amount) FROM tblgenerate WHERE platenumber = ?");
            if (!$stmt) {
                echo json_encode(['error' => 'Prepare failed: ' . $conn->error, 'step' => 'sum-generate-prepare']);
                exit;
            }
            $stmt->bind_param("s", $plate);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'Execute failed: ' . $stmt->error, 'step' => 'sum-generate-execute']);
                exit;
            }
            $stmt->bind_result($generated);
            $stmt->fetch();
            $stmt->close();
            $stmt = $conn->prepare("SELECT SUM(amount) FROM tbl_reciept WHERE plate_number = ?");
            if (!$stmt) {
                echo json_encode(['error' => 'Prepare failed: ' . $conn->error, 'step' => 'sum-receipt-prepare']);
                exit;
            }
            $stmt->bind_param("s", $plate);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'Execute failed: ' . $stmt->error, 'step' => 'sum-receipt-execute']);
                exit;
            }
            $stmt->bind_result($paid);
            $stmt->fetch();
            $stmt->close();
            $data = [
                'plate' => $plate,
                'owner' => $owner,
                'type' => $type,
                'amount_due' => number_format(($generated ?? 0) - ($paid ?? 0), 2)
            ];
            echo json_encode($data);
        } else {
            echo json_encode(['error' => 'This vehicle is not registered.', 'step' => 'plate-not-found']);
        }
    } else {
        echo json_encode(['error' => 'No plate provided or found.', 'step' => 'no-plate']);
    }
    exit;
}

// REPORT FILTERS
$type_result = $conn->query("SELECT DISTINCT vehicletype FROM tblgenerate");
$types = $conn->query("SELECT name FROM vehicle_types");

$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? 'all';
$month_filter = $_GET['month'] ?? '';
$year_filter = $_GET['year'] ?? '';
$years = range(date("Y"), date("Y") - 10);
$where = [];

if ($search) $where[] = "(g.platenumber LIKE '%$search%' OR g.fullname LIKE '%$search%')";
if ($type_filter !== "all") $where[] = "g.vehicletype = '$type_filter'";
if ($month_filter) $where[] = "MONTH(g.due_date) = '$month_filter'";
if ($year_filter) $where[] = "YEAR(g.due_date) = '$year_filter'";
$where_clause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM tblgenerate g $where_clause ORDER BY g.id DESC";
$result = $conn->query($sql);

// HANDLE DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_generate'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM tblgenerate WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $response = "✅ Record deleted successfully.";
    } else {
        $response = "❌ Failed to delete record.";
    }
    $stmt->close();
}

// HANDLE EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_generate'])) {
    $id = intval($_POST['edit_id']);
    $fullname = $_POST['edit_fullname'];
    $vehicletype = $_POST['edit_vehicletype'];
    $platenumber = $_POST['edit_platenumber'];
    $amount = $_POST['edit_amount'];
    $due_date = $_POST['edit_due_date'];
    $stmt = $conn->prepare("UPDATE tblgenerate SET fullname=?, vehicletype=?, platenumber=?, amount=?, due_date=? WHERE id=?");
    $stmt->bind_param("sssssi", $fullname, $vehicletype, $platenumber, $amount, $due_date, $id);
    if ($stmt->execute()) {
        $response = "✅ Record updated successfully.";
    } else {
        $response = "❌ Failed to update record.";
    }
    $stmt->close();
    // If AJAX, exit
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo 'success';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Payment Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #1a73e8;
            --light-blue: #e8f0fe;
            --dark-blue: #0d47a1;
            --white: #ffffff;
            --border-radius: 0;
        }
        
        body {
            background-color: #f5f9ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .main-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            border: 1px solid #e0e0e0;
        }
        
        .header-title {
            color: var(--primary-blue);
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
        }
        
        .btn-outline-primary {
            color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .table-container {
            border-radius: var(--border-radius);
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }
        
        .table-responsive {
            max-height: 65vh;
            overflow-y: auto;
        }
        
        .table {
            margin-bottom: 0;
            font-size: 14px;
        }
        
        .table th {
            background-color: var(--primary-blue);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
            text-align: center;
            vertical-align: middle;
        }
        
        .table td {
            vertical-align: middle;
            padding: 10px 12px;
        }
        
        .table tr:nth-child(even) {
            background-color: var(--light-blue);
        }
        
        .table tr:hover {
            background-color: #e3f2fd;
        }
        
        .badge {
            font-size: 12px;
            padding: 5px 8px;
        }
        
        .form-control, .form-select {
            padding: 8px 12px;
            border-radius: 0;
        }
        
        .alert {
            border-radius: var(--border-radius);
        }
        
        .action-btn {
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 0;
        }
        
        .modal-header {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .delete-modal .modal-content {
            border: 2px solid #e53935;
        }
        
        .delete-modal .modal-header {
            background-color: #e53935;
        }
        
        .filter-section {
            background-color: var(--light-blue);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .amount-cell {
            font-weight: 600;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="header-title">
                <i class="bi bi-receipt me-2"></i>Vehicle Payment 
            </h3>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#generateOneByOne">
                    <i class="bi bi-plus-circle me-1"></i>Generate One By One
                </button>
               <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
                    <i class="bi bi-collection me-1"></i>Generate All
                </button> ---->
            </div>
        </div>

        <?php if ($response): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?= $response ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search plate or owner..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="all">All Types</option>
                        <?php while ($row = $type_result->fetch_assoc()): ?>
                            <option value="<?= $row['vehicletype'] ?>" <?= $type_filter == $row['vehicletype'] ? 'selected' : '' ?>>
                                <?= $row['vehicletype'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $month_filter == $m ? 'selected' : '' ?>>
                                <?= date('F', mktime(0,0,0,$m,10)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= $year_filter == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Report Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Owner</th>
                            <th>Plate</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Duration</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['platenumber']) ?></td>
                            <td><?= htmlspecialchars($row['vehicletype']) ?></td>
                            <td class="amount-cell">$<?= number_format($row['amount'], 2) ?></td>
                            <td><?= date("d M Y", strtotime($row['due_date'])) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['amount_type']) ?></span></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary action-btn" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger action-btn btn-delete" 
                                        data-id="<?= $row['id'] ?>" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-exclamation-circle fs-4 d-block mb-2"></i>
                                No records found
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Single Charge Modal -->
    <div class="modal" id="generateOneByOne" tabindex="-1" aria-labelledby="generateOneByOneLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Single Vehicle Charge</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Plate Number</label>
                                <input type="text" id="plate" class="form-control" placeholder="Enter plate number">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" id="searchBtn" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i>Search
                                </button>
                            </div>
                        </div>
                        <div id="vehicleInfo" class="mt-4 p-4 bg-light rounded shadow-sm border" style="display:none;">
                            <input type="hidden" name="plate" id="formPlate">
                            <input type="hidden" name="owner" id="formOwner">
                            <input type="hidden" name="type" id="formType">
                            <div class="row mb-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><strong>Plate:</strong></label>
                                    <div class="form-control bg-white" id="showPlate"></div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><strong>Owner:</strong></label>
                                    <div class="form-control bg-white" id="showOwner"></div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><strong>Type:</strong></label>
                                    <div class="form-control bg-white d-flex align-items-center justify-content-between">
                                        <span id="showType"></span>
                                        <span id="showTypeAmount" class="badge bg-primary ms-2" style="font-size:1rem;"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><strong>Due Amount:</strong></label>
                                    <div class="form-control bg-white">$<span id="showDue">0.00</span></div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><strong>Duration:</strong></label>
                                    <input type="text" class="form-control bg-white" value="6 bilood" readonly disabled>
                                    <input type="hidden" name="duration" value="6 bilood">
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="form-label"><strong>Amount to Charge:</strong></label>
                                    <input type="number" name="amount" step="0.01" class="form-control" id="amountToCharge" placeholder="Enter amount" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="charge" class="btn btn-success" id="chargeBtn" disabled>
                            <i class="bi bi-check-circle me-1"></i>Confirm Charge
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal delete-modal" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="delete_generate" value="1">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                        <h5 class="my-3">Are you sure you want to delete this record?</h5>
                        <p class="text-muted">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Generate Modal -->
    <div class="modal" id="bulkGenerateModal" tabindex="-1" aria-labelledby="bulkGenerateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-collection me-2"></i>Bulk Generate Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Vehicle Type</label>
                                <select name="vehicletype" class="form-select" required>
                                    <option value="all">All Vehicles</option>
                                    <?php while ($row = $types->fetch_assoc()): ?>
                                        <option value="<?= $row['name'] ?>"><?= $row['name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Duration</label>
                                <select name="duration" class="form-select" required disabled>
                                    <option value="6" selected>6 Months</option>
                                </select>
                                <input type="hidden" name="duration" value="6">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Due Date</label>
                                <input type="datetime-local" name="due_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="register_payment" class="btn btn-primary">
                            <i class="bi bi-lightning-charge me-1"></i>Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="POST">
                    <input type="hidden" name="edit_generate" value="1">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Payment Record</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Owner</label>
                                <input type="text" name="edit_fullname" id="edit_fullname" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Plate Number</label>
                                <input type="text" name="edit_platenumber" id="edit_platenumber" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Type</label>
                                <input type="text" name="edit_vehicletype" id="edit_vehicletype" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="edit_amount" id="edit_amount" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Due Date</label>
                                <input type="datetime-local" name="edit_due_date" id="edit_due_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Delete confirmation
        $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            $('#delete_id').val(id);
            $('#deleteConfirmModal').modal('show');
        });

        // Fix: Submit delete form via AJAX for immediate effect
        $('#deleteConfirmModal form').off('submit').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: '',
                method: 'POST',
                data: form.serialize(),
                success: function() {
                    $('#deleteConfirmModal').modal('hide');
                    setTimeout(function() { location.reload(); }, 500);
                }
            });
        });

        // Vehicle search
        $('#searchBtn').on('click', function() {
            let plate = $('#plate').val();
            if (!plate) {
                alert('Please enter a plate number');
                return;
            }
            $.ajax({
                url: '?ajax=1',
                method: 'GET',
                data: { plate: plate },
                success: function(res) {
                    console.log("AJAX response:", res);
                    let data;
                    try {
                        data = JSON.parse(res);
                    } catch (e) {
                        $('#vehicleInfo').hide();
                        $('#chargeBtn').prop('disabled', true);
                        $('#vehicleInfo').before('<div class="alert alert-danger">Invalid JSON returned from server: ' + res + '</div>');
                        return;
                    }
                    $('.alert-danger').remove(); // Remove previous errors
                    if (data.error) {
                        $('#vehicleInfo').hide();
                        $('#chargeBtn').prop('disabled', true);
                        $('#vehicleInfo').before('<div class="alert alert-danger"><b>Error:</b> ' + data.error + (data.step ? ' <br><small>Step: ' + data.step + '</small>' : '') + '</div>');
                    } else if (data.plate) {
                        $('#vehicleInfo').show();
                        $('#showPlate').text(data.plate);
                        $('#showOwner').text(data.owner);
                        $('#showType').text(data.type);
                        $('#showDue').text(data.amount_due);
                        $('#formPlate').val(data.plate);
                        $('#formOwner').val(data.owner);
                        $('#formType').val(data.type);
                        // Fetch type amount
                        $.ajax({
                            url: 'get_amount.php',
                            method: 'GET',
                            data: { type: data.type },
                            success: function(res2) {
                                let amountData = JSON.parse(res2);
                                if (amountData.amount) {
                                    $('#showTypeAmount').text('$' + amountData.amount + ' / 6mo');
                                    $('#amountToCharge').val(amountData.amount);
                                    $('#showDue').text(amountData.amount);
                                } else {
                                    $('#showTypeAmount').text('N/A');
                                    $('#amountToCharge').val('');
                                    $('#showDue').text('0.00');
                                }
                            }
                        });
                        $('#chargeBtn').prop('disabled', false);
                    } else {
                        $('#vehicleInfo').hide();
                        $('#chargeBtn').prop('disabled', true);
                        $('#vehicleInfo').before('<div class="alert alert-danger">Unknown error occurred.</div>');
                    }
                }
            });
        });
        // Clear form when modal is closed
        $('#generateOneByOne').on('hidden.bs.modal', function() {
            $('#plate').val('');
            $('#vehicleInfo').hide();
            $('#chargeBtn').prop('disabled', true);
            $('#showTypeAmount').text('');
            $('#amountToCharge').val('');
            $('#showType').text('');
            $('#formType').val('');
            $('#showDue').text('0.00');
        });

        // Edit modal trigger
        $('.action-btn[title="Edit"]').on('click', function(e) {
            e.preventDefault();
            var row = $(this).closest('tr');
            var id = $(this).attr('href').split('=')[1];
            var fullname = row.find('td').eq(1).text().trim();
            var platenumber = row.find('td').eq(2).text().trim();
            var vehicletype = row.find('td').eq(3).text().trim();
            var amount = row.find('td').eq(4).text().replace(/[^\d.\-]/g, '');
            var due_date = row.find('td').eq(5).text().trim();
            // Convert due_date to yyyy-MM-ddTHH:mm for input
            var d = new Date(due_date);
            var local = d.toISOString().slice(0,16);
            $('#edit_id').val(id);
            $('#edit_fullname').val(fullname);
            $('#edit_platenumber').val(platenumber);
            $('#edit_vehicletype').val(vehicletype);
            $('#edit_amount').val(amount);
            $('#edit_due_date').val(local);
            $('#editModal').modal('show');
        });
        // Edit form submit
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: '',
                method: 'POST',
                data: form.serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(res) {
                    if (res === 'success') {
                        $('#editModal').modal('hide');
                        setTimeout(function() { location.reload(); }, 500);
                    } else {
                        alert('Failed to update record.');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>