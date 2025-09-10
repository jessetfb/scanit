<?php
// delete_assignment.php
// Admin script to delete an assignment and its associated data.

session_start();
require_once 'db.php';

// Only admin can access this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment_id'])) {
    $assignment_id = (int)$_POST['assignment_id'];

    if ($assignment_id > 0) {
        try {
            // Start transaction for safety
            $pdo->beginTransaction();

            // First, find all checkpoint IDs for this assignment
            $stmt = $pdo->prepare("SELECT id FROM checkpoints WHERE assignment_id = ?");
            $stmt->execute([$assignment_id]);
            $checkpoint_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // If there are checkpoints, delete their related scan_logs first
            if (!empty($checkpoint_ids)) {
                $placeholders = implode(',', array_fill(0, count($checkpoint_ids), '?'));
                $sql_delete_scan_logs = "DELETE FROM scan_logs WHERE checkpoint_id IN ($placeholders)";
                $stmt = $pdo->prepare($sql_delete_scan_logs);
                $stmt->execute($checkpoint_ids);
            }

            // Delete related checkpoints
            $stmt = $pdo->prepare("DELETE FROM checkpoints WHERE assignment_id = ?");
            $stmt->execute([$assignment_id]);

            // Delete related user assignments
            $stmt = $pdo->prepare("DELETE FROM user_assignments WHERE assignment_id = ?");
            $stmt->execute([$assignment_id]);

            // Update logs to set assignment_id to NULL
            $stmt = $pdo->prepare("UPDATE logs SET assignment_id = NULL WHERE assignment_id = ?");
            $stmt->execute([$assignment_id]);

            // Finally delete the assignment itself
            $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
            $stmt->execute([$assignment_id]);

            $pdo->commit();

            if ($stmt->rowCount() > 0) {
                $message = "Assignment and its associated data deleted successfully.";
                $message_type = 'success';
            } else {
                $message = "Assignment not found or could not be deleted.";
                $message_type = 'danger';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Database error: " . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = "Invalid assignment ID provided.";
        $message_type = 'danger';
    }
} else {
    $message = "Invalid request to delete assignment.";
    $message_type = 'warning';
}

// Store message in session for display on the redirected page
$_SESSION['flash_message'] = $message;
$_SESSION['flash_message_type'] = $message_type;

// Redirect back to the manage assignments page
header("Location: manage_assignments.php");
exit;
?>