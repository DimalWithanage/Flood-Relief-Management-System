<?php
require_once 'db_connect.php';

$method = $_SERVER["REQUEST_METHOD"];

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo "Error: Invalid JSON data";
        exit;
    }

    $full_name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');

    if (empty($full_name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        echo "Error: All fields are required";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Error: Invalid email format";
        exit;
    }

    if (strlen($password) < 6) {
        echo "Error: Password must be at least 6 characters";
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        echo "Error: Email already registered";
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'user')");
    mysqli_stmt_bind_param($stmt, "sssss", $full_name, $email, $password, $phone, $address);

    if (mysqli_stmt_execute($stmt)) {
        echo "Success: Registration successful! Please login.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>
