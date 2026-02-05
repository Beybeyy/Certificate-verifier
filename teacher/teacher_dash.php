<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// LOGIN CHECK
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in teacher's ID from session
$teacher_id = $_SESSION['id'];

// Fetch teacher info
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

// Fetch teacher certificates (including certificates uploaded before registration)
$stmt2 = $conn->prepare("
    SELECT control_number, seminar_title, certificate_file, created_at
    FROM certificates
    WHERE teacher_id = ? OR teacher_email_pending = ?
    ORDER BY created_at DESC
");
$stmt2->bind_param("is", $teacher_id, $teacher['email']);
$stmt2->execute();
$certificates = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* General */
* { box-sizing: border-box; margin: 0; padding: 0; font-family: "Segoe UI", Arial, sans-serif; }
body { background: #f4f6f8; color: #1a1a1a; padding: 20px; }

/* Container */
.container { max-width: 1000px; margin: 0 auto; }

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.header h2 { color: #0b4a82; font-size: 24px; }
.logout a {
    background: #d32f2f;
    color: #fff;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: 0.3s;
}
.logout a:hover { background: #b71c1c; }

/* Teacher Info */
.teacher-info { margin-bottom: 30px; }
.teacher-info p { font-size: 16px; color: #333; }

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}
th, td {
    padding: 12px 15px;
    border: 1px solid #ddd;
    text-align: left;
}
th {
    background: #1976d2;
    color: #fff;
    font-weight: 500;
}
tr:nth-child(even) { background: #f9f9f9; }
tr:hover { background: #e3f2fd; transition: background 0.3s; }

/* Certificate Link */
a.view-pdf {
    color: #0b4a82;
    font-weight: bold;
    text-decoration: none;
}
a.view-pdf:hover { text-decoration: underline; }

/* Responsive */
@media (max-width: 768px) {
    table, th, td { font-size: 14px; }
    .header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .logout a { padding: 6px 12px; margin-top: 10px; }
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>
            Welcome, 
            <?php
            if (!empty($teacher['name'])) {
                echo htmlspecialchars($teacher['name']);
            } else {
                echo htmlspecialchars(explode('@', $teacher['email'])[0]);
            }
            ?>
        </h2>
        <div class="logout">
            <a href="../login.php">Logout</a>
        </div>
    </div>

    <div class="teacher-info">
        <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email']) ?></p>
    </div>

    <h3>Your Certificates</h3>

    <?php if ($certificates->num_rows > 0): ?>
    <table>
        <tr>
            <th>Control Number</th>
            <th>Seminar Title</th>
            <th>Certificate</th>
        </tr>
        <?php while ($row = $certificates->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['control_number']) ?></td>
            <td><?= htmlspecialchars($row['seminar_title']) ?></td>
            <td>
                <a class="view-pdf" href="<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">
                    View Certificate
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No certificates found.</p>
    <?php endif; ?>
</div>

</body>
</html>