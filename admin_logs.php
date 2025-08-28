<?php
// admin_logs.php
// Admin page to view and filter all scan and clock-in/out logs.

session_start();
require_once 'db.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch filter values from GET
$selected_user = $_GET['user_id'] ?? '';
$selected_date = $_GET['date'] ?? '';

// Fetch all staff users for dropdown
$users_stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'staff' ORDER BY username ASC");
$users = $users_stmt->fetchAll();

// Prepare logs query
// Join with users table to get username.
// The 'location' column in 'logs' table will store checkpoint names or 'Clock In'/'Clock Out' for direct actions.
$query = "SELECT l.*, u.username FROM logs l JOIN users u ON l.user_id = u.id WHERE 1=1";
$params = [];

if ($selected_user) {
    $query .= " AND u.id = ?";
    $params[] = $selected_user;
}
if ($selected_date) {
    $query .= " AND DATE(l.timestamp) = ?";
    $params[] = $selected_date;
}

$query .= " ORDER BY l.timestamp DESC"; // Order by most recent first
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        /* Styles for the Print Button (Screen only) */
        .print-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 20px; /* Add some space below the button */
        }
        .print-button:hover {
            background-color: #0056b3;
        }

        /* --- Print-Specific Styles --- */
        @media print {
            body {
                margin: 0; /* Remove margins for printing */
                padding: 0;
                font-size: 10pt; /* Smaller font for print */
                color: black;
            }
            h2 {
                text-align: center;
                border-bottom: 1px solid #ccc;
                padding-bottom: 5px;
                margin-bottom: 10px;
                color: black;
            }
            .container {
                width: auto; /* Allow container to expand for print */
                max-width: none;
                padding: 0; /* Remove container padding for full width print */
                margin: 0; /* Remove container margins */
            }
            .mb-3, .mb-4, .btn, .form-label, .form-select, .form-control, .d-flex, .bg-light, .border, .rounded, .alert, .table-responsive {
                /* Hide or simplify elements not needed for print */
                display: none !important; /* Hide elements completely */
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 0; /* Remove margin for print */
                box-shadow: none; /* Remove shadow for print */
            }
            th, td {
                border: 0.5px solid #ccc; /* Lighter border for print */
                padding: 5px;
            }
            thead {
                display: table-header-group; /* Repeat table header on each page */
            }
            tr {
                page-break-inside: avoid; /* Try to keep table rows together */
                page-break-after: auto;
            }
            /* Override Bootstrap table styling that might be problematic in print */
            .table-striped tbody tr:nth-of-type(odd) {
                background-color: transparent !important; /* Remove striped background */
            }
            .table-striped tbody tr:nth-of-type(even) {
                background-color: #f2f2f2 !important; /* Light grey for readability */
            }
            .table-bordered th, .table-bordered td {
                border-color: #dee2e6 !important; /* Ensure borders are visible */
            }
            .table-dark {
                background-color: #e9ecef !important; /* Lighten dark background for print */
                color: black !important;
            }
            /* Make the table visible and remove its display:none */
            .table-responsive {
                display: block !important;
            }
            .table {
                display: table !important; /* Ensure the table is treated as a table for print */
            }
            .container h2 {
                 display: block !important; /* Show the main heading */
                 font-size: 14pt;
                 margin-top: 10mm; /* Add some top margin for the printed page */
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-3"><i class="fas fa-clipboard-list me-2"></i> Scan Logs</h2>
        <a href="dashboard.php" class="btn btn-secondary btn-sm mb-4 print-hide-element">&larr; Back to Dashboard</a>
        <hr class="mb-4 print-hide-element">

        <form method="GET" class="mb-4 p-3 bg-light border rounded print-hide-element">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="user_id" class="form-label">Filter by User:</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">-- All --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= $selected_user == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Filter by Date:</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?= htmlspecialchars($selected_date) ?>">
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                    <a href="admin_logs.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>

        <?php if ($logs): ?>
            <div class="d-flex justify-content-start mb-3 print-hide-element">
                <button class="print-button" onclick="window.print()">Print Logs</button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>User</th>
                            <th>Location/Checkpoint</th>
                            <th>Action</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['username']) ?></td>
                                <td><?= htmlspecialchars($log['location']) ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= htmlspecialchars($log['timestamp']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No logs found matching your criteria.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
