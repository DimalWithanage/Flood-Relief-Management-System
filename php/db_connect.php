<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flood_relief_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Error: Database connection failed: " . mysqli_connect_error());
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        echo "Error: Please login first.";
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        echo "Error: Access denied. Admin only.";
        exit;
    }
}
?>
