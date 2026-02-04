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
            font-family: "Times New Roman", serif;
        }

        body {
            background-color: #ffffff;
            color: #000;
            
        }

        /* NAVBAR */
        .top-nav {
            background-color: #0b3c78;
            padding: 18px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
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
        }

        .top-nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;
            font-size: 16px;
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
        .footer {
            margin-top: 40px;
            background-color: #e0e0e0;
            padding: 50px 5%;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
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
       
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            </div>    
    </div>
 
<!-- ===== CONTENT ===== -->
<div class="container">
        <a href="login.php"  class="login-btn" id="loginBtn">
                Login
            </a>

    <h1>About</h1>

    <div class="breadcrumb">
        <a href="#">About LIS</a> | History | Vision, Mission, Core Values & Mandate | Data Privacy
    </div>

    <div class="content-wrapper">
        <!-- LEFT -->
        <div class="main-content">
            <h2>About the DepEd Learners Information System</h2>

            <div class="info-box">
                <div class="logos">
                    <div class="logo">
                        <!-- Replace with actual image -->
                        <img src="deped-seal.png" alt="DepEd Seal">
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
<div class="footer">
    <div>
        <strong>Republic of the Philippines</strong><br>
        All content is in the public domain unless otherwise stated.
    </div>

    <div>
        <strong>About LIS</strong><br>
        An official, web-based platform developed by the Department of Education to
        systematically manage and maintain learner records in public schools.
    </div>
</div>

<script>
    // Basic JS placeholder (for future use)
    console.log("About page loaded");

    
    document.getElementById('loginBtn').addEventListener('click', function(e) {
        e.preventDefault(); // Stop immediate navigation
        
        const button = this;
        const originalText = button.innerHTML;
        const href = button.getAttribute('href');
        
        // 1. Add click animation class
        button.classList.add('clicked');
        
        // 2. Change to loading text with dots animation
        button.innerHTML = 'Loading';
        
        let dots = 0;
        const loadingInterval = setInterval(() => {
            dots = (dots + 1) % 4;
            button.innerHTML = 'Loading' + '.'.repeat(dots);
        }, 300);
        
        // 3. Wait 800ms (0.8 seconds) for loading animation
        setTimeout(() => {
            // Clear the loading dots animation
            clearInterval(loadingInterval);
            
            // 4. Show "Redirecting..." text
            button.innerHTML = '✓ Redirecting...';
            button.style.background = 'linear-gradient(to right, #2ecc71, #27ae60)';
            
            // 5. Optional: Fade out the whole page
            document.body.style.opacity = '0.8';
            document.body.style.transition = 'opacity 0.3s ease';
            
            // 6. Wait 400ms more then redirect
            setTimeout(() => {
                window.location.href = href;
            }, 400);
            
        }, 800); // Loading animation duration
    });
</script>

</body>
</html>
