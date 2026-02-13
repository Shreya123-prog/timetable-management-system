<?php
include 'config/db_connect.php';
$result = $conn->query("SELECT * FROM years");
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . ": " . $row['name'] . "\n";
}
?>