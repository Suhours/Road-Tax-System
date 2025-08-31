<?php
// ✅ SOO BANDHIG ERROR-KASTA
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ DB CONNECTION
include '../db.php';
if (!$conn || $conn->connect_error) {
  die("❌ Connection error: " . $conn->connect_error);
}

// ✅ GET START/END DATE FROM FILTER
$start_date = isset($_GET['start_date']) && $_GET['start_date'] ? $_GET['start_date'] : date('Y-01-01');
$end_date = isset($_GET['end_date']) && $_GET['end_date'] ? $_GET['end_date'] : date('Y-12-31');

// ✅ TOTAL VEHICLES (date filter)
$vehicle_q = $conn->query("SELECT COUNT(*) AS total FROM vehiclemanagement WHERE registration_date >= '$start_date' AND registration_date <= '$end_date'");
if (!$vehicle_q) die("Error vehiclemanagement: " . $conn->error);
$vehicle_total = $vehicle_q->fetch_assoc()['total'];

// ✅ TOTAL REVENUE
$revenue_q = $conn->query("SELECT SUM(amount) AS total FROM tbl_reciept WHERE due_date >= '$start_date' AND due_date <= '$end_date'");
if (!$revenue_q) die("Error tbl_reciept total: " . $conn->error);
$total_revenue = $revenue_q->fetch_assoc()['total'] ?? 0;

// ✅ PENDING AMOUNT
$pending_q = $conn->query("SELECT SUM(amount) AS total FROM tblgenerate WHERE status = 'pending' AND due_date >= '$start_date' AND due_date <= '$end_date'");
if (!$pending_q) die("Error tblgenerate pending: " . $conn->error);
$pending_amount_total = $pending_q->fetch_assoc()['total'] ?? 0;

$collected_amount = max(0, $total_revenue - $pending_amount_total);

// ✅ MONTHLY REVENUE
$monthly_data = [];
$year = date('Y', strtotime($start_date));
for ($m = 1; $m <= 12; $m++) {
  $month_start = "$year-" . str_pad($m, 2, '0', STR_PAD_LEFT) . "-01";
  $month_end = date('Y-m-t', strtotime($month_start));
  // Only include months within the selected range
  if ($month_end < $start_date || $month_start > $end_date) {
    $monthly_data[] = 0;
    continue;
  }
  $monthly_q = $conn->query("SELECT SUM(amount) AS total FROM tbl_reciept WHERE due_date >= '$month_start' AND due_date <= '$month_end' AND due_date >= '$start_date' AND due_date <= '$end_date'");
  if (!$monthly_q) die("Error monthly data (month $m): " . $conn->error);
  $row = $monthly_q->fetch_assoc();
  $monthly_data[] = $row['total'] ?? 0;
}

// ✅ PIE CHART - Modified to get vehicle types only once
$pie_result = $conn->query("SELECT v.vehicletype as name, COUNT(*) as count 
                           FROM vehiclemanagement v
                           GROUP BY v.vehicletype");
if (!$pie_result) die("Error pie chart query: " . $conn->error);

$pie_labels = $pie_counts = [];
while ($row = $pie_result->fetch_assoc()) {
  $pie_labels[] = $row['name'];
  $pie_counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --accent-color: #4895ef;
      --success-color: #4cc9f0;
      --danger-color: #f72585;
      --warning-color: #f8961e;
      --light-color: #f8f9fa;
      --dark-color: #212529;
    }
    
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
    }
    
    .wrapper {
      max-width: 1400px;
      margin: 0 auto;
      padding: 1.5rem;
    }
    
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .topbar h2 {
      color: var(--primary-color);
      font-weight: 700;
      margin: 0;
      font-size: 1.6rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .icons {
      display: flex;
      gap: 0.8rem;
    }
    
    .icons a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      background: var(--primary-color);
      color: white;
      font-size: 1rem;
      text-decoration: none;
      border-radius: 10px;
      transition: all 0.3s ease;
      box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
    }
    
    .icons a:hover {
      background: var(--secondary-color);
      transform: translateY(-2px);
      box-shadow: 0 5px 7px rgba(0, 0, 0, 0.15);
    }
    
    .card {
      background: white;
      border-radius: 14px;
      box-shadow: 0 5px 12px rgba(0, 0, 0, 0.05);
      padding: 1.2rem;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
      height: 100%;
    }
    
    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .card .icon-label {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }
    
    .card p {
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--primary-color);
      margin: 0.3rem 0;
    }
    
    .card .trend {
      font-size: 0.8rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.3rem;
    }
    
    .card .trend.up {
      color: #28a745;
    }
    
    .card .trend.down {
      color: #dc3545;
    }
    
    .charts-container {
      margin-top: 1.5rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
      gap: 1.2rem;
    }
    
    .chart-box {
      background: white;
      border-radius: 14px;
      padding: 1.2rem;
      box-shadow: 0 5px 12px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
      height: 100%;
    }
    
    .chart-box:hover {
      transform: translateY(-2px);
    }
    
    .chart-box h5 {
      font-size: 1rem;
      margin-bottom: 1rem;
      font-weight: 600;
      color: var(--dark-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .chart-container {
      position: relative;
      height: 240px;
      width: 100%;
    }
    
    .sparkline-container {
      margin-top: 0.6rem;
      height: 40px;
    }
    
    .btn-toggle {
      border: 1px solid var(--primary-color);
      color: var(--primary-color);
      padding: 0.2rem 0.4rem;
      font-size: 0.75rem;
      transition: all 0.3s;
      border-radius: 6px;
    }
    
    .btn-toggle:hover {
      background: var(--primary-color);
      color: white;
    }
    
    @media (max-width: 992px) {
      .wrapper {
        padding: 1rem;
      }
      
      .card p {
        font-size: 1.4rem;
      }
      
      .chart-container {
        height: 220px;
      }
    }
    
    @media (max-width: 768px) {
      .charts-container {
        grid-template-columns: 1fr;
      }
      
      .card {
        padding: 1rem;
      }
      
      .chart-box {
        padding: 1rem;
      }
      
      .chart-container {
        height: 200px;
      }
    }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="topbar">
    <h2><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>
    <div class="d-flex align-items-center flex-wrap gap-2" style="min-width: 0;">
      <form method="get" class="d-flex align-items-center gap-2 mb-0 flex-nowrap">
        <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" style="width: 140px; min-width: 0;" value="<?= htmlspecialchars($start_date) ?>" title="Start date">
        <span class="fw-bold">-</span>
        <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" style="width: 140px; min-width: 0;" value="<?= htmlspecialchars($end_date) ?>" title="End date">
        <button type="submit" class="btn btn-primary btn-sm px-2" title="Apply filter"><i class="bi bi-funnel"></i></button>
        <?php if (isset($_GET['start_date']) || isset($_GET['end_date'])): ?>
          <a href="dashboard_home.php" class="btn btn-outline-secondary btn-sm px-2" title="Reset filter">Reset</a>
        <?php endif; ?>
      </form>
      <div class="icons ms-2 d-flex align-items-center gap-2">
        <a href="settings" title="Settings"><i class="bi bi-gear"></i></a>
        <a href="add_vehicle_type" title="Add Vehicle"><i class="bi bi-car-front-fill"></i></a>
        <a href="../center/center.php" title="center"><i class="bi bi-geo-alt"></i></a>
        <a href="profile_admin" title="Profile"><i class="bi bi-person-circle"></i></a>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-4 col-sm-6">
      <a href="" style="text-decoration:none;color:inherit;">
      <div class="card">
        <div class="icon-label"><i class="bi bi-car-front-fill"></i> Total Vehicles</div>
        <p><?= number_format($vehicle_total); ?></p>
        <div class="trend up">
          <i class="bi bi-arrow-up"></i> 12% from last month
        </div>
        <div class="sparkline-container">
          <canvas id="spark1" class="spark"></canvas>
        </div>
      </div>
      </a>
    </div>
    <div class="col-md-4 col-sm-6">
      <a href="" style="text-decoration:none;color:inherit;">
      <div class="card">
        <div class="icon-label"><i class="bi bi-currency-dollar"></i> Total Revenue</div>
        <p>$<?= number_format($total_revenue, 2); ?></p>
        <div class="trend up">
          <i class="bi bi-arrow-up"></i> 8.5% from last month
        </div>
        <div class="sparkline-container">
          <canvas id="spark2" class="spark"></canvas>
        </div>
      </div>
      </a>
    </div>
    <div class="col-md-4 col-sm-6">
      <a href="" style="text-decoration:none;color:inherit;">
      <div class="card">
        <div class="icon-label"><i class="bi bi-hourglass-split"></i> Pending Amount</div>
        <p>$<?= number_format($pending_amount_total, 2); ?></p>
        <div class="trend down">
          <i class="bi bi-arrow-down"></i> 3.2% from last month
        </div>
        <div class="sparkline-container">
          <canvas id="spark3" class="spark"></canvas>
        </div>
      </div>
      </a>
    </div>
  </div>

  <div class="charts-container">
    <div class="chart-box">
      <h5><i class="bi bi-pie-chart-fill me-2"></i>Vehicle Distribution</h5>
      <div class="chart-container">
        <canvas id="barChart"></canvas>
      </div>
    </div>

    <div class="chart-box">
      <h5>
        <i class="bi bi-graph-up me-2"></i>Monthly Revenue
        <button class="btn btn-toggle btn-sm" onclick="toggleDemo()">
          <span id="modeLabel">Real</span> <i class="bi bi-arrow-repeat ms-1"></i>
        </button>
      </h5>
      <div class="chart-container">
        <canvas id="lineChart"></canvas>
      </div>
    </div>

    <div class="chart-box">
      <h5><i class="bi bi-cash-stack me-2"></i>Payment Status</h5>
      <div class="chart-container">
        <canvas id="pendingChart"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
const demoData = [1200, 1800, 2100, 1600, 2300, 2800, 1900, 2400, 3000, 2700, 2200, 3100];
let isDemo = false;

// Generate random data for sparklines
function generateSparkData(base, variance) {
  return Array.from({length: 12}, (_, i) => base + Math.random() * variance);
}

// Sparkline options
const sparkOptions = {
  type: 'line',
  options: {
    responsive: true,
    maintainAspectRatio: false,
    elements: {
      point: { radius: 0 },
      line: { tension: 0.4, borderWidth: 2 }
    },
    plugins: {
      legend: { display: false },
      tooltip: { enabled: false }
    },
    scales: {
      x: { display: false },
      y: { display: false }
    },
    animation: {
      duration: 800
    }
  }
};

// Spark 1 - Vehicles
new Chart(document.getElementById('spark1'), {
  ...sparkOptions,
  data: {
    labels: Array.from({length: 12}, (_, i) => i + 1),
    datasets: [{
      data: generateSparkData(50, 40),
      borderColor: '#4cc9f0',
      backgroundColor: 'rgba(76, 201, 240, 0.1)',
      fill: true
    }]
  }
});

// Spark 2 - Revenue
new Chart(document.getElementById('spark2'), {
  ...sparkOptions,
  data: {
    labels: Array.from({length: 12}, (_, i) => i + 1),
    datasets: [{
      data: generateSparkData(2000, 1500),
      borderColor: '#4895ef',
      backgroundColor: 'rgba(72, 149, 239, 0.1)',
      fill: true
    }]
  }
});

// Spark 3 - Pending
new Chart(document.getElementById('spark3'), {
  ...sparkOptions,
  data: {
    labels: Array.from({length: 12}, (_, i) => i + 1),
    datasets: [{
      data: generateSparkData(800, 600),
      borderColor: '#f72585',
      backgroundColor: 'rgba(247, 37, 133, 0.1)',
      fill: true
    }]
  }
});

// PIE CHART - Vehicle Distribution
new Chart(document.getElementById('barChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($pie_labels); ?>,
    datasets: [{
      data: <?= json_encode($pie_counts); ?>,
      backgroundColor: [
        '#4361ee', '#3f37c9', '#4895ef', '#4cc9f0',
        '#f72585', '#7209b7', '#3a0ca3', '#f8961e',
        '#43aa8b', '#90be6d', '#577590', '#f94144'
      ],
      borderWidth: 0,
      hoverOffset: 10
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { 
        position: 'right',
        labels: {
          boxWidth: 10,
          padding: 15,
          font: {
            size: 11
          }
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            let label = context.label || '';
            let value = context.raw || 0;
            let total = context.dataset.data.reduce((a, b) => a + b, 0);
            let percentage = Math.round((value / total) * 100);
            return `${label}: ${value} (${percentage}%)`;
          }
        }
      }
    },
    cutout: '60%',
    animation: {
      animateScale: true,
      animateRotate: true
    }
  }
});

// BAR CHART - Monthly Revenue
const lineChart = new Chart(document.getElementById('lineChart'), {
  type: 'bar',
  data: {
    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    datasets: [{
      label: 'Revenue ($)',
      data: <?= json_encode($monthly_data); ?>,
      backgroundColor: '#4361ee',
      borderRadius: 6,
      borderWidth: 0,
      barThickness: 'flex',
      maxBarThickness: 25,
      hoverBackgroundColor: '#3a0ca3'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { 
        display: true,
        position: 'top',
        labels: {
          boxWidth: 10,
          padding: 8,
          font: {
            size: 11
          }
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return '$' + context.raw.toLocaleString();
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: {
          color: 'rgba(0, 0, 0, 0.03)'
        },
        ticks: {
          callback: function(value) {
            return '$' + value.toLocaleString();
          },
          font: {
            size: 10
          }
        }
      },
      x: {
        grid: {
          display: false
        },
        ticks: {
          font: {
            size: 10
          }
        }
      }
    },
    animation: {
      duration: 800
    }
  }
});

function toggleDemo() {
  isDemo = !isDemo;
  const label = document.getElementById("modeLabel");
  label.textContent = isDemo ? "Demo" : "Real";
  lineChart.data.datasets[0].data = isDemo ? demoData : <?= json_encode($monthly_data); ?>;
  lineChart.update();
}

// STACKED CHART - Payment Status
new Chart(document.getElementById('pendingChart'), {
  type: 'bar',
  data: {
    labels: ['Payments'],
    datasets: [
      {
        label: 'Pending',
        data: [<?= $pending_amount_total; ?>],
        backgroundColor: '#f72585',
        borderRadius: 6
      },
      {
        label: 'Collected',
        data: [<?= $collected_amount; ?>],
        backgroundColor: '#4cc9f0',
        borderRadius: 6
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { 
        position: 'top',
        labels: {
          boxWidth: 10,
          padding: 8,
          font: {
            size: 11
          }
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return context.dataset.label + ': $' + context.raw.toLocaleString();
          }
        }
      }
    },
    scales: {
      x: { 
        stacked: true,
        grid: {
          display: false
        }
      },
      y: { 
        stacked: true, 
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return '$' + value.toLocaleString();
          },
          font: {
            size: 10
          }
        }
      }
    },
    animation: {
      duration: 800
    }
  }
});
</script>
</body>
</html>