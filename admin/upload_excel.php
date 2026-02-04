<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// ADMIN CHECK
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== 0) {
        $messages[] = "❌ Please upload a valid CSV file.";
    } else {

        $file = fopen($_FILES['csv_file']['tmp_name'], "r");
        fgetcsv($file); // skip header

        $inserted = 0;
        $skipped  = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count(array_filter($row)) === 0) continue; // skip empty row

            [$control, $teacher_email, $title, $certificate_file] = array_map('trim', $row);

            if (!$control || !$teacher_email || !$title || !$certificate_file) {
                $messages[] = "Skipped row: missing field(s)";
                $skipped++;
                continue;
            }

            // Check for duplicate control number
            $check = $conn->prepare("SELECT id FROM certificates WHERE control_number = ?");
            $check->bind_param("s", $control);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $messages[] = "Skipped row '$control': control number already exists";
                $skipped++;
                continue;
            }

            // Try to get teacher ID
            $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
            $stmt->bind_param("s", $teacher_email);
            $stmt->execute();
            $res = $stmt->get_result();

            $teacher_id = null;
            $teacher_email_pending = null;

            if ($res->num_rows > 0) {
                $teacher_id = $res->fetch_assoc()['id'];
            } else {
                $teacher_email_pending = $teacher_email; // store email for future registration
            }

            // Insert certificate
            $stmt2 = $conn->prepare("
                INSERT INTO certificates
                (control_number, teacher_id, teacher_email_pending, seminar_title, certificate_file)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt2->bind_param("sisss", $control, $teacher_id, $teacher_email_pending, $title, $certificate_file);

            if ($stmt2->execute()) {
                $messages[] = "Inserted row '$control' successfully";
                $inserted++;
            } else {
                $messages[] = "Skipped row '$control': failed to insert";
                $skipped++;
            }
        }

        fclose($file);
        $messages[] = "✅ Total inserted: $inserted | ❌ Total skipped: $skipped";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Certificates CSV</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; padding:20px; }
        h2 { color:#0b4a82; margin-bottom:20px; }
        form { background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); max-width:600px; margin-bottom:20px; }
        input[type="file"] { display:block; margin-bottom:15px; padding:6px; }
        button { background-color:#0b4a82; color:#fff; padding:10px 25px; border-radius:5px; border:none; cursor:pointer; font-size:16px; }
        button:hover { background-color:#084a6b; }
        .messages { background:#fff; padding:15px; border-radius:8px; max-width:600px; box-shadow:0 0 10px rgba(0,0,0,0.05); }
        .messages p { margin-bottom:5px; font-size:14px; }
        .success { color: #155724; }
        .error { color: #721c24; }
    </style>
</head>
<body>

<h2>Upload Certificates (CSV)</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="csv_file" accept=".csv" required>
    <button type="submit">Upload CSV</button>
</form>

<?php if (!empty($messages)): ?>
<div class="messages">
    <?php foreach ($messages as $msg): ?>
        <p class="<?= strpos($msg,'Inserted')!==false||strpos($msg,'✅')!==false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($msg) ?>
        </p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>

