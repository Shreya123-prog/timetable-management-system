<?php
$host = 'localhost';
$username = 'root';
$password = 'Shreya'; // Default XAMPP password
$dbname = 'timetable_gh';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname, 3307);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
