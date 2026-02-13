
<?php

session_start();
// Allow both admin and faculty to edit
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}
include '../config/db_connect.php';



$year_id = $_GET['year'];
$div_id = $_GET['div'];
$day = $_GET['day'];
$time = $_GET['time'];
// Fetch division name (A / B / C)
$divRes = $conn->query("SELECT name FROM divisions WHERE id = $div_id");
$divRow = $divRes->fetch_assoc();
$divisionName = $divRow['name']; // A, B or C
// Generate batches based on division (A1 A2 A3 A4 etc.)
$batches = [];
for ($i = 1; $i <= 4; $i++) {
    $batches[] = $divisionName . $i;
}


// Fetch existing data for this slot
$sql = "SELECT t.*, s.name as subject_name, f.name as faculty_name, c.name as room_name 
        FROM timetable t 
        LEFT JOIN subjects s ON t.subject_id = s.id 
        LEFT JOIN faculty f ON t.faculty_id = f.id 
        LEFT JOIN classrooms c ON t.classroom_id = c.id 
        WHERE t.year_id=$year_id AND t.division_id=$div_id AND t.day='$day' AND t.time_slot='$time'";
$result = $conn->query($sql);
$existing = $result->fetch_assoc();

// Helper function to Get or Create ID
function getOrCreateId($conn, $table, $col_name, $value)
{
    if (empty($value))
        return "NULL";

    $value = mysqli_real_escape_string($conn, $value);

    // Check if exists
    $check = $conn->query("SELECT id FROM $table WHERE $col_name = '$value' LIMIT 1");
    if ($check->num_rows > 0) {
        return $check->fetch_assoc()['id'];
    } else {
        // Create new
        $insert = $conn->query("INSERT INTO $table ($col_name) VALUES ('$value')");
        if ($insert) {
            return $conn->insert_id;
        } else {
            return "NULL";
        }
    }
}
function conflictExists($conn, $column, $value, $day, $time_slot, $exclude_id = null) {

    if ($value == "NULL") return false;

    $query = "SELECT id FROM timetable 
              WHERE $column = $value
              AND day = '$day'
              AND time_slot = '$time_slot'";

    if ($exclude_id) {
        $query .= " AND id != $exclude_id";
    }

    $result = $conn->query($query);

    return ($result && $result->num_rows > 0);
}


// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $slot_type = $_POST['slot_type'];

    // =========================
    // ===== LECTURE LOGIC =====
    // =========================
    if ($slot_type === 'lecture') {

        $subject_name = trim($_POST['subject_name']);
        $faculty_name = trim($_POST['faculty_name']);
        $classroom_name = trim($_POST['classroom_name']);

        if (empty($subject_name) && empty($faculty_name) && empty($classroom_name)) {
            if ($existing) {
                $conn->query("DELETE FROM timetable WHERE id=" . $existing['id']);
            }
            header("Location: manage_timetable.php?year=$year_id&div=$div_id");
            exit();
        }

        $subj_id = getOrCreateId($conn, 'subjects', 'name', $subject_name);
        $fac_id  = getOrCreateId($conn, 'faculty', 'name', $faculty_name);

        $room_id = getOrCreateId($conn, 'classrooms', 'name', $classroom_name);
        $exclude_id = ($existing && isset($existing['id'])) ? $existing['id'] : null;

// 🔥 Faculty Conflict
if (conflictExists($conn, 'faculty_id', $fac_id, $day, $time, $exclude_id)) {
    echo "<script>alert('⚠ Faculty already allocated at this time!'); window.history.back();</script>";
    exit();
}

// 🔥 Classroom Conflict
if (conflictExists($conn, 'classroom_id', $room_id, $day, $time, $exclude_id)) {
    echo "<script>alert('⚠ Classroom already occupied at this time!'); window.history.back();</script>";
    exit();
}


        if ($existing && isset($existing['id'])) {
            $conn->query("UPDATE timetable SET subject_id=$subj_id, faculty_id=$fac_id, classroom_id=$room_id 
                          WHERE id=" . $existing['id']);
        } else {
            $conn->query("INSERT INTO timetable (year_id, division_id, day, time_slot, subject_id, faculty_id, classroom_id) 
                          VALUES ('$year_id', '$div_id', '$day', '$time', $subj_id, $fac_id, $room_id)");
        }
    }

   // =====================
// ===== LAB LOGIC =====
// =====================
if ($slot_type === 'lab') {

    // 1️⃣ calculate next slot FIRST
    $slots = [
        "09:15 - 10:15", "10:15 - 11:15", "11:15 - 11:30",
        "11:30 - 12:30", "12:30 - 01:30", "01:30 - 02:15",
        "02:15 - 03:15", "03:15 - 04:15"
    ];

    $index = array_search($time, $slots);
    $next_time = $slots[$index + 1] ?? null;

    if (!$next_time) {
        die("Next slot not found for lab");
    }

    // 2️⃣ delete old lab entries FIRST
    $conn->query("
        DELETE FROM timetable 
        WHERE year_id=$year_id 
          AND division_id=$div_id 
          AND day='$day'
          AND time_slot IN ('$time', '$next_time')
    ");

    // 3️⃣ insert fresh lab entries (batch-wise)
    foreach ($_POST['lab_subject'] as $batch => $subjectName) {

        if (
            empty($subjectName) &&
            empty($_POST['lab_faculty'][$batch]) &&
            empty($_POST['lab_room'][$batch])
        ) {
            continue;
        }

        $subj_id = getOrCreateId($conn, 'subjects', 'name', $subjectName);
        $fac_id  = getOrCreateId($conn, 'faculty', 'name', $_POST['lab_faculty'][$batch]);
 
        $room_id = getOrCreateId($conn, 'classrooms', 'name', $_POST['lab_room'][$batch]);
         foreach ([$time, $next_time] as $check_time) {

    // 🔥 Faculty Conflict
    if (conflictExists($conn, 'faculty_id', $fac_id, $day, $check_time)) {
        echo "<script>alert('⚠ Faculty already allocated during lab time!'); window.history.back();</script>";
        exit();
    }

    // 🔥 Classroom Conflict
    if (conflictExists($conn, 'classroom_id', $room_id, $day, $check_time)) {
        echo "<script>alert('⚠ Classroom already occupied during lab time!'); window.history.back();</script>";
        exit();
    }
}

        foreach ([$time, $next_time] as $t) {
            $conn->query("
                INSERT INTO timetable 
                (year_id, division_id, batch, day, time_slot, subject_id, faculty_id, classroom_id)
                VALUES
                ('$year_id', '$div_id', '$batch', '$day', '$t', $subj_id, $fac_id, $room_id)
            ");
        }
    }
}

    header("Location: manage_timetable.php?year=$year_id&div=$div_id");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Slot (Manual Entry)</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <style>
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }

        .info-text {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 5px;
        }
    </style>
</head>
<script>
function toggleLabFields() {
    const type = document.getElementById('slot_type').value;
    document.getElementById('lectureFields').style.display = (type === 'lecture') ? 'block' : 'none';
    document.getElementById('labFields').style.display = (type === 'lab') ? 'block' : 'none';
}
</script>


<body>
    <div class="form-container">
        <h2 style="margin-bottom: 20px;">Edit Timetable Slot</h2>
        <div
            style="margin-bottom: 20px; color: var(--text-secondary); background: #f8fafc; padding: 10px; border-radius:8px;">
            <strong><?php echo $day; ?></strong> | <?php echo $time; ?>
        </div>
   <form method="POST">
        <div class="form-group">
    <label>Slot Type</label>
    <select name="slot_type" id="slot_type" class="form-control" onchange="toggleLabFields()">
        <option value="lecture">Lecture (1 Hour)</option>
        <option value="lab">Lab (2 Hours)</option>
    </select>
</div>
 <div id="lectureFields">


            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" name="subject_name" class="form-control"
                    value="<?php echo $existing ? $existing['subject_name'] : ''; ?>"
                    placeholder="Type Subject Name (e.g. Python)">
                <div class="info-text">Leave blank to clear this slot.</div>
            </div>

            <div class="form-group">
                <label>Faculty Name</label>
                <input type="text" name="faculty_name" class="form-control"
                    value="<?php echo $existing ? $existing['faculty_name'] : ''; ?>"
                    placeholder="Type Faculty Name (e.g. Prof. Sharma)">
            </div>

            <div class="form-group">
                <label>Classroom / Lab</label>
                <input type="text" name="classroom_name" class="form-control"
                    value="<?php echo $existing ? $existing['room_name'] : ''; ?>"
                    placeholder="Type Room No (e.g. LH-4 or Lab-1)">
            </div>
        </div>
       <div id="labFields" style="display:none;">
    <div class="info-text" style="margin-bottom:10px;">
        Lab will occupy <strong>2 continuous slots</strong>.
    </div>

<?php foreach ($batches as $batch): ?>
    <div style="border:1px solid #e5e7eb; padding:12px; border-radius:8px; margin-bottom:10px;">
        <strong><?php echo $batch; ?></strong>

        <input type="text"
               name="lab_subject[<?php echo $batch; ?>]"
               class="form-control"
               placeholder="Subject for <?php echo $batch; ?>"
               style="margin-top:8px;">

        <input type="text"
               name="lab_faculty[<?php echo $batch; ?>]"
               class="form-control"
               placeholder="Faculty for <?php echo $batch; ?>"
               style="margin-top:8px;">

        <input type="text"
               name="lab_room[<?php echo $batch; ?>]"
               class="form-control"
               placeholder="Lab Room for <?php echo $batch; ?>"
               style="margin-top:8px;">
    </div>
<?php endforeach; ?>

</div>

    

        
    
</div>


            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn" style="flex: 1;">Save Changes</button>
                <a href="manage_timetable.php?year=<?php echo $year_id; ?>&div=<?php echo $div_id; ?>"
                    class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>