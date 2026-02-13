<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
include '../config/db_connect.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id AND role!='admin'");
    header("Location: manage_users.php");
    exit();
}

$users = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY role, username");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: var(--shadow-sm);
            border-radius: 8px;
            overflow: hidden;
        }

        .user-table th,
        .user-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .user-table th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-faculty {
            background: #e0f2fe;
            color: #0369a1;
        }

        .role-student {
            background: #dcfce7;
            color: #15803d;
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

    <div class="container" style="max-width: 1000px; margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2>Registered Users</h2>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td style="font-weight: 500;"><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo isset($row['email']) ? $row['email'] : '-'; ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $row['role']; ?>">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="manage_users.php?delete=<?php echo $row['id']; ?>" class="btn-delete"
                                onclick="return confirm('Are you sure you want to delete this user?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> G.H. Raisoni College of Engineering and Management Pune. All Rights
            Reserved.
        </div>
    </div>

    <div style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
        <!-- by Om Aka Morningstar -->
    </div>
</body>

</html>