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

// PAGINATION SETTINGS
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rowsPerPage = isset($_GET['rows']) ? (int)$_GET['rows'] : 50; // default 50
$offset = ($page - 1) * $rowsPerPage;

// Total number of certificates for this teacher
$stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM certificates WHERE teacher_id = ? OR teacher_email_pending = ?");
$stmtCount->bind_param("is", $teacher_id, $teacher['email']);
$stmtCount->execute();
$totalRows = $stmtCount->get_result()->fetch_assoc()['total'];

// Fetch teacher certificates with pagination
$stmt2 = $conn->prepare("
    SELECT control_number, seminar_title, certificate_file, created_at
    FROM certificates
    WHERE teacher_id = ? OR teacher_email_pending = ?
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");
$stmt2->bind_param("isii", $teacher_id, $teacher['email'], $rowsPerPage, $offset);
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
* { 
    box-sizing: border-box;
    margin: 0; 
    padding: 0; 
    font-family: "Segoe UI", Arial, sans-serif; 
}
body { margin:0; font-family:"Segoe UI", Arial, sans-serif; background:#fff; color:#1a1a1a; display:flex; flex-direction:column; min-height:100vh; overflow-x:hidden; }

/* Top Nav */
.top-nav { background:#0b4a82; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; color:#fff; position:relative; z-index:1000; }
.nav-brand { font-size:18px; font-weight:500; line-height:1.2; }
.nav-links { display:flex; align-items:center; }
.nav-links a { color:#fff; text-decoration:none; margin-left:35px; font-size:15px; font-weight:400; }
.nav-links a:hover { text-decoration:underline; }

/* Burger */
.burger { display:none; flex-direction:column; cursor:pointer; gap:5px; z-index:1001; }
.burger span { height:3px; width:28px; background:white; border-radius:5px; transition:all 0.3s ease; }
.burger.toggle span:nth-child(1) { transform:rotate(-45deg) translate(-5px,6px); }
.burger.toggle span:nth-child(2) { opacity:0; }
.burger.toggle span:nth-child(3) { transform:rotate(45deg) translate(-5px,-6px); }

/* Container */
.container { max-width: 2000px; margin: 0 auto; padding: 20px; }

/* Header */
.header { display:flex; justify-content:space-between; align-items:center; margin-top:20px; margin-bottom:10px; flex-wrap:wrap; }
.header h2 { color: #0b4a82; font-size: 24px; }
.logout a { background: #d32f2f; color: #fff; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: bold; transition: 0.3s; }
.logout a:hover { background: #b71c1c; }

/* Teacher Info */
.teacher-info { margin-bottom: 30px; }
.teacher-info p { font-size: 16px; color: #333; }

/* Table */
.table-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 10px; }
.cert-table { width: 100%; max-width: 1500px; margin: 0 auto; border-collapse: collapse; font-family: "Segoe UI", Arial, sans-serif; font-size: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: #fff; }
.cert-table thead tr { background-color: #0b4a82; color: white; font-weight: 600; }
.cert-table thead th { padding: 14px 18px; text-align: left; border: none; }
.cert-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.cert-table tbody tr:nth-child(odd) { background-color: #fff; }
.cert-table tbody td { padding: 14px 50px; border: 1px solid #ddd; vertical-align: middle; }
th, td { padding: 12px 15px; border: 1px solid #ddd; text-align:left; }
tr:hover { background: #e3f2fd; transition: background 0.3s; }

/* Certificate Link */
a.view-pdf { color: #0b4a82; font-weight: bold; text-decoration: none; }
a.view-pdf:hover { text-decoration: underline; }
.edit-btn { background:#ff9800; color:#fff; padding:5px 10px; border-radius:5px; }
.edit-btn:hover { background:#f57c00; }

/* Pagination Footer */
.pagination-footer { display:flex; justify-content:space-between; align-items:center; padding:10px 1%0px; background-color:#f8fbff; border:1px solid #e0e0e0; border-top:none; font-size:13px; flex-wrap:wrap; gap:10px; }
.footer-right { display:flex; align-items:center; gap:20px; }
.row-select-wrapper { color:#5c7c99; font-size:13px; }
.row-select-wrapper select { padding:2px 5px; border:1px solid #1976d2; border-radius:4px; color:#0b4a82; background:transparent; font-size:13px; margin-left:5px; }
.pagination-controls { display:flex; align-items:center; gap:4px; }
.page-num, .page-arrow { background:white; border:1px solid #cfd8dc; color:#1976d2; min-width:28px; height:28px; padding:0; display:flex; justify-content:center; align-items:center; cursor:pointer; border-radius:4px; font-size:12px; text-decoration:none; }
.page-num.active { background:#1976d2; color:white; border-color:#1976d2; }
.page-num:hover:not(.active), .page-arrow:hover { background:#f0f7ff; border-color:#1976d2; }

/* Responsive */
@media (max-width: 768px) {
    .cert-table { font-size:14px; max-width:100%; }
    .table-wrapper { overflow-x:auto; }
    .cert-table thead th, .cert-table tbody td { padding:10px 12px; }
}
</style>
</head>
<body>

<!-- TOP NAV -->
<nav class="top-nav">
    <div class="nav-brand">Department of Education<br>Certificate Verifier</div>
    <div class="burger" id="burger"><span></span><span></span><span></span></div>
    <div class="nav-links" id="nav-menu">
        <a href="../index.php">Home</a>
        <a href="../about.php">About</a>
        <a href="#">Contact</a>
        <a href="../login.php" class="logout">Logout</a>
    </div>
</nav>

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
    </div>

    <div class="teacher-info">
        <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email']) ?></p>
    </div>

    <h3>Your Certificates</h3>

    <?php if ($certificates->num_rows > 0): ?>
    <div class="table-wrapper">
        <table class="cert-table">
            <thead>
                <tr>
                    <th>Control Number</th>
                    <th>Seminar Title</th>
                    <th>Certificate</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $certificates->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['control_number']) ?></td>
                    <td><?= htmlspecialchars($row['seminar_title']) ?></td>
                    <td>
                        <a class="view-pdf" href="<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">View Certificate</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="pagination-footer">
        <div class="footer-left">
            <?php
            $start = $totalRows > 0 ? $offset + 1 : 0;
            $end = min($offset + $rowsPerPage, $totalRows);
            ?>
            Showing <b><?= $start ?></b> to <b><?= $end ?></b> of <b><?= $totalRows ?></b> certificates
        </div>

        <div class="footer-right">
            <div class="row-select-wrapper">
                Row per page: 
                <select onchange="location.href='?page=1&rows='+this.value">
                    <option value="50" <?= $rowsPerPage==50 ? 'selected' : '' ?>>50</option>
                    <option value="30" <?= $rowsPerPage==30 ? 'selected' : '' ?>>30</option>
                    <option value="20" <?= $rowsPerPage==2 ? 'selected' : '' ?>>20</option>
                    <option value="10" <?= $rowsPerPage==1 ? 'selected' : '' ?>>10</option>
                </select>
            </div>

            <div class="pagination-controls">
                <?php
                $totalPages = ceil($totalRows / $rowsPerPage);
                $startPage = max(1, $page - 4);
                $endPage = min($totalPages, $page + 5);
                ?>
                <a class="page-arrow" href="?page=<?= max(1, $page-1) ?>&rows=<?= $rowsPerPage ?>">❮</a>
                <?php for ($i=$startPage; $i<=$endPage; $i++): ?>
                    <a class="page-num <?= $i==$page ? 'active' : '' ?>" href="?page=<?= $i ?>&rows=<?= $rowsPerPage ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a class="page-arrow" href="?page=<?= min($totalPages, $page+1) ?>&rows=<?= $rowsPerPage ?>">❯</a>
            </div>
        </div>
    </div>

    <?php else: ?>
        <p>No certificates found.</p>
    <?php endif; ?>
</div>

<script>
// Burger menu toggle
const burger = document.getElementById('burger');
const navMenu = document.getElementById('nav-menu');
burger.addEventListener('click', () => {
    navMenu.classList.toggle('active');
    burger.classList.toggle('toggle');
});
document.querySelectorAll('.nav-links a').forEach(link=>{
    link.addEventListener('click',()=>{
        navMenu.classList.remove('active');
        burger.classList.remove('toggle');
    });
});
</script>
</body>
</html>