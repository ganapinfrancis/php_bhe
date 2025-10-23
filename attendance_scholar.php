<?php
// attendance_scholar.php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db_connect.php';

// initialize message
$message = '';

// require scholar login
if (!isset($_SESSION['scholar_id'])) {
    header("Location: scholar_login.php");
    exit;
}

$scholar_id = (int) $_SESSION['scholar_id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DELETE attendance
    if (isset($_POST['delete_attendance'])) {
        $q = mysqli_query($conn, "SELECT attendance_image FROM scholars WHERE scholar_id = $scholar_id LIMIT 1");
        $row = $q ? mysqli_fetch_assoc($q) : null;
        if ($row && !empty($row['attendance_image'])) {
            $fs_path = __DIR__ . '/' . $row['attendance_image'];
            if (file_exists($fs_path)) @unlink($fs_path);
        }
        $stmt = mysqli_prepare($conn, "UPDATE scholars SET attendance_image = NULL, attendance_date = NULL WHERE scholar_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $scholar_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['attendance_message'] = "✅ Attendance entry deleted.";
        header("Location: attendance_scholar.php");
        exit;
    }

    // UPLOAD attendance
    if (isset($_FILES['attendance_image']) && $_FILES['attendance_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['attendance_image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $message = "❌ Upload error (code {$file['error']}).";
        } else {
            $allowed_ext = ['jpg','jpeg','png'];
            $max_size = 5 * 1024 * 1024; 
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext)) {
                $message = "❌ Invalid file type. Only JPG/JPEG/PNG allowed.";
            } elseif ($file['size'] > $max_size) {
                $message = "❌ File too large. Max 5MB.";
            } else {
                $upload_dir_fs = __DIR__ . '/uploads/attendance/';
                $upload_dir_db = 'uploads/attendance/';

                if (!is_dir($upload_dir_fs)) {
                    mkdir($upload_dir_fs, 0777, true);
                }

                $q = mysqli_query($conn, "SELECT attendance_image FROM scholars WHERE scholar_id = $scholar_id LIMIT 1");
                $old = $q ? mysqli_fetch_assoc($q) : null;
                if ($old && !empty($old['attendance_image'])) {
                    $old_fs = __DIR__ . '/' . $old['attendance_image'];
                    if (file_exists($old_fs)) @unlink($old_fs);
                }

                $new_filename = "scholar_{$scholar_id}_" . time() . '.' . $ext;
                $target_path_fs = $upload_dir_fs . $new_filename;
                $target_path_db = $upload_dir_db . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_path_fs)) {
                    $stmt = mysqli_prepare($conn, "UPDATE scholars SET attendance_image = ?, attendance_date = NOW() WHERE scholar_id = ?");
                    mysqli_stmt_bind_param($stmt, 'si', $target_path_db, $scholar_id);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        $_SESSION['attendance_message'] = "✅ Attendance uploaded successfully.";
                        header("Location: attendance_scholar.php");
                        exit;
                    } else {
                        $message = "❌ DB error: " . htmlspecialchars(mysqli_stmt_error($stmt));
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    $message = "❌ Failed to move uploaded file — check folder permissions.";
                }
            }
        }
    }
}

if (!empty($_SESSION['attendance_message'])) {
    $message = $_SESSION['attendance_message'];
    unset($_SESSION['attendance_message']);
}

$sch_res = mysqli_query($conn, "SELECT full_name, attendance_image, attendance_date FROM scholars WHERE scholar_id = $scholar_id LIMIT 1");
$scholar = $sch_res ? mysqli_fetch_assoc($sch_res) : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Attendance Upload — Scholar</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg,#e9f0f9,#f8fafc); font-family: "Segoe UI", system-ui, -apple-system, Arial; }
    .header-bar {
      background: linear-gradient(90deg,#0d6efd,#3b82f6);
      color: #fff; padding: 20px; border-radius: 10px; 
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card { border: none; border-radius: 12px; transition: 0.2s; box-shadow: 0 6px 18px rgba(0,0,0,0.05); }
    .card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .thumb { max-width: 100%; border-radius: 10px; border: 3px solid #dee2e6; }
    .btn-primary { border-radius: 8px; padding: .55rem 1rem; }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="header-bar d-flex justify-content-between align-items-center mb-4">
    <h3 class="m-0">📌 Attendance — <?= htmlspecialchars($scholar['full_name'] ?? 'Scholar') ?></h3>
    <a href="scholar_dashboard.php" class="btn btn-light btn-sm">← Back to Dashboard</a>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-info shadow-sm"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Upload Section -->
    <div class="col-md-6">
      <div class="card p-4">
        <h5 class="mb-3">⬆️ Upload Attendance</h5>
        <form method="post" enctype="multipart/form-data" novalidate>
          <div class="mb-3">
            <label class="form-label">Choose Image (JPG/PNG, max 5MB)</label>
            <input type="file" name="attendance_image" accept="image/*" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Upload Attendance</button>
        </form>
      </div>
    </div>

    <!-- Last Attendance -->
    <div class="col-md-6">
      <div class="card p-4">
        <h5 class="mb-3">🕒 Last Attendance</h5>
        <?php
          $img_path = $scholar['attendance_image'] ?? '';
          $img_fs = $img_path ? __DIR__ . '/' . $img_path : '';
        ?>
        <?php if ($img_path && file_exists($img_fs)): ?>
          <img src="<?= htmlspecialchars($img_path) ?>" alt="Attendance" class="img-fluid thumb mb-3">
          <p class="mb-2"><strong>Uploaded at:</strong>
            <?= htmlspecialchars(date('M d, Y h:i A', strtotime($scholar['attendance_date'] ?? ''))) ?>
          </p>
          <form method="post" onsubmit="return confirm('Delete this attendance entry?');">
            <button type="submit" name="delete_attendance" class="btn btn-danger btn-sm">🗑 Delete Attendance</button>
          </form>
        <?php else: ?>
          <p class="text-muted">No attendance uploaded yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
