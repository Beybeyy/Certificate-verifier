<?php
session_start();
require_once __DIR__ . "/config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/config/PHPMailer/Exception.php';
require __DIR__ . '/config/PHPMailer/PHPMailer.php';
require __DIR__ . '/config/PHPMailer/SMTP.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email']));

    if (empty($email)) {
        $message = "⚠️ Please enter your email.";
    } else {

        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Save token
            $stmt = $conn->prepare("UPDATE users SET password_reset_token=?, token_expires=? WHERE id=?");
            $stmt->bind_param("ssi", $token, $expires, $user['id']);
            $stmt->execute();

            // Create reset link
            $reset_link = "http://10.10.8.218:8080/Certificate-verifier/set_password.php?token=$token";

            // Send Email
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'depedcertificateverifier@gmail.com';   // CHANGE THIS
                $mail->Password   = 'apebhfivgtvqkche';     // CHANGE THIS
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('depedcertificateverifier@gmail.com', 'Certificate Verifier');
                $mail->addAddress($email, $user['name']);

                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password';
                $mail->Body    = "
                    Hi {$user['name']},<br><br>
                    Click the link below to reset your password:<br>
                    <a href='$reset_link'>$reset_link</a><br><br>
                    This link expires in 1 hour.
                ";

                $mail->send();
                $message = "✅ Reset link sent to your email.";
            } catch (Exception $e) {
                $message = "❌ Email could not be sent.";
            }

        } else {
            $message = "❌ Email not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
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

 /* ===== LOGO SECTION ===== */
 .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 10px 10px 10px 10px; /* Spacing between nav and login card */
        }

        .logo-container img {
            height: 140px; /* Adjust size to match your images */
            width: auto;
            object-fit: contain;
        }

        .logo-container img[alt="Division Logo"] {
            height: 90px; /* Smaller than the DepEd logo */
        }

.burger {
    display: none;
    flex-direction: column;
    cursor: pointer;
    gap: 5px;
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
}

.nav-links a {
    color: #ffffff;
    text-decoration: none;
    margin-left: 35px;
}

.nav-links a:hover { text-decoration: underline; }

/* ===== CARD ===== */
.card-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px;
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
    padding: 12px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
}

.card button:hover { background-color: #004494; }

.card p { font-size: 14px; margin-bottom: 15px; }

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
    }
    .nav-links.active { right: 0; }
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
        <a href="login.php">Login</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </div>
</nav>

<div class="logo-container">
    <img src="img/logo-deped-bagong-pilipinas-colored_orig.png" alt="DepEd Logo">
    <img src="img/deped-csjdm-logo.png" alt="Division Logo">
</div>

<main class="card-wrapper">
    <div class="card">
        <h2>Forgot Password</h2>

        <?php if ($message) echo "<p>$message</p>"; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
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
</script>

</body>
</html>
