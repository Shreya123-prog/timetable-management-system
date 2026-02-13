<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}
include '../config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .dash-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            border: 1px solid #e2e8f0;
        }

        .dash-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .dash-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .dash-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="container" style="max-width: 1000px; margin-top: 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="color: var(--primary-dark);">Welcome,
                    <?php echo $_SESSION['username']; ?>
                </h1>
                <p style="color: var(--text-secondary);">Student Dashboard</p>
            </div>
            <a href="../logout.php" class="btn btn-outline">Logout</a>
        </div>

        <div class="dashboard-grid">
            <a href="view_timetable.php" class="dash-card">
                <div class="dash-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="dash-title">View Timetable</div>
                <p style="color: #64748b;">Check your class schedule</p>
            </a>


        </div>

        <!-- Updates Section -->
        <h2 style="color: var(--primary-dark); margin-top: 40px; margin-bottom: 20px;">📢 Upcoming Events & Updates</h2>
        <div style="display: grid; gap: 20px;">
            <?php
            $updates = $conn->query("SELECT * FROM updates ORDER BY created_at DESC LIMIT 5");
            if ($updates->num_rows > 0) {
                while ($row = $updates->fetch_assoc()) {
                    echo '<div class="card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid var(--primary-color);">';
                    echo '<div style="display:flex; justify-content:space-between;">';
                    echo '<h3 style="margin:0; color: var(--primary-dark);">' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<small style="color:#94a3b8;">' . date('d M Y', strtotime($row['created_at'])) . '</small>';
                    echo '</div>';
                    if ($row['message']) {
                        echo '<p style="margin-top: 10px; color: #475569;">' . nl2br(htmlspecialchars($row['message'])) . '</p>';
                    }
                    if ($row['file_path']) {
                        echo '<div style="margin-top: 15px;">';
                        echo '<a href="../' . $row['file_path'] . '" class="btn btn-outline" target="_blank" download><i class="fas fa-file-pdf"></i> Download PDF</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
            } else {
                echo '<p style="color: #64748b; font-style: italic;">No new updates at the moment.</p>';
            }
            ?>
        </div>

        <div
            style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
            <!-- by Om Aka Morningstar -->
        </div>
</body>

</html>