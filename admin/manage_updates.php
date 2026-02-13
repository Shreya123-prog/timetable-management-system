<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}

$msg = "";
$error = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM updates WHERE id=$id");
    header("Location: manage_updates.php");
}

// Handle Post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $file_path = NULL;

    // File Upload
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["pdf_file"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($file_type == "pdf") {
            if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                $file_path = "uploads/" . basename($_FILES["pdf_file"]["name"]);
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Only PDF files are allowed.";
        }
    }

    if (!$error) {
        $f = $file_path ? "'$file_path'" : "NULL";
        $sql = "INSERT INTO updates (title, message, file_path) VALUES ('$title', '$content', $f)";
        if ($conn->query($sql)) {
            $msg = "Update posted successfully!";
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

$updates = $conn->query("SELECT * FROM updates ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Updates</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container" style="max-width: 800px; margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: var(--primary-dark);">📢 Manage Updates & Events</h2>
            <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <?php if ($msg)
            echo "<p style='color:green; background:#dcfce7; padding:10px; border-radius:5px;'>$msg</p>"; ?>
        <?php if ($error)
            echo "<p style='color:red; background:#fee2e2; padding:10px; border-radius:5px;'>$error</p>"; ?>

        <!-- Post Form -->
        <div class="card"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Event Name or Update Title"
                        required>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Message / Details</label>
                    <textarea name="content" class="form-control" rows="4"
                        placeholder="Type instructions or details here..."></textarea>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Upload PDF (Optional)</label>
                    <input type="file" name="pdf_file" class="form-control" accept=".pdf">
                </div>
                <button type="submit" class="btn" style="margin-top: 20px; width: 100%;">Post Update</button>
            </form>
        </div>

        <!-- List -->
        <h3 style="color: #64748b; margin-bottom: 15px;">Recent Updates</h3>
        <?php while ($row = $updates->fetch_assoc()): ?>
            <div class="card"
                style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary-color); margin-bottom: 15px; position: relative;">
                <a href="?delete=<?php echo $row['id']; ?>"
                    style="position: absolute; top: 10px; right: 10px; color: #ef4444;"
                    onclick="return confirm('Delete this update?')"><i class="fas fa-trash"></i></a>
                <h4 style="margin: 0; color: var(--primary-dark);">
                    <?php echo $row['title']; ?>
                </h4>
                <p style="font-size: 0.8rem; color: #94a3b8; margin: 5px 0;">
                    <?php echo $row['created_at']; ?>
                </p>
                <p style="margin: 10px 0;">
                    <?php echo nl2br($row['message']); ?>
                </p>
                <?php if ($row['file_path']): ?>
                    <a href="../<?php echo $row['file_path']; ?>" target="_blank"
                        style="color: var(--primary-color); font-weight: 500;"><i class="fas fa-file-pdf"></i> Download PDF</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
    <div style="position: fixed; bottom: 10px; right: 20px; font-size: 12px; color: #94a3b8; font-family: monospace;">
        <!-- by Om Aka Morningstar -->
    </div>
</body>

</html>