<?php
session_start();
require_once __DIR__ . "/config/db.php";

$result = null;
$error = "";

// Handle certificate search
if (isset($_GET['control_number'])) {
    $control = trim($_GET['control_number']);

    if ($control !== "") {
        $stmt = $conn->prepare("
            SELECT 
                c.control_number,
                c.seminar_title,
                c.certificate_file,
                COALESCE(u.name, 'Not registered') AS display_name,
                COALESCE(u.email, c.teacher_email_pending) AS display_email
            FROM certificates c
            LEFT JOIN users u 
                ON c.teacher_id = u.id
                OR LOWER(c.teacher_email_pending) = LOWER(u.email)
            WHERE c.control_number = ?
        ");
        $stmt->bind_param("s", $control);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "❌ Certificate not found or invalid control number.";
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

        /* ===== PAGE CENTER ===== */
        .page-wrapper {
            display: flex;
            flex-direction: column; /* Stack vertically if needed, but here we use it to center */
            align-items: center;
            justify-content: flex-start; /* Keeps content from floating too low */
            flex: 1;
            padding: 0 20px 40px;
        }

        /* Optional: Improve Error/Result visibility */
        .error {
            color: #d32f2f;
            margin-top: 15px;
            font-size: 14px;
        }
        .result {
            margin-top: 20px;
            text-align: center;
            background: #f1f8ff;
            padding: 15px;
            border-radius: 8px;
        }

        /* ===== CARD ===== */
        .card {
            width: 100%;
            max-width: 600px; /* Limits width on desktop */
            margin: 0 auto;   /* Ensures equal left/right margins */
            padding: 40px 45px;
            border: 1.8px solid #4a7fc2;
            border-radius: 16px;
            text-align: center;
            background-color: #fff;
        }
        .card h2 {
            margin: 0;
            color: #123a6f;
            font-size: 24px;
        }

        .card p.subtitle {
            margin: 10px 0 30px;
            color: #666;
            font-size: 14px;
        }

        /* ===== SEARCH ROW ===== */
        .search-row {
            display: flex;
            gap: 10px;
        }

        .search-row input {
            flex: 1;
            padding: 12px 14px;
            font-size: 15px;
            border-radius: 6px;
            border: 1px solid #cfd8dc;
            outline: none;
        }

        .search-row button {
            padding: 12px 20px;
            font-size: 15px;
            border-radius: 6px;
            border: none;
            background: #1976d2;
            color: #ffffff;
            cursor: pointer;
        }

        .search-row button:hover {
            background: #155fa8;
        }

        /* ===== DIVIDER ===== */
        .divider {
            margin: 28px 0 18px;
            border: none;
            border-top: 1px solid #e0e0e0;
        }

        /* ===== LOGIN ===== */
        .login-text {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .login-link {
            font-size: 14px;
            color: #1976d2;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link:hover {
            text-decoration: underline;
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
                width: 190px; /* Fixed width for desktop consistency */
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

                    /* Make page wrapper flexible */
            .page-wrapper {
                height: auto; /* Remove fixed height calculation */
                min-height: auto; 
                padding: 20px 15px; /* Add breathing room on sides */
                align-items: flex-start; /* Align to top so it doesn't float in middle */
            }

            /* Ensure the card fits the screen width */
            .card {
                width: 100%;
                max-width: 650px; /* Slightly wider to match your image */
                margin: 0 auto;   /* Perfect horizontal centering */
                padding: 50px 40px;
                border: 1px solid #0b4a82; /* Thin blue border from image */
                border-radius: 30px; /* Rounded corners from image */
                text-align: center;
                background: #ffffff;
                box-shadow: none; /* Keep it flat and clean like the image */
            }

            /* Adjust search row for small screens */
            .search-row {
                flex-direction: column;
            }

            .search-row button {
                width: 100%;
            }

            .burger {
                display: flex;
                flex-direction: column;
                cursor: pointer;
                gap: 5px;
                z-index: 1000;
            }

        
    </style>
</head>
<body>

<!-- TOP NAV -->
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
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </div>
</nav>

<div class="logo-container">
    <img src="img/logo-deped-bagong-pilipinas-colored_orig.png" alt="DepEd Logo">
    <img src="img/deped-csjdm-logo.png" alt="Division Logo">
</div>


<!-- CENTER CONTENT -->
<div class="page-wrapper">
    <div class="card">
        <h2>Certificate Verification</h2>
        <p class="subtitle">Enter the control number to verify the certificate</p>

        <form method="GET">
        <div class="search-row">
        <input type="text" name="control_number" placeholder="Enter Control Number"
               value=""<?= isset($_GET['control_number']) ? htmlspecialchars($_GET['control_number']) : '' ?>" autocomplete="off"required>
        <button type="submit">Search</button>
    </div>  
</form>
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): 
        $row = $result->fetch_assoc();
    ?>
         <div class="result">
           <?php

            $display_name = !empty($row['display_name']) && $row['display_name'] !== 'Not registered' 
                ? $row['display_name'] 
                : (!empty($row['display_email']) ? explode('@', $row['display_email'])[0] : 'Not registered');
            ?>
            <p><strong>Name:</strong> <?= htmlspecialchars($display_name) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($row['display_email']) ?></p>
            <p><strong>Seminar Title:</strong> <?= htmlspecialchars($row['seminar_title']) ?></p>
            <p><strong>Control Number:</strong> <?= htmlspecialchars($row['control_number']) ?></p>
            <p>
                <a href="<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">View Certificate</a>
                    
            </p>
            <p><strong>Status:</strong> ✅ Verified</p>
    </div>
<?php endif; ?>


        <hr class="divider">

        <div class="login-text">Refer to the area below your certificate to find your control number.</div>
        <a href="http://10.10.8.218:8080/Certificate-verifier/login.php" class="login-link">Login</a>
    </div>
</div>

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
