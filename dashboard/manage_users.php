<?php
include '../db.php';

// Helper functions
function check_domain_reputation($domain)
{
  // List of known spam domains
  $spam_domains = [
    'spam',
    'spammy',
    'scam',
    'fake',
    'phishing',
    'malware',
    'virus',
    'hacked',
    'fraud'
  ];

  // Check if domain contains common spam keywords
  foreach ($spam_domains as $spam_domain) {
    if (stripos($domain, $spam_domain) !== false) {
      return 'spam';
    }
  }

  // Check domain length - suspiciously short domains
  if (strlen($domain) < 5) {
    return 'suspicious';
  }

  // Check if domain looks like a random string
  if (preg_match("/^([a-z0-9]{1,3})\.[a-z]{2,}$/i", $domain)) {
    return 'suspicious';
  }

  return 'good';
}

function check_recent_registration_attempts($email)
{
  global $conn;

  // Check attempts in the last 24 hours
  $sql = "SELECT COUNT(*) as attempts 
            FROM users 
            WHERE email = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  return $row['attempts'];
}

$success = $error = "";

// Add status column to users table if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($check_column->num_rows == 0) {
  $conn->query("ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active'");
}

// Handle user dropout (mark as inactive)
if (isset($_GET['dropout'])) {
  $id = intval($_GET['dropout']);
  
  try {
    $stmt = $conn->prepare("UPDATE users SET status = 'dropout' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
      $success = "✅ User has been marked as dropout successfully!";
    } else {
      throw new Exception("Error updating user status: " . $conn->error);
    }
    $stmt->close();
  } catch (Exception $e) {
    $error = "❌ Error: " . $e->getMessage();
  }
  
  // Redirect to clear the URL
  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_user'])) {
  $username = $_POST['username'];
  $email    = $_POST['email'];
  $password = $_POST['password'];
  $confirm  = $_POST['confirm_password'];
  $role     = $_POST['role'];

  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "❌ Invalid email format. Please enter a valid email address.";
  } elseif (strlen($email) > 254) {
    $error = "❌ Email address is too long. Maximum length is 254 characters.";
  } elseif (!preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$/", $email)) {
    $error = "❌ Invalid email format. Please enter a valid email address.";
  } elseif (strlen($email) < 6) {
    $error = "❌ Email address is too short.";
  } elseif (preg_match("/@mailinator|@guerrillamail|@10minutemail|@temp-mail|@tempmail|@fakemail|@throwawaymail/i", $email)) {
    $error = "❌ Disposable email addresses are not allowed. Please use your real email.";
  } elseif (preg_match("/@[0-9]+\\.[a-z]{2,}|@[a-z0-9]+\\.[0-9]+\\.[a-z]{2,}/i", $email)) {
    $error = "❌ Invalid email domain format. Please enter a valid email address.";
  } elseif (preg_match("/@[a-z0-9]{1,3}\\.[a-z]{2,}/i", $email)) {
    $error = "❌ Invalid email domain format. Please enter a valid email address.";
  } elseif (strlen($email) > 254) {
    $error = "❌ Email address is too long.";
  } else {
    // Check if domain exists
    list($user, $domain) = explode('@', $email);

    // Check domain reputation
    $domain_reputation = check_domain_reputation($domain);
    if ($domain_reputation === 'spam') {
      $error = "❌ This email domain has a bad reputation. Please use a different email.";
    } elseif ($domain_reputation === 'suspicious') {
      $error = "❌ This email domain is suspicious. Please use a different email.";
    } else {
      if (!checkdnsrr($domain, 'MX')) {
        $error = "❌ The email domain does not exist or is invalid.";
      } elseif ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
      } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        try {
          $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
          $stmt->bind_param("ssss", $username, $email, $hashed, $role);
          $stmt->execute();
          $success = "✅ User registered successfully!";
          $stmt->close();
        } catch (mysqli_sql_exception $e) {
          if (str_contains($e->getMessage(), 'Duplicate entry')) {
            $error = "❌ Username or email already exists.";
          } else {
            $error = "❌ Registration failed: " . $e->getMessage();
          }
        }
      }
    }
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Modal password update
  if (isset($_POST['update_password'])) {
    $id = $_POST['id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {
      $error = "Passwords do not match.";
    } else {
      $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
      $sql = "UPDATE users SET password='$hashed_password' WHERE id='$id'";
      mysqli_query($conn, $sql);
      $success = "Password updated successfully.";
    }
  }
}

// Handle dropout
if (isset($_GET['dropout'])) {
  $id = $_GET['dropout'];
  $conn->query("UPDATE users SET status='dropout' WHERE id=$id");
  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit;
}

// Handle restore
if (isset($_GET['restore'])) {
  $id = intval($_GET['restore']);
  $conn->query("UPDATE users SET status='active' WHERE id=$id");
  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit;
}

// Handle permanent delete
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];

  // First delete audit log entries for this user
  $conn->query("DELETE FROM audit_log WHERE user_id=$id");

  // Then delete the user
  $conn->query("DELETE FROM users WHERE id=$id");

  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit;
}

// Handle user edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
  $id = $_POST['id'];
  $username = $_POST['edit_username'];
  $email = $_POST['edit_email'];
  $role = $_POST['edit_role'];

  $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
  $stmt->bind_param("sssi", $username, $email, $role, $id);
  if ($stmt->execute()) {
    $success = "✅ User info updated successfully!";
  } else {
    $error = "❌ Error updating user: " . $stmt->error;
  }
  $stmt->close();
}

// Debug: Check all users and their statuses
$all_users = $conn->query("SELECT id, username, status FROM users");
$debug_info = [];
while ($user = $all_users->fetch_assoc()) {
    $debug_info[] = "ID: {$user['id']}, Username: {$user['username']}, Status: " . ($user['status'] ?: 'NULL/empty');
}

// Get active users (status = 'active' or NULL/empty for backward compatibility)
$active_users = $conn->query("SELECT * FROM users WHERE status = 'active' OR status IS NULL OR status = '' ORDER BY id DESC");

// Get dropout users (status = 'dropout')
$dropout_users = $conn->query("SELECT * FROM users WHERE status = 'dropout' ORDER BY id DESC");
$dropout_count = $dropout_users->num_rows;

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Manage & Register Users</title>
  <style>
    /* Custom Confirmation Dialog Styles */
    .confirmation-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 2000;
      justify-content: center;
      align-items: center;
    }

    .confirmation-dialog {
      background: white;
      padding: 25px;
      border-radius: 8px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .confirmation-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: center;
      gap: 10px;
    }

    .confirmation-buttons button {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    }

    .btn-confirm {
      background-color: #e74c3c;
      color: white;
    }

    .btn-cancel {
      background-color: #95a5a6;
      color: white;
    }
  </style>

  <script>
    // Email validation
    document.addEventListener('DOMContentLoaded', function() {
      const emailInput = document.querySelector('input[name="email"]');
      const emailFeedback = document.createElement('div');
      emailFeedback.className = 'email-feedback';
      emailInput.parentNode.insertBefore(emailFeedback, emailInput.nextSibling);

      emailInput.addEventListener('input', function() {
        const email = this.value.trim();
        emailFeedback.textContent = '';
        emailFeedback.className = 'email-feedback';

        if (email === '') return; // Don't validate empty field

        if (!isValidEmailFormat(email)) {
          emailFeedback.textContent = '❌ Invalid email format';
          emailFeedback.className = 'email-feedback error';
          return;
        }

        if (isDisposableEmail(email)) {
          emailFeedback.textContent = '❌ Disposable email addresses are not allowed';
          emailFeedback.className = 'email-feedback error';
          return;
        }

        emailFeedback.textContent = '✅ Valid email format';
        emailFeedback.className = 'email-feedback success';
      });
    });

    function isValidEmailFormat(email) {
      const regex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
      return regex.test(email);
    }

    function isDisposableEmail(email) {
      const disposableDomains = [
        'mailinator', 'guerrillamail', '10minutemail', 'temp-mail',
        'tempmail', 'fakemail', 'throwawaymail', 'mailnesia',
        'trashmail', 'mailnesia', 'mailnull', 'mailinator',
        'spamherelot', 'spamhereplease', 'mailinator', 'mailinator'
      ];

      const domain = email.split('@')[1]?.toLowerCase();
      return disposableDomains.some(domainPart => domain?.includes(domainPart));
    }

    // Password visibility toggle
    function togglePassword(icon) {
      const input = icon.previousElementSibling;
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '👁️';
      } else {
        input.type = 'password';
        icon.textContent = '👁️';
      }
    }
  </script>

  <style>
    :root {
      --primary-blue: #1a73e8;
      --light-blue: #e8f0fe;
      --dark-blue: #0d47a1;
      --white: #ffffff;
      --red-accent: #d32f2f;
      --light-red: #ffebee;
      --light-gray: #f5f5f5;
      --border-radius: 8px;
      --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--light-blue);
      margin: 0;
      padding: 20px;
      color: #333;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      background: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
    }

    h2 {
      text-align: center;
      color: var(--primary-blue);
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid var(--primary-blue);
    }

    .top-actions {
      display: flex;
      justify-content: space-between;
      margin-bottom: 25px;
      gap: 15px;
    }

    .btn {
      background: var(--primary-blue);
      color: var(--white);
      padding: 10px 20px;
      border: none;
      font-weight: 600;
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn:hover {
      background: var(--dark-blue);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-danger {
      background: var(--red-accent);
    }

    .btn-danger:hover {
      background: #b71c1c;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--white);
      box-shadow: var(--box-shadow);
      margin-bottom: 20px;
      border-radius: var(--border-radius);
      overflow: hidden;
    }

    th,
    td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background: var(--primary-blue);
      color: var(--white);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85em;
      letter-spacing: 0.5px;
    }

    tr:nth-child(even) {
      background: var(--light-gray);
    }

    tr:hover {
      background: #e3f2fd;
    }

    input[type="password"],
    input[type="text"],
    input[type="email"] {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      transition: border 0.3s;
    }

    input[type="password"]:focus,
    input[type="text"]:focus,
    input[type="email"]:focus {
      border-color: var(--primary-blue);
      outline: none;
      box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
    }

    select {
      padding: 8px 12px;
      border-radius: 4px;
      border: 1px solid #ccc;
      width: 100%;
      background: var(--white);
    }

    .modal {
      display: none;
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
      backdrop-filter: blur(3px);
    }

    .modal-content {
      background: var(--white);
      padding: 30px;
      width: 90%;
      max-width: 500px;
      border-radius: var(--border-radius);
      position: relative;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .modal h3 {
      margin-top: 0;
      text-align: center;
      color: var(--primary-blue);
      margin-bottom: 20px;
      font-size: 1.5em;
    }

    .modal input,
    .modal select {
      width: 100%;
      padding: 12px;
      margin: 8px 0 15px;
      border: 1px solid #ddd;
      border-radius: var(--border-radius);
      font-size: 1em;
    }

    .close {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 24px;
      color: #666;
      cursor: pointer;
      transition: color 0.3s;
    }

    .close:hover {
      color: var(--red-accent);
    }

    .msg {
      text-align: center;
      font-weight: 600;
      padding: 12px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
      border: 1px solid transparent;
    }

    .success {
      background: #e8f5e9;
      color: #2e7d32;
      border-color: #c8e6c9;
    }

    .error {
      background: var(--light-red);
      color: var(--red-accent);
      border-color: #ef9a9a;
    }

    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
    }

    .action-link {
      color: var(--primary-blue);
      text-decoration: none;
      margin: 0 5px;
      font-weight: 500;
      transition: color 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .action-link:hover {
      color: var(--dark-blue);
      text-decoration: underline;
    }

    .action-link.danger {
      color: var(--red-accent);
    }

    .action-link.danger:hover {
      color: #b71c1c;
    }

    .dropout-modal {
      width: 90%;
      max-width: 900px;
    }

    @media (max-width: 768px) {
      .top-actions {
        flex-direction: column;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }

      table {
        display: block;
        overflow-x: auto;
      }
    }
  </style>
</head>

<body>
  <!-- Dropout Confirmation Dialog -->
  <div id="confirmationOverlay" class="confirmation-overlay">
    <div class="confirmation-dialog">
      <h3>Confirm Action</h3>
      <p>Are you sure you want to drop this user?</p>
      <div class="confirmation-buttons">
        <button id="confirmDropout" class="btn-confirm">Yes, Drop User</button>
        <button id="cancelDropout" class="btn-cancel">Cancel</button>
      </div>
    </div>
  </div>
  
  <!-- Delete Confirmation Dialog -->
  <div id="deleteConfirmationOverlay" class="confirmation-overlay">
    <div class="confirmation-dialog">
      <h3>Confirm Permanent Deletion</h3>
      <p>Are you sure you want to permanently delete this user? This action cannot be undone.</p>
      <div class="confirmation-buttons">
        <button id="confirmDelete" class="btn-confirm danger">Yes, Delete Permanently</button>
        <button id="cancelDelete" class="btn-cancel">Cancel</button>
      </div>
    </div>
  </div>

  <div class="container">
    <h2>User Management</h2>

    <?php if ($success): ?>
      <div class='msg success'><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class='msg error'><?= $error ?></div>
    <?php endif; ?>

    <div class="top-actions">
      <button class="btn" onclick="openRegisterModal()">
        <span>➕</span> Add New User
      </button>

      <button class="btn" onclick="document.getElementById('dropoutModal').style.display='flex'">
        <span>🚫</span> View Dropout Users
      </button>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
      <div class="modal-content">
        <span class="close" onclick="document.getElementById('registerModal').style.display='none'">&times;</span>
        <h3>Register New User</h3>
        <form method="POST">
          <input type="text" name="username" placeholder="Username" required>
          <input type="email" name="email" placeholder="Email" required>

          <div class="password-wrapper">
            <input type="password" name="password" placeholder="Password (min 8 chars)" required>
            <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
          </div>

          <div class="password-wrapper">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
          </div>

          <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="Admin">Admin</option>
            <option value="User">User</option>
          </select>
          <input type="submit" name="register_user" value="Register" class="btn">
        </form>
      </div>
    </div>

    <!-- Dropout Modal -->
    <div class="modal" id="dropoutModal">
      <div class="modal-content dropout-modal">
        <span class="close" onclick="document.getElementById('dropoutModal').style.display='none'">&times;</span>
        <h3>Dropout Users (<?= $dropout_count ?> found)</h3>
        
        <?php /* Debug information - commented out
        <?php if (!empty($debug_info)): ?>
        <div style="background: #f8f9fa; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 12px; display: none;">
          <strong>Debug Info:</strong>
          <pre style="margin: 5px 0 0 0; white-space: pre-wrap;"><?= htmlspecialchars(implode("\n", $debug_info)) ?></pre>
        </div>
        <?php endif; ?>
        */ ?>
        
        <table>
          <tr>
            <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th>
          </tr>
          <?php 
          // Reset the pointer to the beginning of the result set
          $dropout_users->data_seek(0);
          while ($d = $dropout_users->fetch_assoc()): 
          ?>
            <tr>
              <td><?= $d['id'] ?></td>
              <td><?= htmlspecialchars($d['username']) ?></td>
              <td><?= htmlspecialchars($d['email']) ?></td>
              <td><?= $d['role'] ?? 'N/A' ?></td>
              <td><?= $d['status'] ?? 'NULL' ?></td>
              <td>
                <a href="?restore=<?= $d['id'] ?>" class="action-link" title="Restore User">
                  <span>🔄</span> Restore
                </a> | 
                <a href="#" 
                   onclick="return showDeleteConfirmation(<?= $d['id'] ?>);" 
                   class="action-link danger" 
                   title="Permanently Delete">
                  <span>🗑️</span> Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
          
          <?php if ($dropout_count === 0): ?>
            <tr>
              <td colspan="6" style="text-align: center; padding: 20px;">
                No dropout users found. Users marked as 'dropout' will appear here.
              </td>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- User Table -->
    <table>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
      <?php while ($row = $active_users->fetch_assoc()): ?>
        <tr>
          <form method="POST">
            <td><?= $row['id'] ?><input type="hidden" name="id" value="<?= $row['id'] ?>"></td>
            <td><input type="text" name="edit_username" value="<?= htmlspecialchars($row['username']) ?>" readonly></td>
            <td><input type="email" name="edit_email" value="<?= htmlspecialchars($row['email']) ?>" readonly></td>
            <td>
              <select name="edit_role" disabled>
                <option value="Admin" <?= $row['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="User" <?= $row['role'] == 'User' ? 'selected' : '' ?>>User</option>
              </select>
            </td>

            <td>
              <button type="button" class="btn edit-btn" onclick="enableEdit(this)">
                <span>✏️</span> Edit
              </button>

              <button type="submit" name="edit_user" class="btn save-btn" style="display: none;">
                <span>💾</span> Save
              </button>

              <button type="button" class="btn" onclick="openPasswordModal(<?= $row['id'] ?>)">
                <span>🔒</span> Password
              </button>

              <a href="#"
                onclick="return showConfirmation(<?= $row['id'] ?>);"
                class="action-link danger"
                title="Mark as Dropout">
                <span>🚫</span> Dropout
              </a>
            </td>
          </form>
        </tr>
      <?php endwhile; ?>
    </table>

    <!-- Update Password Modal -->
    <div class="modal" id="passwordModal">
      <div class="modal-content">
        <span class="close" onclick="closePasswordModal()">&times;</span>
        <h3>Update Password</h3>
        <form method="POST">
          <input type="hidden" name="id" id="password_user_id">

          <div class="password-wrapper">
            <input type="password" name="new_password" placeholder="New Password (min 8 chars)" required>
            <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
          </div>

          <div class="password-wrapper">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
          </div>

          <input type="submit" name="update_password" value="Update Password" class="btn">
        </form>
      </div>
    </div>
  </div>

  <script>
    // Store the current user ID and action type
    let currentUserId = null;
    let currentAction = null; // 'dropout' or 'delete'

    // Function to show confirmation dialog
    function showConfirmation(userId) {
      currentUserId = userId;
      document.getElementById('confirmationOverlay').style.display = 'flex';
      return false; // Prevent default action
    }

    function showDeleteConfirmation(userId) {
      currentUserId = userId;
      currentAction = 'delete';
      document.getElementById('deleteConfirmationOverlay').style.display = 'flex';
      return false; // Prevent default action
    }

    // Close confirmation dialog
    function closeConfirmation() {
      document.getElementById('confirmationOverlay').style.display = 'none';
      document.getElementById('deleteConfirmationOverlay').style.display = 'none';
      currentUserId = null;
      currentAction = null;
    }

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Handle confirmation dialog actions
      document.getElementById('confirmDropout').addEventListener('click', function(e) {
        e.preventDefault();
        if (currentUserId) {
          window.location.href = '?dropout=' + currentUserId;
          closeConfirmation();
        }
      });

      document.getElementById('cancelDropout').addEventListener('click', function(e) {
        e.preventDefault();
        closeConfirmation();
      });

      document.getElementById('confirmDelete').addEventListener('click', function(e) {
        e.preventDefault();
        if (currentUserId) {
          window.location.href = '?delete=' + currentUserId;
          closeConfirmation();
        }
      });

      document.getElementById('cancelDelete').addEventListener('click', function(e) {
        e.preventDefault();
        closeConfirmation();
      });

      // Close dialog when clicking outside
      document.getElementById('confirmationOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
          closeConfirmation();
        }
      });

      document.getElementById('deleteConfirmationOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
          closeConfirmation();
        }
      });
    });

    function enableEdit(button) {
      const row = button.closest('tr');
      const inputs = row.querySelectorAll('input[type="text"], input[type="email"]');
      const select = row.querySelector('select');

      inputs.forEach(input => input.removeAttribute('readonly'));
      if (select) select.removeAttribute('disabled');

      button.style.display = 'none';
      const saveBtn = row.querySelector('.save-btn');
      if (saveBtn) saveBtn.style.display = 'inline-flex';
    }

    function togglePassword(icon) {
      const input = icon.previousElementSibling;
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '👁️';
      } else {
        input.type = 'password';
        icon.textContent = '👁️';
      }
    }

    function openPasswordModal(userId) {
      document.getElementById('password_user_id').value = userId;
      document.getElementById('passwordModal').style.display = 'flex';
    }

    function closePasswordModal() {
      document.getElementById('passwordModal').style.display = 'none';
    }

    function openRegisterModal() {
      const modal = document.getElementById('registerModal');
      modal.style.display = 'flex';
      modal.querySelector('form').reset();
    }

    window.onclick = function(event) {
      ['registerModal', 'dropoutModal', 'passwordModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (event.target == modal) modal.style.display = "none";
      });
    }
  </script>
</body>

</html>