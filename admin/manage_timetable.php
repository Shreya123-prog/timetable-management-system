<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}
include '../config/db_connect.php';

$year_id = isset($_GET['year']) ? $_GET['year'] : 1;
$div_id = isset($_GET['div']) ? $_GET['div'] : 1;

$years = $conn->query("SELECT * FROM years");
$divisions = $conn->query("SELECT * FROM divisions");

// Time slots structure
$time_slots = [
    "09:15 - 10:15",
    "10:15 - 11:15",
    "11:15 - 11:30", // Break
    "11:30 - 12:30",
    "12:30 - 01:30",
    "01:30 - 02:15", // Lunch
    "02:15 - 03:15",
    "03:15 - 04:15"
];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$labSlots = [
    "09:15 - 10:15" => "10:15 - 11:15",
    "11:30 - 12:30" => "12:30 - 01:30",
    "02:15 - 03:15" => "03:15 - 04:15"
];


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Timetable - Admin</title>
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
            background: #f8fafc;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .break-slot {
            background: #f1f5f9;
            color: #64748b;
            font-weight: 500;
            font-style: italic;
        }

        .slot-content {
            font-size: 0.9rem;
        }

        .subject {
            font-weight: 600;
            color: var(--primary-color);
        }

        .faculty {
            font-size: 0.8rem;
            color: #64748b;
        }

        .room {
            font-size: 0.8rem;
            color: #64748b;
        }

        .edit-btn {
            font-size: 0.8rem;
            color: var(--accent-color);
            cursor: pointer;
            margin-top: 4px;
            display: inline-block;
        }

        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }

        select {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="container" style="max-width: 1400px; margin-top: 20px;">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2>Manage Timetable</h2>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <form class="controls" method="GET">
            <label>Year:</label>
            <select name="year" onchange="this.form.submit()">
                <?php while ($y = $years->fetch_assoc()): ?>
                    <option value="<?php echo $y['id']; ?>" <?php echo $y['id'] == $year_id ? 'selected' : ''; ?>>
                        <?php echo $y['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Division:</label>
            <select name="div" onchange="this.form.submit()">
                <?php while ($d = $divisions->fetch_assoc()): ?>
                    <option value="<?php echo $d['id']; ?>" <?php echo $d['id'] == $div_id ? 'selected' : ''; ?>>
                        <?php echo $d['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <div class="page-header-actions" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" class="btn btn-outline"><i class="fas fa-print"></i> Print</button>
        </div>

        <div style="overflow-x: auto;">
            <table class="timetable-grid">
                <thead>
                    <tr>
                        <th>Time / Day</th>
                        <?php foreach ($days as $day): ?>
                            <th>
                                <?php echo $day; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($time_slots as $slot): ?>
                        <tr>
                            <td style="font-weight: 500;">
                                <?php echo $slot; ?>
                            </td>

                            <?php if (strpos($slot, 'Break') !== false || strpos($slot, 'Lunch') !== false): ?>
                                <!-- Break row -->
                                <td colspan="6" class="break-slot">
                                    <?php echo (strpos($slot, 'Lunch') !== false) ? 'Lunch Break' : 'Short Break'; ?>
                                </td>
                            <?php else: ?>
                                <!-- Regular slots -->
                                <?php foreach ($days as $day): ?>
                                    <?php
                                    // Fetch entry for this slot
                                    $sql = "SELECT t.*, s.name as subject_name, f.name as faculty_name, c.name as room_name 
                                            FROM timetable t 
                                            LEFT JOIN subjects s ON t.subject_id = s.id 
                                            LEFT JOIN faculty f ON t.faculty_id = f.id 
                                            LEFT JOIN classrooms c ON t.classroom_id = c.id 
                                            WHERE t.year_id = $year_id AND t.division_id = $div_id 
                                            AND t.day = '$day' AND t.time_slot = '$slot'";
                                            
                                    $result = $conn->query($sql);   
                                    $entries = [];
                                    while ($row = $result->fetch_assoc()) {
                                        $entries[] = $row;
                                                            }

                                    ?>
                                 <td>
<?php
// check LAB continuation
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
    $labCheck = $conn->query($checkLabSql);
    if ($labCheck && $labCheck->num_rows > 0) {
        $isLabContinuation = true;
    }
}
?>

<?php if ($isLabContinuation): ?>
    <div style="font-style:italic; color:#94a3b8;">
        LAB continues...
    </div>

<?php elseif (!empty($entries)): ?>

    <?php if (count($entries) > 1): ?>
        <!-- LAB FIRST SLOT -->
        <div class="slot-content">
            <strong>LAB</strong><br>
            <?php foreach ($entries as $e): ?>
                <div style="font-size:12px; margin-bottom:4px; border-bottom:1px dashed #e2e8f0;">
                    <b><?php echo $e['batch']; ?></b> :
                    <?php echo $e['subject_name']; ?><br>
                    <?php echo $e['faculty_name']; ?> |
                    <?php echo $e['room_name']; ?>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- LECTURE -->
        <div class="slot-content">
            <div class="subject"><?php echo $entries[0]['subject_name']; ?></div>
            <div class="faculty"><?php echo $entries[0]['faculty_name']; ?></div>
            <div class="room"><?php echo $entries[0]['room_name']; ?></div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div style="color:#cbd5e1;">--</div>
<?php endif; ?>

<!-- Edit Action -->
<a href="edit_slot.php?year=<?php echo $year_id; ?>&div=<?php echo $div_id; ?>&day=<?php echo $day; ?>&time=<?php echo urlencode($slot); ?>"
   class="edit-btn">
   <i class="fas fa-edit"></i> Edit
</a>
</td>

                                <?php endforeach; ?>
                            <?php endif; ?>
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
        <!-- by Om Aka Morningstar -->
    </div>
</body>

</html>