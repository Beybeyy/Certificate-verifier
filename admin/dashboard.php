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
    body { 
        font-family: Arial, 
        sans-serif; 
        background:#f4f6f8; 
        padding:20px; 
    }

    h2 { 
        color:#0b4a82; 
        margin-bottom: 20px; 
    }

    /* Styling for the new Upload Button */
    .upload-btn {
        background-color: #ff9800;
        color: white;
        padding: 8px 25px;
        border-radius: 5px; /* Makes it pill-shaped like the screenshot */
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
</style>
</head>
<body>

<div class="logout">
    <a href="../login.php">➜]Logout</a>
</div>

<div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
    <h2 style="margin: 0;">Admin Dashboard - All Certificates</h2>
    <a href="upload_certificate.php" class="upload-btn">Upload</a>
</div>

<?php if ($result->num_rows > 0): ?>
<table>
    <tr>
        <th>User Name</th>
        <th>Email</th>
        <th>Control Number</th>
        <th>Seminar Title</th> 
        <th>Certificate</th>
        <th>Date Issued</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['control_number']) ?></td>
        <td><?= htmlspecialchars($row['seminar_title']) ?></td>
        <td>
            <a href="../uploads/certificates/<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">View PDF</a>
        </td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
            <a class="edit-btn" href="edit_certificate.php?id=<?= $row['cert_id'] ?>">Edit</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<p>No certificates found.</p>
<?php endif; ?>

</body>
</html>
