<?php
include '../db.php';
$user_id = 1; // admin ID toos ah haddii aan session la isticmaaleyn

$success = $error = "";

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);

    if (!empty($username) && !empty($email)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        if ($stmt->execute()) {
            $success = "✅ Profile updated successfully.";
        } else {
            $error = "❌ Failed to update profile.";
        }
    } else {
        $error = "❌ All fields are required.";
    }
}

$stmt = $conn->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #eef2f5;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 420px;
      margin: 60px auto;
      background: #fff;
      padding: 25px 30px;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: #444;
    }

    input {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .info-box {
      background: #f8f8f8;
      padding: 10px;
      border-left: 4px solid #007bff;
      margin: 10px 0;
      font-size: 14px;
    }

    .btn {
      width: 100%;
      background-color: #007bff;
      border: none;
      color: white;
      padding: 12px;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    .back-link {
      display: block;
      margin-top: 15px;
      text-align: center;
      color: #007bff;
      text-decoration: none;
    }

    .success {
      color: green;
      text-align: center;
      margin-bottom: 10px;
    }

    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Admin Profile</h2>
  <?php if ($success) echo "<div class='success'>$success</div>"; ?>
  <?php if ($error) echo "<div class='error'>$error</div>"; ?>

  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>

    <div class="info-box"><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></div>
    <div class="info-box"><strong>Registered:</strong> <?php echo htmlspecialchars($user['created_at']); ?></div>

    <button class="btn" type="submit">Update Profile</button>
  </form>


</div>

</body>
</html>