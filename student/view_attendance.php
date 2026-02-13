<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}
include '../config/db_connect.php';

$student_id = $_SESSION['user_id'];

// Fetch Student Class Info
$std_q = $conn->query("SELECT year_id, division_id FROM users WHERE id = $student_id");
$std_info = $std_q->fetch_assoc();
$my_year = $std_info['year_id'];
$my_div = $std_info['division_id'];

// Fetch all subjects (Could limit by year if schema supported it, but simpler to show all or handled by logic)
// Optimal: SELECT * FROM subjects WHERE year_id = $my_year
$subjects = $conn->query("SELECT * FROM subjects WHERE year_id = $my_year");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .attendance-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .att-stats {
            text-align: right;
        }

        .percentage-badge {
            background: #dcfce7;
            color: #166534;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .percentage-badge.low {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 800px; margin-top: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="color: var(--primary-dark);">My Attendance</h1>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <?php if ($my_year && $subjects && $subjects->num_rows > 0): ?>
            <?php while ($sub = $subjects->fetch_assoc()): ?>
                <?php
                $sub_id = $sub['id'];

                // Count Total Lectures for MY Class (Year + Div + Subject)
                $total_q = $conn->query("SELECT COUNT(DISTINCT date) FROM attendance WHERE subject_id = $sub_id AND year_id = $my_year AND division_id = $my_div");
                $total_lectures = $total_q->fetch_row()[0];

                // Count My Presence
                $my_q = $conn->query("SELECT COUNT(*) FROM attendance WHERE subject_id = $sub_id AND student_id = $student_id AND status = 'Present'");
                $my_present = $my_q->fetch_row()[0];

                $percentage = ($total_lectures > 0) ? round(($my_present / $total_lectures) * 100, 1) : 0;

                // Badge Color
                $badge_class = ($percentage < 75) ? 'percentage-badge low' : 'percentage-badge';
                ?>
                <div class="attendance-card">
                    <div>
                        <h3 style="margin-bottom: 5px; color: var(--text-primary);"><?php echo $sub['name']; ?></h3>
                    </div>
                    <div class="att-stats">
                        <div style="margin-bottom: 5px;">
                            <span class="<?php echo $badge_class; ?>"><?php echo $percentage; ?>%</span>
                        </div>
                        <div style="font-size: 0.85rem; color: #64748b;">
                            Attended: <strong><?php echo $my_present; ?></strong> / <?php echo $total_lectures; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; color: #64748b;">No subjects found for your Year. Please ensure your profile is
                updated.</p>
        <?php endif; ?>

    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> G.H. Raisoni College of Engineering and Management Pune. All Rights Reserved.
    </div>
</body>

</html>