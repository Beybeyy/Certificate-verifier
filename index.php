

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate Verifier</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .container {
            width: 500px;
            margin: 80px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Search box */
        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 50px 12px 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .search-box button {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: #1976d2;
            color: #fff;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-box button:hover {
            background: #125aa0;
        }

        .result {
            background: #e8f5e9;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .error {
            background: #fdecea;
            color: #b71c1c;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
        }

        .login-link a {
            text-decoration: none;
            color: #1976d2;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- 🔍 Certificate Search -->
    <h2>Certificate Verification</h2>

    <form method="GET">
        <div class="search-box">
            <input 
                type="text" 
                name="control_number" 
                placeholder="Enter Control Number"
                value="<?= isset($_GET['control_number']) ? htmlspecialchars($_GET['control_number']) : '' ?>"
                required
            >
            <button type="submit">🔍</button>
        </div>
    </form>

    <!-- ❌ Error Message -->
    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <!-- ✅ Search Result -->
    <!-- <?php if ($result && $result->num_rows > 0): 
        $row = $result->fetch_assoc();
    ?>
        <div class="result">
            <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
            <p><strong>Title:</strong> <?= htmlspecialchars($row['certificate_title']) ?></p>
            <p><strong>Control Number:</strong> <?= htmlspecialchars($row['control_number']) ?></p>
            <p>
                <a href="uploads/certificates/<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">
                    📄 View Certificate
                </a>
            </p>
        </div>
    <?php endif; ?> -->

    <!-- 🔐 Login Option -->
    <div class="login-link">
        <hr>
        <p>Are you a teacher or admin?</p>
        <a href="login.php">🔐 Login here</a>
    </div>

</div>

</body>
</html>
