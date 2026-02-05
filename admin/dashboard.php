<?php
session_start();
require_once __DIR__ . "/../config/db.php";
 
 /* ===== INLINE NAME UPDATE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $cert_id = (int)$_POST['id'];
    $new_name = trim($_POST['name']);

    if ($new_name !== '') {
        // Check if certificate has a registered user
        $stmt = $conn->prepare("SELECT teacher_id FROM certificates WHERE id=?");
        $stmt->bind_param("i", $cert_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res['teacher_id']) {
            // Registered user → update users.name
            $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
            $stmt->bind_param("si", $new_name, $res['teacher_id']);
            $stmt->execute();
        } else {
            // Not registered → update certificates.temp_name
            $stmt = $conn->prepare("UPDATE certificates SET temp_name=? WHERE id=?");
            $stmt->bind_param("si", $new_name, $cert_id);
            $stmt->execute();
        }
    }
    exit;
}


/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ===== HANDLE CSV UPLOAD ===== */
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {

    if ($_FILES['csv_file']['error'] !== 0) {
        $messages[] = "❌ Please upload a valid CSV file.";
    } else {

        $file = fopen($_FILES['csv_file']['tmp_name'], "r");
        if (!$file) {
            $messages[] = "❌ Failed to open CSV file.";
        } else {

            fgetcsv($file); // skip header
            $inserted = 0;
            $skipped  = 0;

            while (($row = fgetcsv($file)) !== false) {

                if (count(array_filter($row)) === 0) continue;

                [$control, $teacher_email, $title, $certificate_file] = array_map('trim', $row);

                if (!$control || !$teacher_email || !$title || !$certificate_file) {
                    $skipped++;
                    continue;
                }

                // Check duplicate control number
                $check = $conn->prepare("SELECT id FROM certificates WHERE control_number=?");
                $check->bind_param("s", $control);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    $skipped++;
                    continue;
                }

                // Check if teacher exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=LOWER(?)");
                $stmt->bind_param("s", $teacher_email);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res->num_rows > 0) {
                    $teacher_id = $res->fetch_assoc()['id'];
                    $teacher_email_pending = null;
                } else {
                    $teacher_id = null;
                    $teacher_email_pending = $teacher_email;
                }

                // Insert certificate
                $insert = $conn->prepare("
                    INSERT INTO certificates 
                    (control_number, teacher_id, teacher_email_pending, seminar_title, certificate_file)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insert->bind_param(
                    "sisss",
                    $control,
                    $teacher_id,
                    $teacher_email_pending,
                    $title,
                    $certificate_file
                );

                if ($insert->execute()) {
                    $inserted++;
                } else {
                    $skipped++;
                }
            }

            fclose($file);
            $messages[] = "✅ Inserted: $inserted | ❌ Skipped: $skipped";
        }
    }
}

/* ===== FETCH CERTIFICATES (FIXED) ===== */
$sql = "
    SELECT 
        c.id AS cert_id,
        c.control_number,
        c.seminar_title,
        c.certificate_file,
        c.created_at,

        u.id AS user_id,
        u.name AS user_name,
        c.temp_name AS temp_name,          -- add this

        COALESCE(u.email, c.teacher_email_pending) AS display_email
    FROM certificates c
    LEFT JOIN users u 
        ON c.teacher_id = u.id
        OR LOWER(c.teacher_email_pending) = LOWER(u.email)
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
/* General */
body { margin:0; font-family:"Segoe UI", Arial, sans-serif; background:#fff; color:#1a1a1a; display:flex; flex-direction:column; min-height:100vh; overflow-x:hidden; }
h2 { color:#0b4a82; margin-top:0; }

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

/* Main */
.main-container { margin:20px; }
.upload-btn { 
    background: #1b5e20; /* Dark green */
    color: white; 
    padding: 8px 25px; 
    border-radius: 8px; 
    font-weight: bold; 
    border: none;
    cursor: pointer;
}
.upload-btn:hover { background:#e68a00; text-decoration:none; }

/* Search Bar Container */
.search-container {
    position: relative;
    display: flex;
    align-items: center;
}

.search-container input {
    width: 250px;
    padding: 8px 15px 8px 35px; /* Extra padding on left for icon */
    border: 1px solid #1976d2;
    border-radius: 20px;
    outline: none;
    font-size: 14px;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: #1976d2;
    pointer-events: none;
    font-size: 14px;
}

/* Table */
table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.05); }
th, td { padding:12px 15px; border:1px solid #ddd; text-align:center; }
th { background:#1976d2; color:#fff; }
tr:nth-child(even){ background:#f9f9f9; }
tr:hover{ background:#e3f2fd; }

/* Pagination Footer */
.pagination-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px; /* Thinner padding */
    background-color: #f8fbff; /* Very light blue tint */
    border: 1px solid #e0e0e0;
    border-top: none;
    font-size: 13px; /* Slightly smaller text */
    color: #333;
}

.footer-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.row-select-wrapper {
    color: #5c7c99;
    font-size: 13px;
}

.row-select-wrapper select {
    padding: 2px 5px;
    border: 1px solid #1976d2;
    border-radius: 4px;
    color: #0b4a82;
    background: transparent;
    font-size: 13px;
    margin-left: 5px;
}

/* Compact Pagination Buttons */
.pagination-controls {
    display: flex;
    align-items: center;
    gap: 4px; /* Tight gap between buttons */
}

.page-num, .page-arrow {
    background: white;
    border: 1px solid #cfd8dc;
    color: #1976d2;
    min-width: 28px; /* Fixed small width */
    height: 28px;    /* Fixed small height */
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    border-radius: 4px;
    font-size: 12px;
    transition: 0.2s;
}

.page-num.active {
    background: #1976d2;
    border-color: #1976d2;
    color: white;
}

.page-num:hover:not(.active), .page-arrow:hover {
    background: #f0f7ff;
    border-color: #1976d2;
}

.page-arrow {
    font-size: 10px; /* Small arrows */
    color: #78909c;
}

a { color:#0b4a82; font-weight:bold; text-decoration:none; }
a:hover { text-decoration:underline; }
.edit-btn { background:#ff9800; color:#fff; padding:5px 10px; border-radius:5px; }
.edit-btn:hover { background:#f57c00; }

/* Modal */
.modal { display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); }
.modal-content { background:#fefefe; margin:10% auto; padding:20px; border-radius:10px; width:400px; position:relative; box-shadow:0 0 20px rgba(0,0,0,0.2); }
.close { color:#aaa; position:absolute; top:10px; right:15px; font-size:28px; font-weight:bold; cursor:pointer; }
.close:hover { color:#000; }
.messages { margin-top:10px; background:#fff; padding:15px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05); max-height:200px; overflow:auto; }
.messages p { margin:5px 0; font-size:14px; }
.success { color:#155724; }
.error { color:#721c24; }
input[type="file"] { display:block; margin-bottom:15px; padding:6px; }
button { background:#0b4a82; color:#fff; padding:10px 25px; border-radius:5px; border:none; cursor:pointer; font-size:16px; }
button:hover { background:#084a6b; }
</style>
</head>
<body>

<!-- TOP NAV -->
<nav class="top-nav">
    <div class="nav-brand">Department of Education<br>Certificate Verifier</div>
    <div class="burger" id="burger"><span></span><span></span><span></span></div>
    <div class="nav-links" id="nav-menu">
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="../login.php">Logout</a>
    </div>
</nav>  

<main class="main-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Admin Dashboard</h2>
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <button id="uploadBtn" class="upload-btn">Upload</button>
                
                <div class="search-container">
                    <input type="text" id="certificateSearch" placeholder="Search" onkeyup="filterTable()">
                    <span class="search-icon">🔍</span>
                </div>
            </div>
        </div>

    <!-- Modal -->
    <div id="uploadModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Upload Certificates (CSV)</h2>
        <form method="POST" enctype="multipart/form-data" action="">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit">Upload CSV</button>
        </form>
        <?php if (!empty($messages)): ?>
        <div class="messages">
            <?php foreach ($messages as $msg): ?>
                <p class="<?= strpos($msg,'Inserted')!==false||strpos($msg,'✅')!==false?'success':'error' ?>">
                    <?= htmlspecialchars($msg) ?>
                </p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Control Number</th>
            <th>Name</th>
            <th>Seminar/Workshop Attended</th>
            <th>Email</th>
            <th>Certificate</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['control_number']) ?></td>
            <td>
                <span class="name-text">
                    <?= htmlspecialchars($row['temp_name'] ?? $row['user_name'] ?? explode('@', $row['display_email'])[0] ?? 'Not registered') ?>
                </span>

                <input type="text"
                    class="name-input"
                    value="<?= htmlspecialchars($row['temp_name'] ?? $row['user_name'] ?? explode('@', $row['display_email'])[0] ?? '') ?>"
                    style="display:none; width:120px;">

            </td>
            <td><?= htmlspecialchars($row['seminar_title']) ?></td>
            <td><?= htmlspecialchars($row['display_email'] ?? '') ?></td>
            <td><a href="<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">View Certificate</a></td>
            <td><button class="edit-btn" onclick="editName(this, <?= $row['cert_id'] ?>)">
               Edit
             </button></td>
        </tr>
        <?php endwhile; ?>
    </table>

        <div class="pagination-footer">
            <div class="footer-left">
                Showing <b>1</b> to <b>20</b> of <b>50</b> teachers
            </div>
            
            <div class="footer-right">
                <div class="row-select-wrapper">
                    Row per page: 
                    <select id="rowPerPage" onchange="changeRowsPerPage()">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30" selected>30</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="pagination-controls">
                    <button class="page-arrow" onclick="prevPage()">❮</button>
                    <button class="page-num active">1</button>
                    <button class="page-num">2</button>
                    <button class="page-num">3</button>
                    <button class="page-num">4</button>
                    <button class="page-num">5</button>
                    <button class="page-arrow" onclick="nextPage()">❯</button>
                </div>
            </div>
        </div>

    <?php else: ?>
    <p>No certificates found.</p>
    <?php endif; ?>
</main>

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

// Modal logic
const modal = document.getElementById("uploadModal");
const uploadBtn = document.getElementById("uploadBtn");
const spanClose = document.getElementsByClassName("close")[0];

uploadBtn.onclick = () => modal.style.display="block";
spanClose.onclick = () => modal.style.display="none";
window.onclick = e => { if(e.target==modal) modal.style.display="none"; }

function filterTable() {
    const input = document.getElementById("certificateSearch");
    const filter = input.value.toLowerCase();
    const table = document.querySelector("table");
    const tr = table.getElementsByTagName("tr");

    // Loop through all table rows (except the header)
    for (let i = 1; i < tr.length; i++) {
        let match = false;
        const tds = tr[i].getElementsByTagName("td");
        
        // Check Name, Email, and Control Number columns for a match
        for (let j = 0; j < tds.length; j++) {
            if (tds[j] && tds[j].innerText.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }
        tr[i].style.display = match ? "" : "none";
    }
}
function editName(btn, certId) {
    const row = btn.closest('tr');
    const text = row.querySelector('.name-text');
    const input = row.querySelector('.name-input');

    if (btn.innerText === 'Edit') {
        text.style.display = 'none';
        input.style.display = 'inline-block';
        input.focus();
        btn.innerText = 'Save';
    } else {
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `update_name=1&id=${certId}&name=${encodeURIComponent(input.value)}`
        }).then(() => {
            text.innerText = input.value || 'Not registered';
            text.style.display = 'inline';
            input.style.display = 'none';
            btn.innerText = 'Edit';
        });
    }
}

// Function to handle clicking page numbers
document.querySelectorAll('.page-num').forEach(button => {
    button.addEventListener('click', function() {
        // Remove active class from all
        document.querySelectorAll('.page-num').forEach(b => b.classList.remove('active'));
        // Add to clicked
        this.classList.add('active');
        
        const page = parseInt(this.innerText);
        console.log("Navigating to page: " + page);
        // Add your logic to filter/load data for the specific page here
    });
});

function prevPage() {
    console.log("Previous Page Clicked");
}

function nextPage() {
    console.log("Next Page Clicked");
}
</script>

</body>
</html>
