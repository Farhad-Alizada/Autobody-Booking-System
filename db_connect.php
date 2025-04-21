<?php
// db_connect.php

// Database configuration
$host     = 'localhost';
$dbname   = 'autobody_db';
$username = 'root';         // Change if your MySQL username is different
$password = '';             // Change if you have a MySQL password

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // Create a PDO instance (connect to the database)
    $pdo = new PDO($dsn, $username, $password);
    
    // Set PDO error mode to exception to catch errors effectively
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Enable persistent connections if desired (be cautious)
    // $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);

    // Connection successful
    // Uncomment the line below for debugging purposes if needed
    // echo "Connected successfully to the $dbname database.";
} catch (PDOException $e) {
    // Handle any connection errors
    die("Database Connection Failed: " . $e->getMessage());
}
?>
