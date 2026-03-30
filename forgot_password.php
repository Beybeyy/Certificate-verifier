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

            $stmt = $conn->prepare("UPDATE users SET password_reset_token=?, token_expires=? WHERE id=?");
            $stmt->bind_param("ssi", $token, $expires, $user['id']);
            $stmt->execute();

            $reset_link = "http://10.10.8.218:8080/Certificate-verifier/set_password.php?token=$token";
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'depedcertificateverifier@gmail.com'; 
                $mail->Password   = 'apebhfivgtvqkche'; 
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
                $message = "<span style='color: green;'>✅ Reset link sent to your email.</span>";
            } catch (Exception $e) {
                $message = "<span style='color: red;'>❌ Email could not be sent.</span>";
            }
        } else {
            $message = "<span style='color: red;'>❌ Email not found.</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | CerVer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/cerverlogo2.svg">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #e4e4e6;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
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
            font-size: 20px;
            font-weight: bold;
            line-height: 1.2;
        }

        .nav-brand strong {
            font-size: 22px;
            font-weight: 300;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            transition: 0.3s;
        }

        .nav-links a:hover { opacity: 0.8; text-decoration: underline; }

        /* ===== LOGOS & PAGE CONTENT ===== */
        .logo-container { display: flex; justify-content: center; align-items: center; gap: 10px; padding: 10px; }
        .logo-container img { height: 200px; width: auto; object-fit: contain; } 

        /* ===== CARD ===== */
        .card-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Aligns card near top like login */
            padding: 20px;
        }

        .card {
            background: #fff;
            padding: 40px 30px;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            border: 1px solid #0b4a82;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .card h2 {
            color: #0b4a82;
            margin-bottom: 25px;
            font-size: 24px;
        }

        .card input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .card button {
            background-color: #0b4a82;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: 0.3s;
        }

        .card button:hover { background-color: #004085; }

        .message { font-size: 14px; margin-bottom: 20px; font-weight: bold; }

        .back-link {
            text-decoration: none;
            color: #666;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
        }

        .back-link:hover { color: #0b4a82; }

        /* ===== FOOTER ===== */
        footer { 
            background-color: #fff; 
            padding: 20px 40px; 
            font-size: 13px; 
            border-top: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: auto;
        }

        /* ===== MOBILE RESPONSIVE ===== */
        .burger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            z-index: 1001;
        }

        .burger span {
            height: 3px;
            width: 25px;
            background: white;
            border-radius: 3px;
            transition: 0.4s;
        }

        @media (max-width: 768px) {
            .top-nav { padding: 15px 20px; }
            .burger { display: flex; }
            .nav-links {
                position: fixed;
                right: -100%;
                top: 0;
                height: 100vh;
                width: 200px;
                background: #0b4a82;
                flex-direction: column;
                padding: 80px 20px;
                transition: 0.4s ease-in-out;
                box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            }
            .nav-links.active { right: 0; }
            .nav-links a { font-size: 18px; width: 100%; padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
            
            .burger.toggle span:nth-child(1) { transform: rotate(-45deg) translate(-5px, 6px); }
            .burger.toggle span:nth-child(2) { opacity: 0; }
            .burger.toggle span:nth-child(3) { transform: rotate(45deg) translate(-5px, -6px); }

            footer { flex-direction: column; text-align: center; gap: 10px; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        DEPARTMENT OF EDUCATION<br>
        <strong>CerVer - Certificate Verifier</strong>
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
    <img src="img/sdo_logo.svg" alt="Logo">
    
</div>

<main class="card-wrapper">
    <div class="card">
        <h2>Forgot Password</h2>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <a href="login.php" class="back-link">
            <span>&larr;</span> Back to login
        </a>
    </div>
</main>

<footer>
    <div>© 2026 Department of Education Certificate Verifier System</div>
    <div>Front-End Developer: Larry Cruz | Back-End Developer: Bea Patrice Cortez</div>
</footer>

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