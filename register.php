<?php
session_start();
require_once __DIR__ . "/config/db.php"; // Adjust path if needed

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "⚠️ All fields are required!";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "⚠️ Passwords do not match!";
        header("Location: register.php");
        exit();
    }
        // Convert email to lowercase and trim
    $email = strtolower(trim($email));

    // // Check email ends with @deped.gov.ph
    // if (!str_ends_with($email, '@deped.gov.ph')) {
    //     $_SESSION['error'] = "⚠️ Only DepEd email addresses are allowed!";
    //     header("Location: register.php");
    //     exit();
    // }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "⚠️ Email already registered!";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Insert new user with default role 'teacher'
    $role = "teacher";
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $password, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Registration successful! You can now log in.";
        header("Location: register.php"); // Reload page to show message
        exit();
    } else {
        $_SESSION['error'] = "❌ Registration failed. Please try again.";
        header("Location: register.php");
        exit();
    }

    $stmt->close();
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
            border: 2px solid #0b4a82;
            border-radius: 25px;
            padding: 40px 35px;
            text-align: left; /* Text labels are left-aligned in image */
            background: #fff;
        }

        .register-card h2 { 
            color: #0b4a82; 
            margin-bottom: 25px; 
            font-size: 26px; 
            text-align: center; /* Heading is centered */
        }

        .form-group { margin-bottom: 15px; }
        
        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1.2px solid #8ba8c7;
            border-radius: 8px;
            font-size: 15px;
        }

        /* ===== BUTTONS ===== */
        .btn-register {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .back-link {
            text-decoration: none;
            color: #0b4a82;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            justify-content: center;
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

<main class="register-wrapper">
    <div class="register-card">
        <h2>Register</h2>
                    <?php
            if (isset($_SESSION['success'])) {
                echo "<p style='color:green; text-align:center;'>" . $_SESSION['success'] . "</p>";
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo "<p style='color:red; text-align:center;'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }
            ?>
        <form action="registration_process.php" method="POST">
            <div class="form-group">
                <label>Complete Name</label>
                <input type="text" name="fullname" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autocomplete="off">
            </div>
            
            <button type="submit" class="btn-register">Register</button>
            
            <a href="login.php" class="back-link">
                <span>&lt;</span> back to login
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