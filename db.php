<?php
// db.php for Render (PostgreSQL)

// Get database credentials from Render's environment variables
// The getenv() function retrieves the VALUE of the variable, not the name itself.
$host = getenv('PGHOST');
$dbname = getenv('PGDATABASE');
$user = getenv('PGUSER');
$pass = getenv('PGPASSWORD');

try {
    // Create a new PDO instance using the PostgreSQL DSN
    // Use the retrieved variables to build the connection string.
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    
    // Set PDO to throw exceptions for errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, terminate script and display error
    die("Connection failed: " . $e->getMessage());
}
?>