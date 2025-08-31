<?php
include '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Update reset_requested to 2 = approved
    $stmt = $conn->prepare("UPDATE users SET reset_requested = 2 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<p style='
            font-family: Arial;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 20px;
            width: 500px;
            margin: 50px auto;
            border-radius: 8px;
            text-align: center;
        '>✅ Request approved. User can now reset their password.</p>";
    } else {
        echo "<p style='
            font-family: Arial;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 20px;
            width: 500px;
            margin: 50px auto;
            border-radius: 8px;
            text-align: center;
        '>❌ Approval failed. Please try again.</p>";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
