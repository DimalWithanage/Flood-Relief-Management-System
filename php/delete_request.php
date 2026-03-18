<?php
require_once 'db_connect.php';
requireLogin();

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

$request_id = intval($data['id'] ?? 0);
if ($request_id <= 0) {
    echo "Error: Invalid request ID.";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT user_id FROM relief_requests WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $request_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "Error: Request not found.";
    exit;
}

$request = mysqli_fetch_assoc($result);
if ($_SESSION['role'] !== 'admin' && $request['user_id'] !== $_SESSION['user_id']) {
    echo "Error: You can only delete your own requests.";
    exit;
}
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "DELETE FROM relief_requests WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $request_id);

if (mysqli_stmt_execute($stmt)) {
    echo "Success: Relief request deleted successfully.";
} else {
    echo "Error: Failed to delete request.";
}

mysqli_stmt_close($stmt);
?>
