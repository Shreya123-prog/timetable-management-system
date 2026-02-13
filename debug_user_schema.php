<?php
include 'config/db_connect.php';
$fp = fopen('user_schema_log.txt', 'w');
$result = $conn->query("DESCRIBE users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fwrite($fp, $row['Field'] . " | ");
    }
} else {
    fwrite($fp, "Error: " . $conn->error);
}
fclose($fp);
?>