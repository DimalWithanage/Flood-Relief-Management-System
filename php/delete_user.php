<?php
require_once 'db_connect.php';
requireAdmin();

$method = $_SERVER["REQUEST_METHOD"];
if ($method !== 'POST') {
    echo "Error: Invalid request method.";
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo "Error: Invalid JSON data";
    exit;
}

$user_id = intval($data['id'] ?? 0);
if ($user_id <= 0) {
    echo "Error: Invalid user ID.";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT role FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "Error: User not found.";
    exit;
}

$user = mysqli_fetch_assoc($result);
if ($user['role'] === 'admin') {
    echo "Error: Cannot delete admin accounts.";
    exit;
}
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);

if (mysqli_stmt_execute($stmt)) {
    echo "Success: User deleted successfully.";
} else {
    echo "Error: Failed to delete user.";
}

mysqli_stmt_close($stmt);
?>
