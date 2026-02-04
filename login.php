<?php
session_start();
require_once __DIR__ . "/config/db.php";

$error = "";

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === "" || $password === "") {
        $error = "⚠️ Please enter email and password.";
    } else {
        // Check credentials
        $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Set sessions
            $_SESSION['id']    = $user['id'];   // Important: use 'id'
            $_SESSION['name']  = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role']  = $user['role'];

            // Redirect based on role
            if ($user['role'] === "teacher") {
                header("Location: teacher/teacher_dash.php");
                exit();
            } else {
                header("Location: admin/dashboard.php");
                exit();
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
    <title>Certificate Verifier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #ffffff;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden; /* Prevents side-scrolling on mobile */
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

        .nav-links a:hover {
            text-decoration: underline;
        }

        /* ===== LOGO SECTION ===== */
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 30px 10px 10px 10px; /* Spacing between nav and login card */
        }

        .logo-container img {
            height: 100px; /* Adjust size to match your images */
            width: auto;
            object-fit: contain;
        }

        /* ===== BURGER ICON & ANIMATION ===== */
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

        /* Animation to transform burger into 'X' */
        .burger.toggle span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        .burger.toggle span:nth-child(2) {
            opacity: 0;
        }
        .burger.toggle span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        

        /* ===== LOGIN CONTAINER ===== */
        .login-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: 1.5px solid #0056b3;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            background: #fff;
        }

        .login-card h2 {
            color: #004085;
            margin-bottom: 25px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1.2px solid #8ba8c7;
            border-radius: 8px;
            font-size: 16px;
        }

        /* ===== BUTTONS ===== */
        .btn-upload {
            background-color: #d3d3d3; /* Gray by default */
            color: #777;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            font-size: 14px;
            cursor: not-allowed;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        .btn-upload.admin-active {
            background-color: #28a745; /* Green */
            color: white;
            cursor: pointer;
        }

        .btn-login {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            display: block;
            margin-bottom: 20px;
        }

        /* ===== NEW REGISTRATION TEXT STYLING ===== */
        .register-text {
            font-size: 15px;
            margin-bottom: 15px;
            color: #1a1a1a;
            font-weight: 500;
        }

        .register-text a {
            color: #0b4a82;
            text-decoration: none;
            font-weight: 600;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-decoration: none;
            color: #0b4a82;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

       /* ===== MOBILE RESPONSIVE LOGIC ===== */
        /*@media (max-width: 768px) {
            .top-nav {
                padding: 15px 20px;
            }*/

            @media (max-width: 480px) {
                .nav-links {
                    width: 70%; /* Takes up more space on small phones */
                }
            }

            .burger {
                display: flex;
            }

            .nav-links {
                position: fixed;
                right: -100%; /* Hidden off-screen by default */
                top: 0;
                height: 100vh;
                width: 300px; /* Fixed width for desktop consistency */
                background-color: #0b4a82; /* Matches the blue-grey in your screenshot */
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                padding-top: 80px; 
                gap: 0;
                transition: 0.3s ease-in-out;
                box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links a {
                margin: 0;
                padding: 20px 30px;
                width: 100%;
                text-align: left;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                font-size: 18px;
            }
        
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        Department of Education<br>
        Certificate Verifier
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

<div class="logo-container">
    <img src="img/logo-deped-bagong-pilipinas-colored_orig.png" alt="DepEd Logo">
    <img src="img/deped-csjdm-logo.png" alt="Division Logo">
</div>

<main class="login-wrapper">
    <div class="login-card">
        <h2>Login</h2>
         <?php if($error) echo "<div class='error'>$error</div>"; ?>

            <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email" required autocomplete="off">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Enter your password" required autocomplete="off">
            </div>

            <!-- <button type="button" id="uploadBtn" class="btn-upload" disabled>upload excel</button> -->

            <button type="submit" class="btn-login">Login</button>

            <div class="register-text">Don't have an account? <a href="register.php">Register here</a> </div>

            <a href="http://10.10.8.218:8080/Certificate-verifier/index.php" class="back-link">
                <span>&lt;</span> back to verifier
            </a>
        </form>
    </div>
</main>

<script>
    const burger = document.getElementById('burger');
    const navMenu = document.getElementById('nav-menu');

    // Toggle menu and burger animation
    burger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        burger.classList.toggle('toggle');
    });

    // Close menu when a link is clicked (useful for mobile)
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            burger.classList.remove('toggle');
        });
    });
</script>

</body>
</html>