<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.html");
    exit;
}

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    echo "<p>Invalid user ID. <a href='../admin.html'>Back to Admin Dashboard</a></p>";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, full_name, email, phone, address, role, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    echo "<p>User not found. <a href='../admin.html'>Back to Admin Dashboard</a></p>";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM relief_requests WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$requests_result = mysqli_stmt_get_result($stmt);
$requests = [];
while ($row = mysqli_fetch_assoc($requests_result)) {
    $requests[] = $row;
}
mysqli_stmt_close($stmt);

$total_requests = count($requests);
$severity_counts = ['High' => 0, 'Medium' => 0, 'Low' => 0];
$type_counts = ['Food' => 0, 'Water' => 0, 'Medicine' => 0, 'Shelter' => 0];

foreach ($requests as $r) {
    if (isset($severity_counts[$r['severity']])) {
        $severity_counts[$r['severity']]++;
    }
    if (isset($type_counts[$r['relief_type']])) {
        $type_counts[$r['relief_type']]++;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User Report - Detailed information for <?php echo htmlspecialchars($user['full_name']); ?>">
    <title>User Report - <?php echo htmlspecialchars($user['full_name']); ?> - Flood Relief System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="brand">Flood Relief System <span style="font-size: 0.75rem; opacity: 0.7; margin-left: 4px;">ADMIN</span>
        </div>
        <ul class="nav-links">
            <li><a href="../admin.html">← Back to Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="report-page">
        <a href="../admin.html" class="report-back-link">← Back to Admin Dashboard</a>

        <div class="report-header">
            <h1>📋 User Summary Report</h1>
            <p>Detailed information for registered user</p>
        </div>

        <div class="report-body">

            <div class="report-section">
                <h2>👤 User Profile</h2>
                <div class="report-info-grid">
                    <div class="report-info-item">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                    </div>
                    <div class="report-info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="report-info-item">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                    </div>
                    <div class="report-info-item">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['address']); ?></span>
                    </div>
                    <div class="report-info-item">
                        <span class="info-label">Role:</span>
                        <span class="info-value" style="text-transform: capitalize;"><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                    <div class="report-info-item">
                        <span class="info-label">Registered On:</span>
                        <span class="info-value"><?php echo date('F j, Y, g:i A', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="report-section">
                <h2>📊 Summary Statistics</h2>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="label">Total Relief Requests</div>
                        <div class="value"><?php echo $total_requests; ?></div>
                    </div>
                    <div class="summary-item danger">
                        <div class="label">High Severity Requests</div>
                        <div class="value"><?php echo $severity_counts['High']; ?></div>
                    </div>
                    <div class="summary-item warning">
                        <div class="label">Medium Severity Requests</div>
                        <div class="value"><?php echo $severity_counts['Medium']; ?></div>
                    </div>
                    <div class="summary-item success">
                        <div class="label">Low Severity Requests</div>
                        <div class="value"><?php echo $severity_counts['Low']; ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Food Requests</div>
                        <div class="value"><?php echo $type_counts['Food']; ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Water Requests</div>
                        <div class="value"><?php echo $type_counts['Water']; ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Medicine Requests</div>
                        <div class="value"><?php echo $type_counts['Medicine']; ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Shelter Requests</div>
                        <div class="value"><?php echo $type_counts['Shelter']; ?></div>
                    </div>
                </div>
            </div>

            <div class="report-section">
                <h2>📝 Relief Requests</h2>
                <?php if (count($requests) === 0): ?>
                    <div class="empty-state">
                        <div class="icon">📋</div>
                        <p>This user has not submitted any relief requests.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>District</th>
                                    <th>Div. Secretariat</th>
                                    <th>GN Division</th>
                                    <th>Severity</th>
                                    <th>Family Members</th>
                                    <th>Contact Person</th>
                                    <th>Contact Number</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $index => $r): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($r['relief_type']); ?>"><?php echo htmlspecialchars($r['relief_type']); ?></span></td>
                                    <td><?php echo htmlspecialchars($r['district']); ?></td>
                                    <td><?php echo htmlspecialchars($r['divisional_secretariat']); ?></td>
                                    <td><?php echo htmlspecialchars($r['gn_division']); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($r['severity']); ?>"><?php echo htmlspecialchars($r['severity']); ?></span></td>
                                    <td><?php echo intval($r['family_members']); ?></td>
                                    <td><?php echo htmlspecialchars($r['contact_person']); ?></td>
                                    <td><?php echo htmlspecialchars($r['contact_number']); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($r['created_at'])); ?></td>
                                </tr>
                                <?php if (!empty($r['description'])): ?>
                                <tr>
                                    <td></td>
                                    <td colspan="10" style="font-size: 0.85rem; color: #64748b; padding-top: 0;">
                                        <strong>Description:</strong> <?php echo htmlspecialchars($r['description']); ?><br>
                                        <strong>Address:</strong> <?php echo htmlspecialchars($r['address']); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; padding-top: 24px; border-top: 2px solid #e2e8f0; color: #64748b; font-size: 0.85rem;">
                <p>Report generated on <?php echo date('F j, Y, g:i A'); ?></p>
                <p>Flood Relief Management System</p>
            </div>
        </div>
    </div>
</body>
</html>
