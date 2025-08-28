<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Delete the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session completely
session_destroy();

// Regenerate a fresh session ID
session_regenerate_id(true);

// Redirect to login
header("Location: http://localhost/scanit/login.php");
exit;
