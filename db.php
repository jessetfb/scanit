<?php
// db.php
// This file establishes a PDO database connection.

$host = "localhost";
$dbname = "scanit_db";
$user = "root";
$pass = "";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    
    // Set PDO to throw exceptions for errors, which helps in debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, terminate script and display error
    die("Connection failed: " . $e->getMessage());
}
?>