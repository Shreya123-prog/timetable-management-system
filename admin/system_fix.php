<?php
session_start();
include '../config/db_connect.php';

// Diagnostic Logic
$users = $conn->query("SELECT id, username, role, year_id, division_id FROM users WHERE role='student'");
$years = $conn->query("SELECT y.id, y.name, COUNT(s.id) as sub_count FROM years y LEFT JOIN subjects s ON y.id=s.year_id GROUP BY y.id");

// Fix Action
if (isset($_POST['fix_student'])) {
    $uid = $_POST['student_id'];
    $new_year = $_POST['target_year'];
    $conn->query("UPDATE users SET year_id = $new_year WHERE id = $uid");
    echo "<script>alert('Updated Student Year!'); window.location.href='system_fix.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Fixer</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .btn { padding: 5px 10px; cursor: pointer; background: #007bff; color: white; border: none; }
    </style>
</head>
<body>
    <h1>System Diagnosis & Repair</h1>
    <a href="../index.php">Back to Home</a> | <a href="../student/view_attendance.php">Check Student View</a>

    <h2>1. Available Years & Subjects</h2>
    <p>Students must be in a Year that has > 0 Subjects.</p>
    <table>
        <tr><th>Year ID</th><th>Year Name</th><th>Subject Count</th></tr>
        <?php 
        $valid_years = [];
        while($row = $years->fetch_assoc()): 
            if($row['sub_count'] > 0) $valid_years[] = $row;
        ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td style="font-weight:bold; color: <?php echo $row['sub_count']>0 ? 'green':'red'; ?>">
                    <?php echo $row['sub_count']; ?> Subjects
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>2. Student Configuration</h2>
    <table>
        <tr><th>ID</th><th>Username</th><th>Current Year ID</th><th>Action</th></tr>
        <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo $u['username']; ?></td>
                <td><?php echo $u['year_id'] ? $u['year_id'] : '<span style="color:red">NULL</span>'; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="student_id" value="<?php echo $u['id']; ?>">
                        <select name="target_year" required>
                            <option value="">Move to...</option>
                            <?php foreach($valid_years as $vy): ?>
                                <option value="<?php echo $vy['id']; ?>">
                                    ID <?php echo $vy['id']; ?> (<?php echo $vy['name']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="fix_student" class="btn">Fix</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
