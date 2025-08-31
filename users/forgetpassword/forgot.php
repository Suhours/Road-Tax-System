<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            text-align: center;
        }
        input, button {
            margin: 10px;
            padding: 10px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
        }
    </style>
</head>
<body>
    <form method="POST" action="request_reset.php">
        <h2>Forgot Password</h2>
        <input type="text" name="username" placeholder="Your Username" required><br>
        <input type="email" name="email" placeholder="Your Email" required><br>
        <button type="submit">Send Request</button>
    </form>
</body>
</html>
