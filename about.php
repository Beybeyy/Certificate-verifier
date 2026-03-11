<?php
// about.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About | CerVer - Certificate Verifier</title>
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

        /* ===== MAIN CONTENT ===== */
        .container {
            max-width: 1120px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
            flex: 1;
        }

        h1 {
            font-size: 32px;
            color: #0b4a82;
            margin-bottom: 20px;
            border-bottom: 2px solid #0b4a82;
            display: inline-block;
            padding-bottom: 5px;
        }

        .content-wrapper {
            display: flex;
            gap: 40px;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid #0b4a82;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .main-content { flex: 2; }

        .info-box {
            display: flex;
            gap: 40px;
        }

        .logos {
            display: flex;
            flex-direction: column;
            gap: 25px;
            min-width: 150px;
            align-items: center; /* Centers logos in their column */
        }

        .logo-img {
            width: 130px; /* Uniform width for all logos */
            height: auto;
            object-fit: contain;
        }

        .text-content {
            text-align: justify;
            font-size: 16px;
            line-height: 1.8;
            color: #333;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            flex: 0.4;
            padding-left: 50px;
            border-left: 1px solid #ddd;
        }

        .sidebar h3 {
            font-size: 18px;
            color: #0b4a82;
            margin-bottom: 15px;
        }

        .sidebar a {
            display: block;
            margin-bottom: 12px;
            font-size: 15px;
            color: #0b4a82;
            text-decoration: none;
            font-weight: 500;
        }

        .sidebar a:hover { text-decoration: underline; }

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
        @media (max-width: 992px) {
            .content-wrapper { flex-direction: column; padding: 25px; }
            .sidebar { border-left: none; border-top: 1px solid #ddd; padding: 20px 0 0 0; }
            .info-box { flex-direction: column; align-items: center; }
            .logos { flex-direction: row; flex-wrap: wrap; justify-content: center; width: 100%; }
            .logo-img { width: 100px; } /* Slightly smaller logos on tablet/mobile */
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
                z-index: 999;
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

    <div class="burger" id="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="nav-links" id="nav-menu">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </div>
</nav>

<div class="container">
    <h1>About</h1>  

    <div class="content-wrapper">
        <div class="main-content">
            <div class="info-box">
                <div class="logos">
                    <img src="img/cerverlogo2.svg" alt="CerVer Logo" class="logo-img">
                    <img src="img/logo-deped-bagong-pilipinas-colored_orig.png" alt="DepEd Seal" class="logo-img">
                    <img src="http://localhost/log-in/LISproject/public/images/deped_matatag_logo.png" alt="DepEd Matatag Logo" class="logo-img">
                </div>

                <div class="text-content">
                    <h4>About the Certificate Verification System (CerVer)</h4>
                    <p>The Certificate Verification System is a dedicated digital platform designed to ensure the authenticity and integrity of professional certifications issued by the Department of Education Division Office.</p>
                    <p>As a centralized hub for monitoring and validation, this system bridges the gap between manual record-keeping and modern, secure digital verification. Our goal is to provide school administrators, teachers, and stakeholders with a reliable tool to confirm the legitimacy of educational and professional credentials at a glance.</p>
                    <br>
                    <h4> How it Works</h4>
                    <p><b>Centralized Uploads:</b> Authorized Division Office personnel upload official certificates into the system.</p>
                    <p><b>Unique Identification:</b> Each document is logged with specific metadata (such as Control Numbers or QR codes) for easy retrieval.</p>
                    <p><b>Instant Verification:</b> Users can input the required control number on the verification portal to receive an immediate confirmation of the document’s status and authenticity.</p>

                </div>
            </div>
        </div>

        <div class="sidebar">
            <h3>Quick Links</h3>
            <a href="https://depedcsjdm.weebly.com/" target="_blank">DepEd Website</a>
            <a href="https://www.facebook.com/DepEdTayoSanJosedelMonte" target="_blank">Follow Facebook</a>
        </div>
    </div>
</div>

<footer>
    <div>© 2026 Department of Education Certificate Verifier System</div>
    <div>Front-End Development: Larry Cruz | Back-End Development: Bea Patrice Cortez  </div>
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