<?php
session_start();

include 'db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function log_action($conn, $user_id, $action, $page, $details = '') {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = 'unknown';
    }

    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, page, details, ip) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $action, $page, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

$maxAttempts = 3;
$lockTime = 60;

if (!isset($_SESSION['failed_attempts'])) $_SESSION['failed_attempts'] = 0;
if (!isset($_SESSION['last_attempt_time'])) $_SESSION['last_attempt_time'] = 0;

$remaining = $lockTime - (time() - $_SESSION['last_attempt_time']);
if ($_SESSION['failed_attempts'] >= $maxAttempts && $remaining > 0) {
    $error = "⛔ Too many login attempts. Try again in $remaining seconds.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;

        log_action($conn, $user['id'], 'Login', 'login.php', 'User logged in');

        if ($user['role'] === 'Admin') {
            header("Location: dashboard/dashboard");
            exit;
        } elseif ($user['role'] === 'User') {
            header("Location: users/dashboard_user");
            exit;
        }
    } else {
        $_SESSION['failed_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $attemptsLeft = $maxAttempts - $_SESSION['failed_attempts'];
        if ($attemptsLeft <= 0) {
            $error = "⛔ Too many login attempts. Try again in $lockTime seconds.";
        } else {
            $error = "❌ Login failed. You have $attemptsLeft attempt(s) left.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | System Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-blue: #1a73e8;
      --dark-blue: #0d47a1;
      --light-blue: #e8f0fe;
      --white: #ffffff;
      --off-white: #f8f9fa;
      --gray: #5f6368;
      --light-gray: #dadce0;
      --error-red: #d32f2f;
      --warning-yellow: #f9a825;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Roboto', 'Segoe UI', sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, var(--light-blue), var(--white));
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      overflow: hidden;
    }
    
    .background-animation {
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: 0;
    }
    
    .bubble {
      position: absolute;
      border-radius: 50%;
      background: rgba(26, 115, 232, 0.1);
      animation: float 15s infinite linear;
    }
    
    .bubble:nth-child(1) {
      width: 300px;
      height: 300px;
      top: 20%;
      left: 10%;
      animation-delay: 0s;
    }
    
    .bubble:nth-child(2) {
      width: 200px;
      height: 200px;
      top: 60%;
      left: 20%;
      animation-delay: 2s;
    }
    
    .bubble:nth-child(3) {
      width: 250px;
      height: 250px;
      top: 30%;
      right: 15%;
      animation-delay: 4s;
    }
    
    .bubble:nth-child(4) {
      width: 180px;
      height: 180px;
      bottom: 20%;
      right: 20%;
      animation-delay: 6s;
    }
    
    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 420px;
      background: var(--white);
      border-radius: 12px;
      padding: 40px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      animation: fadeInUp 0.8s ease-out;
    }
    
    .logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .logo img {
      width: 80px;
      height: auto;
      transition: transform 0.3s ease;
    }
    
    .logo img:hover {
      transform: scale(1.1);
    }
    
    .logo h1 {
      color: var(--primary-blue);
      font-size: 24px;
      margin-top: 15px;
      font-weight: 600;
    }
    
    .login-form h2 {
      color: var(--gray);
      text-align: center;
      margin-bottom: 25px;
      font-weight: 500;
      font-size: 20px;
    }
    
    .input-group {
      position: relative;
      margin-bottom: 20px;
    }
    
    .input-group i {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: var(--gray);
      transition: color 0.3s;
    }
    
    .input-group input {
      width: 100%;
      padding: 14px 20px 14px 45px;
      background: var(--off-white);
      border: 1px solid var(--light-gray);
      border-radius: 8px;
      color: var(--gray);
      font-size: 15px;
      transition: all 0.3s ease;
    }
    
    .input-group input:focus {
      outline: none;
      border-color: var(--primary-blue);
      background: var(--white);
      box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
    }
    
    .input-group input:focus + i {
      color: var(--primary-blue);
    }
    
    .input-group input::placeholder {
      color: var(--gray);
      opacity: 0.7;
    }
    
    .remember-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      font-size: 14px;
    }
    
    .remember-me {
      display: flex;
      align-items: center;
      color: var(--gray);
    }
    
    .remember-me input {
      margin-right: 8px;
      accent-color: var(--primary-blue);
    }
    
    .forgot-password a {
      color: var(--primary-blue);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    
    .forgot-password a:hover {
      color: var(--dark-blue);
      text-decoration: underline;
    }
    
    .login-btn {
      width: 100%;
      padding: 14px;
      background: var(--primary-blue);
      border: none;
      border-radius: 8px;
      color: var(--white);
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .login-btn:hover {
      background: var(--dark-blue);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(26, 115, 232, 0.4);
    }
    
    .login-btn:active {
      transform: translateY(0);
    }
    
    .login-btn::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        to bottom right,
        rgba(255, 255, 255, 0.3),
        rgba(255, 255, 255, 0)
      );
      transform: rotate(30deg);
      transition: all 0.3s;
    }
    
    .login-btn:hover::after {
      left: 100%;
    }
    
    .error-message {
      color: var(--error-red);
      background: rgba(211, 47, 47, 0.1);
      padding: 14px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
      border: 1px solid rgba(211, 47, 47, 0.2);
      animation: shake 0.5s ease;
      font-size: 14px;
    }
    
    .error-message i {
      margin-right: 8px;
    }
    
    .rejected-message {
      color: var(--warning-yellow);
      background: rgba(249, 168, 37, 0.1);
      padding: 14px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
      border: 1px solid rgba(249, 168, 37, 0.2);
      font-size: 14px;
    }
    
    .rejected-message i {
      margin-right: 8px;
    }
    
    .register-link {
      text-align: center;
      margin-top: 30px;
      color: var(--gray);
      font-size: 14px;
    }
    
    .register-link a {
      color: var(--primary-blue);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    
    .register-link a:hover {
      color: var(--dark-blue);
      text-decoration: underline;
    }
    
    @keyframes float {
      0% {
        transform: translateY(0) rotate(0deg);
      }
      50% {
        transform: translateY(-20px) rotate(180deg);
      }
      100% {
        transform: translateY(0) rotate(360deg);
      }
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 480px) {
      .login-container {
        padding: 30px 20px;
        margin: 0 15px;
        width: 95%;
      }
      
      .logo img {
        width: 70px;
      }
      
      .logo h1 {
        font-size: 22px;
      }
      
      .login-form h2 {
        font-size: 18px;
      }
      
      .input-group input {
        padding: 12px 15px 12px 40px;
      }
    }
  </style>
</head>
<body>
  <div class="background-animation">
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
  </div>
  
  <div class="login-container">
    <div class="logo">
      <img src="img/logo.png" alt="System Logo">
    </div>
    
    <form method="POST" class="login-form">
      <h2>Sign in to your account</h2>
      
      <?php if (isset($_GET['rejected'])): ?>
        <div class="rejected-message">
          <i class="fas fa-exclamation-triangle"></i> Your password reset request was rejected by the admin.
        </div>
      <?php endif; ?>
      
      <?php if (isset($error)): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="username" id="username" placeholder="Username" required>
      </div>
      
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      
      <div class="remember-forgot">
        <div class="remember-me">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">Remember me</label>
        </div>
        <div class="forgot-password">
          <a href="forgetpassword/forget_password.php">Forgot password?</a>
        </div>
      </div>
      
      <button type="submit" class="login-btn">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
    </form>
    
    <div class="register-link">
      Need access? <a href="#">Contact your administrator</a>
    </div>
  </div>

  <script>
    // Save username if "Remember me" is checked
    document.querySelector('form').addEventListener('submit', function(e) {
      const rememberMe = document.getElementById('remember').checked;
      const username = document.getElementById('username').value;
      
      if (rememberMe) {
        localStorage.setItem('rememberedUsername', username);
      } else {
        localStorage.removeItem('rememberedUsername');
      }
    });

    // Auto-fill username if remembered
    window.addEventListener('DOMContentLoaded', function() {
      const rememberedUsername = localStorage.getItem('rememberedUsername');
      if (rememberedUsername) {
        document.getElementById('username').value = rememberedUsername;
        document.getElementById('remember').checked = true;
      }
      
      // Add floating animation to bubbles
      const bubbles = document.querySelectorAll('.bubble');
      bubbles.forEach((bubble, index) => {
        // Randomize animation duration and delay
        const duration = 15 + Math.random() * 10;
        const delay = Math.random() * 5;
        bubble.style.animation = `float ${duration}s infinite ${delay}s linear`;
      });
    });
  </script>
</body>
</html>