<?php
require_once 'db_connect.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo "Error: Invalid request method.";
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$requests = [];

if ($role === 'admin') {
    $sql = "SELECT r.*, u.full_name as user_name, u.email as user_email FROM relief_requests r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM relief_requests WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
    mysqli_stmt_close($stmt);
}

header('Content-Type: application/json');
echo json_encode(["success" => true, "message" => "Requests fetched.", "data" => $requests]);
?>
