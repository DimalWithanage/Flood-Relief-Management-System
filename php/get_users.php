<?php
require_once 'db_connect.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo "Error: Invalid request method.";
    exit;
}

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, phone, address, role, created_at FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT * FROM relief_requests WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $requests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
    mysqli_stmt_close($stmt);

    $user['requests'] = $requests;
    echo json_encode(["success" => true, "message" => "User details fetched.", "data" => $user]);
    exit;
}

$sql = "SELECT id, full_name, email, phone, address, role, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode(["success" => true, "message" => "Users fetched.", "data" => $users]);
?>
