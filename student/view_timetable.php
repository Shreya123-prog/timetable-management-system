<?php
session_start();
include '../config/db_connect.php';

// Allow admin to view as student too
// Secure access for students
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$year_id = isset($_GET['year']) ? $_GET['year'] : 1;
$div_id = isset($_GET['div']) ? $_GET['div'] : 1;

$years = $conn->query("SELECT * FROM years");
$divisions = $conn->query("SELECT * FROM divisions");

$time_slots = [
    "09:15 - 10:15",
    "10:15 - 11:15",
    "11:15 - 11:30",
    "11:30 - 12:30",
    "12:30 - 01:30",
    "01:30 - 02:15",
    "02:15 - 03:15",
    "03:15 - 04:15"
];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Timetable</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .timetable-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: var(--shadow-sm);
            border-radius: 8px;
            overflow: hidden;
        }

        .timetable-grid th,
        .timetable-grid td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: center;
        }

        .timetable-grid th {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .break-slot {
            background: #f8fafc;
            color: #64748b;
            font-weight: 500;
            font-style: italic;
            letter-spacing: 1px;
        }

        .subject {
            font-weight: 600;
            color: var(--text-primary);
        }

        .faculty {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .room {
            font-size: 0.8rem;
            color: var(--accent-color);
            font-weight: 500;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>

    <div class="container" style="max-width: 1400px; margin-top: 20px;">

        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: var(--primary-dark);">Class Timetable</h1>
            <p>Department of Information Technology</p>
        </div>

        <form class="filters" method="GET">
            <div style="display:flex; flex-direction: column;">
                <label style="font-size: 0.8rem; margin-bottom: 4px; color: #64748b;">Select Year</label>
                <select name="year" onchange="this.form.submit()"
                    style="padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <?php while ($y = $years->fetch_assoc()): ?>
                        <option value="<?php echo $y['id']; ?>" <?php echo $y['id'] == $year_id ? 'selected' : ''; ?>>
                            <?php echo $y['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="display:flex; flex-direction: column;">
                <label style="font-size: 0.8rem; margin-bottom: 4px; color: #64748b;">Select Division</label>
                <select name="div" onchange="this.form.submit()"
                    style="padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <?php while ($d = $divisions->fetch_assoc()): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo $d['id'] == $div_id ? 'selected' : ''; ?>>
                            <?php echo $d['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../admin/dashboard.php" class="btn btn-outline" style="margin-left: auto;">Back to Admin</a>
            <?php else: ?>
                <a href="../logout.php" class="btn btn-outline" style="margin-left: auto;">Logout</a>
            <?php endif; ?>
        </form>

        <div class="page-header-actions" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" class="btn btn-outline"><i class="fas fa-print"></i> Print
                Timetable</button>
        </div>

        <div style="overflow-x: auto;">
            <table class="timetable-grid">
                <thead>
                    <tr>
                        <th style="width: 150px;">Time / Day</th>
                        <?php foreach ($days as $day): ?>
                            <th>
                                <?php echo $day; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            <tbody>
<?php foreach ($time_slots as $slot): 

$isSecondLabSlot = false;
$labSlots = [
    "02:15 - 03:15" => "03:15 - 04:15",
    "09:15 - 10:15" => "10:15 - 11:15",
    "11:30 - 12:30" => "12:30 - 01:30"
];

foreach ($labSlots as $first => $second) {
    if ($slot === $second) {
        $isSecondLabSlot = true;
        break;
    }
}
?>
<tr>
    <td style="font-weight:500;"><?php echo $slot; ?></td>

    <?php foreach ($days as $day): ?>
        <?php
        $sql = "SELECT t.*, s.name AS subject_name, f.name AS faculty_name, c.name AS room_name
                FROM timetable t
                LEFT JOIN subjects s ON t.subject_id=s.id
                LEFT JOIN faculty f ON t.faculty_id=f.id
                LEFT JOIN classrooms c ON t.classroom_id=c.id
                WHERE t.year_id=$year_id 
                  AND t.division_id=$div_id
                  AND t.day='$day'
                  AND t.time_slot='$slot'";
                  // 🔥 check if this slot is continuation of LAB
$isLabContinuation = false;
$previousSlot = null;

foreach ($labSlots as $first => $second) {
    if ($slot === $second) {
        $previousSlot = $first;
        break;
    }
}

if ($previousSlot) {
    $checkLabSql = "SELECT * FROM timetable
                    WHERE year_id = $year_id
                      AND division_id = $div_id
                      AND day = '$day'
                      AND time_slot = '$previousSlot'
                      AND batch IS NOT NULL";

    $labCheckResult = $conn->query($checkLabSql);

    if ($labCheckResult && $labCheckResult->num_rows > 0) {
        $isLabContinuation = true;
    }
}


        $result = $conn->query($sql);
        $entries = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $entries[] = $row;
            }
        }
        ?>

        <td>
<?php if ($isLabContinuation): ?>
    <div style="font-style:italic;color:#94a3b8;">
        LAB continues...
    </div>

<?php elseif (!empty($entries)): ?>
    <?php foreach ($entries as $e): ?>
        <?php if (!empty($e['batch'])): ?>
            <div style="font-size:0.75rem;font-weight:600;">
                <?php echo $e['batch']; ?>
            </div>
        <?php endif; ?>

        <div class="subject"><?php echo $e['subject_name']; ?></div>
        <div class="faculty"><?php echo $e['faculty_name']; ?></div>
        <div class="room"><?php echo $e['room_name']; ?></div>
    <?php endforeach; ?>

<?php else: ?>
    --
<?php endif; ?>
</td>

    <?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>

            </table>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> G.H. Raisoni College of Engineering and Management Pune. All Rights Reserved.
    </div>
    <div style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
        by Shreya Aka Morningstar
    </div>
</body>

</html>