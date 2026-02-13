<?php
include 'config/db_connect.php';
$fp = fopen('year_list.txt', 'w');
$result = $conn->query("SELECT * FROM years");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fwrite($fp, $row['id'] . ": " . $row['name'] . "\n");
    }
} else {
    fwrite($fp, "No results");
}
fclose($fp);
?>