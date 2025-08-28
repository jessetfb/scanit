<?php
// add-checkpoints.php
// Admin page to add checkpoints to a specific assignment.

session_start();
require_once 'db.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$assignment_id = $_GET['assignment_id'] ?? '';
$message = '';

// Fetch assignment name for display
$assignment = null;
if ($assignment_id) {
    $stmt = $pdo->prepare("SELECT id, name FROM assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch();
    if (!$assignment) {
        // Redirect if assignment not found
        header("Location: dashboard.php?error=assignment_not_found");
        exit;
    }
} else {
    // Redirect if no assignment ID provided
    header("Location: dashboard.php?error=no_assignment_id");
    exit;
}

// Handle form submission for adding new checkpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkpoint_name = trim($_POST['checkpoint_name'] ?? '');

    if ($checkpoint_name) {
        // Insert new checkpoint into the database
        $stmt = $pdo->prepare("INSERT INTO checkpoints (assignment_id, name) VALUES (?, ?)");
        $stmt->execute([$assignment_id, $checkpoint_name]);
        $message = "Checkpoint added successfully.";
        // Clear the input field after successful addition
        $_POST['checkpoint_name'] = ''; 
    } else {
        $message = "Please enter a checkpoint name.";
    }
}

// Fetch existing checkpoints for this assignment to display them
$stmt = $pdo->prepare("SELECT id, name FROM checkpoints WHERE assignment_id = ? ORDER BY name ASC");
$stmt->execute([$assignment_id]);
$checkpoints = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Checkpoints</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-3">Add Checkpoints to Assignment: <span class="text-primary"><?= htmlspecialchars($assignment['name']) ?></span></h2>
        <a href="manage_assignments.php" class="btn btn-secondary btn-sm mb-4">&larr; Back to Manage Assignments</a>
        <hr class="mb-4">

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="checkpoint_name" class="form-label">New Checkpoint Name:</label>
                <input type="text" name="checkpoint_name" id="checkpoint_name" placeholder="e.g., Gate, Kitchen, Main Office" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Checkpoint</button>
        </form>

        <h3 class="mb-3">Existing Checkpoints:</h3>
        <?php if (empty($checkpoints)): ?>
            <div class="alert alert-info" role="alert">
                No checkpoints added to this assignment yet.
            </div>
        <?php else: ?>
            <ul class="list-group mb-4">
                <?php foreach ($checkpoints as $cp): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($cp['name']) ?>
                        </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="generate-qrs.php?assignment_id=<?= $assignment['id'] ?>"
               class="btn btn-success btn-lg">
                <i class="fas fa-qrcode me-2"></i> Done? Generate QR Codes
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>
</html>

