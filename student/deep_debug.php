<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login as student first.");
}

$student_id = $_SESSION['user_id'];
echo "<h1>Deep Debug for Student ID: $student_id</h1>";

// 1. Student Profile
$u = $conn->query("SELECT * FROM users WHERE id=$student_id")->fetch_assoc();
echo "<h2>1. Student Profile</h2>";
echo "Year ID: " . var_export($u['year_id'], true) . "<br>";
echo "Division ID: " . var_export($u['division_id'], true) . "<br>";

// 2. Years Table
echo "<h2>2. Valid Years</h2>";
$years = $conn->query("SELECT * FROM years");
while ($y = $years->fetch_assoc()) {
    echo "ID: " . $y['id'] . " - Name: " . $y['name'] . "<br>";
}

// 3. Subjects Table
echo "<h2>3. All Subjects</h2>";
$subjects = $conn->query("SELECT * FROM subjects");
$has_subjects_for_student_year = false;
if ($subjects->num_rows > 0) {
    while ($s = $subjects->fetch_assoc()) {
        echo "Subject ID: " . $s['id'] . " - Name: " . $s['name'] . " - Year ID: " . $s['year_id'] . "<br>";
        if ($s['year_id'] == $u['year_id']) {
            $has_subjects_for_student_year = true;
        }
    }
} else {
    echo "No subjects found in database.<br>";
}

// 4. Diagnosis
echo "<h2>4. Diagnosis</h2>";
if (!$u['year_id']) {
    echo "<span style='color:red;'>FAIL: Student has no Year ID.</span>";
} elseif (!$has_subjects_for_student_year) {
    echo "<span style='color:red;'>FAIL: Student is in Year ID " . $u['year_id'] . ", but there are NO subjects for this year.</span>";
} else {
    echo "<span style='color:green;'>PASS: Data looks correct. The query should work.</span>";
}
?>