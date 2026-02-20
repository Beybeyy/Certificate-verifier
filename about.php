<?php
// about.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About | Certificate Verifier</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;  
            font-family: "Segoe UI", Arial, sans-serif;;
        }

        body {
            background-color: #ffffff;
            color: #000;
            
        }

        /* NAVBAR */
        .top-nav {
            background-color: #0b3c78;
            padding: 15px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: relative;
            z-index: 1000;
        }

        /* Left Brand Side */
        .nav-brand {
            text-align: left;
            line-height: 1.2;
            margin-left: 20px;
            font-weight: bold;
            font-size: 18px;
        }

        .nav-brand small {
            font-weight: normal;
            font-size: 14px;
            opacity: 0.9;
        }

        .nav-links {
            display: flex;
            align-items: center;
            transition: 0.3s ease-in-out;
        }

        .top-nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;
            font-size: 16px;
            font-weight: 400;
        }

        .top-nav a:hover {
            text-decoration: underline;
        }

        .login-btn {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            display: block;
            margin-bottom: 20px;
        }
        /* ===== MAIN CONTENT ===== */
        .container {
            width: 90%;
            margin: 30px 150px 150px 150px;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .breadcrumb {
            font-size: 14px;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #000;
            text-decoration: none;
        }

        .content-wrapper {
            display: flex;
            gap: 20px;
        }

        /* ===== LEFT CONTENT ===== */
        .main-content {
            flex: 3;
        }

        .main-content h2 {
            color: #0b4a8b;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .info-box {
            display: flex;
            gap: 20px;
        }

        .logos {
            width: 180px;
            text-align: center;
        }

        .logo {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
        }

        .logo img {
            width: 160px;
        }

        .text-content {
            text-align: justify;
            font-size: 15px;
            line-height: 1.6;
        }

        /* ===== BURGER ICON & ANIMATION ===== */
        .burger {
            display: flex ;
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

        /* ===== RIGHT SIDEBAR ===== */
        .sidebar {
            flex: .7;
            margin-right: 150px;
            border: 1px solid #8aa6c1;
            padding: 15px;
            height: fit-content;
        }

        .sidebar h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #0b4a8b;
            text-decoration: none;
        }

        /* ===== FOOTER ===== */
        footer { 
            background-color: #e6e6e6; 
            padding: 30px; 
            text-align: center; 
            font-size: 0.8rem; 
            border-top: 1px solid #ccc; 
            margin-top: 50px; }
        

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
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<div class="top-nav">
        <div class="nav-brand">
            Department of Education<br>
            <small>Learning Information System</small>
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
            <a href="login.php">Login</a>
            </div>    
    </div>
 
<!-- ===== CONTENT ===== -->
<div class="container">

    <h1>About</h1>  

    <div class="content-wrapper">
        <!-- LEFT -->
        <div class="main-content">
            <h2>About the DepEd Learners Information System</h2>

            <div class="info-box">
                <div class="logos">
                    <div class="logo">
                        <!-- Replace with actual image -->
                        <img src="C:\xampp\htdocs\Certificate-verifier\img\deped-csjdm-logo.png" alt="DepEd Seal">
                    </div>
                    <div class="logo">
                        <!-- Replace with actual image -->
                        <img src="http://localhost/log-in/LISproject/public/images/deped_matatag_logo.png" 
                alt="DepEd Matatag Logo" 
                class="welcome-logo">
                    </div>
                </div>

                <div class="text-content">
                    <p>
                        The DepEd Learners Information System (LIS) is an official, web-based
                        platform developed by the Department of Education to systematically
                        manage and maintain learner records in public schools. It serves as
                        the primary repository of learner information, ensuring that data
                        from enrollment to completion of basic education is accurate,
                        consistent, and up to date.
                    </p><br>

                    <p>
                        The system allows schools to register learners, update personal and
                        academic information, and monitor enrollment status each school year.
                        Through the LIS, school administrators and teachers can efficiently
                        manage large volumes of data while reducing manual paperwork and errors.
                    </p><br>

                    <p>
                        It also supports smooth coordination between schools, divisions, and
                        regional offices. The DepEd LIS plays a vital role in educational
                        planning and policy implementation. The data collected through the
                        system is used for generating official reports, tracking learner
                        progress, allocating resources, and supporting government programs
                        and interventions.
                    </p><br>

                    <p>
                        Security and data privacy are key priorities of the DepEd Learners
                        Information System. Access to learner information is limited to
                        authorized users, and safeguards are in place to protect sensitive
                        data in compliance with existing data privacy policies.
                    </p><br>

                    <p>
                        Overall, the DepEd Learners Information System aims to strengthen
                        school management, improve data integrity, and support the Department
                        of Education’s mission of delivering accessible, efficient, and
                        quality basic education to every learner.
                    </p>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="sidebar">
            <h3>DepEd Tayo Bulacan</h3>
            <a href="#">DepEd website</a>
            <a href="#">Follow page</a>
            <a href="#">Follow X</a>
        </div>
    </div>
</div>

<!-- ===== FOOTER ===== -->
<footer>
    <p>Eco Park Muzon East, City of San Jose del Monte, Bulacan 3023, Philippines<br>
    sanjosedelmonte.city@deped.gov.ph | (044) 305-7395<br>
    <strong>DepEd Tayo City of San Jose del Monte</strong></p>
</footer>

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
