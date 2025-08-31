<?php
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

$carname_result = $conn->query("SELECT DISTINCT vehicletype FROM vehiclemanagement ORDER BY vehicletype");

$where = [];
if (!empty($search)) $where[] = "(platenumber LIKE '%$search%' OR owner LIKE '%$search%')";
if (!empty($type_filter) && $type_filter !== 'All') $where[] = "vehicletype = '$type_filter'";
if (!empty($month_filter)) $where[] = "MONTH(registration_date) = '$month_filter'";
if (!empty($year_filter)) $where[] = "YEAR(registration_date) = '$year_filter'";

$where_clause = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT * FROM vehiclemanagement $where_clause ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vehicle Report</title>
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
      font-family: "Segoe UI", sans-serif;
      background: #eaf3fb;
      overflow-x: hidden;
    }
    .wrapper {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 30px 15px 30px; /* Adjusted padding */
    }
    .card-style {
      background-color: white;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      padding: 20px;
      width: 100%;
      max-width: 1300px; /* Further increased width */
      margin-top: 40px; /* Increased the margin top */
    }
    .title {
      color: #0d6efd;
      font-weight: bold;
    }
    .btn-export {
      border-radius: 22px;
      font-weight: 500;
      padding: 6px 16px;
      font-size: 0.98rem;
      box-shadow: 0 2px 8px rgba(13,110,253,0.08);
      transition: background 0.3s, color 0.3s, box-shadow 0.3s;
      border: none;
    }
    .btn-export.export-pdf {
      background: linear-gradient(90deg, #ff5858 0%, #f857a6 100%);
      color: #fff;
    }
    .btn-export.export-pdf:hover {
      background: linear-gradient(90deg, #f857a6 0%, #ff5858 100%);
      color: #fff;
      box-shadow: 0 4px 16px rgba(248,87,166,0.15);
    }
    .btn-export.export-excel {
      background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
      color: #fff;
    }
    .btn-export.export-excel:hover {
      background: linear-gradient(90deg, #38f9d7 0%, #43e97b 100%);
      color: #fff;
      box-shadow: 0 4px 16px rgba(67,233,123,0.15);
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

    /* Table container with smaller height */
    .table-responsive {
      max-height: 50vh; /* Reduced height of the table to avoid unnecessary scroll */
      overflow-y: auto; /* Enable vertical scrolling only if necessary */
    }

    /* Modal box */
    .modal-dialog {
      max-width: 400px; /* Reduced modal width */
    }

    .modal-content {
      padding: 10px; /* Reduced padding */
    }
    .total-records-badge {
      display: inline-block;
      background: linear-gradient(90deg, #e0eafc 0%, #cfdef3 100%);
      color: #0d6efd;
      font-weight: 600;
      border-radius: 50px;
      padding: 8px 28px;
      font-size: 1.08rem;
      box-shadow: 0 2px 8px rgba(13,110,253,0.07);
      margin-top: 10px;
      letter-spacing: 0.5px;
    }
    .total-records-badge i {
      margin-right: 7px;
      font-size: 1.1em;
    }
    @media print {
      body * {
        visibility: hidden !important;
      }
      .wrapper, .wrapper * {
        visibility: visible !important;
      }
      .d-flex.justify-content-end.mb-3,
      .filter-bar,
      .filter-bar *,
      .btn,
      .btn-export,
      form,
      .form-control,
      .form-select {
        display: none !important;
        visibility: hidden !important;
      }
      .card-style {
        box-shadow: none !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .table-responsive {
        max-height: none !important;
        overflow: visible !important;
      }
      .total-records-badge {
        margin-top: 20px !important;
      }
      .container.card-style {
        margin: 0 auto !important;
        float: none !important;
      }
    }
  </style>
</head>
<body>

<div class="wrapper">
  <div class="container card-style">

    <h2 class="text-center mb-4 title">🚘 Vehicle Reports</h2>

    <!-- Export Buttons Row -->
    <div class="d-flex justify-content-end mb-3">
      <button onclick="confirmAndDownload('pdf')" class="btn btn-export me-2 export-pdf">
        <i class="bi bi-file-earmark-pdf"></i> PDF
      </button>
      <button onclick="confirmAndDownload('excel')" class="btn btn-export export-excel me-2">
        <i class="bi bi-file-earmark-excel"></i> Excel
      </button>
      <button onclick="printReport(event)" class="btn btn-export btn-primary">
        <i class="bi bi-printer"></i> Print
      </button>
    </div>
    <!-- End Export Buttons Row -->
    <form method="GET" class="row g-3 filter-bar">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="🔍 Plate or Owner" value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select">
          <option value="All">🚗 All Types</option>
          <?php while ($row = $carname_result->fetch_assoc()): ?>
            <option value="<?= $row['vehicletype'] ?>" <?= $type_filter == $row['vehicletype'] ? 'selected' : '' ?>>
              <?= $row['vehicletype'] ?>
            </option>
          <?php endwhile; ?>
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
      <div class="col-md-3 d-grid" >
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
      </div>
    </form>

   

    <div class="table-responsive" style="max-height: 50vh; overflow-y: auto;">
      <table id="vehicleTable" class="table table-striped table-bordered text-center align-middle table-sm" >
        <thead>
          <tr>
            <th>#</th>
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
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['platenumber']) ?></td>
              <td><?= htmlspecialchars($row['chesis_no']) ?></td>
              <td><?= htmlspecialchars($row['serial_no']) ?></td>
              <td><?= htmlspecialchars($row['owner']) ?></td>
              <td><?= htmlspecialchars($row['vehicletype']) ?></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td><?= htmlspecialchars($row['model']) ?></td>
              <td><?= htmlspecialchars($row['color']) ?></td>
              <td><?= htmlspecialchars($row['cylinder']) ?></td>
              <td><?= htmlspecialchars($row['manufacture']) ?></td>
              <td><?= htmlspecialchars($row['center']) ?></td>
              <td><?= htmlspecialchars($row['registration_date']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <div class="d-flex justify-content-center mt-3">
        <span class="total-records-badge">
          <i class="bi bi-collection"></i> Total Records: <?= $result->num_rows ?>
        </span>
      </div>
    </div>
  </div>
</div>

<script>
function confirmAndDownload(type) {
  if (confirm("Are you sure you want to download the report?")) {
    if (type === 'pdf') downloadPDF();
    else if (type === 'excel') downloadExcel();
  }
}

function downloadPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape' });
  doc.text("Vehicle Report", 14, 15);
  const headers = [["#", "Plate #", "Chesis #", "Serial #", "Owner", "Vehicle Type", "Description", "Model", "Color", "Cylinder", "Manufacture", "Center", "Date"]];
  const data = [];
  document.querySelectorAll("#vehicleTable tbody tr").forEach(row => {
    const cells = row.querySelectorAll("td");
    if (cells.length >= 13) {
      data.push(Array.from(cells).map(cell => cell.innerText));
    }
  });
  doc.autoTable({
    head: headers,
    body: data,
    startY: 20,
    theme: 'grid',
    styles: {
      fontSize: 8,
      cellPadding: { top: 2, right: 2, bottom: 2, left: 2 },
      overflow: 'linebreak',
    },
    headStyles: {
      fillColor: [0, 123, 255],
      textColor: 255,
      fontStyle: 'bold',
      halign: 'center'
    },
    alternateRowStyles: {
      fillColor: [245, 245, 245]
    },
    columnStyles: {
      0: { cellWidth: 8, halign: 'center' },
      4: { cellWidth: 30 }, // Owner
      5: { cellWidth: 25 }, // Vehicle Type
      6: { cellWidth: 'auto' }, // Description
    },
    didDrawPage: function(data) {
        let str = "Page " + doc.internal.getNumberOfPages();
        doc.setFontSize(10);
        let pageSize = doc.internal.pageSize;
        let pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight();
        doc.text(str, data.settings.margin.left, pageHeight - 10);
    }
  });
  doc.save("vehicle_report.pdf");
}

function downloadExcel() {
  const table = document.getElementById("vehicleTable").cloneNode(true);
  const wb = XLSX.utils.table_to_book(table, { sheet: "Vehicle Report" });
  XLSX.writeFile(wb, "vehicle_report.xlsx");
}

function printReport(event) {
  event.preventDefault();
  window.print();
}
</script>

</body>
</html>

<?php $conn->close(); ?>
