<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'faculty')) {
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
    <title>Admin Dashboard - Timetable GH</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .nav-item {
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-item:hover,
        .nav-item.active {
            background: #eff6ff;
            color: var(--primary-color);
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .card h3 {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #eff6ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-calendar-alt"></i> GH Raisoni
            </div>
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="manage_timetable.php" class="nav-item">
                <i class="fas fa-clock"></i> Manage Timetable
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="manage_users.php" class="nav-item">
                    <i class="fas fa-users"></i> Manage Users
                </a>
            <?php endif; ?>
            <a href="manage_updates.php" class="nav-item">
                <i class="fas fa-bullhorn"></i> Updates & Events
            </a>
            <div style="margin-top: auto;">
                <a href="../logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="header">
                <h2>Welcome <?php echo ($_SESSION['role'] == 'admin') ? 'Admin' : 'Teacher'; ?></h2>
                <div class="user-badge"><?php echo $_SESSION['username']; ?></div>
            </div>

            <div class="card-grid">
                <a href="manage_subjects.php" class="card" style="text-decoration: none; display: block;">
                    <div class="icon-box"><i class="fas fa-book"></i></div>
                    <h3>Total Subjects</h3>
                    <p>
                        <?php echo $conn->query("SELECT COUNT(*) FROM subjects")->fetch_row()[0]; ?>
                    </p>
                </a>
                <a href="manage_faculty.php" class="card" style="text-decoration: none; display: block;">
                    <div class="icon-box"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3>Total Faculty</h3>
                    <p>
                        <?php echo $conn->query("SELECT COUNT(*) FROM faculty")->fetch_row()[0]; ?>
                    </p>
                </a>
                <div class="card">
                    <div class="icon-box"><i class="fas fa-users"></i></div>
                    <h3>Divisions</h3>
                    <p>3 (A, B, C)</p>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="manage_users.php" class="card" style="text-decoration: none; display: block;">
                        <div class="icon-box"><i class="fas fa-user-plus"></i></div>
                        <h3>Registered Users</h3>
                        <p><?php echo $conn->query("SELECT COUNT(*) FROM users WHERE role!='admin'")->fetch_row()[0]; ?></p>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Timetable Quick View placeholder -->
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 20px;">Quick Actions</h3>
                <a href="manage_timetable.php" class="btn">Create/Edit Timetable</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="manage_users.php" class="btn btn-outline" style="margin-left: 10px;">View Registrations</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
        <!-- by Om Aka Morningstar -->
    </div>
</body>

</html>