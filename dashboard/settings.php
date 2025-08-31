<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo "<div style='padding:20px;color:red;font-family:sans-serif;'>Access denied. Admin only.</div>";
    exit;
}

include '../db.php';

$pages = [
    'Main Pages' => [
        '../dashboard/dashboard_home.php',
        '../dashboard/form.php',
        '../generate/generate_payment.php',
        '../generate/generate_all.php',
        '../reciept/reciept_payment.php',
        '../dashboard/reports.php',
        '../generate/generate_report.php',
        '../reciept/reciept_report.php',
        '../dashboard/Vehiclestatement.php',
        '../dashboard/settings.php'
    ]
];

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = $_POST['role_user_id'];
    $new_role = $_POST['new_role'];
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    $stmt->execute();
    $message = "🔁 User role updated to <strong>$new_role</strong>.";
}

// Handle page assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pages'])) {
    $user_id = $_POST['user_id'];
    $selected_pages = $_POST['pages'];

    foreach ($selected_pages as $page) {
        $parts = explode('/', trim($page, '/'));
        $folder = $parts[1] ?? 'dashboard';
        $file = $parts[2] ?? $parts[1];
        $clean_page = $folder . '/' . $file;

        $check = $conn->prepare("SELECT id FROM tbl_user_pages WHERE user_id = ? AND page_name = ?");
        $check->bind_param("is", $user_id, $clean_page);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO tbl_user_pages (user_id, page_name) VALUES (?, ?)");
            $insert->bind_param("is", $user_id, $clean_page);
            $insert->execute();
        }
    }
    $message = "✅ Pages assigned successfully.";
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['uid'])) {
    $del = $conn->prepare("DELETE FROM tbl_user_pages WHERE user_id = ? AND page_name = ?");
    $del->bind_param("is", $_GET['uid'], $_GET['delete']);
    $del->execute();
    $message = "🗑️ Page deleted successfully.";
}

$users = $conn->query("SELECT id, username, role FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Manage Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1976D2;
            --light-blue: #E3F2FD;
            --white: #FFFFFF;
            --light-gray: #F5F5F5;
            --border-color: #E0E0E0;
        }
        
        body {
            background-color: #F9FBFE;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background: var(--white);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary-blue);
            color: var(--white);
            padding: 15px 20px;
            border-bottom: none;
            border-radius: 8px 8px 0 0 !important;
        }
        
        h2, h4 {
            color: var(--primary-blue);
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            border-radius: 4px;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background-color: #1565C0;
            border-color: #1565C0;
        }
        
        .btn-outline-danger {
            border-radius: 4px;
            padding: 5px 12px;
            font-size: 13px;
        }
        
        .form-select, .form-control {
            border-radius: 4px;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
        }
        
        .alert {
            border-radius: 4px;
            padding: 12px 15px;
        }
        
        .table {
            border-radius: 4px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--primary-blue);
            color: var(--white);
        }
        
        .table tbody tr:nth-child(even) {
            background-color: var(--light-gray);
        }
        
        .table tbody tr:hover {
            background-color: var(--light-blue);
        }
        
        .form-check {
            padding: 10px 15px;
            margin-bottom: 5px;
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .form-check:hover {
            background-color: var(--light-blue);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .section-box {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            color: var(--primary-blue);
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-blue);
        }
        
        .badge-role {
            background-color: var(--light-blue);
            color: var(--primary-blue);
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">User Settings & Access Control</h2>
        </div>
        
        <div class="card-body">
            <?php if (isset($message)): ?>
                <div class="alert alert-info alert-dismissible fade show mb-4">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Role Update Section -->
            <div class="section-box">
                <h4 class="section-title">Update User Role</h4>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Select User</label>
                            <select name="role_user_id" class="form-select" required>
                                <option value="">-- Choose User --</option>
                                <?php $users->data_seek(0); while ($user = $users->fetch_assoc()): ?>
                                    <option value="<?= $user['id'] ?>">
                                        <?= $user['username'] ?> <span class="badge-role"><?= $user['role'] ?></span>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Select New Role</label>
                            <select name="new_role" class="form-select" required>
                                <option value="">-- Choose Role --</option>
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" name="update_role" class="btn btn-primary w-100">
                                Update Role
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Assign Pages Section -->
            <div class="section-box">
                <h4 class="section-title">Assign Pages</h4>
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Select User</label>
                        <select name="user_id" class="form-select" onchange="this.form.submit()" required>
                            <option value="">-- Choose User --</option>
                            <?php
                            $users->data_seek(0);
                            $selected_user = $_POST['user_id'] ?? $_GET['uid'] ?? "";
                            while ($user = $users->fetch_assoc()):
                                $selected = ($user['id'] == $selected_user) ? "selected" : "";
                            ?>
                                <option value="<?= $user['id'] ?>" <?= $selected ?>>
                                    <?= $user['username'] ?> <span class="badge-role"><?= $user['role'] ?></span>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <?php if (!empty($selected_user)): ?>
                        <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
                            <?php foreach ($pages['Main Pages'] as $page): 
                                $label = ucwords(str_replace([".php", "_", "-"], ["", " ", " "], basename($page)));
                            ?>
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pages[]" value="<?= $page ?>" id="<?= md5($page) ?>">
                                        <label class="form-check-label" for="<?= md5($page) ?>">
                                            <?= $label ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Save Pages
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Assigned Pages Table -->
            <?php if (!empty($selected_user)): 
                $assigned = $conn->query("SELECT * FROM tbl_user_pages WHERE user_id = $selected_user");
            ?>
                <div class="section-box">
                    <h4 class="section-title">Assigned Pages</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $assigned->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?= ucwords(str_replace([".php", "_", "-"], ["", " ", " "], basename($row['page_name']))) ?>
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-outline-danger" 
                                               href="?delete=<?= urlencode($row['page_name']) ?>&uid=<?= $selected_user ?>" 
                                               onclick="return confirm('Are you sure to remove this page?');">
                                               Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>