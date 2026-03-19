<?php
session_start();
require_once __DIR__ . "/config/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | CerVer - Certificate Verifier</title>
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

        /* ===== TOP NAV (IDENTICAL) ===== */
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

        /* ===== LOGO SECTION (IDENTICAL) ===== */
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 20px;
        }

        .logo-container img {
            height: 100px;
            width: auto;
            object-fit: contain;
        }

        /* ===== REGISTER CARD ===== */
        .register-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        .register-card {
            width: 100%;
            max-width: 450px;
            border: 1px solid #0b4a82;
            border-radius: 20px;
            padding: 40px 35px;
            text-align: left;
            background: #fff;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .register-card h2 { 
            color: #0b4a82; 
            margin: 0 0 25px; 
            font-size: 24px; 
            text-align: center;
        }

        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        /* ===== BUTTONS ===== */
        .btn-register {
            background-color: #0b4a82;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .btn-register:hover {
            background-color: #004085;
            transform: scale(1.01);
        }

        .back-link {
            text-decoration: none;
            color: #666;
            font-size: 14px;
           
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        .back-link:hover { text-decoration: underline; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== FOOTER (IDENTICAL) ===== */
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
        <strong>CerVer - Certificate Verifier</strong>
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
    <img src="img/logo-deped-bagong-pilipinas-colored_orig.png" alt="DepEd Logo">
    <img src="img/deped-csjdm-logo.png" alt="Division Logo">
</div>

<main class="register-wrapper">
    <div class="register-card">
        <h2>Register</h2>
        
        <?php
        if (isset($_SESSION['success'])) {
            echo "<p style='color:green; text-align:center; font-weight:bold;'>" . $_SESSION['success'] . "</p>";
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo "<p style='color:red; text-align:center; font-weight:bold;'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }
        ?>

        <form action="registration_process.php" method="POST">
            <div class="form-group">
                <label>Complete Name</label>
                <input type="text" name="fullname" placeholder="Juan Dela Cruz" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="example@deped.gov.ph" required autocomplete="off">
            </div>
            
            <button type="submit" class="btn-register">Register Account</button>
            
            <a href="login.php" class="back-link">
                <span>&larr;</span> Back to Login
            </a>
        </form>
    </div>
</main>

<footer>
    <div>© 2026 Department of Education Certificate Verifier System</div>
    <div>Front-End Development: Larry Cruz | Back-End Development: Bea Patrice Cortez</div>
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