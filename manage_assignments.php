<?php
// manage_assignments.php
// Admin page to view all assignments and link to their checkpoints, and now delete them.

require_once 'check_auth.php';
require_once 'db.php';

// Ensure only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Fetch all assignments
$stmt = $pdo->query("SELECT id, name FROM assignments ORDER BY name ASC");
$assignments = $stmt->fetchAll();

// Handle flash messages from redirects
$flash_message = $_SESSION['flash_message'] ?? '';
$flash_message_type = $_SESSION['flash_message_type'] ?? '';
unset($_SESSION['flash_message']); // Clear message after displaying
unset($_SESSION['flash_message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
        }
        .card-link .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .card-link .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Manage Assignments</h2>
        <a href="dashboard.php" class="btn btn-secondary mb-4">&larr; Back to Dashboard</a>
        <hr class="mb-4">

        <?php if ($flash_message): ?>
            <div class="alert alert-<?= $flash_message_type ?>" role="alert">
                <?= htmlspecialchars($flash_message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($assignments)): ?>
            <div class="alert alert-info" role="alert">
                No assignments created yet. <a href="create-assignment.php" class="alert-link">Create a new one.</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($assignments as $assignment): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-primary mb-3"><?= htmlspecialchars($assignment['name']) ?></h3>
                                <div class="d-grid gap-2">
                                    <a href="add-checkpoints.php?assignment_id=<?= $assignment['id'] ?>"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-map-marker-alt me-2"></i> Add/View Checkpoints
                                    </a>
                                    <a href="generate-qrs.php?assignment_id=<?= $assignment['id'] ?>"
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-qrcode me-2"></i> Generate QR Codes
                                    </a>
                                    <form action="delete_assignment.php" method="POST" onsubmit="return confirm('Are you sure you want to delete the assignment &quot;<?= htmlspecialchars($assignment['name']) ?>&quot;? This will also delete all its checkpoints and unassign all staff from it.');">
                                        <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="fas fa-trash-alt me-2"></i> Delete Assignment
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
