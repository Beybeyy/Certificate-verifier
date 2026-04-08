<?php
session_start();
require_once __DIR__ . "/config/db.php";

/* ADMIN ONLY */
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* HANDLE FORM SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($fullname === '' || $email === '' || $password === '') {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email address.";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
        header("Location: register.php");
        exit();
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$check) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: register.php");
        exit();
    }

    $check->bind_param("s", $email);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['error'] = "Email already exists.";
        header("Location: register.php");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: register.php");
        exit();
    }

    $stmt->bind_param("ssss", $fullname, $email, $hashed, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Admin created successfully!";
    } else {
        $_SESSION['error'] = "Insert failed: " . $stmt->error;
    }

    header("Location: register.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin | CerVer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
            color: #0b4a82;
            text-align: center;
        }

        .msg {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .success { color: green; }
        .error { color: red; }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0b4a82;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #08375f;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #0b4a82;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Admin Account</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="msg success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="msg error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST">
            <label>Complete Name</label>
            <input type="text" name="fullname" required autocomplete="off">

            <label>Email Address</label>
            <input type="email" name="email" required autocomplete="off">

            <label>Password</label>
            <input type="password" name="password" required autocomplete="off">

            <button type="submit">Create Admin</button>
        </form>

        <a class="back" href="admin/dashboard.php">← Back to Admin Dashboard</a>
    </div>
</body>
</html>