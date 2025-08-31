<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<div style='background:#ffdddd;color:#a00;padding:10px;border:2px solid #a00;margin:10px 0;'><b>PHP Error:</b> [$errno] $errstr in $errfile on line $errline</div>";
    return false;
});
set_exception_handler(function($exception) {
    echo "<div style='background:#ffdddd;color:#a00;padding:10px;border:2px solid #a00;margin:10px 0;'><b>Uncaught Exception:</b> ", $exception->getMessage(), "<br><pre>", $exception->getTraceAsString(), "</pre></div>";
});

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit;
}
include '../db.php';

$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? 'All';
$month_filter = $_GET['month'] ?? '';
$year_filter = $_GET['year'] ?? '';
$years = range(date("Y"), date("Y") - 10);

$types_result = $conn->query("SELECT DISTINCT vehicle_type FROM tbl_reciept");
$vehicle_types = [];
while ($row = $types_result->fetch_assoc()) {
    $vehicle_types[] = $row['vehicle_type'];
}

$where = "WHERE (r.plate_number LIKE ? OR r.vehicle_type LIKE ?)";
$params = ["ss", "%$search%", "%$search%"];

if ($type_filter !== 'All') {
    $where .= " AND r.vehicle_type = ?";
    $params[0] .= "s";
    $params[] = $type_filter;
}
if (!empty($month_filter)) {
    $where .= " AND MONTH(r.due_date) = ?";
    $params[0] .= "i";
    $params[] = (int)$month_filter;
}
if (!empty($year_filter)) {
    $where .= " AND YEAR(r.due_date) = ?";
    $params[0] .= "i";
    $params[] = (int)$year_filter;
}

$sql = "SELECT r.*, c.center_name FROM tbl_reciept r 
        LEFT JOIN vehiclemanagement v ON r.plate_number = v.platenumber 
        LEFT JOIN centers c ON r.center_id = c.id
        $where ORDER BY r.due_date DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<div style='background:#ffdddd;color:#a00;padding:10px;border:2px solid #a00;margin:10px 0;'><b>SQL Prepare Error:</b> " . htmlspecialchars($conn->error) . "<br><b>Query:</b> " . htmlspecialchars($sql) . "</div>");
}
$stmt->bind_param(...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Receipt Payment Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
      font-family: "Segoe UI", sans-serif;
      background: #eaf3fb;
    }
    .wrapper {
      height: 100vh;
      overflow-y: auto;
      padding: 30px;
    }
    .title {
      color: #0d6efd;
      font-weight: bold;
    }
    .card-style {
      background-color: white;
      border-radius: 15px;
      padding: 30px;
      width: 100%;
      max-width: 1200px;
      margin-top: 20px;
    }
    .btn-export {
      border-radius: 22px;
      font-weight: 500;
      padding: 6px 16px;
      font-size: 0.98rem;
      border: none;
    }
    .btn-export.export-pdf {
      background: #f857a6;
      color: #fff;
    }
    .btn-export.export-excel {
      background: #43e97b;
      color: #fff;
    }
    .btn-export i {
      margin-right: 6px;
      font-size: 1em;
    }
    .form-control, .form-select {
      border-radius: 10px;
    }
    .filter-bar {
      background: #f1f9ff;
      padding: 15px;
      border-radius: 12px;
      margin-bottom: 20px;
    }
    .alert-info {
      background: #d0ebff;
      color: #084298;
      font-weight: bold;
    }
    table th {
      background-color: #007bff;
      color: white;
    }
    .table thead th, .table td {
      white-space: nowrap;
      vertical-align: middle;
    }
    .table-responsive {
      max-height: 50vh;
      overflow-y: auto;
    }
    .modal-dialog {
      max-width: 400px;
    }
    .modal-content {
      padding: 10px;
    }
    .total-records-badge {
      display: inline-block;
      background: #e0eafc;
      color: #0d6efd;
      font-weight: 600;
      border-radius: 50px;
      padding: 8px 28px;
      font-size: 1.08rem;
      margin-top: 10px;
      letter-spacing: 0.5px;
    }
    .total-records-badge i {
      margin-right: 7px;
      font-size: 1.1em;
    }
    @media print {
  /* Ogolow bogag badan */
  html, body {
    height: auto !important;
    overflow: visible !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Qaybta la rabo in la daabaco */
  .wrapper, .wrapper * {
    visibility: visible !important;
  }

  /* Disable qaybaha aan loo baahnayn */
  .d-flex.justify-content-end.mb-3,
  .filter-bar,
  .btn,
  .btn-export,
  form,
  .form-control,
  .form-select {
    display: none !important;
    visibility: hidden !important;
  }

  /* Card-ka ha noqdo mid buuxa oo aan lahayn margins */
  .card-style {
    box-shadow: none !important;
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 auto !important;
  }

  /* Table ha oggolaado bogag badan */
  .table-responsive {
    max-height: none !important;
    overflow: visible !important;
  }

  .table {
    page-break-inside: auto;
    width: 100%;
  }

  .table tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }

  /* Optional: Qurxin total badge markay print gareyso */
  .total-records-badge {
    margin-top: 20px !important;
    display: block !important;
    text-align: center !important;
    font-size: 1.2rem !important;
    font-weight: bold !important;
  }
}

  </style>
</head>
<body>

<div class="wrapper">
  <div class="container card-style">
    <h2 class="text-center mb-4 title">🧾 Receipt Payment Report</h2>

    <div class="d-flex justify-content-end mb-3">
      <button onclick="confirmAndRun('pdf')" class="btn btn-export me-2 export-pdf">
        <i class="bi bi-file-earmark-pdf"></i> PDF
      </button>
      <button onclick="confirmAndRun('excel')" class="btn btn-export me-2 export-excel">
        <i class="bi bi-file-earmark-excel"></i> Excel
      </button>
      <button onclick="printReport(event)" class="btn btn-export btn-primary">
        <i class="bi bi-printer"></i> Print
      </button>
    </div>

    <form method="GET" class="row g-3 filter-bar">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="🔍 Plate or Phone" value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select">
          <option value="All">🚘 All Types</option>
          <?php foreach ($vehicle_types as $type): ?>
            <option value="<?= $type ?>" <?= $type_filter == $type ? 'selected' : '' ?>><?= $type ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="month" class="form-select">
          <option value="">📅 Month</option>
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $month_filter == $m ? 'selected' : '' ?>>
              <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="year" class="form-select">
          <option value="">📆 Year</option>
          <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= $year_filter == $y ? 'selected' : '' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
      </div>
    </form>

 

    <div class="table-responsive" style="max-height: 50vh; overflow-y: auto;">
      <table id="receiptTable" class="table table-striped table-bordered text-center align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Plate</th>
            <th>Serial</th>
            <th>Receipt</th>
            <th>Owner</th>
            <th>Type</th>
            <th>Center</th>
            <th>RoadTax<br><small>(114526)</small></th>
            <th>Stamp<br><small>(115103)</small></th>
            <th>Admin<br><small>(142261)</small></th>
            <th>Due Date</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $i = 1; 
            $total_amount = 0;
            $total_stamp = 0;
            $total_admin = 0;
            while ($row = $result->fetch_assoc()): 
              $total_amount += $row['amount'];
              $total_stamp += $row['stamp'] ?? 0;
              $total_admin += $row['admin'] ?? 0;
          ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['serial_num']) ?></td>
              <td><?= htmlspecialchars($row['receipt_num']) ?></td>
              <td><?= htmlspecialchars($row['plate_number']) ?></td>
              <td><?= htmlspecialchars($row['owner']) ?></td>
              <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
              <td><?= htmlspecialchars($row['center_name'] ?? 'N/A') ?></td>
              <td class="text-success fw-bold">$<?= number_format($row['amount'], 2) ?></td>
              <td class="text-info fw-bold">$<?= number_format($row['stamp'] ?? 0, 2) ?></td>
              <td class="text-warning fw-bold">$<?= number_format($row['admin'] ?? 0, 2) ?></td>
              <td><?= date('d M Y', strtotime($row['due_date'])) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
     
    </div>
    <div class="d-flex justify-content-center mt-3">
        <div class="row text-center">
          <div class="col-md-4">
            <span class="total-records-badge">
              <i class=""></i> RoadTax $<?= number_format($total_amount, 2) ?>
            </span>
          </div>
          <div class="col-md-4">
            <span class="total-records-badge">
              <i class="bi bi-stamp"></i> Stamp: $<?= number_format($total_stamp, 2) ?>
            </span>
          </div>
          <div class="col-md-4">
            <span class="total-records-badge">
              <i class="bi bi-person-shield"></i> Admin: $<?= number_format($total_admin, 2) ?>
            </span>
          </div>
        </div>
      </div>
  </div>
</div>

<script>
function confirmAndRun(type) {
  if (confirm("Do you want to download the report?")) {
    if (type === 'pdf') downloadPDF();
    else if (type === 'excel') downloadExcel();
  }
}

function downloadPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.text("Receipt Payment Report", 14, 15);
  const headers = [["#", "Serial No", "Receipt No", "Description", "Plate", "Owner", "Type", "Center", "Amount", "Stamp", "Admin", "Due Date"]];
  const data = [];
  let totalAmount = 0;
  let totalStamp = 0;
  let totalAdmin = 0;
  document.querySelectorAll("#receiptTable tbody tr").forEach(row => {
    const cells = row.querySelectorAll("td");
    if (cells.length === 12) {
      // Extract amount (column 8, index 8), remove $ and commas
      const amount = parseFloat(cells[8].innerText.replace(/[^\d.\-]/g, "")) || 0;
      const stamp = parseFloat(cells[9].innerText.replace(/[^\d.\-]/g, "")) || 0;
      const admin = parseFloat(cells[10].innerText.replace(/[^\d.\-]/g, "")) || 0;
      totalAmount += amount;
      totalStamp += stamp;
      totalAdmin += admin;
      // Remove phone column from data
      const rowData = Array.from(cells).map(cell => cell.innerText);
      rowData.splice(4, 1); // Remove phone
      data.push(rowData);
    }
  });
  doc.autoTable({ head: headers, body: data, startY: 20 });
  // Add totals below the table
  const finalY = doc.lastAutoTable.finalY || 20;
  doc.setFontSize(12);
  doc.setTextColor(13, 110, 253);
  doc.text(`Total Amount: $${totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 14, finalY + 12);
  doc.text(`Total Stamp: $${totalStamp.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 14, finalY + 20);
  doc.text(`Total Admin: $${totalAdmin.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 14, finalY + 28);
  doc.save("receipt_report.pdf");
}

function downloadExcel() {
  const table = document.getElementById("receiptTable").cloneNode(true);
  // Remove phone column from cloned table
  Array.from(table.rows).forEach(row => {
    if (row.cells.length > 4) row.deleteCell(4);
  });
  const wb = XLSX.utils.table_to_book(table, { sheet: "Receipts" });
  XLSX.writeFile(wb, "receipt_report.xlsx");
}

function printReport(event) {
  event.preventDefault();
  window.print();
}
</script>

</body>
</html>

<?php $conn->close(); ?>
