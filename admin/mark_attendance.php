<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}
include '../config/db_connect.php';

$success_msg = '';
$error_msg = '';

// Fetch Dropdown Data
$years = $conn->query("SELECT * FROM years");
$divisions = $conn->query("SELECT * FROM divisions");
$subjects = $conn->query("SELECT * FROM subjects");

$selected_year = isset($_POST['year']) ? $_POST['year'] : '';
$selected_div = isset($_POST['div']) ? $_POST['div'] : '';
$selected_sub = isset($_POST['subject']) ? $_POST['subject'] : '';
$selected_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$students = null;

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Action 1: Load Students (clicked "Load" or just changed selection and submitted)
    if (isset($_POST['load_students']) || isset($_POST['submit_attendance'])) {
        if ($selected_year && $selected_div) {
            // Filter by Year and Division
            $students = $conn->query("SELECT * FROM users WHERE role='student' AND year_id=$selected_year AND division_id=$selected_div ORDER BY full_name");
        }
    }

    // Action 2: Submit Attendance
    if (isset($_POST['submit_attendance'])) {
        $present_students = isset($_POST['students']) ? $_POST['students'] : [];

        if (empty($selected_year) || empty($selected_div) || empty($selected_sub) || empty($selected_date)) {
            $error_msg = "Please select all fields (Year, Division, Subject, Date).";
        } else {
            // Delete existing logic to clear old state for this slot
            $conn->query("DELETE FROM attendance WHERE year_id=$selected_year AND division_id=$selected_div AND subject_id=$selected_sub AND date='$selected_date'");

            // Insert new Present records
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, year_id, division_id, subject_id, date, status) VALUES (?, ?, ?, ?, ?, 'Present')");
            $inserted_count = 0;
            foreach ($present_students as $std_id) {
                $stmt->bind_param("iiiis", $std_id, $selected_year, $selected_div, $selected_sub, $selected_date);
                if ($stmt->execute()) {
                    $inserted_count++;
                }
            }
            $success_msg = "Attendance marked for $inserted_count students on $selected_date.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .student-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .student-item {
            background: #f8fafc;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .student-item:hover {
            border-color: var(--primary-color);
            background: #eff6ff;
        }

        .student-item input {
            transform: scale(1.2);
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 1000px; margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Mark Attendance</h2>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert"
                style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert"
                style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-section" style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 150px;">
                    <label>Year</label>
                    <select name="year" class="form-control" required>
                        <option value="">Select Year</option>
                        <?php while ($y = $years->fetch_assoc()): ?>
                            <option value="<?php echo $y['id']; ?>" <?php echo $selected_year == $y['id'] ? 'selected' : ''; ?>>
                                <?php echo $y['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label>Division</label>
                    <select name="div" class="form-control" required>
                        <option value="">Select Div</option>
                        <?php while ($d = $divisions->fetch_assoc()): ?>
                            <option value="<?php echo $d['id']; ?>" <?php echo $selected_div == $d['id'] ? 'selected' : ''; ?>>
                                <?php echo $d['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex: 2; min-width: 200px;">
                    <label>Subject</label>
                    <select name="subject" class="form-control" required>
                        <option value="">Select Subject</option>
                        <?php while ($s = $subjects->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $selected_sub == $s['id'] ? 'selected' : ''; ?>>
                                <?php echo $s['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" required>
                </div>
                <!-- Load Button -->
                <div style="flex-basis: 100%; margin-top: 10px;">
                    <button type="submit" name="load_students" class="btn btn-outline" style="width: 100%;">Load Student
                        List</button>
                </div>
            </div>

            <?php if ($students && $students->num_rows > 0): ?>
                <div class="form-section">
                    <h3>Select Students (Present)</h3>
                    <div class="student-list">
                        <?php while ($std = $students->fetch_assoc()): ?>
                            <label class="student-item">
                                <input type="checkbox" name="students[]" value="<?php echo $std['id']; ?>">
                                <div>
                                    <div style="font-weight: 600;"><?php echo $std['full_name']; ?></div>
                                    <div style="font-size: 0.8rem; color: #64748b;"><?php echo $std['username']; ?></div>
                                </div>
                            </label>
                        <?php endwhile; ?>
                    </div>

                    <div style="margin-top: 30px; text-align: center;">
                        <button type="submit" name="submit_attendance" class="btn"
                            style="padding: 12px 40px; font-size: 1.1rem;">Submit Attendance</button>
                    </div>
                </div>
            <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['load_students'])): ?>
                <div class="form-section" style="text-align: center; color: #64748b;">
                    No students found for this Year and Division.
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div style="text-align: center; margin-top: 50px; color: #94a3b8; font-size: 0.8rem;">
        <!-- by Om Aka Morningstar -->
    </div>
</body>

</html>