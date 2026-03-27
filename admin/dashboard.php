<?php
// You don't have these at the top of your file:
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Handle CSV upload for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Your existing upload code here...
    
    // If it's an AJAX request, return JSON response
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $inserted > 0,
            'messages' => $messages,
            'inserted' => $inserted,
            'skipped' => $skipped
        ]);
        exit;
    }
}
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
$inserted = 0;
$skipped = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {

    // Check for upload errors
    if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
            UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",
            UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
            UPLOAD_ERR_NO_FILE => "No file was uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
        ];
        $errorMsg = $uploadErrors[$_FILES['csv_file']['error']] ?? "Unknown upload error";
        $messages[] = "❌ Upload failed: " . $errorMsg;
        
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'messages' => $messages,
                'inserted' => 0,
                'skipped' => 0
            ]);
            exit;
        }
    } else {
        // Check file type
        $fileType = mime_content_type($_FILES['csv_file']['tmp_name']);
        $allowedTypes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
        
        if (!in_array($fileType, $allowedTypes) && pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION) !== 'csv') {
            $messages[] = "❌ Please upload a valid CSV file.";
        } else {
            $file = fopen($_FILES['csv_file']['tmp_name'], "r");
            if (!$file) {
                $messages[] = "❌ Failed to open CSV file.";
            } else {
                // Start transaction for better performance with large files
                $conn->begin_transaction();
                
                try {
                    fgetcsv($file); // skip header
                    
                    // Prepare statements for better performance
                    $checkStmt = $conn->prepare("SELECT id FROM certificates WHERE control_number=?");
                    $insertStmt = $conn->prepare("
                        INSERT INTO certificates 
                        (control_number, temp_name, seminar_title, certificate_file)
                        VALUES (?, ?, ?, '')
                    ");
                    
                    $batchSize = 100; // Process in batches for better memory management
                    $counter = 0;
                    
                    while (($row = fgetcsv($file)) !== false) {
                        // Skip empty rows
                        if (count(array_filter($row)) === 0) continue;
                        
                        // CSV column order: control_number | name | seminar_title | teacher_email | certificate_file
                        $control = isset($row[0]) ? trim($row[0]) : '';
                        $name = isset($row[1]) ? trim($row[1]) : '';
                        $title = isset($row[2]) ? trim($row[2]) : '';
                        
                        if (!$control || !$name || !$title) {
                            $skipped++;
                            continue;
                        }
                        
                        // Check duplicate control number
                        $checkStmt->bind_param("s", $control);
                        $checkStmt->execute();
                        if ($checkStmt->get_result()->num_rows > 0) {
                            $skipped++;
                            continue;
                        }
                        
                        // Insert certificate
                        $insertStmt->bind_param("sss", $control, $name, $title);
                        
                        if ($insertStmt->execute()) {
                            $inserted++;
                        } else {
                            $skipped++;
                        }
                        
                        $counter++;
                        // Commit in batches to avoid memory issues
                        if ($counter % $batchSize === 0) {
                            $conn->commit();
                            $conn->begin_transaction();
                        }
                    }
                    
                    // Final commit
                    $conn->commit();
                    
                    $messages[] = "✅ Upload completed! Inserted: $inserted | Skipped: $skipped";
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    $messages[] = "❌ Error during upload: " . $e->getMessage();
                } finally {
                    fclose($file);
                    if (isset($checkStmt)) $checkStmt->close();
                    if (isset($insertStmt)) $insertStmt->close();
                }
            }
        }
    }
    
    // If it's an AJAX request, return JSON response
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $inserted > 0,
            'messages' => $messages,
            'inserted' => $inserted,
            'skipped' => $skipped
        ]);
        exit;
    }
}

/* ===== PAGINATION SETUP ===== */
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rowsPerPage = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
$offset = ($page - 1) * $rowsPerPage;                                                                                                              

/* ===== SORTING ===== */
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$orderBy = "ORDER BY c.created_at DESC"; // default

switch ($sort) {
    case 'control_new':
        $orderBy = "ORDER BY c.control_number DESC";
        break;
    case 'control_old':
        $orderBy = "ORDER BY c.control_number ASC";
        break;
    case 'name_asc':
        $orderBy = "ORDER BY COALESCE(u.name, c.temp_name) COLLATE utf8mb4_general_ci ASC";
        break;
    case 'name_desc':
        $orderBy = "ORDER BY COALESCE(u.name, c.temp_name) COLLATE utf8mb4_general_ci DESC";
        break;
}

/* ===== FETCH CERTIFICATES ===== */
$sql = "
    SELECT 
        c.id AS cert_id,
        c.control_number,
        c.seminar_title,
        c.certificate_file,
        c.created_at,
        u.id AS user_id,
        u.name AS user_name,
        c.temp_name AS temp_name,
        COALESCE(u.email, c.teacher_email_pending) AS display_email
    FROM certificates c
    LEFT JOIN users u 
        ON c.teacher_id = u.id
    $orderBy
    LIMIT $rowsPerPage OFFSET $offset
";

$totalResult = $conn->query("SELECT COUNT(*) AS total FROM certificates");
$totalRows = $totalResult->fetch_assoc()['total'];
$result = $conn->query($sql);
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">

    <title>Admin Dashboard | CerVer - Certificate Verifier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/cerverlogo2.svg">

    <style>
    /* General */
    body { margin:0; font-family:"Segoe UI", Arial, sans-serif; background:#fff; color:#1a1a1a; display:flex; flex-direction:column; min-height:100vh; overflow-x:hidden; }
    h2 { color:#0b4a82; margin-top:0; }

    /* Top Nav */
    .top-nav { background:#0b4a82; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; color:#fff; position:sticky; top:0; z-index:1000; }
    .nav-brand { font-size:18px; font-weight:500; line-height:1.2; }
    .nav-links { display:flex; align-items:center; }
    .nav-links a { color:#fff; text-decoration:none; margin-left:35px; font-size:15px; font-weight:400; }
    .nav-links a:hover { text-decoration:underline; }

    /* Burger */
    .burger { 
        display:none; 
        flex-direction:column; 
        cursor:pointer; 
        gap:5px; z-index:1001; 
        display: flex;
        flex-direction: column;
        cursor: pointer;
        gap: 5px;
        z-index: 1003; /* Higher than .nav-links */
        position: relative; /* Ensure it stays above */
    }

    .burger span { 
        height:3px; 
        width:28px; 
        background:white; 
        border-radius:5px; 
        transition:all 0.3s ease; }
        
    .burger.toggle span:nth-child(1) { transform:rotate(-45deg) translate(-5px,6px); }
    .burger.toggle span:nth-child(2) { opacity:0; }
    .burger.toggle span:nth-child(3) { transform:rotate(45deg) translate(-5px,-6px); }

    /* Main */
    .main-container { margin:20px; }
    .upload-btn { 
        background: #28a745; /* Dark green */
        color: white; 
        padding: 8px 25px; 
        border-radius: 8px; 
        font-weight: bold; 
        border: none;
        cursor: pointer;
    }
    .upload-btn:hover { background:#1b5e20; text-decoration:none; }

    .dlbtn { 
        background: #1b5e20; /* Dark green */
        color: white; 
        padding: 8px 25px; 
        border-radius: 8px; 
        font-weight: bold; 
        border: none;
        cursor: pointer;
    }
    .dlbtn:hover { background:#e68a00; text-decoration:none; }

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

    .logout {
    color: black;        /* normal color */
    text-decoration: none;
    transition: color 0.2s ease; /* smooth change */
    background: #e21717; /* Dark green */
        color: white; 
        padding: 8px 25px; 
        border-radius: 8px; 
        font-weight: bold; 
        border: none;
        cursor: pointer;
    }

    .logout:hover {
    background-color: #c61010;
    }

    /* Modal */
    .modal { 
        display:none; 
        position:fixed; 
        z-index:999;
        left:0; 
        top:0; 
        width:100%; 
        height:100%; 
        overflow:auto; 
        background-color:rgba(0,0,0,0.5); 
    }
    .modal-content { 
        background:#fefefe; margin:10% auto; padding:20px; border-radius:10px; width:400px; position:relative; box-shadow:0 0 20px rgba(0,0,0,0.2); }
    .close { color:#aaa; position:absolute; top:10px; right:15px; font-size:28px; font-weight:bold; cursor:pointer; }
    .close:hover { color:#000; }
    .messages { margin-top:10px; background:#fff; padding:15px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05); max-height:200px; overflow:auto; }
    .messages p { margin:5px 0; font-size:14px; }
    .success { color:#155724; }
    .error { color:#721c24; }
    input[type="file"] { display:block; margin-bottom:15px; padding:6px; }
    button { background:#0b4a82; color:#fff; padding:10px 25px; border-radius:5px; border:none; cursor:pointer; font-size:16px; }
    button:hover { background:#084a6b; }

    .mobile-close {
        display: none;
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 28px;
        color: white;
        cursor: pointer;
    }

    /* Remove old conflicting burger styles first */
    .burger { display: none; }  /* Hide on desktop */

    /* Mobile burger and slide-in menu */
    @media (max-width: 768px) {
        .burger {
            display: flex;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            z-index: 1003;
        }

        .burger span {
            height: 3px;
            width: 28px;
            background: white;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .burger.toggle span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        .burger.toggle span:nth-child(2) { opacity: 0; }
        .burger.toggle span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        .nav-links {
            position: fixed;
            top: 0;
            right: -100%;
            height: 100%;
            width: 30%;
            background: #0b4a82;
            flex-direction: column;
            align-items: flex-start;
            padding: 60px 20px;
            gap: 25px;
            transition: right 0.3s ease;
            z-index: 1002;
        }

        .nav-links.active {
            right: 0;
        }

        .nav-links a {
            margin: 0;
            padding: 20px 30px;
            width: 100%;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 18px;
        }
    }

    /* Desktop nav links always visible */
    @media (min-width: 769px) {
        .nav-links {
            display: flex !important;
            position: static;
            height: auto;
            width: auto;
            flex-direction: row;
            background: none;
            padding: 0;
            gap: 35px;
        }
    }

        .header-container { 
            display:flex; 
            flex-direction:column; 
            gap:10px; 
        }
        .controls-container {
            display:flex; 
            flex-direction:column; 
            align-items:stretch; 
            gap:10px; 
        }
        .controls-container button.upload-btn { 
            width:100%; 
        }
        .search-container input { 
            width:100%; 
        }

        .controls-container {
            display: flex;
            flex-direction: column; /* stack Upload and Search */
            align-items: stretch;   /* full width */
            gap: 10px;
        }

        .controls-container button.upload-btn {
            width: 100%;
        }

        .search-container input {
            width: 100%; /* full width search bar */
        }
        .header-container {
            display: flex;
            flex-direction: column;
            gap: 10px; /* spacing between heading and controls */
        }

        .controls-container {
            display: flex;
            flex-direction: column; /* stack Upload and Search */
            align-items: stretch;   /* full width */
            gap: 10px;
        }

        .controls-container button.upload-btn {
            width: 100%;
        }

        .search-container input {
            width: 100%; /* full width search bar */
        }
    .dbutton {
        display: inline-block;       /* Makes it behave like a button */
        padding: 8px 25px;          /* Space inside the button */
        background-color: #28a745;   /* Button background color */
        color: white;                /* Text color */
        
        border-radius: 10px;          /* Rounded corners */
        font-weight: 600;            /* Bold text */
        transition: background 0.3s; /* Smooth hover effect */
        text-decoration: none;
    }

    .dbutton:hover {
        background-color: #0f4d1d;   /* Darker shade on hover */
    }

    .dbutton,
    .dbutton:link,
    .dbutton:visited,
    .dbutton:hover,
    .dbutton:active {
        text-decoration: none;
    }
    /* Sorting Dropdown inside Table Header */
    th select {
        background: rgba(255,255,255,0.15);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.6);
        border-radius: 5px;
        padding: 3px 6px;
        font-size: 12px;
        margin-left: 8px;
        cursor: pointer;
        outline: none;
    }

    /* Dropdown hover */
    th select:hover {
        background: rgba(255,255,255,0.25);
    }

    /* Dropdown options */
    th select option {
        color: #000; /* options must be black or unreadable */
        background: #fff;
    }
    
/* Fixed sizes for each table column */
th.no-col, td.no-col {
    width: 43px; /* was 58px */
    height: 92px;
}

th.control-col, td.control-col {                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    width: 137px;
    height: 92px;
}

th.name-col, td.name-col {
    width: 276.89px;
    height: 92px;
}

th.seminar-col, td.seminar-col {
    width: 570.79px;
    height: 92px;
}

th.action-col, td.action-col {
    width: 64.99px; /* was 79.99px */
    height: 92px;
}

/* Optional: keep text tidy */
th, td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    position: relative;
}

/* Table fixed layout */
table {
    table-layout: fixed;  /* ensures fixed column widths */
    border-collapse: collapse;          /* table width adjusts to column widths */
}
.seminar-cell {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
}

/* Expanded (show full text) */
.seminar-cell.expanded {
    white-space: normal;
    overflow: visible;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#loadingText {
    display: none;
    margin-left: 10px;
    font-weight: bold;
    color: #1976d2;
    align-items: center;a
    gap: 5px;
}

#loadingText::before {
    display: inline-block;
    animation: spin 1s linear infinite;
}
    </style>
    </head>
    <body> 

    <!-- TOP NAV -->
    <nav class="top-nav">
        <div class="nav-brand">Department of Education<br>CSJDM Certificate Verifier</div>
        
        <div class="burger" id="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="nav-links" id="nav-menu">
            <!-- <a href="dashboard.php">Home</a> -->
            <!-- <a href="../about.php">About</a> -->
            <!-- <a href="#">Contact</a> -->
            <a href="logout.php"> <button class="logout">Log out</button> </a>
        </div>
    </nav>  

    <main class="main-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Admin Dashboard</h2>
                
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="../files/template.xlsx" download class="dbutton">Download Template</a>
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
            <form id="csvForm" method="POST" enctype="multipart/form-data" action="">
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit">Upload CSV</button>
                <span id="loadingText" style="display:none;">
                    <span style="display: inline-block; animation: spin 1s linear infinite;">⏳</span>
                    Uploading CSV...
                </span>
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
            <?php $rowNumber = 1; ?>
            <tr>
                <th class="no-col">No.</th>
            <th>
            Control Number
            <select onchange="location = this.value;">
                <option value="?page=1&rows=<?= $rowsPerPage ?>&sort=control_new"
                    <?= ($sort=='control_new')?'selected':'' ?>>New</option>
                <option value="?page=1&rows=<?= $rowsPerPage ?>&sort=control_old"
                    <?= ($sort=='control_old')?'selected':'' ?>>Old</option>
            </select>
        </th>

        <th>
            Name
            <select onchange="location = this.value;">
                <option value="?page=1&rows=<?= $rowsPerPage ?>&sort=name_asc"
                    <?= ($sort=='name_asc')?'selected':'' ?>>
                    A - Z
                </option>
                <option value="?page=1&rows=<?= $rowsPerPage ?>&sort=name_desc"
                    <?= ($sort=='name_desc')?'selected':'' ?>>
                    Z - A
                </option>
        </select>
    </th>
                <th>Seminar/Workshop Attended</th>
                <!-- <th>Email</th> -->
                <!-- <th>Certificate</th> -->
                <th class="action-col">Action</th>
            </tr>
            <?php $rowNumber = $offset + 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $rowNumber++ ?></td>
                <td><?= htmlspecialchars($row['control_number']) ?></td>
                <td>
                    <span class="name-text">
                <?= htmlspecialchars(
                    $row['user_name']
                    ?? $row['temp_name']
                    ?? (isset($row['display_email']) ? explode('@', $row['display_email'])[0] : null)
                    ?? 'Not registered'
                ) ?>
            </span>

                    <input type="text"
                            class="name-input"
                            value="<?= htmlspecialchars($row['temp_name'] ?? $row['user_name'] ?? explode('@', $row['display_email'])[0] ?? '') ?>"
                        style="display:none; width:120px;">

                </td>
                <td class="seminar-cell" onclick="toggleSeminar(this)">
                    <?= htmlspecialchars($row['seminar_title']) ?>
                </td>
                <!-- <td><?= htmlspecialchars($row['display_email'] ?? '') ?></td> -->
                <!-- <td><a href="<?= htmlspecialchars($row['certificate_file']) ?>" target="_blank">Link</a></td> -->
                <td><button class="edit-btn" onclick="editName(this, <?= $row['cert_id'] ?>)">
                Edit
                </button></td>
            </tr>
            <?php endwhile; ?>
        </table>

            <div class="pagination-footer">
        <div class="footer-left">
            <?php
            $start = $offset + 1;
            $end = min($offset + $rowsPerPage, $totalRows);
            ?>
            Showing <b><?= $start ?></b> to <b><?= $end ?></b> of <b><?= $totalRows ?></b> teachers
        </div>
        
        <div class="footer-right">
            <div class="row-select-wrapper">
                Row per page: 
            <select onchange="location.href='?page=1&rows='+this.value">
                    <option value="100" <?= $rowsPerPage==100 ? 'selected' : '' ?>>100</option>
                    <option value="50" <?= $rowsPerPage==50 ? 'selected' : '' ?>>50</option>
                    <option value="30" <?= $rowsPerPage==30 ? 'selected' : '' ?>>30</option>
                    <option value="20" <?= $rowsPerPage==20 ? 'selected' : '' ?>>20</option>
                    <option value="10" <?= $rowsPerPage==10 ? 'selected' : '' ?>>10</option>
                </select>
            </div>

            <div class="pagination-controls">
                <!-- Previous arrow -->
                <a class="page-arrow" href="?page=<?= max(1, $page-1) ?>&rows=<?= $rowsPerPage ?>">❮</a>

                <?php
                $totalPages = ceil($totalRows / $rowsPerPage);
                // Show up to 10 page numbers (adjustable)
                $startPage = max(1, $page - 4);
                $endPage = min($totalPages, $page + 5);

                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a class="page-num <?= $i==$page ? 'active' : '' ?>" href="?page=<?= $i ?>&rows=<?= $rowsPerPage ?>"><?= $i ?></a>
                <?php endfor; ?>

                <!-- Next arrow -->
                <a class="page-arrow" href="?page=<?= min($totalPages, $page+1) ?>&rows=<?= $rowsPerPage ?>">❯</a>
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
    if (burger) {
        burger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            burger.classList.toggle('toggle');
        });
    }
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

    if (uploadBtn) {
        uploadBtn.onclick = () => modal.style.display = "block";
    }
    if (spanClose) {
        spanClose.onclick = () => modal.style.display = "none";
    }
    window.onclick = e => { if(e.target == modal) modal.style.display = "none"; }

    function filterTable() {
        const input = document.getElementById("certificateSearch");
        if (!input) return;
        const filter = input.value.toLowerCase();
        const table = document.querySelector("table");
        if (!table) return;
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let match = false;
            const tds = tr[i].getElementsByTagName("td");
            
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

    document.querySelectorAll('.page-num').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.page-num').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const page = parseInt(this.innerText);
            console.log("Navigating to page: " + page);
        });
    });
    
    // FIXED: AJAX Form submission with loading indicator
    const form = document.getElementById('csvForm');
    const loadingText = document.getElementById('loadingText');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent page reload
            
            // Show loading indicator
            if (loadingText) {
                loadingText.style.display = 'inline';
            }
            
            // Get the submit button and disable it
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Uploading...';
            }
            
            // Create FormData object
            const formData = new FormData(form);
            
            try {
                // Send the file via fetch
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                // Get the response text
                const htmlResponse = await response.text();
                
                // Create a temporary div to parse the response
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlResponse;
                
                // Extract messages from the response
                const messagesHtml = tempDiv.querySelector('.messages');
                
                // Update the messages in the modal
                let messagesDiv = document.querySelector('.messages');
                if (!messagesDiv) {
                    // If messages div doesn't exist, create it
                    messagesDiv = document.createElement('div');
                    messagesDiv.className = 'messages';
                    const modalContent = document.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.appendChild(messagesDiv);
                    }
                }
                
                if (messagesHtml) {
                    messagesDiv.innerHTML = messagesHtml.innerHTML;
                } else {
                    // Check if there are any messages in the PHP session
                    messagesDiv.innerHTML = '<p class="success">✅ Upload completed. Please refresh the page to see changes.</p>';
                }
                
                // Refresh the table data after upload
                setTimeout(() => {
                    location.reload(); // Reload to show new data
                }, 2000);
                
            } catch (error) {
                console.error('Upload error:', error);
                // Show error message
                let messagesDiv = document.querySelector('.messages');
                if (!messagesDiv) {
                    messagesDiv = document.createElement('div');
                    messagesDiv.className = 'messages';
                    const modalContent = document.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.appendChild(messagesDiv);
                    }
                }
                messagesDiv.innerHTML = '<p class="error">❌ Upload failed. Please try again.</p>';
                
            } finally {
                // Hide loading indicator
                if (loadingText) {
                    loadingText.style.display = 'none';
                }
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Upload CSV';
                }
            }
        });
    }

    function prevPage() {
        console.log("Previous Page Clicked");
    }

    function nextPage() {
        console.log("Next Page Clicked");
    }
    
    function toggleSeminar(cell) {    
        cell.classList.toggle('expanded');
    }
</script>

    </body>
    </html>
