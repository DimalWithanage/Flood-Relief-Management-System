<?php
require_once 'db_connect.php';
requireLogin();

$method = $_SERVER["REQUEST_METHOD"];
if ($method !== 'POST') {
    echo "Error: Invalid request method.";
    exit;
}

if ($_SESSION['role'] !== 'user') {
    echo "Error: Only affected persons can create relief requests.";
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo "Error: Invalid JSON data";
    exit;
}

$relief_type = trim($data['relief_type'] ?? '');
$district = trim($data['district'] ?? '');
$divisional_secretariat = trim($data['divisional_secretariat'] ?? '');
$gn_division = trim($data['gn_division'] ?? '');
$contact_person = trim($data['contact_person'] ?? '');
$contact_number = trim($data['contact_number'] ?? '');
$address = trim($data['address'] ?? '');
$family_members = intval($data['family_members'] ?? 0);
$severity = trim($data['severity'] ?? '');
$description = trim($data['description'] ?? '');

$required = [$relief_type, $district, $divisional_secretariat, $gn_division, $contact_person, $contact_number, $address, $severity];
foreach ($required as $field) {
    if (empty($field)) {
        echo "Error: All required fields must be filled.";
        exit;
    }
}

if ($family_members < 1) {
    echo "Error: Number of family members must be at least 1.";
    exit;
}

$valid_types = ['Food', 'Water', 'Medicine', 'Shelter'];
if (!in_array($relief_type, $valid_types)) {
    echo "Error: Invalid relief type.";
    exit;
}

$valid_severity = ['Low', 'Medium', 'High'];
if (!in_array($severity, $valid_severity)) {
    echo "Error: Invalid severity level.";
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "INSERT INTO relief_requests (user_id, relief_type, district, divisional_secretariat, gn_division, contact_person, contact_number, address, family_members, severity, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "isssssssiss", $user_id, $relief_type, $district, $divisional_secretariat, $gn_division, $contact_person, $contact_number, $address, $family_members, $severity, $description);

if (mysqli_stmt_execute($stmt)) {
    echo "Success: Your relief request has been created successfully.";
} else {
    echo "Error: Failed to create request. Please try again.";
}

mysqli_stmt_close($stmt);
?>
