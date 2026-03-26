<?php
session_start();
require_once __DIR__ . "/config/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === "" || $password === "") {
        $error = "⚠️ Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, role, password FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $db_password = trim($user['password']);
            $login_ok = false;

            if (!empty($db_password)) {
                // Check if password is already hashed
                if (password_get_info($db_password)['algo'] !== 0) {
                    if (password_verify($password, $db_password)) $login_ok = true;
                } else {
                    // Plain text password (for migration)
                    if ($password === $db_password) {
                        $login_ok = true;
                        // Hash the plain password and update DB for security
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                        $stmt_update->bind_param("si", $hashed, $user['id']);
                        $stmt_update->execute();
                    }
                }
            }

            if ($login_ok) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === "teacher") {
                    header("Location: teacher/teacher_dash.php");
                } else {
                    header("Location: admin/dashboard.php");
                }
                exit();
            } else {
                $error = "❌ Invalid email or password.";
            }
        } else {
            $error = "❌ Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | CerVer - Certificate Verifier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/cerverlogo2.svg">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif;
            background: #e4e4e6;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== TOP NAV (Identical to Index) ===== */
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
            font-weight: 400;
            transition: 0.3s;
        }

        .nav-links a:hover { opacity: 0.8; text-decoration: underline; }

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

        /* ===== LOGOS & PAGE CONTENT ===== */
        .logo-container { display: flex; justify-content: center; align-items: center; gap: 10px; padding: 10px; }
        .logo-container img { height: 200px; width: auto; object-fit: contain; }
        /* ===== LOGIN CARD ===== */
        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: 1px solid #0b4a82;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            background: #fff;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .login-card h2 {
            color: #0b4a82;
            margin: 0 0 25px;
            font-size: 24px;
        }

        .form-group { margin-bottom: 15px; text-align: left; }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .error { color: #d32f2f; margin-bottom: 15px; font-weight: bold; font-size: 14px; }

        .forgot-password { text-align: right; margin-bottom: 15px; }
        .forgot-password a { color: #0b4a82; font-size: 14px; text-decoration: none; }

        .forgot-password a:hover {
        color: #0b4a82; /* Changes to your brand blue */
       
        opacity: 0.8;
        }

        .btn-login { 
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
            margin-bottom: 15px;
        }

        .btn-login:hover { background-color: #004085; }

        .register-text { font-size: 14px; margin-bottom: 20px; }
        .register-text a { color: #0b4a82; text-decoration: none; font-weight: 600; }

        .back-link {
            text-decoration: none;
            color: #666;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-link:hover {
            color: #0b4a82; /* Changes color to a lighter blue */
            
        }

        .back-link:hover span {
            transform: translateX(1px); /* Makes the arrow nudge to the left */
        }


        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

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
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        DEPARTMENT OF EDUCATION<br>
            CSJDM CERVER<br>
        Certificate Verifier System
    </div>
    </div>

    <div class="nav-links" id="nav-menu">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </div>

    <div class="burger" id="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<div class="logo-container">
<img src="img/sdologo.svg" alt="Logo">
    
</div>

<main class="login-wrapper">    
    <div class="login-card">
        <h2>Login</h2>
        
        <?php if($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required autocomplete="off">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required autocomplete="off">
            </div>
            
            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login">Login</button>

            <!--div class="register-text">
            Don't have an account? <a href="register.php">Register here</a>
            </div>-->
             
            <a href="index.php" class="back-link">
                <span>&larr;</span> Back to Verifier
            </a>
        </form>
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

    // Close menu when a link is clicked
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            burger.classList.remove('toggle'); 
        });
    });
</script>

</body>
</html>