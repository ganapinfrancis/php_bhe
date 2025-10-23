<?php
include 'db_connect.php';
session_start();

$message = "";

// Check kung may file na in-upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $applicant_id = $_SESSION['applicant_id'] ?? null;

    if (!$applicant_id) {
        $message = "Applicant not logged in.";
    } else {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_error = $_FILES['file']['error'];

        // Allowed file types
        $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'png'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_error === 0) {
            if (in_array($file_ext, $allowed_ext)) {
                if ($file_size <= 5 * 1024 * 1024) { // 5MB limit
                    $new_name = uniqid('file_', true) . '.' . $file_ext;
                    $upload_dir = "uploads/";

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $upload_path = $upload_dir . $new_name;

                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Save to database
                        $stmt = $conn->prepare("INSERT INTO applicant_uploads (applicant_id, file_name, file_path) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $applicant_id, $file_name, $upload_path);

                        if ($stmt->execute()) {
                            $message = "File uploaded successfully!";
                        } else {
                            $message = "Database error: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $message = "Failed to move uploaded file.";
                    }
                } else {
                    $message = "File too large. Maximum size is 5MB.";
                }
            } else {
                $message = "Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG.";
            }
        } else {
            $message = "File upload error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applicant File Upload</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="card shadow p-4">
      <h3>Upload File</h3>
      <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
      <?php endif; ?>
      <form action="applicant_upload.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="file" class="form-label">Choose file</label>
          <input type="file" name="file" id="file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
      </form>
    </div>
  </div>
</body>
</html>
