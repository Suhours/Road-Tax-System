<?php
session_start();

if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 1800) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


include 'db.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Filters
$date = preg_replace('/[^0-9\-]/', '', $_GET['date'] ?? '');
$action = preg_replace('/[^a-zA-Z]/', '', $_GET['action'] ?? '');
$table = preg_replace('/[^a-zA-Z_]/', '', $_GET['table'] ?? '');
$user = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['user'] ?? '');
$role = preg_replace('/[^a-zA-Z]/', '', $_GET['role'] ?? '');
$keyword = $conn->real_escape_string($_GET['keyword'] ?? '');

$query = "
  SELECT a.*, u.username, u.role 
  FROM audit_log a
  LEFT JOIN users u ON a.user_id = u.id
  WHERE 1=1";

if (!empty($date)) $query .= " AND DATE(a.created_at) = '$date'";
if (!empty($action)) $query .= " AND a.action = '$action'";
if (!empty($table)) $query .= " AND a.page = '$table'";
if (!empty($user)) $query .= " AND u.username = '$user'";
if (!empty($role)) $query .= " AND u.role = '$role'";
if (!empty($keyword)) $query .= " AND a.details LIKE '%$keyword%'";

$query .= " ORDER BY a.created_at DESC LIMIT 500";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Audit Log Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #eef3f7;
      font-family: 'Segoe UI', sans-serif;
    }
    .card-custom {
      border-radius: 12px;
      border-top: 4px solid #007bff;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      padding: 25px;
    }
    .table thead {
      background-color: #007bff;
      color: white;
    }
    .table tbody tr:hover {
      background-color: #f2f9ff;
    }
    .badge-primary { background-color: #0d6efd; }
    .badge-success { background-color: #198754; }
    .badge-danger { background-color: #dc3545; }
    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-info { background-color: #0dcaf0; color: #212529; }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="card card-custom">
    <h4 class="text-center text-primary mb-4">
      <i class="fas fa-file-alt me-2"></i>Audit Log Report
    </h4>

    <!-- Filter Section -->
    <form method="GET" class="row g-3 mb-3">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div class="col-md-2">
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control">
      </div>
      <div class="col-md-2">
        <select name="action" class="form-select">
          <option value="">All Actions</option>
          <?php foreach (['Login', 'Logout', 'Add', 'Edit', 'Delete', 'View'] as $a): ?>
            <option value="<?= $a ?>" <?= $action == $a ? 'selected' : '' ?>><?= $a ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="table" class="form-select">
          <option value="">All Tables</option>
          <?php foreach (['users', 'vehicles', 'tbl_reciept', 'audit_log'] as $t): ?>
            <option value="<?= $t ?>" <?= $table == $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="user" class="form-select">
          <option value="">All Users</option>
          <?php $users = $conn->query("SELECT DISTINCT username FROM users");
          while ($u = $users->fetch_assoc()): ?>
            <option value="<?= $u['username'] ?>" <?= $user == $u['username'] ? 'selected' : '' ?>><?= $u['username'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="role" class="form-select">
          <option value="">All Roles</option>
          <option value="Admin" <?= $role == 'Admin' ? 'selected' : '' ?>>Admin</option>
          <option value="User" <?= $role == 'User' ? 'selected' : '' ?>>User</option>
        </select>
      </div>
      <div class="col-md-2">
        <input type="text" name="keyword" class="form-control" placeholder="Search details..." value="<?= htmlspecialchars($keyword) ?>">
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
        <a href="audit_log.php" class="btn btn-secondary"><i class="fas fa-sync-alt me-1"></i> Reset</a>
   
      </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-striped table-hover text-center">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>IP</th>
            <th>User</th>
            <th>Role</th>
            <th>Page</th>
            <th>Action</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): $i = 1; while ($row = $result->fetch_assoc()): ?>
            <?php
            $badgeClass = match (strtolower($row['action'])) {
              'login' => 'badge-primary',
              'logout' => 'badge-secondary',
              'add' => 'badge-success',
              'edit' => 'badge-warning',
              'delete' => 'badge-danger',
              default => 'badge-info',
            };
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= date("Y-m-d H:i:s", strtotime($row['created_at'])) ?></td>
             <td><?= htmlspecialchars($row['ip'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['role']) ?></td>
              <td><?= htmlspecialchars($row['page']) ?></td>
              <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['action']) ?></span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal<?= $row['id'] ?>">
                  <i class="fas fa-eye"></i>
                </button>

                <!-- Modal -->
                <div class="modal fade" id="modal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Details</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <p><strong>Date:</strong> <?= $row['created_at'] ?></p>
                        <p><strong>User:</strong> <?= $row['username'] ?></p>
                        <p><strong>Role:</strong> <?= $row['role'] ?></p>
                        <p><strong>Page:</strong> <?= $row['page'] ?></p>
                        <p><strong>Action:</strong> <?= $row['action'] ?></p>
                        <p><strong>Details:</strong></p>
                        <div class="border p-2 bg-light"><?= nl2br(htmlspecialchars($row['details'])) ?></div>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="8" class="text-muted">No logs found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
