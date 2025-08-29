<?php
// check_auth.php
// This file is included at the top of pages that require a logged-in user.



// Check if the user_id session variable is not set
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit; // Stop script execution after redirection
}
?>