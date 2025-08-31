<?php
include '../db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login");
    exit;
}
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_type'])) {
    $name = trim($_POST['name']);
    $amount_type = '6'; // Only 6 months allowed
    $amount = trim($_POST['amount']);
    $stamp = trim($_POST['stamp']);
    $admin = trim($_POST['admin']);

    if (!empty($name) && !empty($amount) && $stamp !== '' && $admin !== '') {
        $base_amount = $amount;
        // No conversion or lookup needed, only 6 months
        $stmt = $conn->prepare("INSERT INTO vehicle_types (name, amount, amount_type, stamp, admin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsdd", $name, $base_amount, $amount_type, $stamp, $admin);
        if ($stmt->execute()) {
            $success = "✅ Vehicle type added successfully!";
        } else {
            $error = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "❌ Please fill in all fields correctly.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_type'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $amount = $_POST['edit_amount'];
    $amount_type = $_POST['edit_amount_type'];
    $stamp = $_POST['edit_stamp'];
    $admin = $_POST['edit_admin'];

    $stmt = $conn->prepare("UPDATE vehicle_types SET name=?, amount=?, amount_type=?, stamp=?, admin=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("sdsddi", $name, $amount, $amount_type, $stamp, $admin, $id);
        if ($stmt->execute()) {
            $success = "✅ Vehicle type updated successfully!";
        } else {
            $error = "❌ Update failed.";
        }
        $stmt->close();
    } else {
        $error = "❌ Update prepare failed.";
    }
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$sql = "SELECT * FROM vehicle_types";
if (!empty($search)) {
    $sql .= " WHERE name LIKE '%$search%' OR amount_type LIKE '%$search%'";
}
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Type Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin-top: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 15px;
        }
        
        .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 12px;
            color: #6c757d;
        }
        
        .search-box input {
            padding-left: 40px;
        }
        
        .badge-duration {
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .amount-cell {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .action-btns .btn {
            margin-right: 5px;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent-color);
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">
        <i class="fas fa-car me-2"></i>Vehicle Type Management
    </h2>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show text-center">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show text-center">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-2"></i>Add Vehicle Type
        </button>
        
        <form method="GET" class="d-flex search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control rounded-pill ms-2" 
                   placeholder="Search vehicle types..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><i class="fas fa-car me-2"></i>Vehicle Type</th>
                    <th><i class="fas fa-dollar-sign me-2"></i>Amount</th>
                    <th><i class="fas fa-stamp me-2"></i>Stamp</th>
                    <th><i class="fas fa-user-shield me-2"></i>Admin</th>
                    <th><i class="fas fa-calendar-alt me-2"></i>Duration</th>
                    <th><i class="fas fa-cog me-2"></i>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td class="amount-cell">$<?= number_format($row['amount'], 2) ?></td>
                    <td class="amount-cell">$<?= number_format($row['stamp'], 2) ?></td>
                    <td class="amount-cell">$<?= number_format($row['admin'], 2) ?></td>
                    <td><span class="badge-duration"><?= htmlspecialchars($row['amount_type']) ?> months</span></td>
                    <td class="action-btns">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                onclick="setEditData('<?= $row['id'] ?>', '<?= htmlspecialchars(addslashes($row['name'])) ?>', '<?= $row['amount'] ?>', '<?= $row['amount_type'] ?>', '<?= $row['stamp'] ?>', '<?= $row['admin'] ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="delete_type.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this vehicle type?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-car me-2"></i>Add Vehicle Type</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="name" class="form-label">Vehicle Type Name</label>
                <input type="text" name="name" class="form-control" id="name" placeholder="e.g. Bajaaj, Car" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount (for 6 months)</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="amount" class="form-control" id="amount" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="stamp" class="form-label">Stamp</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="stamp" class="form-control" id="stamp" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="admin" class="form-label">Admin</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="admin" class="form-control" id="admin" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="amount_type" class="form-label">Duration</label>
                <select name="amount_type" class="form-select" id="amount_type" required disabled>
                    <option value="6" selected>6 Months</option>
                </select>
                <input type="hidden" name="amount_type" value="6">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="submit_type" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>Save Vehicle Type
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Vehicle Type</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="mb-3">
                <label for="edit_name" class="form-label">Vehicle Type Name</label>
                <input type="text" class="form-control" name="edit_name" id="edit_name" required>
            </div>
            <div class="mb-3">
                <label for="edit_amount" class="form-label">Amount (for 6 months)</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" name="edit_amount" id="edit_amount" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="edit_stamp" class="form-label">Stamp</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" name="edit_stamp" id="edit_stamp" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="edit_admin" class="form-label">Admin</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" name="edit_admin" id="edit_admin" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="edit_amount_type" class="form-label">Duration</label>
                <select name="edit_amount_type" id="edit_amount_type" class="form-select" required disabled>
                    <option value="6" selected>6 Months</option>
                </select>
                <input type="hidden" name="edit_amount_type" value="6">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_type" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setEditData(id, name, amount, type, stamp, admin) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_amount').value = amount;
    document.getElementById('edit_amount_type').value = type;
    document.getElementById('edit_stamp').value = stamp;
    document.getElementById('edit_admin').value = admin;
}

// Auto-focus search input when page loads
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.focus();
    }
});
</script>
</body>
</html>

<?php $conn->close(); ?>