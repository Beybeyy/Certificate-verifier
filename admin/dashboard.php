<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$sql = "
    SELECT c.id AS cert_id, c.control_number, c.seminar_title, c.certificate_file, c.created_at,
           u.id AS user_id, u.name, u.email
    FROM certificates c
    JOIN users u ON c.teacher_id = u.id
    ORDER BY c.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
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

    .main-container{
        margin: 0;
    }

    h2 { 
        color:#0b4a82; 
        margin-top: 10px;
        margin-bottom: 10px; 
    }

    /* Styling for the new Upload Button */
    .upload-btn {
        background-color:rgb(25, 105, 39);
        color: white;
        padding: 8px 25px;
        border-radius: 10px; /* Makes it pill-shaped like the screenshot */
        text-decoration: none;
        font-weight: bold;
        font-size: 14px;
        transition: background 0.3s;
    }

    .upload-btn:hover {
        background-color: #e68a00;
        text-decoration: none;
    }
    
    table { 
        width:100%; 
        border-collapse: collapse; 
        background:#fff; 
        box-shadow: 0 0 10px rgba(0,0,0,0.05); 
    }
    
    th, td { 
        padding: 12px 15px; 
        border: 1px solid #ddd; 
        text-align:left; 
    }
    
    th { 
        background-color:#1976d2; 
        color:#fff; 
    }
    tr:nth-child(even){
        background:#f9f9f9;
    }

    tr:hover{
        background:#e3f2fd;
    }
    a { 
        color:#0b4a82; 
        text-decoration:none; 
        font-weight:bold; 
    }
    a:hover { 
        text-decoration:underline; 
    }
    .logout { 
        float:right; margin-bottom:20px; 
    }
    .edit-btn { 
        background:#ff9800; 
        color:#fff; 
        padding:5px 10px; 
        border-radius:5px; 
    }
    .edit-btn:hover { 
        background:#f57c00; 
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
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="../login.php">➜] Logout</a>
    </div>
</nav>  

    <main class="main-container">
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
            <h2 style="margin: 0;">Admin Dashboard - All Certificates</h2>
            <a href="upload_excel.php" class="upload-btn">Upload</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Control Number</th>
                <th>Seminar Title</th> 
                <th>Certificate</th>
                
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['control_number']) ?></td>
                <td><?= htmlspecialchars($row['seminar_title']) ?></td>
                <td>
                    <a href="<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">View Certificate</a>
                </td>
                
                <td>
                    <a class="edit-btn" href="edit_certificate.php?id=<?= $row['cert_id'] ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p>No certificates found.</p>
        <?php endif; ?>
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
