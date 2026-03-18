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
    echo "Error: You can only update your own requests.";
    exit;
}
mysqli_stmt_close($stmt);

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

$stmt = mysqli_prepare($conn, "UPDATE relief_requests SET relief_type=?, district=?, divisional_secretariat=?, gn_division=?, contact_person=?, contact_number=?, address=?, family_members=?, severity=?, description=? WHERE id=?");
mysqli_stmt_bind_param($stmt, "sssssssissi", $relief_type, $district, $divisional_secretariat, $gn_division, $contact_person, $contact_number, $address, $family_members, $severity, $description, $request_id);

if (mysqli_stmt_execute($stmt)) {
    echo "Success: Relief request updated successfully.";
} else {
    echo "Error: Failed to update request.";
}

mysqli_stmt_close($stmt);
?>
