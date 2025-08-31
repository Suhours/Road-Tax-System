<?php


include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";

$vehicle = null;
$total_paid = 0;
$total_charged = 0;
$balance = 0;
$last_payment = null;
$last_charge = null;
$days_since_last_payment = null;
$charge_data = [];
$payment_data = [];

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM vehiclemanagement WHERE platenumber LIKE CONCAT('%', ?, '%') OR owner LIKE CONCAT('%', ?, '%') LIMIT 1");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $vehicle_result = $stmt->get_result();

    if ($vehicle_result && $vehicle_result->num_rows > 0) {
        $vehicle = $vehicle_result->fetch_assoc();
        $plate = $vehicle['platenumber'];

        // Get all charges
        $stmt_charge = $conn->prepare("SELECT id, amount, due_date, status, amount_type FROM tblgenerate WHERE platenumber = ? ORDER BY due_date DESC");
        $stmt_charge->bind_param("s", $plate);
        $stmt_charge->execute();
        $charged_result = $stmt_charge->get_result();

        while ($row = $charged_result->fetch_assoc()) {
            $total_charged += floatval($row['amount']);
            $charge_data[] = $row;
            if (!$last_charge) $last_charge = $row;
        }

        // Get all payments
        $stmt_payment = $conn->prepare("SELECT r.id, r.amount, r.due_date, r.status, r.serial_num, r.receipt_num, r.description, c.center_name FROM tbl_reciept r LEFT JOIN centers c ON r.center_id = c.id WHERE r.plate_number = ? ORDER BY r.due_date DESC");
        $stmt_payment->bind_param("s", $plate);
        $stmt_payment->execute();
        $payment_result = $stmt_payment->get_result();

        while ($row = $payment_result->fetch_assoc()) {
            $total_paid += floatval($row['amount']);
            $payment_data[] = $row;

            if (!$last_payment && !empty($row['due_date'])) {
                $last_payment = $row;
                try {
                    $payment_date = new DateTime($last_payment['due_date']);
                    $today = new DateTime();
                    $days_since_last_payment = $today->diff($payment_date)->format("%a");
                } catch (Exception $e) {
                    $days_since_last_payment = null;
                }
            }
        }

        $balance = $total_charged - $total_paid;
        // If total charged is 0, balance and total paid should be 0
        if ($total_charged == 0) {
            $total_paid = 0.00;
            $balance = 0.00;
        } else if (abs($balance) < 0.01) {
            // Fix floating point precision: treat very small balances as zero
            $balance = 0.00;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vehicle Statement</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #2563eb;  /* Modern blue */
      --secondary-color: #fff;
      --accent-color: #ef4444;   /* Modern red */
      --success-color: #22c55e;
      --danger-color: #ef4444;
      --warning-color: #f59e42;
      --light-color: #f3f4f6;
      --dark-color: #1e293b;
      --border-radius: 0.75rem;
      --shadow: 0 2px 16px 0 rgba(30,41,59,0.08);
    }
    body {
      background-color: var(--light-color);
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--dark-color);
    }
    .card {
      border-radius: var(--border-radius);
      border: none;
      background-color: var(--secondary-color);
      box-shadow: var(--shadow);
    }
    .card-header {
      border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
      background: linear-gradient(135deg, var(--primary-color), #3b82f6);
      color: var(--secondary-color);
      font-weight: 600;
      letter-spacing: 0.5px;
    }
    .card-title, .modal-title {
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .text-primary { color: var(--primary-color) !important; }
    .text-danger { color: var(--accent-color) !important; }
    .text-success { color: var(--success-color) !important; }
    .text-warning { color: var(--warning-color) !important; }
    .text-info { color: #0ea5e9 !important; }
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      font-weight: 600;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
    }
    .btn-primary:hover {
      background-color: #1d4ed8;
      border-color: #1d4ed8;
    }
    .btn-danger {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
      font-weight: 600;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
    }
    .btn-danger:hover {
      background-color: #b91c1c;
      border-color: #b91c1c;
    }
    .form-control {
      border-radius: var(--border-radius);
      border: 1.5px solid #e5e7eb;
      font-size: 1.1rem;
      padding: 0.75rem 1.25rem;
      background: #f9fafb;
    }
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.15rem rgba(37,99,235,0.15);
      background: #fff;
    }
    .badge {
      padding: 0.5em 0.9em;
      font-weight: 600;
      font-size: 0.95em;
      border-radius: 0.5em;
      letter-spacing: 0.2px;
    }
    .balance-display {
      font-size: 1.7rem;
      font-weight: 700;
      padding: 1.2rem;
      border-radius: var(--border-radius);
      background-color: var(--secondary-color);
      box-shadow: var(--shadow);
      margin-bottom: 0.5rem;
    }
    .list-group-item {
      border-left: 4px solid transparent;
      background-color: var(--secondary-color);
      border-radius: var(--border-radius);
    }
    .modal-header {
      background: linear-gradient(135deg, var(--primary-color), #3b82f6);
      color: var(--secondary-color);
      border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
    }
    .search-box {
      position: relative;
    }
    .search-icon {
      position: absolute;
      top: 50%;
      right: 18px;
      transform: translateY(-50%);
      color: var(--primary-color);
      font-size: 1.3em;
      opacity: 0.7;
    }
    .table {
      border-radius: var(--border-radius);
      overflow: hidden;
      background: #fff;
      margin-bottom: 0;
    }
    .table th, .table td {
      vertical-align: middle;
      border-color: #e5e7eb !important;
      font-size: 1.05em;
    }
    .table thead th {
      background: #f1f5f9;
      color: var(--dark-color);
      font-weight: 700;
      border-bottom: 2px solid #e5e7eb !important;
    }
    .table-bordered {
      border-radius: var(--border-radius);
      border: 1.5px solid #e5e7eb;
    }
    .table-hover tbody tr:hover {
      background: #f3f4f6;
    }
    .alert-warning {
      background-color: #fef9c3;
      border-color: #fde68a;
      color: #b45309;
      border-radius: var(--border-radius);
      font-size: 1.1em;
    }
    .alert-success {
      background-color: #d1fae5;
      border-color: #6ee7b7;
      color: #065f46;
      border-radius: var(--border-radius);
      font-size: 1.1em;
    }
    .patriotic-header {
      background: linear-gradient(90deg, var(--accent-color), var(--secondary-color), var(--primary-color));
      color: var(--dark-color);
      padding: 1.2rem;
      border-radius: var(--border-radius);
      margin-bottom: 2rem;
      text-align: center;
      font-weight: 700;
      font-size: 2rem;
      letter-spacing: 1px;
    }
    .patriotic-stripe {
      height: 6px;
      background: linear-gradient(90deg, var(--accent-color), var(--secondary-color), var(--primary-color));
      margin: 1.2rem 0;
      border-radius: var(--border-radius);
    }
    .flag-icon {
      color: var(--accent-color);
      margin: 0 5px;
    }
    .modal-xl, .modal-xxl {
      max-width: 98vw !important;
    }
    .modal-content {
      background: #f4f9ff !important;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
    }
    .details-modal-content {
      border-radius: var(--border-radius);
      background: linear-gradient(120deg, #f4f9ff 80%, #e3f0ff 100%);
      border: 1.5px solid #b3d7ff;
      padding: 2.5rem 2.5rem 0 2.5rem;
      min-height: 85vh;
      max-width: 1200px;
      margin: 0 auto;
      box-shadow: var(--shadow);
    }
    .details-modal-header {
      background: linear-gradient(90deg,#2563eb,#3b82f6); 
      color: #fff;
      border-top-left-radius: var(--border-radius);
      border-top-right-radius: var(--border-radius);
      border-bottom: 1.5px solid #b3d7ff;
    }
    .details-modal-footer {
      background: #e3f0ff;
      border-bottom-left-radius: var(--border-radius);
      border-bottom-right-radius: var(--border-radius);
      border-top: 1.5px solid #b3d7ff;
    }
    .details-balance-badge {
      background: #2563eb; font-size: 1.2em; padding: .7em 1.5em; border-radius: 0.5em;
    }
    .details-section {
      background: linear-gradient(90deg,#e3f0ff,#f8fbff); border: 1px solid #b3d7ff;
      border-radius: var(--border-radius);
    }
    .modal-header, .modal-footer {
      border: none !important;
    }
    .table-primary th, .table-primary td, .table-info th, .table-info td {
      border-color: #b3d7ff !important;
    }
    .badge.bg-warning {
      background: #ffe066 !important;
      color: #333 !important;
    }
    .badge.bg-success {
      background: #22c55e !important;
      color: #fff !important;
    }
    .badge.bg-primary, .badge.bg-info {
      background: #2563eb !important;
      color: #fff !important;
    }
    .badge.bg-danger {
      background: #ef4444 !important;
      color: #fff !important;
    }
    .modal-header .btn-close {
      filter: invert(1) grayscale(1) brightness(2);
      opacity: 0.8;
    }
    .modal-header .btn-close:hover {
      opacity: 1;
    }
    .d-grid .btn {
      border-radius: var(--border-radius);
      font-weight: 600;
    }
    .table-responsive {
      border-radius: var(--border-radius);
      overflow: auto;
      box-shadow: none;
    }
    @media (max-width: 768px) {
      .balance-display {
        font-size: 1.1rem;
        padding: 0.7rem;
      }
      .details-modal-content {
        padding: 1rem 0.5rem 0 0.5rem;
      }
      .card-header, .modal-header {
        font-size: 1.1rem;
      }
    }
    .fw-bold { font-weight: 700 !important; }
    .fs-5 { font-size: 1.25rem !important; }
    .fs-3 { font-size: 2rem !important; }
    .rounded-0 { border-radius: 0 !important; }
    .shadow-sm { box-shadow: 0 1px 4px 0 rgba(30,41,59,0.06) !important; }
    .gap-2 { gap: 0.5rem !important; }
    .gap-4 { gap: 1.5rem !important; }
    .mb-0 { margin-bottom: 0 !important; }
    .mb-3 { margin-bottom: 1rem !important; }
    .mb-4 { margin-bottom: 2rem !important; }
    .mt-3 { margin-top: 1rem !important; }
    .mt-4 { margin-top: 2rem !important; }
    .py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
    .py-5 { padding-top: 3rem !important; padding-bottom: 3rem !important; }
    .px-4 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
    .p-3 { padding: 1rem !important; }
    .p-4 { padding: 2rem !important; }
    .h-100 { height: 100% !important; }
    .w-100 { width: 100% !important; }
    .text-center { text-align: center !important; }
    .text-muted { color: #6b7280 !important; }
    .text-dark { color: var(--dark-color) !important; }
    .text-white { color: #fff !important; }
    .align-middle { vertical-align: middle !important; }
    .d-flex { display: flex !important; }
    .justify-content-center { justify-content: center !important; }
    .justify-content-end { justify-content: flex-end !important; }
    .align-items-center { align-items: center !important; }
    .col-md-6, .col-md-12, .col-lg-10 { border-radius: var(--border-radius); }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card border-0">
        <div class="card-header py-3">
          <h2 class="text-center text-white mb-0">
              <i class="fas fa-car me-2"></i> Vehicle Payment Statement
            <div class="patriotic-stripe"></div>
            </h2>
        </div>
        
        <div class="card-body">
          <form method="get" class="row g-2 justify-content-center mb-4">
            <div class="col-md-8 search-box">
              <input type="text" name="search" class="form-control form-control-lg" 
                     placeholder="Search by plate number or owner name..." 
                     value="<?= htmlspecialchars($search) ?>" required>
                    <i class="fas fa-search search-icon"></i>
                  </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-search me-1"></i> Search
                  </button>
                </div>
              </form>
          
          <?php if ($vehicle): ?>
              <div class="row mb-4">
                <div class="col-md-6 mb-3">
                  <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                      <h5 class="card-title mb-0"><i class="fas fa-car me-2"></i> Vehicle Information</h5>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-borderless">
                          <tbody>
                            <tr>
                            <th>Plate</th>
                            <td><?= isset($vehicle['platenumber']) ? htmlspecialchars($vehicle['platenumber']) : '<span class="text-muted">N/A</span>' ?></td>
                            </tr>
                            <tr>
                            <th>Owner</th>
                            <td><?= isset($vehicle['owner']) ? htmlspecialchars($vehicle['owner']) : '<span class="text-muted">N/A</span>' ?></td>
                            </tr>
                         
                            <tr>
                            <th>Vehicle Type</th>
                            <td><?= isset($vehicle['vehicletype']) ? htmlspecialchars($vehicle['vehicletype']) : '<span class="text-muted">N/A</span>' ?></td>
                            </tr>
                            <tr>
                            <th>Description</th>
                            <td><?= isset($vehicle['description']) ? htmlspecialchars($vehicle['description']) : '<span class="text-muted">N/A</span>' ?></td>
                            </tr>
                          </tbody>
                        </table>
                    </div>
                
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                <div class="card h-100 border-danger">
                  <div class="card-header bg-danger text-white">
                      <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i> Payment Summary</h5>
                    </div>
                    <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-borderless">
                        <tbody>
                          <tr>
                            <th class="text-nowrap"><i class="fas fa-money-bill-wave me-2 text-danger"></i>Total Charged:</th>
                            <td class="text-danger fw-bold">USD <?= number_format($total_charged, 2) ?></td>
                          </tr>
                          <tr>
                            <th class="text-nowrap"><i class="fas fa-hand-holding-usd me-2 text-success"></i>Total Paid:</th>
                            <td class="text-success fw-bold">USD <?= number_format($total_paid, 2) ?></td>
                          </tr>
                          <tr>
                            <th class="text-nowrap"><i class="fas fa-balance-scale me-2 text-warning"></i>Remaining Balance:</th>
                            <td class="text-danger fw-bold">USD <?= number_format($balance, 2) ?></td>
                          </tr>
                          <?php if ($last_charge): ?>
                          <tr>
                            <th class="text-nowrap"><i class="fas fa-calendar-plus me-2 text-info"></i>Last Charge:</th>
                            <td>
                              USD <?= number_format($last_charge['amount'], 2) ?> 
                              on <?= date("M j, Y", strtotime($last_charge['due_date'])) ?>
                            </td>
                          </tr>
                          <?php endif; ?>
                          <?php if ($last_payment): ?>
                          <tr>
                            <th class="text-nowrap"><i class="fas fa-calendar-check me-2 text-success"></i>Last Payment:</th>
                            <td>
                              USD <?= number_format($last_payment['amount'], 2) ?> 
                              on <?= date("M j, Y", strtotime($last_payment['due_date'])) ?>
                              <?php if ($days_since_last_payment): ?>
                                <span class="badge bg-secondary status-badge ms-2">
                                  <?= $days_since_last_payment ?> days ago
                                </span>
                              <?php endif; ?>
                            </td>
                          </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="d-grid mt-3">
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal">
                          <i class="fas fa-file-invoice-dollar me-1"></i> View Detailed Statement
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
            <!-- Balance Summary Card -->
            <div class="card mb-4 border-danger">
                <div class="card-body text-center">
                <h4 class="card-title text-dark mb-3">
                  <i class="fas fa-info-circle me-2 text-danger"></i>Account Status
                  </h4>
                  <div class="balance-display <?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
                    <?php if ($balance > 0): ?>
                      <i class="fas fa-exclamation-triangle me-2"></i> Outstanding Balance: USD <?= number_format($balance, 2) ?>
                    <?php else: ?>
                      <i class="fas fa-check-circle me-2"></i> Account is Fully Paid
                    <?php endif; ?>
                  </div>
                  <?php if ($balance > 0 && $last_payment): ?>
                  <div class="mt-3">
                    <span class="badge bg-<?= $days_since_last_payment > 30 ? 'danger' : 'warning' ?>">
                      <i class="fas fa-clock me-1"></i>
                      <?= $days_since_last_payment ?> days since last payment
                    </span>
                  </div>
                  <?php endif; ?>
              </div>
            </div>
            
            <!-- Modal -->
            <div class="modal" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewModalLabel">
                      <i class="fas fa-file-alt me-2"></i> Detailed Payment & Receipt Breakdown
                    </h5>
                    <button type="button" class="btn btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  
                  </div>
                  <div class="modal-body">
                    <div class="patriotic-stripe mb-3"></div>
                    <div class="row">
                      <div class="col-md-12 mb-4">
                        <h6 class="mb-3 text-danger"><i class="fas fa-calendar-times me-2"></i> Generated Charges</h6>
                        <div class="table-responsive">
                          <table class="table table-bordered table-hover align-middle">
                            <thead class="table-danger">
                              <tr>
                                <th>#</th>
                                <th>ID</th>
                                <th>Month/Period</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Amount Type</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php if (count($charge_data) > 0): $i=1; foreach ($charge_data as $row): ?>
                              <tr>
                                <td><?= $i++ ?></td>
                                <td><?= isset($row['id']) ? $row['id'] : '<span class="text-muted">N/A</span>' ?></td>
                                <td><?= date('F Y', strtotime($row['due_date'])) ?></td>
                                <td class="fw-bold text-danger">USD <?= number_format($row['amount'], 2) ?></td>
                                <td><span class="badge bg-<?= ($row['status'] ?? 'pending') === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($row['status'] ?? 'Pending') ?></span></td>
                                <td><?= date('M j, Y', strtotime($row['due_date'])) ?></td>
                                <td><?= isset($row['amount_type']) ? htmlspecialchars($row['amount_type']) : '<span class="text-muted">N/A</span>' ?></td>
                              </tr>
                              <?php endforeach; else: ?>
                              <tr><td colspan="7" class="text-center text-muted">No charges generated for this vehicle.</td></tr>
                              <?php endif; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <div class="col-md-12 mb-4">
                        <h6 class="mb-3 text-success"><i class="fas fa-calendar-check me-2"></i> Receipts / Payments</h6>
                        <div class="table-responsive">
                          <table class="table table-bordered table-hover align-middle">
                            <thead class="table-success">
                              <tr>
                                <th>#</th>
                                <th>Receipt</th>
                                <th>Serial</th>
                                <th>Receipt </th>
                                <th>Description</th>
                                <th>Center</th>
                                <th>Amount</th>
                                <th>Paid Date</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php if (count($payment_data) > 0): $i=1; foreach ($payment_data as $row): ?>
                              <tr>
                                <td><?= $i++ ?></td>
                                <td><?= isset($row['id']) ? $row['id'] : '<span class="text-muted">N/A</span>' ?></td>
                                <td><?= isset($row['serial_num']) ? htmlspecialchars($row['serial_num']) : '<span class="text-muted">N/A</span>' ?></td>
                                <td><?= isset($row['receipt_num']) ? htmlspecialchars($row['receipt_num']) : '<span class="text-muted">N/A</span>' ?></td>
                                <td><?= isset($row['description']) ? htmlspecialchars($row['description']) : '<span class="text-muted">N/A</span>' ?></td>
                                <td><?= isset($row['center_name']) ? htmlspecialchars($row['center_name']) : '<span class="text-muted">N/A</span>' ?></td>
                                <td class="fw-bold text-success">USD <?= number_format($row['amount'], 2) ?></td>
                                <td><?= isset($row['due_date']) ? date('M j, Y', strtotime($row['due_date'])) : '<span class="text-muted">N/A</span>' ?></td>
                                <td><span class="badge bg-<?= ($row['status'] ?? 'On Time') === 'On Time' ? 'success' : 'warning' ?>"><?= htmlspecialchars($row['status'] ?? 'On Time') ?></span></td>
                              </tr>
                              <?php endforeach; else: ?>
                              <tr><td colspan="9" class="text-center text-muted">No receipts/payments found for this vehicle.</td></tr>
                              <?php endif; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="patriotic-stripe mb-3"></div>
                    <div class="text-center">
                      <h4 class="fw-bold mb-0">
                        Remaining Balance:
                        <span class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
                          USD <?= number_format($balance, 2) ?>
                        </span>
                      </h4>
                      <?php if ($balance > 0): ?>
                        <div class="alert alert-warning mt-3 mb-0">
                          <i class="fas fa-exclamation-triangle me-2"></i> This account has an outstanding balance
                        </div>
                        <?php else: ?>
                        <div class="alert alert-success mt-3 mb-0">
                          <i class="fas fa-check-circle me-2"></i> This account is fully paid
                        </div>
                        <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Details Modal -->
            <div class="modal" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xxl modal-dialog-centered" style="max-width:1300px;">
                <div class="modal-content details-modal-content">
                  <div class="modal-header details-modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-primary p-3 fs-5"><i class="fas fa-info-circle me-2"></i></span>
                      <h5 class="modal-title mb-0" id="detailsModalLabel">
                        Vehicle Full Details
                      </h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                      <button type="button" class="btn-close btn-close-white fs-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                  </div>
                  <div class="modal-body p-4 bg-light">
                    <div class="mb-4 d-flex justify-content-end gap-2">
                      <button class="btn btn-success" id="exportExcelBtn"><i class="fas fa-file-excel me-1"></i> Export Excel</button>
                      <button class="btn btn-danger" id="exportPdfBtn"><i class="fas fa-file-pdf me-1"></i> Export PDF</button>
                    </div>
                    <div class="row mb-4 g-4">
                      <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                          <div class="card-header bg-primary text-white rounded-0">
                            <h5 class="card-title mb-0"><i class="fas fa-car me-2"></i> Vehicle Info</h5>
                          </div>
                          <div class="card-body">
                            <table class="table table-bordered table-striped mb-0" id="vehicleInfoTable">
                              <tbody>
                                <tr><th>Owner</th><td><?= htmlspecialchars($vehicle['owner']) ?></td></tr>
                                <tr><th>Phone</th><td><?= isset($vehicle['phone']) ? htmlspecialchars($vehicle['phone']) : '<span class="text-muted">N/A</span>' ?></td></tr>
                                <tr><th>Vehicle Type</th><td><?= htmlspecialchars($vehicle['vehicletype']) ?></td></tr>
                                <tr><th>Description</th><td><?= isset($vehicle['description']) ? htmlspecialchars($vehicle['description']) : '<span class="text-muted">N/A</span>' ?></td></tr>
                                <tr><th>Registration Date</th><td><?= isset($vehicle['registration_date']) ? htmlspecialchars($vehicle['registration_date']) : '<span class="text-muted">N/A</span>' ?></td></tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                          <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                          <div class="card-header bg-info text-white rounded-0">
                            <h5 class="card-title mb-0"><i class="fas fa-balance-scale me-2"></i> Account Status</h5>
                              </div>
                              <div class="card-body">
                            <?php if ($balance > 0): ?>
                              <span class="badge details-balance-badge">Outstanding Balance: <span class="fw-bold">USD <?= number_format($balance,2) ?></span></span>
                            <?php else: ?>
                              <span class="badge details-balance-badge">Balance: <span class="fw-bold">0</span></span>
                            <?php endif; ?>
                            <div class="mt-4">
                              <h6 class="mb-2 text-primary">Latest Unpaid Charges</h6>
                              <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0" id="unpaidChargesTable">
                                  <thead class="table-primary">
                                    <tr>
                                      <th>#</th>
                                      <th>Month/Period</th>
                                      <th>Amount</th>
                                      <th>Status</th>
                                      <th>Due Date</th>
                                      <th>Amount Type</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php
                                    $unpaid = array_filter($charge_data, function($c) {
                                      return !isset($c['status']) || strtolower($c['status']) !== 'completed';
                                    });
                                    if (count($unpaid) > 0): $i=1; foreach ($unpaid as $row): ?>
                                    <tr>
                                      <td><?= $i++ ?></td>
                                      <td><?= date('F Y', strtotime($row['due_date'])) ?></td>
                                      <td class="fw-bold text-danger">USD <?= number_format($row['amount'], 2) ?></td>
                                      <td><span class="badge bg-warning text-dark">Unpaid</span></td>
                                      <td><?= date('M j, Y', strtotime($row['due_date'])) ?></td>
                                      <td><?= isset($row['amount_type']) ? htmlspecialchars($row['amount_type']) : '<span class="text-muted">N/A</span>' ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No unpaid charges.</td></tr>
                                    <?php endif; ?>
                                  </tbody>
                                </table>
                              </div>
                            </div>
                          </div>
                                </div>
                              </div>
                            </div>
                    <div class="row mt-4 g-4">
                      <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                          <div class="card-header bg-primary text-white rounded-0">
                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Recent Charges</h6>
                              </div>
                              <div class="card-body">
                                <div class="table-responsive">
                              <table class="table table-bordered table-hover align-middle mb-0" id="recentChargesTable">
                                <thead class="table-info">
                                  <tr>
                                    <th>#</th>
                                    <th>Month/Period</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Amount Type</th>
                                      </tr>
                                </thead>
                                <tbody>
                                  <?php if (count($charge_data) > 0): $i=1; foreach (array_slice($charge_data,0,5) as $row): ?>
                                  <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= date('F Y', strtotime($row['due_date'])) ?></td>
                                    <td class="fw-bold text-primary">USD <?= number_format($row['amount'], 2) ?></td>
                                    <td><span class="badge bg-<?= (isset($row['status']) && strtolower($row['status']) === 'completed') ? 'success' : 'warning' ?>"><?= ucfirst($row['status'] ?? 'Pending') ?></span></td>
                                    <td><?= date('M j, Y', strtotime($row['due_date'])) ?></td>
                                    <td><?= isset($row['amount_type']) ? htmlspecialchars($row['amount_type']) : '<span class="text-muted">N/A</span>' ?></td>
                                      </tr>
                                  <?php endforeach; else: ?>
                                  <tr><td colspan="6" class="text-center text-muted">No charges found.</td></tr>
                                  <?php endif; ?>
                                    </tbody>
                                  </table>
                            </div>
                          </div>
                        </div>
                      </div>
                          <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                          <div class="card-header bg-info text-white rounded-0">
                            <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Recent Receipts / Payments</h6>
                              </div>
                              <div class="card-body">
                                <div class="table-responsive">
                              <table class="table table-bordered table-hover align-middle mb-0" id="recentReceiptsTable">
                                <thead class="table-info">
                                  <tr>
                                    <th>#</th>
                                    <th>Receipt ID</th>
                                    <th>Amount</th>
                                    <th>Paid Date</th>
                                    <th>Status</th>
                                      </tr>
                                </thead>
                                <tbody>
                                  <?php if (count($payment_data) > 0): $i=1; foreach (array_slice($payment_data,0,5) as $row): ?>
                                  <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= isset($row['id']) ? $row['id'] : '<span class="text-muted">N/A</span>' ?></td>
                                    <td class="fw-bold text-primary">USD <?= number_format($row['amount'], 2) ?></td>
                                    <td><?= isset($row['due_date']) ? date('M j, Y', strtotime($row['due_date'])) : '<span class="text-muted">N/A</span>' ?></td>
                                    <td><span class="badge bg-<?= ($row['status'] ?? 'On Time') === 'On Time' ? 'success' : 'warning' ?>"><?= htmlspecialchars($row['status'] ?? 'On Time') ?></span></td>
                                      </tr>
                                  <?php endforeach; else: ?>
                                  <tr><td colspan="5" class="text-center text-muted">No receipts/payments found for this vehicle.</td></tr>
                                      <?php endif; ?>
                                    </tbody>
                                  </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer details-modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                  </div>
                </div>
              </div>
            </div>
            
          <?php elseif (!empty($search)): ?>
            <div class="alert alert-warning text-center py-3 mt-3">
              <i class="fas fa-exclamation-triangle fa-2x mb-3 text-danger"></i>
              <h4 class="alert-heading">No Records Found</h4>
              <p>We couldn't find any vehicle matching "<strong><?= htmlspecialchars($search) ?></strong>"</p>
              <hr>
              <p class="mb-0">Please check the plate number or owner name and try again.</p>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="fas fa-car fa-4x text-primary mb-4"></i>
              <h3 class="text-primary">Vehicle Payment Statement</h3>
              <p class="text-muted">Search for a vehicle to view its payment history and balance</p>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="card-footer text-center text-muted py-3">
          <div class="patriotic-stripe"></div>
          <p class="mb-0">
            <i class="fas fa-flag flag-icon"></i>
           Vehicle Statement
            <i class="fas fa-flag flag-icon"></i>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var viewModal = document.getElementById('viewModal');
    if (viewModal) {
      viewModal.addEventListener('hidden.bs.modal', function (event) {
        event.preventDefault();
      });
    }
  });
</script>

<!-- Add required export libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/excellentexport@3.4.3/dist/excellentexport.min.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function() {
  // Focus on search input when page loads
  const searchInput = document.querySelector('input[name="search"]');
  if (searchInput) {
    searchInput.focus();
  }

  // Prevent scroll/jump on modal close
  var viewModal = document.getElementById('viewModal');
  if (viewModal) {
    viewModal.addEventListener('hidden.bs.modal', function (event) {
      // Prevent scroll restoration
      event.preventDefault();
      // Do not focus or scroll anywhere
    });
  }
});
</script>
</body>
</html>