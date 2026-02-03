<?php
session_start();
require_once __DIR__ . "/../config/db.php"; // adjust path if needed

$success = "";
$error = "";

// ======= Handle form submission =======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $control_number = trim($_POST['control_number']);
    $teacher_email  = trim($_POST['teacher_email']);
    $seminar_title  = trim($_POST['seminar_title']);
    $certificate_file = "";

    if (!empty($_FILES['certificate_file']['name'])) {
        $uploadDir = __DIR__ . "/../uploads/certificates/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = basename($_FILES['certificate_file']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $targetFile)) {
            $certificate_file = $filename;
        } else {
            $error = "❌ Failed to upload certificate PDF.";
        }
    }

    if (!$error) {
        // Get teacher_id from email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $teacher_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "❌ Teacher email not found in database.";
        } else {
            $teacher = $result->fetch_assoc();
            $teacher_id = $teacher['id'];

            // Insert into certificates
            $stmt2 = $conn->prepare("
                INSERT INTO certificates (control_number, teacher_id, seminar_title, certificate_file)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE seminar_title=?, certificate_file=?
            ");
            $stmt2->bind_param(
                "sissss",
                $control_number,
                $teacher_id,
                $seminar_title,
                $certificate_file,
                $seminar_title,
                $certificate_file
            );

            if ($stmt2->execute()) {
                $success = "✅ Certificate added successfully!";
            } else {
                $error = "❌ Failed to insert certificate: " . $stmt2->error;
            }
        }
    }
}

// Fetch all teachers for dropdown
$teachers = $conn->query("SELECT name, email FROM users ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Add Certificate</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: Arial, sans-serif; background: #f4f6f8; margin:0; padding:0;}
.container { max-width: 700px; margin: 50px auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
h2 { text-align:center; color:#0b4a82; }
form { margin-top: 20px; }
input, select { width: 100%; padding:12px; margin:5px 0; border-radius:6px; border:1px solid #cfd8dc; }
button { margin-top: 10px; padding:12px 20px; background:#1976d2; color:#fff; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#155fa8; }
.success { background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:6px; margin-top:15px; }
.error { background:#fdecea; color:#b71c1c; padding:12px; border-radius:6px; margin-top:15px; }
.logout { text-align:right; margin-top:10px; }
a { color:#1976d2; text-decoration:none; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
    <div class="logout"><a href="../logout.php">Logout</a></div>
    <h2>Admin Dashboard - Add Certificate</h2>

    <?php if($success) echo "<div class='success'>$success</div>"; ?>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Control Number</label>
        <input type="text" name="control_number" required>

        <label>Teacher</label>
        <select name="teacher_email" required>
            <option value="">-- Select Teacher --</option>
            <?php while($t = $teachers->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($t['email']) ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Seminar Title</label>
        <input type="text" name="seminar_title" required>

        <label>Certificate PDF</label>
        <input type="file" name="certificate_file" accept="application/pdf" required>

        <button type="submit">Add Certificate</button>
    </form>
</div>

</body>
</html>
