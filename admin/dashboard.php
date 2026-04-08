<?php
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

session_start();
require_once __DIR__ . "/../config/db.php";

/* ===== INLINE NAME UPDATE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $cert_id = (int)$_POST['id'];
    $new_name = trim($_POST['name']);

    if ($new_name !== '') {
        $stmt = $conn->prepare("SELECT teacher_id FROM certificates WHERE id=?");
        $stmt->bind_param("i", $cert_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if (!empty($res['teacher_id'])) {
            $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
            $stmt->bind_param("si", $new_name, $res['teacher_id']);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("UPDATE certificates SET temp_name=? WHERE id=?");
            $stmt->bind_param("si", $new_name, $cert_id);
            $stmt->execute();
        }
    }
    exit;
}

/* ===== BULK DELETE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $ids = $_POST['selected_ids'] ?? [];

    if (!empty($ids) && is_array($ids)) {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));

            $stmt = $conn->prepare("DELETE FROM certificates WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$ids);
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
$updated = 0;
$skipped = 0;
$skippedReasons = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {

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
    } else {
        $fileType = mime_content_type($_FILES['csv_file']['tmp_name']);
        $allowedTypes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];

        if (!in_array($fileType, $allowedTypes) && strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION)) !== 'csv') {
            $messages[] = "❌ Please upload a valid CSV file.";
        } else {
            $file = fopen($_FILES['csv_file']['tmp_name'], "r");

            if (!$file) {
                $messages[] = "❌ Failed to open CSV file.";
            } else {
                $conn->begin_transaction();

                try {
                    fgetcsv($file); // skip header

                    $insertStmt = $conn->prepare("
                        INSERT INTO certificates
                        (control_number, temp_name, seminar_title, certificate_file)
                        VALUES (?, ?, ?, '')
                        ON DUPLICATE KEY UPDATE
                            temp_name = VALUES(temp_name),
                            seminar_title = VALUES(seminar_title)
                    ");

                    if (!$insertStmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $batchSize = 100;
                    $counter = 0;
                    $rowNumber = 1; // header row

                    while (($row = fgetcsv($file)) !== false) {
                        $rowNumber++;

                        if (!is_array($row) || count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) {
                            continue;
                        }

                        $control = trim($row[0] ?? '');
                        $name    = trim($row[1] ?? '');
                        $title   = trim($row[2] ?? '');

                        if ($control === '' || $name === '') {
                            $skipped++;

                            $reason = "Row {$rowNumber} skipped: missing field";
                            if ($control === '') $reason .= " [control_number empty]";
                            if ($name === '')    $reason .= " [name empty]";
                            if ($title === '')   $reason .= " [seminar_title empty]";

                            $skippedReasons[] = $reason;
                            continue;
                        }

                        $insertStmt->bind_param("sss", $control, $name, $title);

                        if ($insertStmt->execute()) {
                            if ($insertStmt->affected_rows === 1) {
                                $inserted++;
                            } elseif ($insertStmt->affected_rows === 2) {
                                $updated++;
                            } else {
                                $updated++;
                            }
                        } else {
                            $skipped++;
                            $skippedReasons[] = "Row {$rowNumber} failed insert: " . $insertStmt->error . " [control_number: {$control}]";
                        }

                        $counter++;

                        if ($counter % $batchSize === 0) {
                            $conn->commit();
                            $conn->begin_transaction();
                        }
                    }

                    $conn->commit();

                    $messages[] = "✅ Upload completed! Inserted: $inserted | Updated: $updated | Skipped: $skipped";

                    if (!empty($skippedReasons)) {
                        $logFile = __DIR__ . "/skip_log_" . date("Ymd_His") . ".txt";
                        file_put_contents($logFile, implode(PHP_EOL, $skippedReasons));
                        $messages[] = "📄 Skip log saved: " . basename($logFile);
                    }

                } catch (Exception $e) {
                    $conn->rollback();
                    $messages[] = "❌ Error during upload: " . $e->getMessage();
                } finally {
                    fclose($file);
                    if (isset($insertStmt)) {
                        $insertStmt->close();
                    }
                }
            }
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => ($inserted > 0 || $updated > 0),
            'messages' => $messages,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped
        ]);
        exit;
    }
}

/* ===== PAGINATION SETUP ===== */
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rowsPerPage = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;

/* Optional safety for invalid row values */
$allowedRows = [10, 20, 30, 50, 100];
if (!in_array($rowsPerPage, $allowedRows)) {
    $rowsPerPage = 10;
}

/* ===== SORTING ===== */
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'control_new';
$orderBy = "ORDER BY c.control_number DESC";

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
    default:
        $sort = 'control_new';
        $orderBy = "ORDER BY c.control_number DESC";
        break;
}
/* ===== LIVE SEARCH SUGGESTIONS ===== */
/* ===== AJAX LIVE TABLE SEARCH ===== */
if (isset($_GET['ajax_live_search'])) {

    $search = trim($_GET['ajax_live_search']);
    $safeSearch = $conn->real_escape_string($search);

    $sql = "
        SELECT 
            c.id AS cert_id,
            c.control_number,
            c.seminar_title,
            u.name AS user_name,
            c.temp_name,
            COALESCE(u.email, c.teacher_email_pending) AS display_email
        FROM certificates c
        LEFT JOIN users u ON c.teacher_id = u.id
        WHERE
            c.control_number LIKE '%$safeSearch%' OR
            c.seminar_title LIKE '%$safeSearch%' OR
            u.name LIKE '%$safeSearch%' OR
            c.temp_name LIKE '%$safeSearch%' OR
            c.teacher_email_pending LIKE '%$safeSearch%'
        ORDER BY c.control_number DESC
        LIMIT 50
    ";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td></td>"; // checkbox column if needed
        echo "<td>" . htmlspecialchars($row['control_number']) . "</td>";

        $name = $row['user_name']
            ?? $row['temp_name']
            ?? (isset($row['display_email']) ? explode('@', $row['display_email'])[0] : 'Not registered');

        echo "<td>" . htmlspecialchars($name) . "</td>";
        echo "<td>" . htmlspecialchars($row['seminar_title']) . "</td>";

        echo "<td><button class='edit-btn'>Edit</button></td>";
        echo "</tr>";
    }

    exit;
}

/* ===== SEARCH ===== */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$letter = isset($_GET['letter']) ? trim($_GET['letter']) : '';

$whereConditions = [];

if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $whereConditions[] = "(
        c.control_number LIKE '%$safeSearch%' OR
        c.seminar_title LIKE '%$safeSearch%' OR
        u.name LIKE '%$safeSearch%' OR
        c.temp_name LIKE '%$safeSearch%' OR
        c.teacher_email_pending LIKE '%$safeSearch%'
    )";
}

if ($letter !== '') {
    $safeLetter = $conn->real_escape_string($letter);
    $whereConditions[] = "UPPER(COALESCE(u.name, c.temp_name)) LIKE UPPER('$safeLetter%')";
}

$where = "";
if (!empty($whereConditions)) {
    $where = "WHERE " . implode(" AND ", $whereConditions);
}

/* ===== TOTAL COUNT ===== */
$countSql = "
    SELECT COUNT(*) AS total
    FROM certificates c
    LEFT JOIN users u
        ON c.teacher_id = u.id
    $where
";

$totalResult = $conn->query($countSql);
$totalRows = (int)($totalResult->fetch_assoc()['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalRows / $rowsPerPage));

/* If current page is beyond available pages, clamp it */
if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $rowsPerPage;

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
    $where
    $orderBy
    LIMIT $rowsPerPage OFFSET $offset
";

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
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
    * { font-family: 'Poppins', sans-serif; }

    body {
        margin: 0;
        font-family: "Segoe UI", Arial, sans-serif;
        background: #fff;
        color: #1a1a1a;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        overflow-x: hidden;
    }

    h2 { color: #0b4a82; margin-top: 0; }

    .top-nav {
        background-color: #0056b3;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #ffffff;
        position: relative;
        z-index: 1000;
    }

    .nav-brand {
        display: flex;
        flex-direction: column;
        gap: 4px;
        line-height: 1.2;
        min-width: 0;
    }

    .brand-top {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        opacity: 0.8;
        margin: 0;
    }

    .brand-title {
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        margin: 0;
    }

    .brand-subtitle {
        font-size: 12px;
        font-weight: 300;
        opacity: 0.7;
        margin: 0;
    }

    @media (min-width: 640px) {
        .brand-top { font-size: 11px; }
        .brand-title { font-size: 18px; }
        .brand-subtitle { font-size: 13px; }
    }

    @media (min-width: 768px) {
        .brand-top { font-size: 12px; letter-spacing: 0.18em; }
        .brand-title { font-size: 20px; }
        .brand-subtitle { font-size: 14px; }
    }

    @media (min-width: 1024px) {
        .brand-title { font-size: 22px; }
        .brand-subtitle { font-size: 15px; }
    }

    .nav-brand p,
    .nav-brand h1 {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nav-links {
        display: flex;
        align-items: center;
    }

    .nav-links a {
        color: #fff;
        text-decoration: none;
        margin-left: 35px;
        font-size: 15px;
        font-weight: 400;
    }

    .nav-links a:hover { text-decoration: underline; }

    .burger {
        display: none;
    }

    .burger span {
        height: 3px;
        width: 28px;
        background: white;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .burger.toggle span:nth-child(1) { transform: rotate(-45deg) translate(-5px, 6px); }
    .burger.toggle span:nth-child(2) { opacity: 0; }
    .burger.toggle span:nth-child(3) { transform: rotate(45deg) translate(-5px, -6px); }

    .main-container { margin: 20px; }

    .upload-btn {
        background: #28a745;
        color: white;
        padding: 8px 25px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }

    .upload-btn:hover { background: #1b5e20; }

    .delete-toggle-btn {
        background: #e53935;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    .delete-toggle-btn:hover { background: #c62828; }

    .delete-selected-btn {
        background: #b71c1c;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        display: none;
    }

    .delete-selected-btn:hover { background: #8e0000; }

    .cancel-delete-btn {
        background: #757575;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        display: none;
    }

    .cancel-delete-btn:hover { background: #5f5f5f; }

    .delete-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .row-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .search-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-container input {
        width: 250px;
        padding: 8px 15px 8px 35px;
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

    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        table-layout: fixed;
    }

    th, td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        text-align: center;
    }

    th {
        background: #1976d2;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
    }

    td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        position: relative;
        font-size: 11px;
        padding: 6px 8px;
    }

    tr:nth-child(even) { background: #f9f9f9; }
    tr:hover { background: #e3f2fd; }

    .pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        background-color: #f5faff;
        border: 1px solid #e0e0e0;
        border-top: none;
        font-size: 13px;
        color: #333;
        margin-bottom: 2px;
        position: sticky;
    }

    .footer-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .row-select-wrapper,
    .footer-filter {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #5c7c99;
        font-size: 13px;
    }

    .row-select-wrapper select,
    .footer-filter select {
        padding: 2px 5px;
        border: 1px solid #1976d2;
        border-radius: 4px;
        color: #0b4a82;
        background: transparent;
        font-size: 13px;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .page-num, .page-arrow {
        background: white;
        border: 1px solid #cfd8dc;
        color: #1976d2;
        min-width: 28px;
        height: 28px;
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

    a {
        color: #0b4a82;
        font-weight: bold;
        text-decoration: none;
    }

    a:hover { text-decoration: underline; }

    .edit-btn {
        background: #ff9800;
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .edit-btn:hover { background: #f57c00; }

    .logout {
        background: #e21717;
        color: white;
        padding: 8px 25px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }

    .logout:hover { background-color: #c61010; }

    .modal {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border-radius: 10px;
        width: 400px;
        position: relative;
        box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }

    .close {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover { color: #000; }

    .messages {
        margin-top: 10px;
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        max-height: 200px;
        overflow: auto;
    }

    .messages p { margin: 5px 0; font-size: 14px; }
    .success { color: #155724; }
    .error { color: #721c24; }

    input[type="file"] {
        display: block;
        margin-bottom: 15px;
        padding: 6px;
    }

    button {
        background: #0b4a82;
        color: #fff;
        padding: 10px 25px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }

    button:hover { background: #084a6b; }

    .dbutton {
        display: inline-block;
        padding: 8px 25px;
        background-color: #28a745;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        transition: background 0.3s;
        text-decoration: none;
    }

    .dbutton:hover { background-color: #0f4d1d; }

    .dbutton,
    .dbutton:link,
    .dbutton:visited,
    .dbutton:hover,
    .dbutton:active {
        text-decoration: none;
    }

    th.no-col, td.no-col { width: 43px; height: 60px; }
    th.control-col, td.control-col { width: 137px; height: 60px; }
    th.name-col, td.name-col { width: 276.89px; height: 60px; }
    th.seminar-col, td.seminar-col { width: 570.79px; height: 60px; }
    th.action-col, td.action-col { width: 64.99px; height: 60px; }

    .select-col,
    .select-cell {
        display: none;
        width: 50px;
        text-align: center;
    }

    .table-container.delete-mode .select-col,
    .table-container.delete-mode .select-cell {
        display: table-cell;
    }

    .seminar-cell {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }

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
        align-items: center;
        gap: 5px;
    }

    #loadingText::before {
        display: inline-block;
        animation: spin 1s linear infinite;
    }

    .table-container {
        max-height: 700px;
        overflow-y: auto;
        border: 1px solid #ccc;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    @media (max-width: 768px) {
        .burger {
            display: flex;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            z-index: 1003;
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

        .nav-links.active { right: 0; }

        .nav-links a {
            margin: 0;
            padding: 20px 30px;
            width: 100%;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 18px;
        }
    }

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

    @media (max-width: 790px) {
        .main-container {
            margin: 10px;
        }

        .main-container > div:first-child {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .main-container > div:first-child > div {
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
        }

        .upload-btn,
        .delete-toggle-btn,
        .delete-selected-btn,
        .cancel-delete-btn,
        .dbutton {
            width: 100%;
            text-align: center;
        }

        .search-container {
            width: 100%;
        }

        .search-container input {
            width: 100%;
        }

        .table-container {
            overflow-x: auto;
            max-height: 500px;
        }

        table {
            min-width: 700px;
        }

        th { font-size: 12px; }
        td { font-size: 11px; padding: 8px; }

        .edit-btn {
            padding: 4px 6px;
            font-size: 11px;
        }

        .pagination-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .footer-right {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            width: 100%;
        }

        .row-select-wrapper,
        .footer-filter {
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .pagination-controls {
            flex-wrap: wrap;
            gap: 4px;
        }
        .page-dots {
        min-width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #78909c;
}

        .select-col,
        .select-cell {
            width: 40px;
        }
    }
.search-container {
    position: relative;
    display: flex;
    align-items: center;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: #fff;
    border: 1px solid #ccc;
    border-top: none;
    border-radius: 0 0 10px 10px;
    max-height: 220px;
    overflow-y: auto;
    z-index: 9999;
    display: none;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

.search-suggestion-item {
    padding: 10px 12px;
    cursor: pointer;
    font-size: 13px;
    border-bottom: 1px solid #eee;
    background: #fff;
}

.search-suggestion-item:hover {
    background: #f0f7ff;
}

.search-suggestion-item:last-child {
    border-bottom: none;
}
</style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-brand">
        <p class="brand-top">Department of Education</p>
        <h1 class="brand-title">CITY OF SAN JOSE DEL MONTE</h1>
        <p class="brand-subtitle">Certificate Verifier System - CERVER</p>
    </div>

    <div class="burger" id="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="nav-links" id="nav-menu">
        <a href="logout.php"><button class="logout">Log out</button></a>
    </div>
</nav>

<main class="main-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Admin Dashboard</h2>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="../files/template.xlsx" download class="dbutton">Download Template</a>
            <button id="uploadBtn" class="upload-btn">Upload</button>

            <div class="delete-actions">
                <button type="button" id="selectBtn" class="delete-toggle-btn" onclick="toggleDeleteMode()">Select</button>
                <button type="button" id="deleteSelectedBtn" class="delete-selected-btn" onclick="deleteSelected()">Delete Selected</button>
                <button type="button" id="cancelDeleteBtn" class="cancel-delete-btn" onclick="cancelDeleteMode()">Cancel</button>
            </div>

            <div class="search-container">
                <input type="text"
                       id="certificateSearch"
                       placeholder="Search"
                       value="<?= htmlspecialchars($search) ?>"
                        oninput="liveSearchAjax()"
                       autocomplete="off">
                <span class="search-icon">🔍</span>
                <div id="searchSuggestions" class="search-suggestions"></div>
            </div>
        </div>
    </div>

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
                        <p class="<?= strpos($msg, 'Inserted') !== false || strpos($msg, '✅') !== false ? 'success' : 'error' ?>">
                            <?= htmlspecialchars($msg) ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

   <div class="pagination-footer">
    <div class="footer-left">
        <?php
        if ($totalRows > 0) {
            $start = $offset + 1;
            $end = min($offset + $rowsPerPage, $totalRows);
        } else {
            $start = 0;
            $end = 0;
        }
        ?>
        Showing <b><?= $start ?></b> to <b><?= $end ?></b> of <b><?= $totalRows ?></b> teachers
    </div>

    <div class="footer-right">
        <div class="row-select-wrapper">
            Row per page:
            <select onchange="updateTableControls({ rows: this.value })">
                <option value="100" <?= $rowsPerPage == 100 ? 'selected' : '' ?>>100</option>
                <option value="50" <?= $rowsPerPage == 50 ? 'selected' : '' ?>>50</option>
                <option value="30" <?= $rowsPerPage == 30 ? 'selected' : '' ?>>30</option>
                <option value="20" <?= $rowsPerPage == 20 ? 'selected' : '' ?>>20</option>
                <option value="10" <?= $rowsPerPage == 10 ? 'selected' : '' ?>>10</option>
            </select>
        </div>

        <div class="footer-filter">
            <span>Control #:</span>
            <select onchange="sortCurrentPage(this.value)">
                <option value="control_new">New</option>
                <option value="control_old">Old</option>
            </select>
        </div>

        <div class="footer-filter">
            <span>Name:</span>
            <select onchange="updateTableControls({ letter: this.value, search: '' })">
                <option value="">All</option>
                <?php foreach (range('A', 'Z') as $ltr): ?>
                    <option value="<?= $ltr ?>" <?= (isset($_GET['letter']) && $_GET['letter'] == $ltr) ? 'selected' : '' ?>>
                        <?= $ltr ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="pagination-controls">
            <a class="page-arrow" href="?page=1&rows=<?= $rowsPerPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&letter=<?= urlencode($letter) ?>">«</a>

            <a class="page-arrow" href="?page=<?= max(1, $page - 1) ?>&rows=<?= $rowsPerPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&letter=<?= urlencode($letter) ?>">❮</a>

            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);

            if ($startPage > 1): ?>
                <a class="page-num" href="?page=1&rows=<?= $rowsPerPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&letter=<?= urlencode($letter) ?>">1</a>
                <?php if ($startPage > 2): ?>
                    <span class="page-dots">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a class="page-num <?= $i == $page ? 'active' : '' ?>"
                   href="?page=<?= $i ?>&rows=<?= $rowsPerPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&letter=<?= urlencode($letter) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span class="page-dots">...</span>
                <?php endif; ?>
                <a class="page-num" href="?page=<?= $totalPages ?>&rows=<?= $rowsPerPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&letter=<?= urlencode($letter) ?>">
                    <?= $totalPages ?>
                </a>
            <?php endif; ?>

            <a class="page-arrow" href="?page=<?= min($totalPages, $page + 1) ?>&rows=<?= $rowsPerPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&letter=<?= urlencode($letter) ?>">❯</a>

            <a class="page-arrow" href="?page=<?= $totalPages ?>&rows=<?= $rowsPerPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&letter=<?= urlencode($letter) ?>">»</a>
        </div>
    </div>
</div>

  <?php if ($result && $result->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="select-col">
                        <input type="checkbox" id="selectAllRows" onclick="toggleSelectAll(this)">
                    </th>
                    <th class="no-col">No.</th>
                    <th class="control-col">Control Number</th>
                    <th class="name-col">Name</th>
                    <th class="seminar-col">Seminar/Workshop Attended</th>
                    <th class="action-col">Action</th>
                </tr>
            </thead>

            <tbody id="tableBody">
                <?php $rowNumber = $offset + 1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="select-cell">
                            <input type="checkbox" class="row-checkbox" value="<?= $row['cert_id'] ?>">
                        </td>
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
                                   value="<?= htmlspecialchars(
                                       $row['temp_name']
                                       ?? $row['user_name']
                                       ?? (isset($row['display_email']) ? explode('@', $row['display_email'])[0] : '')
                                   ) ?>"
                                   style="display:none; width:120px;">
                        </td>
                        <td class="seminar-cell" onclick="toggleSeminar(this)">
                            <?= htmlspecialchars($row['seminar_title']) ?>
                        </td>
                        <td>
                            <button class="edit-btn" onclick="editName(this, <?= $row['cert_id'] ?>)">Edit</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="select-col">
                        <input type="checkbox" id="selectAllRows" onclick="toggleSelectAll(this)">
                    </th>
                    <th class="no-col">No.</th>
                    <th class="control-col">Control Number</th>
                    <th class="name-col">Name</th>
                    <th class="seminar-col">Seminar/Workshop Attended</th>
                    <th class="action-col">Action</th>
                </tr>
            </thead>

            <tbody id="tableBody">
                <tr>
                    <td colspan="6">No certificates found.</td>
                </tr>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</main>

<script>
    const burger = document.getElementById('burger');
    const navMenu = document.getElementById('nav-menu');

    if (burger) {
        burger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            burger.classList.toggle('toggle');
        });
    }

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            burger.classList.remove('toggle');
        });
    });

    const modal = document.getElementById("uploadModal");
    const uploadBtn = document.getElementById("uploadBtn");
    const spanClose = document.getElementsByClassName("close")[0];

    if (uploadBtn) {
        uploadBtn.onclick = () => modal.style.display = "block";
    }

    if (spanClose) {
        spanClose.onclick = () => modal.style.display = "none";
    }

    window.onclick = e => {
        if (e.target == modal) modal.style.display = "none";
    };

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

    const form = document.getElementById('csvForm');
    const loadingText = document.getElementById('loadingText');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (loadingText) {
                loadingText.style.display = 'inline';
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Uploading...';
            }

            const formData = new FormData(form);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const htmlResponse = await response.text();
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlResponse;

                const messagesHtml = tempDiv.querySelector('.messages');

                let messagesDiv = document.querySelector('.messages');
                if (!messagesDiv) {
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
                    messagesDiv.innerHTML = '<p class="success">✅ Upload completed. Please refresh the page to see changes.</p>';
                }

                setTimeout(() => {
                    location.reload();
                }, 2000);

            } catch (error) {
                console.error('Upload error:', error);

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
                if (loadingText) {
                    loadingText.style.display = 'none';
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Upload CSV';
                }
            }
        });
    }

    function toggleSeminar(cell) {
        cell.classList.toggle('expanded');
    }

    function updateTableControls(params) {
        const url = new URL(window.location.href);

        url.searchParams.forEach((value, key) => {
            if (!params.hasOwnProperty(key)) {
                params[key] = value;
            }
        });

        Object.keys(params).forEach(key => {
            url.searchParams.set(key, params[key]);
        });

        window.location.href = url.toString();
    }

    function toggleDeleteMode() {
        const tableContainer = document.querySelector('.table-container');
        const selectBtn = document.getElementById('selectBtn');
        const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const selectAll = document.getElementById('selectAllRows');

        if (!tableContainer || !selectBtn || !deleteSelectedBtn || !cancelDeleteBtn) return;

        tableContainer.classList.add('delete-mode');

        selectBtn.style.display = 'none';
        deleteSelectedBtn.style.display = 'inline-block';
        cancelDeleteBtn.style.display = 'inline-block';

        if (selectAll) selectAll.checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    }

    function cancelDeleteMode() {
        const tableContainer = document.querySelector('.table-container');
        const selectBtn = document.getElementById('selectBtn');
        const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const selectAll = document.getElementById('selectAllRows');

        if (!tableContainer || !selectBtn || !deleteSelectedBtn || !cancelDeleteBtn) return;

        tableContainer.classList.remove('delete-mode');

        selectBtn.style.display = 'inline-block';
        deleteSelectedBtn.style.display = 'none';
        cancelDeleteBtn.style.display = 'none';

        if (selectAll) selectAll.checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    }

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }

    function deleteSelected() {
        const checked = document.querySelectorAll('.row-checkbox:checked');

        if (checked.length === 0) {
            alert('Please select at least one row to delete.');
            return;
        }

        if (!confirm('Are you sure you want to delete the selected record(s)?')) {
            return;
        }

        const formData = new FormData();
        formData.append('delete_selected', '1');

        checked.forEach(cb => {
            formData.append('selected_ids[]', cb.value);
        });

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            location.reload();
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('Delete failed.');
        });
    }

    function handleSearch(event) {
        if (event.key === 'Enter') {
            const searchValue = document.getElementById('certificateSearch').value.trim();
            const url = new URL(window.location.href);

            if (searchValue !== '') {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }

            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }
    }
function sortCurrentPage(order) {
    const table = document.querySelector(".table-container table");
    if (!table) return;

    const tbodyRows = Array.from(table.querySelectorAll("tr")).slice(1); // skip header

    tbodyRows.sort((a, b) => {
        const aVal = a.children[2].innerText.trim(); // Control Number column
        const bVal = b.children[2].innerText.trim();

        return order === "control_new"
            ? bVal.localeCompare(aVal, undefined, { numeric: true })
            : aVal.localeCompare(bVal, undefined, { numeric: true });
    });

    tbodyRows.forEach(row => table.appendChild(row));
}
async function showSuggestions() {
    const input = document.getElementById('certificateSearch');
    const box = document.getElementById('searchSuggestions');
    const query = input.value.trim();

    if (query.length === 0) {
        box.style.display = 'none';
        box.innerHTML = '';
        return;
    }

    try {
        const response = await fetch(`?ajax_search=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (!data.length) {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }

        box.innerHTML = data.map(item => `
            <div class="search-suggestion-item"
                 onclick="selectSuggestion('${String(item.control_number).replace(/'/g, "\\'")}')">
                <strong>${item.control_number}</strong><br>
                <small>${item.display_name} - ${item.seminar_title}</small>
            </div>
        `).join('');

        box.style.display = 'block';
    } catch (error) {
        console.error('Suggestion fetch error:', error);
        box.style.display = 'none';
    }
}

function selectSuggestion(value) {
    const input = document.getElementById('certificateSearch');
    const box = document.getElementById('searchSuggestions');
    const url = new URL(window.location.href);

    input.value = value;
    box.style.display = 'none';

    url.searchParams.set('search', value);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

document.addEventListener('click', function(e) {
    const input = document.getElementById('certificateSearch');
    const box = document.getElementById('searchSuggestions');

    if (!input || !box) return;

    if (!input.contains(e.target) && !box.contains(e.target)) {
        box.style.display = 'none';
    }
});
let searchTimeout;

async function liveSearchAjax() {
    const input = document.getElementById('certificateSearch');
    const tableBody = document.getElementById('tableBody');

    if (!input || !tableBody) return;

    const value = input.value.trim();

    // pag empty, ibalik ang normal page para makita lahat ng data ulit
    if (value === '') {
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
        return;
    }

    try {
        const response = await fetch(`?ajax_live_search=${encodeURIComponent(value)}`);
        const html = await response.text();
        tableBody.innerHTML = html;
    } catch (error) {
        console.error('Live search error:', error);
    }
}
</script>

</body>
</html>