<?php
session_start();

// Clear all session variables
$_SESSION = [];

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

// 🚫 Do NOT regenerate ID here
// session_regenerate_id(true); <-- remove this line

// Redirect to login
header("Location: https://scanit-fnex.onrender.com/login.php");
exit;

