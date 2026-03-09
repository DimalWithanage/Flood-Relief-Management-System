<?php
require_once 'db_connect.php';

$method = $_SERVER["REQUEST_METHOD"];

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo "Error: Invalid JSON data";
        exit;
    }

    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo "Error: Email and password are required";
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, role, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        echo "Error: Invalid email or password";
        exit;
    }

    $user = mysqli_fetch_assoc($result);

    if ($password !== $user['password']) {
        echo "Error: Invalid email or password";
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    echo "Success: " . $user['role'];

    mysqli_stmt_close($stmt);
}
?>
