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
        @media (max-width: 768px) {
            .top-nav {
                padding: 15px 20px;
            }

            .burger {
                display: flex;
            }

            .nav-links {
                position: fixed;
                right: -100%; /* Hidden off-screen to the right */
                top: 0;
                height: 100vh;
                width: 30%; /* Menu takes 70% of screen width */
                background-color: #0b4a82;
                flex-direction: column;
                justify-content: center;
                gap: 30px;
                box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            }

            .nav-links.active {
                right: 0; /* Slide in */
            }

            .nav-links a {
                margin: 0;
                font-size: 20px;
                width: 100%;
                text-align: center;
            }
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

<main class="login-wrapper">
    <div class="login-card">
        <h2>Login</h2>
        <form>
            <div class="form-group">
                <input type="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <input type="password" placeholder="Enter your password" required>
            </div>

            <button type="button" id="uploadBtn" class="btn-upload" disabled>upload excel</button>

            <button type="submit" class="btn-login">Login</button>

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