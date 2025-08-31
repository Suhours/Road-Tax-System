<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../db.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$type_result = $conn->query("SELECT DISTINCT vehicletype FROM tblgenerate");

$search = $type_filter = $month_filter = $year_filter = "";
$where = [];

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where[] = "(g.platenumber LIKE '%$search%' OR g.fullname LIKE '%$search%')";
}
if (isset($_GET['type']) && $_GET['type'] !== "all") {
    $type_filter = $conn->real_escape_string($_GET['type']);
    $where[] = "g.vehicletype = '$type_filter'";
}
if (!empty($_GET['month'])) {
    $month_filter = $conn->real_escape_string($_GET['month']);
    $where[] = "MONTH(g.due_date) = '$month_filter'";
}
if (!empty($_GET['year'])) {
    $year_filter = $conn->real_escape_string($_GET['year']);
    $where[] = "YEAR(g.due_date) = '$year_filter'";
}
$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM tblgenerate g $where_clause ORDER BY g.id DESC";
$result = $conn->query($sql);
$years = range(date("Y"), date("Y") - 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Report</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 25px;
        }
        .export-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-bottom: 20px;
        }
        .export-buttons button {
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        .pdf-btn {
            background: #28a745;
            color: white;
        }
        .excel-btn {
            background: #ffc107;
            color: black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #007bff;
            color: white;
            padding: 12px;
        }
        td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .no-data {
            text-align: center;
            color: #999;
            padding: 20px;
        }
        .filter-section {
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
        }
        .filter-section select,
        .filter-section input {
            padding: 10px;
            border-radius: 8px;
            font-size: 15px;
        }
        .table-responsive {
            max-height: 60vh;
            overflow-y: auto;
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
            body * {
                visibility: hidden;
            }
            .container, .container * {
                visibility: visible;
            }
            .export-buttons, .filter-section, .filter-section * {
                display: none !important;
            }
            .container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .table-responsive {
                max-height: none !important;
                overflow: visible !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 Generate Payment Report</h2>

    <div class="export-buttons d-flex justify-content-end mb-3">
        <button class="btn btn-export me-2 export-pdf" onclick="confirmAndDownload('pdf')">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </button>
        <button class="btn btn-export me-2 export-excel" onclick="confirmAndDownload('excel')">
            <i class="bi bi-file-earmark-excel"></i> Excel
        </button>
        <button class="btn btn-export btn-primary" onclick="printReport(event)">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>

    <div class="filter-section">
        <form method="GET" class="w-100 d-flex gap-3">
            <input type="text" name="search" class="form-control" placeholder="Search Plate or Name" value="<?= htmlspecialchars($search) ?>">
            <select name="type" class="form-select">
                <option value="all">-- All Types --</option>
                <?php while ($row = $type_result->fetch_assoc()): ?>
                    <option value="<?= $row['vehicletype'] ?>" <?= $type_filter == $row['vehicletype'] ? 'selected' : '' ?>><?= $row['vehicletype'] ?></option>
                <?php endwhile; ?>
            </select>
            <select name="month" class="form-select">
                <option value="">-- Month --</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $month_filter == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 10)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-select">
                <option value="">-- Year --</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $year_filter == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
            <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
            </div>
        </form>
    </div>

    <div class="table-responsive" >
        <table id="reportTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Plate</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                $total_amount = 0;
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $total_amount += $row['amount'];
                ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['platenumber']) ?></td>
                        <td><?= htmlspecialchars($row['vehicletype']) ?></td>
                        <td>$<?= number_format($row['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($row['due_date']) ?></td>
                        <td><?= htmlspecialchars($row['amount_type']) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="no-data">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
    </div>
    <div class="d-flex justify-content-center mt-3">
            <span class="total-records-badge">
                <i class="bi bi-wallet2"></i> Total Amount: $<?= number_format($total_amount, 2) ?>
            </span>
    </div>
</div>

<script>
function confirmAndDownload(type) {
    if (confirm("Do you want to download the report?")) {
        if (type === 'pdf') downloadPDF();
        if (type === 'excel') downloadExcel();
    }
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Vehicle Payment Report", 14, 15);
    const headers = [["#", "Name", "Plate", "Type", "Amount", "Due Date", "Duration"]];
    const data = [];
    let totalAmount = 0;
    document.querySelectorAll("#reportTable tbody tr").forEach(row => {
        const cells = row.querySelectorAll("td");
        if (cells.length === 7) {
            const amount = parseFloat(cells[4].innerText.replace(/[^\d.\-]/g, "")) || 0;
            totalAmount += amount;
            data.push([
                cells[0].innerText, cells[1].innerText, cells[2].innerText,
                cells[3].innerText, cells[4].innerText, cells[5].innerText,
                cells[6].innerText
            ]);
        }
    });
    doc.autoTable({ head: headers, body: data, startY: 20 });
    // Add total amount below the table
    const finalY = doc.lastAutoTable.finalY || 20;
    doc.setFontSize(12);
    doc.setTextColor(13, 110, 253);
    doc.text(`Total Amount: $${totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`, 14, finalY + 12);
    doc.save("payment_report.pdf");
}

function downloadExcel() {
    const table = document.getElementById("reportTable").cloneNode(true);
    const wb = XLSX.utils.table_to_book(table, { sheet: "Report" });
    XLSX.writeFile(wb, "payment_report.xlsx");
}

function printReport(event) {
    event.preventDefault();
    window.print();
}
</script>

</body>
</html>

<?php $conn->close(); ?>
