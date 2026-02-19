<?php
session_start();
require_once __DIR__ . "/config/db.php";

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/config/PHPMailer/Exception.php";
require __DIR__ . "/config/PHPMailer/PHPMailer.php";
require __DIR__ . "/config/PHPMailer/SMTP.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $email = strtolower(trim($_POST['email']));

    // 1. Validate
    if ($fullname === "" || $email === "") {
        $_SESSION['error'] = "All fields required.";
        header("Location: register.php");
        exit();
    }

    // 2. Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // 3. Generate token and expiry
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
    $role = "teacher";

    // 4. Insert user WITHOUT password
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, role, password_reset_token, token_expires)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $fullname, $email, $role, $token, $expires);

    if ($stmt->execute()) {

       $link = "http://10.10.8.218:8080/Certificate-verifier/set_password.php?token=$token";

        // 5. PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'depedcertificateverifier@gmail.com'; // replace with your Gmail
            $mail->Password   = 'apebhfivgtvqkche';    // 16-char Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('depedcertificateverifier@gmail.com', 'Certificate Verifier');
            $mail->addAddress($email, $fullname);

            $mail->isHTML(true);
            $mail->Subject = 'Set your password';
            $mail->Body    = "
                <p>Hello <b>$fullname</b>,</p>
                <p>Click the link below to set your password:</p>
                <p><a href='$link'>$link</a></p>    
                <p>This link expires in 1 hour.</p>
            ";

            $mail->send();
            $_SESSION['success'] = "✅ Registration successful! Check your email to set your password.";

        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Email failed: {$mail->ErrorInfo}";
        }

    } else {
        $_SESSION['error'] = "❌ Registration failed.";
    }

    header("Location: register.php");
    exit();
}
?>
