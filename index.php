<?php
session_start();
require_once __DIR__ . "/config/db.php";

$result = null;
$error = "";

if (isset($_GET['control_number'])) {
    $control = trim($_GET['control_number']);
    if ($control !== "") {
        $stmt = $conn->prepare("
            SELECT c.control_number, c.seminar_title, c.certificate_file,
                   COALESCE(u.name, c.temp_name) AS display_name,
                   COALESCE(u.email, c.teacher_email_pending) AS display_email
            FROM certificates c
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE c.control_number = ?
        ");
        $stmt->bind_param("s", $control);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $error = "❌ Invalid control number.";
        }
    } else {
        $error = "⚠️ Please enter a control number.";
    }   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home | CerVer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/cerverlogo2.svg">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        * { box-sizing: border-box; 
            font-family: 'Poppins', sans-serif;}
        body {
            margin: 0;
            font-family:'Poppins', sans-serif;  
            background: #e4e4e6;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== NAVIGATION (DESKTOP FIRST) ===== */
        .top-nav {
            background-color: #0056b3;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
            position: relative;
            z-index: 1000;
        }
            .nav-brand {
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        .nav-brand {
        display: flex;
        flex-direction: column;
        gap: 4px;
        line-height: 1.2;
        min-width: 0;
    }


            /* Top small text */
            .brand-top {
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                opacity: 0.8;
                margin: 0;
            }

            /* Main title */
            .brand-title {
                font-size: 16px;
                font-weight: 700;
                color: #fff;
                margin: 0;
            }

            /* Subtitle */
            .brand-subtitle {
                font-size: 12px;
                font-weight: 300;
                opacity: 0.7;
                margin: 0;
            }

            /* ===== RESPONSIVE (matches Tailwind scaling) ===== */
            @media (min-width: 640px) {
                .brand-top { font-size: 11px; }
                .brand-title { font-size: 18px; }
                .brand-subtitle { font-size: 13px; }
            }

            @media (min-width: 768px) {
                .brand-top { font-size: 12px; letter-spacing: 0.18em; }
                .brand-title { font-size: 20px; }
                .brand-subtitle { font-size: 14px; }
            }

            @media (min-width: 1024px) {
                .brand-title { font-size: 22px; }
                .brand-subtitle { font-size: 15px; }
            }

            .nav-brand p,
            .nav-brand h1 {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

        /* Desktop Nav Links */
        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 400; transition: 0.3s; }
        .nav-links a:hover { opacity: 0.8; text-decoration: underline; }

        /* Burger Hidden on Desktop */
        .burger { display: none; flex-direction: column; cursor: pointer; gap: 5px; }
        .burger span { height: 3px; width: 25px; background: white; border-radius: 3px; transition: 0.4s; }

        /* ===== LOGOS & PAGE CONTENT ===== */
        .logo-container { display: flex; justify-content: center; align-items: center; gap: 10px; padding: 10px; }
        .logo-container img { height: 150px; width: auto; object-fit: contain; }

        .page-wrapper { flex: 1; display: flex; justify-content: center; align-items: flex-start; padding: 20px; }
        .card {
            width: 100%; max-width: 450px; padding: 40px; border-radius: 20px;
            border: 1px solid #0b4a82; background: #fff; text-align: center;
            animation: fadeInUp 0.6s ease-out;
        }
        .card h2 { color: #123a6f; margin: 0 0 10px; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 25px; }

        /* Search Layout */
        .search-row { display: flex; flex-direction: column; gap: 12px; }
        .search-row input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; }
        .button-group { display: flex; gap: 10px; margin-bottom: 20px;}
        .button-group button, 
        
        .reset-btn {
            flex: 1; padding: 12px; border: none; border-radius: 8px;
            cursor: pointer; font-weight: 600; text-decoration: none; color: white;
        }
        .btn-search { background: #1976d2; }
        .reset-btn { background: #e74c3c; display: flex; justify-content: center; align-items: center; }

        /* Results */
        .result { margin-top: 20px; padding: 15px; background: #f1f8ff; border-radius: 8px; text-align: left; }
        .error { color: #d32f2f; margin-top: 15px; font-weight: bold; }
        
        .login-account-link {
            margin-top: 20px;
            color: #1976d2;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block; /* Helps with padding and transforms if needed */
        }

        .login-account-link:hover {
            color: #0b4a82; /* Darkens slightly to match your brand blue */
           
            transform: translateY(-1px); /* Subtle "lift" effect */
        }

        .custom-hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 25px 0;
        }
        
        footer {
            background: #fff; padding: 20px 40px; border-top: 1px solid #ccc;
            display: flex; justify-content: space-between; font-size: 13px; margin-top: auto;
        }

        /* ===== RESPONSIVE (MOBILE) ===== */
        @media (max-width: 768px) {
            .top-nav { padding: 15px 20px; }
            .burger { display: flex; } /* Show Burger */
            .nav-links {
                position: fixed; right: -100%; top: 0; height: 100vh; width: 200px;
                background: #0b4a82; flex-direction: column; padding: 80px 20px;
                transition: 0.4s ease-in-out; box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            }
            .nav-links.active { right: 0; } /* Slide in */
            .nav-links a { font-size: 18px; width: 100%; padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
            
            /* Burger Animation to X */
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
    <p class="brand-top">Department of Education</p>
    <h1 class="brand-title">CSJDM CERVER</h1>
    <p class="brand-subtitle">
        Certificate Verifier System
    </p>
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
    <img src="img/sdo_logo.svg" alt="Logo">
    
</div>

<div class="page-wrapper">
    <div class="card">
        <h2>Certificate Verification</h2>
        <p class="subtitle">Enter the control number to verify the certificate.</p>

        <form method="GET">
            <div class="search-row">
                <input type="text" name="control_number" placeholder="Enter Control Number"
                       value="<?= isset($_GET['control_number']) ? htmlspecialchars($_GET['control_number']) : '' ?>" required autocomplete="off">
                <div class="button-group">
                    <button type="submit" class="btn-search">Search</button>
                    <a href="index.php" class="reset-btn">Clear</a>
                </div>
            </div>
        </form>

        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <?php if ($result && $row = $result->fetch_assoc()): ?>
            <div class="result">       
                <p><strong>Control No:</strong> <?= htmlspecialchars($row['control_number']) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($row['display_name']) ?></p>
                <p><strong>Activity:</strong> <?= htmlspecialchars($row['seminar_title']) ?></p>
                <h3><strong>Status:</strong> ✅ Verified</h3>

                <!-- <a href="<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank" style="color:#1976d2;">View Certificate</a> -->
            </div>
        <?php endif; ?>

        <hr class="btn-loginlink">
    <a href="login.php" class="login-account-link">Login account</a>
    </div>
</div>

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
</script>

</body>
</html>