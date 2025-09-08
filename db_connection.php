<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Use your actual password
$database = 'rfid_system';

$mysqli = new mysqli($host, $user, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
