<?php
// unassign_staff.php
// Admin script to unassign a staff member from a specific assignment.

session_start();
require_once 'db.php';

// Only admin can access this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_assignment_id'])) {
    $user_assignment_id = (int)$_POST['user_assignment_id'];

    if ($user_assignment_id > 0) {
        try {
            // Delete the specific user_assignment record
            $stmt = $pdo->prepare("DELETE FROM user_assignments WHERE id = ?");
            $stmt->execute([$user_assignment_id]);

            if ($stmt->rowCount() > 0) {
                $message = "Staff member successfully unassigned from patrol.";
                $message_type = 'success';
            } else {
                $message = "Assignment not found or could not be unassigned.";
                $message_type = 'danger';
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = "Invalid unassignment ID provided.";
        $message_type = 'danger';
    }
} else {
    $message = "Invalid request to unassign staff.";
    $message_type = 'warning';
}

// Store message in session for display on the redirected page
$_SESSION['flash_message'] = $message;
$_SESSION['flash_message_type'] = $message_type;

// Redirect back to the assign staff page
header("Location: assign-staff.php");
exit;
?>
