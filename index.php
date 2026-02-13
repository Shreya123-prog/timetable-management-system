<?php
session_start();
include 'config/db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // For prototype we are using simple password verify or direct compare if not hashed yet
        // In database.sql we inserted a hash.
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['username'] = $row['username'];

            if ($row['role'] == 'admin' || $row['role'] == 'faculty') {
                // Faculty and Admin share the same dashboard for now (Editing access)
                header("Location: admin/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Timetable Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <h1 class="college-name">G H Raisoni College Of Engineering And Management Pune</h1>
            <div class="dept-name">Department of Information Technology</div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username"
                        required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Login</button>
            </form>



            <div style="margin-top: 20px; font-size: 0.9rem;">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <div style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
        by Shreya Aka Morningstar
    </div>
</body>

</html>