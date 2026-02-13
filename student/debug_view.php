<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login as student first.");
}

$student_id = $_SESSION['user_id'];
echo "<h1>Debug Info for User ID: $student_id</h1>";

// 1. Check User Data
$u = $conn->query("SELECT * FROM users WHERE id=$student_id")->fetch_assoc();
echo "<h2>User Profile</h2>";
echo "<pre>" . print_r($u, true) . "</pre>";
if (!$u['year_id'] || !$u['division_id']) {
    echo "<h3 style='color:red'>WARNING: Year or Division is NULL for this student!</h3>";
    echo "This is why attendance is not showing. Please update this user manually or register a new student.";
}

// 2. Check Attendance Records for this Student
echo "<h2>My Attendance Records</h2>";
$att = $conn->query("SELECT * FROM attendance WHERE student_id=$student_id");
if ($att->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Subject</th><th>Date</th><th>Status</th></tr>";
    while ($row = $att->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['subject_id'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No attendance records found for this student ID.";
}

// 3. Check All Attendance (Global)
echo "<h2>All Attendance Records (First 10)</h2>";
$all_att = $conn->query("SELECT * FROM attendance LIMIT 10");
while ($row = $all_att->fetch_assoc()) {
    print_r($row);
    echo "<br>";
}
?>