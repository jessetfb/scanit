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
            // Delete the assignment. Due to FOREIGN KEY constraints with ON DELETE CASCADE
            // on 'checkpoints' and 'user_assignments', related records in those tables
            // will be automatically deleted.
            // For 'logs', the assignment_id will be set to NULL due to ON DELETE SET NULL.
            $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
            $stmt->execute([$assignment_id]);

            if ($stmt->rowCount() > 0) {
                $message = "Assignment and its associated data deleted successfully.";
                $message_type = 'success';
            } else {
                $message = "Assignment not found or could not be deleted.";
                $message_type = 'danger';
            }
        } catch (PDOException $e) {
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
