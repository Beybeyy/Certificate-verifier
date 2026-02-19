<?php
require_once __DIR__ . "/config/db.php";

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (!$token) {
    die("Invalid link.");
}

// Fetch user by token
$stmt = $conn->prepare(
    "SELECT id, token_expires FROM users
     WHERE password_reset_token = ?"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Invalid link.");
}

$user = $result->fetch_assoc();

// Check expiry
if (strtotime($user['token_expires']) < time()) {
    die("Link expired.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "UPDATE users
             SET password = ?, password_reset_token = NULL, token_expires = NULL
             WHERE id = ?"
        );
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();

        $success = "Password set successfully! <a href='login.php'>Login here</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Set New Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    * { box-sizing: border-box; }

    body {
        margin: 0;
        font-family: "Segoe UI", Arial, sans-serif;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* ===== TOP NAV ===== */
    .top-nav {
        background-color: #0b4a82;
        padding: 15px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #ffffff;
        position: relative;
        z-index: 1000;
    }

    .nav-brand {
        font-size: 18px;
        line-height: 1.2;
        font-weight: 500;
    }

    .burger {
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 5px;
        z-index: 1001;
    }

    .burger span {
        height: 3px;
        width: 28px;
        background: white;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .burger.toggle span:nth-child(1) { transform: rotate(-45deg) translate(-5px, 6px); }
    .burger.toggle span:nth-child(2) { opacity: 0; }
    .burger.toggle span:nth-child(3) { transform: rotate(45deg) translate(-5px, -6px); }

    .nav-links {
        display: flex;
        align-items: center;
        transition: 0.3s ease-in-out;
    }

    .nav-links a {
        color: #ffffff;
        text-decoration: none;
        margin-left: 35px;
        font-size: 15px;
        font-weight: 400;
    }

    .nav-links a:hover { text-decoration: underline; }

    /* ===== PASSWORD CARD ===== */
    .card-wrapper {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .card {
        background: #fff;
        padding: 40px 35px;
        border-radius: 25px;
        width: 100%;
        max-width: 450px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .card h2 {
        color: #0b4a82;
        margin-bottom: 25px;
        font-size: 26px;
    }

    .card input {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1.2px solid #8ba8c7;
        border-radius: 8px;
        font-size: 15px;
    }

    .card button {
        background-color: #0056b3;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
    }

    .card button:hover { background-color: #004494; }

    .card p { font-size: 14px; margin-bottom: 15px; }
    .card p a { color: #0b4a82; font-weight: bold; text-decoration: none; }
    .card p a:hover { text-decoration: underline; }

    @media (max-width: 768px) {
        .top-nav { padding: 15px 20px; }
        .burger { display: flex; }
        .nav-links {
            position: fixed;
            right: -100%;
            top: 0;
            height: 100vh;
            width: 30%;
            background-color: #0b4a82;
            flex-direction: column;
            justify-content: center;
            gap: 30px;
            box-shadow: -5px 0 15px rgba(0,0,0,0.2);
        }
        .nav-links.active { right: 0; }
        .nav-links a { margin: 0; font-size: 20px; width: 100%; text-align: center; }
    }
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        Department of Education<br>Certificate Verifier
    </div>

    <div class="burger" id="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="nav-links" id="nav-menu">
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
    </div>
</nav>

<main class="card-wrapper">
    <div class="card">
        <h2>Set New Password</h2>

        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
        <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

        <form method="POST">
            <input type="password" name="password" placeholder="New password" required>
            <input type="password" name="confirm" placeholder="Confirm password" required>
            <button type="submit">Set Password</button>
        </form>
    </div>
</main>

<script>
    const burger = document.getElementById('burger');
    const navMenu = document.getElementById('nav-menu');

    burger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        burger.classList.toggle('toggle');
    });

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            burger.classList.remove('toggle');
        });
    });
</script>

</body>
</html>