<?php
// dashboard.php
// Main dashboard for both admin and staff users.

session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once 'db.php';
require_once 'check_auth.php'; // Ensures user is logged in and sets $_SESSION['user_id'], $_SESSION['role']

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';
$role = strtolower(trim($_SESSION['role']));
if ($role === 'administrator') $role = 'admin'; // normalize admin role


// Handle flash messages
$flash_message = $_SESSION['flash_message'] ?? '';
$flash_message_type = $_SESSION['flash_message_type'] ?? '';
unset($_SESSION['flash_message']); // Clear after displaying
unset($_SESSION['flash_message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ScanIt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="manifest" href="/scanit/manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ScanIt</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-white">Welcome, <?= htmlspecialchars($username) ?> (<?= htmlspecialchars(ucfirst($role)) ?>)</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Dashboard</h2>

        <?php if ($flash_message): ?>
            <div class="alert alert-<?= $flash_message_type ?>" role="alert">
                <?= htmlspecialchars($flash_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <h3 class="mb-3">Admin Panel</h3>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Create New Patrol</h5>
                            <p class="card-text">Set up new security assignments.</p>
                            <a href="create-assignment.php" class="btn btn-primary mt-auto">Go to Create Patrol</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tasks fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Manage Patrols</h5>
                            <p class="card-text">View, edit checkpoints, and generate QRs for existing patrols.</p>
                            <a href="manage_assignments.php" class="btn btn-info mt-auto">Go to Manage Patrols</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Manage Users</h5>
                            <p class="card-text">Add new staff or admin users to the system.</p>
                            <a href="add-user.php" class="btn btn-success mt-auto">Go to Manage Users</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-friends fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Assign Staff to Patrols</h5>
                            <p class="card-text">Link staff members to specific security patrols.</p>
                            <a href="assign-staff.php" class="btn btn-warning mt-auto">Go to Assign Staff</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-list fa-3x text-danger mb-3"></i>
                            <h5 class="card-title">View Activity Logs</h5>
                            <p class="card-text">Review all system activities and patrol logs.</p>
                            <a href="admin_logs.php" class="btn btn-danger mt-auto">Go to Activity Logs</a>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <h3 class="mb-3">Staff Dashboard</h3>

            <h4 class="mb-3">Your Assigned Patrols</h4>
            <div class="row row-cols-1 g-3">
                <?php
                    // Fetch assignments and their corresponding Clock In/Out checkpoint IDs
                    $stmt = $pdo->prepare("
                        SELECT
                            a.id AS assignment_id,
                            a.name AS assignment_name,
                            ci.id AS clock_in_checkpoint_id,
                            co.id AS clock_out_checkpoint_id
                        FROM assignments a
                        JOIN user_assignments ua ON ua.assignment_id = a.id
                        LEFT JOIN checkpoints ci ON ci.assignment_id = a.id AND ci.name = 'Clock In'
                        LEFT JOIN checkpoints co ON co.assignment_id = a.id AND co.name = 'Clock Out'
                        WHERE ua.user_id = ?
                        ORDER BY a.name
                    ");
                    $stmt->execute([$user_id]);
                    $assigned_patrols = $stmt->fetchAll();

                    if ($assigned_patrols) {
                        foreach ($assigned_patrols as $patrol) {
                            $assignment_id = $patrol['assignment_id'];
                            
                            // --- Determine current clock status for this specific assignment ---
                            $status_stmt = $pdo->prepare("
                                SELECT action FROM logs
                                WHERE user_id = ? AND assignment_id = ?
                                AND (action = 'Clock In' OR action = 'Clock Out')
                                ORDER BY timestamp DESC LIMIT 1
                            ");
                            $status_stmt->execute([$user_id, $assignment_id]);
                            $last_action = $status_stmt->fetchColumn();

                            $is_clocked_in = ($last_action === 'Clock In');
                            $status_color = $is_clocked_in ? 'text-success' : 'text-danger';
                            $status_label = $is_clocked_in ? 'Clocked In' : 'Clocked Out / Not Started';
                            // --- End status determination ---

                            echo '<div class="col">';
                            echo '<div class="card h-100 shadow-sm">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title text-primary">' . htmlspecialchars($patrol['assignment_name']) . '</h5>';
                            echo '<p class="card-text mb-3">Status: <span class="fw-bold ' . $status_color . '">' . $status_label . '</span></p>';
                            echo '<div class="d-grid gap-2">';
                            
                            // Start Patrol (Clock In) Button
                            $clock_in_disabled = $is_clocked_in ? 'disabled' : '';
                            if ($patrol['clock_in_checkpoint_id']) {
                                echo '<a href="scan.php?assignment_id=' . $patrol['assignment_id'] . '&action_type=clock_in&checkpoint_id=' . $patrol['clock_in_checkpoint_id'] . '" class="btn btn-success ' . $clock_in_disabled . '"><i class="fas fa-sign-in-alt me-2"></i> Start Patrol (Clock In)</a>';
                            } else {
                                echo '<button class="btn btn-success" disabled><i class="fas fa-sign-in-alt me-2"></i> Clock In QR Missing</button>';
                            }

                            // End Patrol (Clock Out) Button
                            $clock_out_disabled = !$is_clocked_in ? 'disabled' : '';
                            if ($patrol['clock_out_checkpoint_id']) {
                                echo '<a href="scan.php?assignment_id=' . $patrol['assignment_id'] . '&action_type=clock_out&checkpoint_id=' . $patrol['clock_out_checkpoint_id'] . '" class="btn btn-danger ' . $clock_out_disabled . '"><i class="fas fa-sign-out-alt me-2"></i> End Patrol (Clock Out)</a>';
                            } else {
                                echo '<button class="btn btn-danger" disabled><i class="fas fa-sign-out-alt me-2"></i> Clock Out QR Missing</button>';
                            }

                            // Scan Checkpoints Button
                            $scan_checkpoints_disabled = !$is_clocked_in ? 'disabled' : ''; // Only scan checkpoints if clocked in
                            echo '<a href="scan.php?assignment_id=' . $patrol['assignment_id'] . '&action_type=checkpoint_scan" class="btn btn-primary ' . $scan_checkpoints_disabled . '"><i class="fas fa-qrcode me-2"></i> Scan Checkpoints</a>';
                            
                            echo '</div>'; // end d-grid
                            echo '</div>'; // end card-body
                            echo '</div>'; // end card
                            echo '</div>'; // end col
                        }
                    } else {
                        echo '<div class="col">';
                        echo '<div class="alert alert-info" role="alert">';
                        echo 'No patrols assigned to you yet. Please contact your admin.';
                        echo '</div>';
                        echo '</div>';
                    }
                ?>
            </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
