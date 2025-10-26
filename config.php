<!-- <?php
$servername = "localhost";
$username   = "root";   // default in XAMPP
$password   = "";       // default in XAMPP (blank)
$dbname     = "my_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> -->

<?php
// config.php - DB connection (safe, verbose)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Use 127.0.0.1 to avoid socket vs host ambiguities on Windows
$DB_HOST = '127.0.0.1';
$DB_PORT = 3306;
$DB_USER = 'root';
$DB_PASS = '';          // change if you set a root password
$DB_NAME = 'my_db';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn->connect_error) {
    // a clear failure message - shown only in dev environment
    error_log('DB connection failed: ' . $conn->connect_error);
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}

// optional: set charset
$conn->set_charset('utf8mb4');
