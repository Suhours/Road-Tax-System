<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    exit("Access denied.");
}
$conn = new mysqli("localhost", "root", "", "roadtaxsystem");
$user_id = $_SESSION['user_id'];
$success = $error = "";

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if ($username && $email) {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        if ($stmt->execute()) {
            $success = "✅ Profile updated successfully.";
        } else {
            $error = "❌ Failed to update.";
        }
    } else {
        $error = "❌ All fields are required.";
    }
}

$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<style>
.modal-profile input {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.modal-profile label {
    font-weight: bold;
    display: block;
    margin-top: 10px;
}
.modal-profile .btn {
    width: 100%;
    background-color: #007bff;
    color: white;
    padding: 12px;
    border: none;
    margin-top: 15px;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}
.modal-profile .btn:hover {
    background-color: #0056b3;
}
</style>

<div class="modal-profile">
    <?php if ($success) echo "<div style='color:green; margin-bottom:10px;'>$success</div>"; ?>
    <?php if ($error) echo "<div style='color:red; margin-bottom:10px;'>$error</div>"; ?>

    <form id="profileForm" onsubmit="submitProfileForm(event)">
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Registered</label>
        <input type="text" value="<?= htmlspecialchars($user['created_at']) ?>" readonly>

        <button class="btn" type="submit">Update Profile</button>
    </form>
</div>
