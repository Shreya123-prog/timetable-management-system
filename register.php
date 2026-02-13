<?php
session_start();
include 'config/db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $year_id = isset($_POST['year']) ? $_POST['year'] : NULL;
    $div_id = isset($_POST['div']) ? $_POST['div'] : NULL;

    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif ($role !== 'faculty' && $role !== 'student') {
        $error = "Invalid role selected!";
    } elseif ($role === 'student' && (empty($year_id) || empty($div_id))) {
        $error = "Please select Year and Division for Student!";
    } else {
        // Check if username or email exists
        $check = $conn->query("SELECT username, email FROM users WHERE username = '$username' OR email = '$email'");
        if ($check->num_rows > 0) {
            $existing = $check->fetch_assoc();
            if ($existing['username'] === $username) {
                $error = "Username already taken!";
            } else {
                $error = "Email already registered!";
            }
        } else {
            // Create user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $y = $year_id ? "'$year_id'" : "NULL";
            $d = $div_id ? "'$div_id'" : "NULL";

            $sql = "INSERT INTO users (username, email, password, role, full_name, year_id, division_id) VALUES ('$username', '$email', '$hash', '$role', '$full_name', $y, $d)";

            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful! You can now <a href='index.php'>Login here</a>.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}

// Fetch Options
$years_res = $conn->query("SELECT * FROM years");
$divs_res = $conn->query("SELECT * FROM divisions");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Timetable Management</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <h2 style="color: var(--primary-dark); margin-bottom: 20px;">Create Account</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert" style="background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                    <?php echo $success; ?>
                </div>
            <?php else: ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="role" class="form-control" required onchange="toggleStudentFields()">
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty (Teacher)</option>
                        </select>
                    </div>

                    <div id="student-fields" style="display: none;">
                        <div class="form-group">
                            <label>Year</label>
                            <select name="year" class="form-control">
                                <option value="">Select Year</option>
                                <?php while ($y = $years_res->fetch_assoc()): ?>
                                    <option value="<?php echo $y['id']; ?>"><?php echo $y['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Division</label>
                            <select name="div" class="form-control">
                                <option value="">Select Division</option>
                                <?php while ($d = $divs_res->fetch_assoc()): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo $d['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <script>
                        function toggleStudentFields() {
                            var role = document.getElementById('role').value;
                            var fields = document.getElementById('student-fields');
                            if (role === 'student') {
                                fields.style.display = 'block';
                            } else {
                                fields.style.display = 'none';
                            }
                        }
                    </script>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create password" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password"
                            required>
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">Register</button>
                </form>
            <?php endif; ?>

            <div style="margin-top: 20px; font-size: 0.9rem;">
                Already have an account? <a href="index.php">Login here</a>
            </div>
        </div>
    </div>

    <div class="footer">
        &copy;
        <?php echo date('Y'); ?> G.H. Raisoni College of Engineering and Management Pune. All Rights Reserved.
    </div>

    <div style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
        <!-- by Shreya -->
    </div>
</body>

</html>