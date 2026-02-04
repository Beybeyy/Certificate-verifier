<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// ADMIN CHECK
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$cert_id = intval($_GET['id'] ?? 0);
if ($cert_id === 0) {
    die("Invalid certificate ID");
}

// FETCH CERTIFICATE + TEACHER INFO
$stmt = $conn->prepare("
    SELECT c.*, u.name AS teacher_name, u.id AS teacher_id
    FROM certificates c
    JOIN users u ON c.teacher_id = u.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $cert_id);
$stmt->execute();
$cert = $stmt->get_result()->fetch_assoc();

if (!$cert) {
    die("Certificate not found");
}

// HANDLE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $control = trim($_POST['control_number']);
    $title   = trim($_POST['seminar_title']);
    $name    = trim($_POST['teacher_name']);

    // UPDATE CERTIFICATE
    $stmt2 = $conn->prepare("
        UPDATE certificates 
        SET control_number = ?, seminar_title = ? 
        WHERE id = ?
    ");
    $stmt2->bind_param("ssi", $control, $title, $cert_id);
    $stmt2->execute();

    // UPDATE TEACHER NAME
    $stmt3 = $conn->prepare("
        UPDATE users 
        SET name = ? 
        WHERE id = ?
    ");
    $stmt3->bind_param("si", $name, $cert['teacher_id']);
    $stmt3->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<style>
    /* Page background */
    body {
        background: #f4f6f8;
        font-family: Arial, sans-serif;
        padding: 40px;
    }

    /* Form container */
    form {
        max-width: 420px;
        margin: 0 auto;
        background: #ffffff;
        padding: 25px 30px;
        border-radius: 8px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
    }

    /* Labels */
    label {
        font-weight: bold;
        color: #333;
        display: block;
        margin-bottom: 6px;
    }

    /* Inputs */
    input[type="text"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    input[type="text"]:focus {
        border-color: #1976d2;
        outline: none;
        box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.15);
    }

    /* Spacing fix for <br><br> */
    br {
        line-height: 10px;
    }

    /* Button */
    button {
        width: 100%;
        padding: 12px;
        background: #1976d2;
        color: #fff;
        font-size: 15px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    button:hover {
        background: #0d47a1;
    }

    button:active {
        transform: scale(0.98);
    }

    /* Mobile responsive */
    @media (max-width: 480px) {
        body {
            padding: 20px;
        }

        form {
            padding: 20px;
        }
    }
</style>


<form method="POST">
    <label>Teacher Name:</label><br>
    <input type="text" name="teacher_name"
           value="<?= htmlspecialchars($cert['teacher_name']) ?>" required><br><br>

    <label>Control Number:</label><br>
    <input type="text" name="control_number"
           value="<?= htmlspecialchars($cert['control_number']) ?>" required><br><br>

    <label>Seminar Title:</label><br>
    <input type="text" name="seminar_title"
           value="<?= htmlspecialchars($cert['seminar_title']) ?>" required><br><br>

    <button type="submit">Update</button>
</form>
