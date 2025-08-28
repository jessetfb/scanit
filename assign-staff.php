<?php
// assign-staff.php
// Admin page to assign staff users to specific assignments, and now unassign them.

session_start();
require_once 'db.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = ''; // Initialize message variable

// Handle form submission for assigning staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['unassign'])) { // Check if it's an assign request
    $staff_id = $_POST['staff_id'] ?? null;
    $assignment_id = $_POST['assignment_id'] ?? null;

    if ($staff_id && $assignment_id) {
        // Check if staff is already assigned to this assignment
        $stmt = $pdo->prepare("SELECT * FROM user_assignments WHERE user_id = ? AND assignment_id = ?");
        $stmt->execute([$staff_id, $assignment_id]);
        if ($stmt->fetch()) {
            $message = "This staff member is already assigned to this assignment.";
        } else {
            // Insert the new assignment record
            $stmt = $pdo->prepare("INSERT INTO user_assignments (user_id, assignment_id) VALUES (?, ?)");
            if ($stmt->execute([$staff_id, $assignment_id])) {
                $message = "Staff assigned to assignment successfully.";
            } else {
                $message = "Failed to assign staff. Please try again.";
            }
        }
    } else {
        $message = "Please select both staff and assignment.";
    }
}

// Handle flash messages from redirects (e.g., from unassign_staff.php)
$flash_message = $_SESSION['flash_message'] ?? '';
$flash_message_type = $_SESSION['flash_message_type'] ?? '';
unset($_SESSION['flash_message']); // Clear message after displaying
unset($_SESSION['flash_message_type']);

// Fetch all staff users for the dropdown
$staffs = $pdo->query("SELECT id, username FROM users WHERE role = 'staff' ORDER BY username ASC")->fetchAll();

// Fetch all assignments for the dropdown
$assignments = $pdo->query("SELECT id, name FROM assignments ORDER BY name ASC")->fetchAll();

// Fetch all current staff assignments for display
$current_assignments_stmt = $pdo->query("
    SELECT
        ua.id AS user_assignment_id,
        u.username,
        a.name AS assignment_name
    FROM user_assignments ua
    JOIN users u ON ua.user_id = u.id
    JOIN assignments a ON ua.assignment_id = a.id
    ORDER BY u.username, a.name ASC
");
$current_assignments = $current_assignments_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Staff to Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .form-container, .table-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            width: 100%;
            max-width: 700px; /* Increased max-width for better table display */
            margin: 2rem auto; /* Center with margin */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center mb-4">Assign Staff to Assignment</h2>
        <a href="dashboard.php" class="btn btn-secondary btn-sm mb-4">&larr; Back to Dashboard</a>
        <hr class="mb-4">

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <?php if ($flash_message): ?>
            <div class="alert alert-<?= $flash_message_type ?>" role="alert">
                <?= htmlspecialchars($flash_message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="staff_id" class="form-label">Select Staff:</label>
                <select name="staff_id" id="staff_id" class="form-select" required>
                    <option value="">-- Select Staff --</option>
                    <?php foreach ($staffs as $staff): ?>
                        <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="assignment_id" class="form-label">Select Assignment:</label>
                <select name="assignment_id" id="assignment_id" class="form-select" required>
                    <option value="">-- Select Assignment --</option>
                    <?php foreach ($assignments as $assignment): ?>
                        <option value="<?= $assignment['id'] ?>"><?= htmlspecialchars($assignment['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Assign Staff</button>
        </form>
    </div>

    <div class="table-container mt-4">
        <h3 class="mb-3">Current Staff Assignments</h3>
        <?php if (empty($current_assignments)): ?>
            <div class="alert alert-info" role="alert">
                No staff currently assigned to any patrols.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Staff Username</th>
                            <th>Assigned Patrol</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_assignments as $ua): ?>
                            <tr>
                                <td><?= htmlspecialchars($ua['username']) ?></td>
                                <td><?= htmlspecialchars($ua['assignment_name']) ?></td>
                                <td>
                                    <form action="unassign_staff.php" method="POST" onsubmit="return confirm('Are you sure you want to unassign &quot;<?= htmlspecialchars($ua['username']) ?>&quot; from &quot;<?= htmlspecialchars($ua['assignment_name']) ?>&quot;?');">
                                        <input type="hidden" name="user_assignment_id" value="<?= $ua['user_assignment_id'] ?>">
                                        <button type="submit" name="unassign" class="btn btn-danger btn-sm">
                                            <i class="fas fa-user-minus me-2"></i> Unassign
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
