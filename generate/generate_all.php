<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$response = "";
$inserted = 0;

// Statistics queries
$total_vehicles = $conn->query("SELECT COUNT(*) as total FROM vehiclemanagement")->fetch_assoc()['total'] ?? 0;
$total_types = $conn->query("SELECT COUNT(*) as total FROM vehicle_types")->fetch_assoc()['total'] ?? 0;
$last_generate = $conn->query("SELECT due_date FROM tblgenerate ORDER BY id DESC LIMIT 1");
if ($row = $last_generate->fetch_assoc()) {
    $last_date = date('F Y', strtotime($row['due_date']));
} else {
    $last_date = 'N/A';
}

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
                $base_amount = floatval(str_replace(',', '', $row['amount']));
                $final_amount = $base_amount;
                $duration_label = '6 bilood';
                $insert = $conn->prepare("INSERT INTO tblgenerate (fullname, vehicletype, platenumber, amount, amount_type, due_date) VALUES (?, ?, ?, ?, ?, ?)");
                $insert->bind_param("sssdss", $owner, $raw_type, $plate, $final_amount, $duration_label, $due_date);
                if ($insert->execute()) $inserted++;
            }
        }

        $response = "✅ $inserted vehicle(s) charged in bulk.";
    }
}

$types = $conn->query("SELECT name FROM vehicle_types");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate All Vehicle Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary:rgb(255, 255, 255);
            --primary-dark:rgb(212, 212, 237);
            --primary-light: #4895ef;
            --secondary: #f72585;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            overflow: hidden;
            transition: all 0.3s ease;
            max-width: 600px;
            width: 100%;
            margin: 0 auto 2rem auto;
            padding: 0;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.2);
        }
        
        .glass-card .card-header {
            padding: 1rem 1.2rem;
        }
        
        .glass-card .card-body {
            padding: 1.2rem 1.2rem 1.5rem 1.2rem;
        }
        
        .stat-card {
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(240,242,255,0.95) 100%);
            border: none;
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
            padding: 25px;
            text-align: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(67, 97, 238, 0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: blue !important;
            background: none;
            margin-bottom: 15px;
        }
        
        .stat-title {
            font-size: 1rem;
            font-weight: 600;
            color: #111;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #111;
            margin-bottom: 0;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            border-bottom: none;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .card-title {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #111;
        }
        
        .card-icon {
            font-size: 1.6rem;
            color: blue !important;
        }
        
        .form-label {
            font-weight: 600;
            color: #111;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 18px;
            border: 1px solid rgba(67, 97, 238, 0.2);
            background-color: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
            color: #111;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.2);
            background-color: white;
        }
        
        .btn-primary {
            background: blue;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            background: #0033cc;
        }
        
        .alert-success {
            background: rgba(76, 201, 240, 0.15);
            border: 1px solid rgba(76, 201, 240, 0.2);
            color: #0d6efd;
            border-radius: 12px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .success-icon {
            font-size: 1.8rem;
        }
        
        .footer {
            text-align: center;
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 30px;
        }
        
        .form-text {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
            font-size: 0.85rem;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Unified Card: Statistics + Form -->
        <div class="glass-card mb-4">
            <div class="card-header">
                <h1 class="card-title mb-3">
                    <i class="bi bi-lightning-charge-fill card-icon"></i>
                    Generate All Vehicle Payments
                </h1>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="margin-top: 10px;">
                    <div class="text-center flex-fill">
                        <i class="bi bi-car-front-fill stat-icon"></i>
                        <div class="stat-title">Total Vehicles</div>
                        <div class="stat-value"><?= $total_vehicles ?></div>
                    </div>
                    <div class="text-center flex-fill">
                        <i class="bi bi-tags-fill stat-icon"></i>
                        <div class="stat-title">Vehicle Types</div>
                        <div class="stat-value"><?= $total_types ?></div>
                    </div>
                    <div class="text-center flex-fill">
                        <i class="bi bi-calendar2-check-fill stat-icon"></i>
                        <div class="stat-title">Last Generated</div>
                        <div class="stat-value"><?= $last_date ?></div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if ($response): ?>
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                        <div><?= $response ?></div>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-4">
                        <label for="vehicletype" class="form-label">
                            <i class="bi bi-truck"></i> Vehicle Type
                        </label>
                        <select name="vehicletype" id="vehicletype" class="form-select form-select-lg" required>
                            <option value="all">All Vehicles</option>
                            <?php while ($row = $types->fetch_assoc()): ?>
                                <option value="<?= $row['name'] ?>"><?= $row['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text">
                            <i class="bi bi-info-circle"></i> Select vehicle type or "All Vehicles" to generate payments
                        </small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-clock"></i> Duration
                        </label>
                        <input type="text" class="form-control form-control-lg" value="6 Months" disabled>
                        <input type="hidden" name="duration" value="6">
                    </div>
                    <div class="mb-4">
                        <label for="due_date" class="form-label">
                            <i class="bi bi-calendar-event"></i> Due Date
                        </label>
                        <input type="datetime-local" name="due_date" id="due_date" class="form-control form-control-lg" required>
                    </div>
                    <button type="submit" name="register_payment" class="btn btn-primary btn-lg mt-3">
                        <i class="bi bi-lightning-charge-fill"></i> Generate All Payments
                    </button>
                </form>
            </div>
        </div>
        
        <footer class="footer">
            &copy; <?php echo date('Y'); ?> Roadtax System. All rights reserved.
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default datetime to now
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const timezoneOffset = now.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
            document.getElementById('due_date').value = localISOTime;
            
            // Add animation to cards on load
            const cards = document.querySelectorAll('.stat-card, .glass-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>