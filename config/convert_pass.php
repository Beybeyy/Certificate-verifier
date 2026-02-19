<?php
require_once __DIR__ . "/db.php";

$sql = "SELECT id, password FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($user = $result->fetch_assoc()) {
        $db_password = trim($user['password']);

        // Skip empty passwords (optional)
        if (empty($db_password)) continue;

        // Hash the password regardless of current state
        $hashed = password_hash($db_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();

        echo "User ID {$user['id']} password hashed successfully.<br>";
    }
    echo "<br>All passwords processed.";
} else {
    echo "No users found.";
}
?>
    