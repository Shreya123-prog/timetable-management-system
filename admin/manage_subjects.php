<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}
include '../config/db_connect.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE id=$id");
    header("Location: manage_subjects.php");
    exit();
}

$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: var(--shadow-sm);
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .btn-delete {
            color: #ef4444;
            background: #fee2e2;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-delete:hover {
            background: #fecaca;
        }
    </style>
</head>

<body>

    <div class="container" style="max-width: 800px; margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2>Total Subjects</h2>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($subjects->num_rows > 0): ?>
                    <?php while ($row = $subjects->fetch_assoc()): ?>
                        <tr>
                            <td>#
                                <?php echo $row['id']; ?>
                            </td>
                            <td style="font-weight: 500;">
                                <?php echo $row['name']; ?>
                            </td>
                            <td>
                                <a href="manage_subjects.php?delete=<?php echo $row['id']; ?>" class="btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this subject?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #64748b;">No subjects found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer">
            &copy;
            <?php echo date('Y'); ?> G.H. Raisoni College of Engineering and Management Pune. All Rights Reserved.
        </div>
    </div>

    <div style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
        by Om Aka Morningstar
    </div>
</body>

</html>