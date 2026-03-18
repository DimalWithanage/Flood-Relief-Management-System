<?php
require_once 'db_connect.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo "Error: Invalid request method.";
    exit;
}

$area = trim($_GET['area'] ?? '');
$relief_type = trim($_GET['relief_type'] ?? '');

$where_conditions = [];
$params = [];
$types = "";

if (!empty($area)) {
    $where_conditions[] = "(r.district LIKE ? OR r.divisional_secretariat LIKE ? OR r.gn_division LIKE ? OR r.address LIKE ?)";
    $area_param = "%" . $area . "%";
    $params[] = $area_param;
    $params[] = $area_param;
    $params[] = $area_param;
    $params[] = $area_param;
    $types .= "ssss";
}

if (!empty($relief_type)) {
    $where_conditions[] = "r.relief_type = ?";
    $params[] = $relief_type;
    $types .= "s";
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

$user_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
$user_params = [];
$user_types = "";

if (!empty($area)) {
    $user_sql .= " AND address LIKE ?";
    $user_params[] = "%" . $area . "%";
    $user_types .= "s";
}

if (!empty($user_params)) {
    $stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($stmt, $user_types, ...$user_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_users = mysqli_fetch_assoc($result)['count'];
    mysqli_stmt_close($stmt);
} else {
    $total_users_result = mysqli_query($conn, $user_sql);
    $total_users = mysqli_fetch_assoc($total_users_result)['count'];
}

$type_counts = [];
$relief_types_list = ['Food', 'Water', 'Medicine', 'Shelter'];

foreach ($relief_types_list as $type) {
    $type_where = $where_conditions;
    $type_params = $params;
    $type_types = $types;

    $type_where[] = "r.relief_type = ?";
    $type_params[] = $type;
    $type_types .= "s";

    $type_where_sql = "WHERE " . implode(" AND ", $type_where);
    $sql = "SELECT COUNT(*) as count FROM relief_requests r $type_where_sql";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($type_params)) {
        mysqli_stmt_bind_param($stmt, $type_types, ...$type_params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $type_counts[$type] = mysqli_fetch_assoc($result)['count'];
    mysqli_stmt_close($stmt);
}

$sql = "SELECT COUNT(*) as count FROM relief_requests r $where_sql";
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_requests = mysqli_fetch_assoc($result)['count'];
    mysqli_stmt_close($stmt);
} else {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM relief_requests");
    $total_requests = mysqli_fetch_assoc($result)['count'];
}

$severity_counts = [];
foreach (['Low', 'Medium', 'High'] as $sev) {
    $sev_where = $where_conditions;
    $sev_params = $params;
    $sev_types = $types;
    $sev_where[] = "r.severity = ?";
    $sev_params[] = $sev;
    $sev_types .= "s";
    $sev_where_sql = "WHERE " . implode(" AND ", $sev_where);
    $sql = "SELECT COUNT(*) as count FROM relief_requests r $sev_where_sql";
    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($sev_params)) {
        mysqli_stmt_bind_param($stmt, $sev_types, ...$sev_params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $severity_counts[$sev] = mysqli_fetch_assoc($result)['count'];
    mysqli_stmt_close($stmt);
}

$report = [
    "Total Registered Users" => intval($total_users),
    "Total Relief Requests" => intval($total_requests),
    "High Severity Households" => intval($severity_counts['High']),
    "Medium Severity Households" => intval($severity_counts['Medium']),
    "Low Severity Households" => intval($severity_counts['Low']),
    "Food Requests" => intval($type_counts['Food']),
    "Water Requests" => intval($type_counts['Water']),
    "Medicine Requests" => intval($type_counts['Medicine']),
    "Shelter Requests" => intval($type_counts['Shelter'])
];

header('Content-Type: application/json');
echo json_encode(["success" => true, "message" => "Report generated.", "data" => $report]);
?>
