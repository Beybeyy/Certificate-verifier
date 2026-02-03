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
        }

        /* ===== TOP NAV ===== */
        .top-nav {
            background-color: #0b4a82;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
        }

        .nav-brand {
            font-size: 20px;
            line-height: 1.3;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            margin-left: 30px;
            font-size: 16px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        /* ===== PAGE CENTER ===== */
        .page-wrapper {
            height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== CARD ===== */
        .card {
            width: 600px;
            padding: 40px 45px;
            border: 1.8px solid #4a7fc2;
            border-radius: 16px;
            text-align: center;
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

        /* ===== RESPONSIVE ===== */
        @media (max-width: 700px) {
            .card {
                width: 90%;
                padding: 30px 25px;
            }

            .nav-links {
                display: none;
            }
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
    <div class="nav-links">
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
    </div>
</nav>

<!-- CENTER CONTENT -->
<div class="page-wrapper">
    <div class="card">
        <h2>Certificate Verification</h2>
        <p class="subtitle">Enter the control number to verify the certificate</p>

        <div class="search-row">
            <input type="text" placeholder="Enter Control Number">
            <button>Search</button>
        </div>

        <hr class="divider">

        <div class="login-text">Are you a teacher or administrator?</div>
        <a href="#" class="login-link">Login to the system</a>
    </div>
</div>

</body>
</html>
