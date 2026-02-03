<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate Verifier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   
   <style>
        
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #e3f2fd, #f4f6f8);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .top-nav {
            background-color: #0b3c78;
            padding: 18px 0;
            display:flex;
            align-items:center;
            justify-content:space-between;
            color:white;
            }

        .nav-brand { 
            text-align:left; 
            line-height:1.2; 
            font-weight:bold; 
            font-size:18px; 
            margin-left:20px; 
        
        }
        .nav-brand small { 
            font-weight:normal; 
            font-size:14px; 
            opacity:0.9; }

        .nav-links a { 
            color:white; 
            text-decoration:none; 
            margin:0 20px; 
            font-size:16px; 
        }

        .nav-links a:hover { 
            text-decoration:underline; 
        } 

        .container {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            padding: 30px 28px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #1f3b78;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        /* Search box */
        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 14px 52px 14px 14px;
            font-size: 15px;
            border-radius: 8px;
            border: 1px solid #cfd8dc;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            border-color: #1976d2;
        }

        .search-box button {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: #1976d2;
            color: #fff;
            padding: 9px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background: #125aa0;
        }

        /* Result */
        .result {
            background: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 15px 18px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .result p {
            margin: 6px 0;
        }

        .result a {
            color: #2e7d32;
            font-weight: bold;
            text-decoration: none;
        }

        .result a:hover {
            text-decoration: underline;
        }

        /* Error */
        .error {
            background: #fdecea;
            border-left: 4px solid #c62828;
            color: #b71c1c;
            padding: 12px 16px;
            margin-top: 18px;
            border-radius: 8px;
            font-size: 14px;
        }

        /* Login section */
        .login-link {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
        }

        .login-link hr {
            margin-bottom: 15px;
            border: none;
            border-top: 1px solid #e0e0e0;
        }

        .login-link a {
            display: inline-block;
            margin-top: 6px;
            text-decoration: none;
            color: #1976d2;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
        /* Responsive */
        @media (max-width:850px) {
            .main-container { flex-direction:column; text-align:center; padding:40px 20px; }
            .welcome-wrapper { flex-direction:column; }
}
    </style>
</head>
<body>
<nav class="top-nav">
    <div class="nav-brand">
        Department of Education<br>
        <small>Certificate Verifier</small>
    </div>
    <div class="nav-links">
        <a href="http://10.10.8.218:8080/log-in/LISproject/resources/views/pages/home.blade.php">Home</a>
        <a href="http://10.10.8.218:8080/log-in/LISproject/resources/views/pages/about.blade.php">About</a>
        <a href="http://10.10.8.218:8080/log-in/LISproject/resources/views/pages/contact.blade.php">Contact</a>

    </div>
</nav>


<div class="container">

    <!-- 🔍 Certificate Search -->
    <h2>Certificate Verification</h2>
    <div class="subtitle">
        Enter the control number to verify the certificate
    </div>

    <form method="GET">
        <div class="search-box">
            <input
                type="text"
                name="control_number"
                placeholder="Enter Control Number"
                value="<?= isset($_GET['control_number']) ? htmlspecialchars($_GET['control_number']) : '' ?>"
                required
            >
            <button type="submit">Search</button>
        </div>
    </form>

    <!-- ❌ Error Message -->
    <?php if (!empty($error)): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Search Result -->
    <!--
    <?php if ($result && $result->num_rows > 0):
        $row = $result->fetch_assoc();
    ?>
        <div class="result">
            <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
            <p><strong>Certificate Title:</strong> <?= htmlspecialchars($row['certificate_title']) ?></p>
            <p><strong>Control Number:</strong> <?= htmlspecialchars($row['control_number']) ?></p>
            <p>
                <a href="uploads/certificates/<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">
                    View Certificate (PDF)
                </a>
            </p>
        </div>
    <?php endif; ?>
    -->

    <!-- 🔐 Login Option -->
    <div class="login-link">
        <hr>
        <p>Are you a teacher or administrator?</p>
        <a href="login.php">Login to the system</a>
    </div>

</div>

</body>
</html>
