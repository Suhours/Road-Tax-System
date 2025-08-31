<?php
session_start();

$conn = new mysqli("localhost", "root", "", "roadtaxsystem");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        if ($user['role'] === 'Admin') {
            header("Location: dashboard/dashboard.php");
            exit;
        } elseif ($user['role'] === 'User') {
            header("Location: dashboard_user.php");
            exit;
        }
    } else {
        $error = "Login failed. Username or password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #333333;
            --box-bg: #ffffff;
            --box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
            --input-border: #007bff;
            --input-bg: #ffffff;
            --button-bg: #007bff;
            --button-hover: #0056b3;
            --error-color: #dc3545;
            --rejected-bg: #f8d7da;
            --rejected-border: #f5c6cb;
            --rejected-text: #721c24;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --box-bg: #2d2d2d;
            --box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            --input-border: #4a9eff;
            --input-bg: #3a3a3a;
            --button-bg: #4a9eff;
            --button-hover: #357abd;
            --error-color: #ff6b6b;
            --rejected-bg: #4a2c2c;
            --rejected-border: #6b3a3a;
            --rejected-text: #ffb3b3;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('img/login-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--box-bg);
            border: 2px solid var(--input-border);
            border-radius: 50px;
            padding: 10px;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        .theme-toggle svg {
            width: 20px;
            height: 20px;
            fill: var(--text-color);
        }

        .login-box {
            background: var(--box-bg);
            padding: 40px;
            border-radius: 25px;
            box-shadow: var(--box-shadow);
            text-align: center;
            max-width: 400px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .login-box img {
            width: 80px;
            margin-bottom: 20px;
        }

        .login-box h2 {
            color: var(--button-bg);
            margin-bottom: 30px;
            transition: color 0.3s ease;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 2px solid var(--input-border);
            border-radius: 25px;
            background: var(--input-bg);
            color: var(--text-color);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .login-box input[type="text"]:focus,
        .login-box input[type="password"]:focus {
            outline: none;
            border-color: var(--button-bg);
            box-shadow: 0 0 10px rgba(74, 158, 255, 0.3);
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background: var(--button-bg);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-box button:hover {
            background: var(--button-hover);
            transform: translateY(-2px);
        }

        .login-box a {
            color: var(--button-bg);
            text-decoration: none;
            display: block;
            margin-top: 15px;
            transition: color 0.3s ease;
        }

        .login-box a:hover {
            color: var(--button-hover);
        }

        .error-message {
            color: var(--error-color);
            margin-bottom: 15px;
        }

        .rejected-message {
            background-color: var(--rejected-bg);
            color: var(--rejected-text);
            border: 1px solid var(--rejected-border);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        /* Dark mode background overlay */
        [data-theme="dark"] body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
        <svg class="sun-icon" viewBox="0 0 24 24">
            <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
        </svg>
        <svg class="moon-icon" style="display: none;" viewBox="0 0 24 24">
            <path d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z"/>
        </svg>
    </div>

    <form method="POST" class="login-box">
        <img src="img/logo.png" alt="Logo">
        <h2>Login</h2>

        <?php if (isset($_GET['rejected'])): ?>
            <div class="rejected-message">
                ❌ Your password reset request was rejected by the admin.
            </div>
        <?php endif; ?>

        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>

        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <a href="forgetpassword/forgot.php">Forgot Password?</a>
        <button type="submit">Login</button>
    </form>

    <script>
        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        updateThemeIcon(currentTheme);

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const sunIcon = document.querySelector('.sun-icon');
            const moonIcon = document.querySelector('.moon-icon');
            
            if (theme === 'dark') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        }
    </script>
</body>
</html>