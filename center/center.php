<?php
include '../db.php';
$success = $error = "";

// Handle Register Center
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register_center'])) {
    $center_name = trim($_POST['center_name']);
    if (!empty($center_name)) {
        $stmt = $conn->prepare("INSERT INTO centers (center_name) VALUES (?)");
        $stmt->bind_param("s", $center_name);
        if ($stmt->execute()) $success = "✔️ Center registered successfully!";
        else $error = "❌ Failed to register center.";
        $stmt->close();
    } else {
        $error = "❌ Center name is required.";
    }
}

// Handle Edit Center
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_center'])) {
    $edit_id = $_POST['edit_id'];
    $edit_name = trim($_POST['edit_name']);
    if (!empty($edit_id) && !empty($edit_name)) {
        $stmt = $conn->prepare("UPDATE centers SET center_name=? WHERE id=?");
        $stmt->bind_param("si", $edit_name, $edit_id);
        if ($stmt->execute()) $success = "✔️ Center updated successfully!";
        else $error = "❌ Failed to update center.";
        $stmt->close();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $conn->query("DELETE FROM centers WHERE id=$delete_id");
    header("Location: center.php");
    exit;
}

// Search filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$sql = "SELECT * FROM centers";
if (!empty($search)) {
    $sql .= " WHERE center_name LIKE '%$search%'";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Center Management</title>
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
      max-width: 900px;
      margin-top: 40px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      padding: 32px 32px 24px 32px;
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
      color: #212529;
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
  </style>
</head>
<body>
<div class="container">
  <h2 class="text-center mb-4">
    <i class="fas fa-building me-2"></i>Center Management
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
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCenterModal">
      <i class="fas fa-plus me-2"></i>Register New Center
    </button>
    <form method="GET" class="d-flex search-box">
      <i class="fas fa-search"></i>
      <input type="text" name="search" class="form-control rounded-pill ms-2" placeholder="Search centers..." value="<?= htmlspecialchars($search) ?>">
    </form>
  </div>

  <div class="table-container">
    <table class="table table-hover">
      <thead>
        <tr>
          <th><i class="fas fa-building me-2"></i>Center Name</th>
          <th><i class="fas fa-cog me-2"></i>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['center_name']) ?></td>
          <td class="action-btns">
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" onclick="setEdit(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['center_name'])) ?>')">
              <i class="fas fa-edit"></i>
            </button>
            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete?')">
              <i class="fas fa-trash-alt"></i>
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="newCenterModal" tabindex="-1" aria-labelledby="newCenterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="newCenterModalLabel"><i class="fas fa-building me-2"></i>Register New Center</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="center_name" class="form-control" placeholder="Enter Center Name" required>
        </div>
        <div class="modal-footer">
          <button type="submit" name="register_center" class="btn btn-success"><i class="fas fa-save me-2"></i>Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
          <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Center</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_id">
          <input type="text" name="edit_name" id="edit_name" class="form-control" required>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_center" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function setEdit(id, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
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
