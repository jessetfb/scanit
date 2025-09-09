<?php
// manage_assignments.php

session_start();
require_once 'db.php';

// Initialize flash message
$flash_message = null;

// Handle delete action
if (isset($_GET['delete'])) {
    $assignment_id = (int) $_GET['delete'];

    try {
        // Delete assignment
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
        $stmt->execute([$assignment_id]);

        if ($stmt->rowCount() > 0) {
            $flash_message = [
                "type" => "success",
                "text" => "Assignment deleted successfully."
            ];
        } else {
            $flash_message = [
                "type" => "danger",
                "text" => "Assignment not found."
            ];
        }

    } catch (PDOException $e) {
        // Catch foreign key violation
        if ($e->getCode() == "23503") {
            $flash_message = [
                "type" => "danger",
                "text" => "Cannot delete assignment: it has linked records (users, logs, or checkpoints)."
            ];
        } else {
            $flash_message = [
                "type" => "danger",
                "text" => "Error deleting assignment: " . $e->getMessage()
            ];
        }
    }
}

// Fetch all assignments
$stmt = $pdo->query("SELECT * FROM assignments ORDER BY id DESC");
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

    <h2>Assignments</h2>

    <?php if ($flash_message): ?>
        <div class="alert alert-<?= htmlspecialchars($flash_message['type']) ?>">
            <?= htmlspecialchars($flash_message['text']) ?>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($assignments): ?>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?= htmlspecialchars($assignment['id']) ?></td>
                        <td><?= htmlspecialchars($assignment['name']) ?></td>
                        <td><?= htmlspecialchars($assignment['created_at']) ?></td>
                        <td>
                            <a href="manage_assignments.php?delete=<?= $assignment['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this assignment?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No assignments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
