<?php
// db.php for Render (PostgreSQL)

// Get database credentials from Render's environment variables
$host = getenv('PGHOST');
$dbname = getenv('PGDATABASE');
$user = getenv('PGUSER');
$pass = getenv('PGPASSWORD');
$port = getenv('PGPORT') ?: 5432; // default to 5432 if not set

try {
    // Create DSN string with host, port, and dbname
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass);

    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
