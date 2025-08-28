<?php
// create-assignment.php
// Admin page to create a new assignment.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';

// Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = ''; // Initialize message variable

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); // Get and trim assignment name

    if ($name) {
        // Prepare and execute SQL to insert new assignment
        $stmt = $pdo->prepare("INSERT INTO assignments (name) VALUES (?)");
        $stmt->execute([$name]);

// Get the ID of the newly inserted assignment
        $assignmentId = $pdo->lastInsertId();

        // --- NEW: Automatically create "Clock In" and "Clock Out" checkpoints ---
        $stmt_cp_in = $pdo->prepare("INSERT INTO checkpoints (assignment_id, name) VALUES (?, ?)");
        $stmt_cp_in->execute([$assignmentId, 'Clock In']);

        $stmt_cp_out = $pdo->prepare("INSERT INTO checkpoints (assignment_id, name) VALUES (?, ?)");
        $stmt_cp_out->execute([$assignmentId, 'Clock Out']);
        // --- END NEW ---

        // Redirect to add-checkpoints page for the new assignment
        // (You'll now see 'Clock In' and 'Clock Out' already listed)
        header("Location: add-checkpoints.php?assignment_id=$assignmentId");
        exit;
    } else {
        $message = "Assignment name is required."; // Error if name is empty
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .form-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center mb-4">Create New Assignment</h2>

        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Assignment Name:</label>
                <input type="text" name="name" id="name" placeholder="e.g., Babs Company Patrol" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create Assignment</button>
        </form>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'required') !== false ? 'alert-danger' : 'alert-success' ?> mt-4" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-link text-decoration-none">&larr; Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
