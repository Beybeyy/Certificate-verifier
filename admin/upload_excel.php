<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// ADMIN CHECK
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== 0) {
        $message = "❌ Please upload a valid CSV file.";
    } else {

        $file = fopen($_FILES['csv_file']['tmp_name'], "r");

        // Skip header row
        fgetcsv($file);

        $inserted = 0;
        $skipped  = 0;

        while (($row = fgetcsv($file)) !== false) {

            [$control, $teacher_email, $title, $certificate_file] = $row;

            if (!$control || !$teacher_email || !$title || !$certificate_file) {
                $skipped++;
                continue;
            }

            // GET TEACHER ID
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $teacher_email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 0) {
                $skipped++;
                continue;
            }

            $teacher_id = $res->fetch_assoc()['id'];

            // PREVENT DUPLICATE CONTROL NUMBER
            $check = $conn->prepare("SELECT id FROM certificates WHERE control_number = ?");
            $check->bind_param("s", $control);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $skipped++;
                continue;
            }

            // INSERT CERTIFICATE
            $stmt2 = $conn->prepare("
                INSERT INTO certificates
                (control_number, teacher_id, seminar_title, certificate_file)
                VALUES (?, ?, ?, ?)
            ");
            $stmt2->bind_param("siss", $control, $teacher_id, $title, $certificate_file);

            if ($stmt2->execute()) {
                $inserted++;
            } else {
                $skipped++;
            }
        }

        fclose($file);

        $message = "✅ Uploaded: $inserted | ❌ Skipped: $skipped";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Certificates CSV</title>
</head>
<body>

<h2>Upload Certificates (CSV)</h2>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="csv_file" accept=".csv" required>
    <br><br>
    <button type="submit">Upload CSV</button>
</form>

</body>
</html>
