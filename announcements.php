<?php
include 'db_connect.php';

$message = '';

// =============================
// CREATE ANNOUNCEMENT
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement'])) {
  $title    = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
  $content  = mysqli_real_escape_string($conn, $_POST['content'] ?? '');
  $audience = mysqli_real_escape_string($conn, $_POST['audience'] ?? 'All');

  if ($title === '' || $content === '') {
    $message = "<div class='alert alert-danger'>❌ Title and Content are required.</div>";
  } else {
    $created_by = 'Admin';
    $ins = "INSERT INTO announcements (title, content, audience, created_by)
            VALUES ('$title', '$content', '$audience', '$created_by')";

    if (mysqli_query($conn, $ins)) {
      $message = "<div class='alert alert-success'>✅ Announcement published.</div>";
    } else {
      $message = "<div class='alert alert-danger'>❌ DB Error: " . mysqli_error($conn) . "</div>";
    }
  }
}

// =============================
// DELETE ANNOUNCEMENT
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement'])) {
  $announcement_id = (int)($_POST['announcement_id'] ?? 0);
  if ($announcement_id > 0) {
    $del = "DELETE FROM announcements WHERE announcement_id = $announcement_id";
    if (mysqli_query($conn, $del)) {
      $message = "<div class='alert alert-success'>🗑️ Announcement deleted.</div>";
    } else {
      $message = "<div class='alert alert-danger'>❌ DB Error: " . mysqli_error($conn) . "</div>";
    }
  }
}

// =============================
// FETCH ANNOUNCEMENTS
// =============================
$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Announcements</title>
  <link rel="stylesheet" href="style_announcement.css">
  <style>
    /* Back Button Design */
    .btn-gradient {
      background: linear-gradient(90deg, #007bff, #00c6ff);
      color: white !important;
      font-weight: 600;
      border-radius: 30px;
      padding: 10px 20px;
      transition: all 0.3s ease-in-out;
      text-decoration: none;
      display: inline-block;
    }
    .btn-gradient:hover {
      background: linear-gradient(90deg, #0056b3, #0096c7);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="announcement.php">Announcements</a>
    <a href="logout.php">Logout</a>
  </div>

  <div class="container">
    <!-- Back to Dashboard Button -->
    <div class="mb-3">
      <a href="admin_dashboard.php" class="btn-gradient">
        ⬅️ Back to Dashboard
      </a>
    </div>

    <!-- Show Messages -->
    <?= $message ?>

    <!-- Create Announcement Form -->
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">Create Announcement</div>
      <div class="card-body">
        <form method="POST">
          <div class="row g-2">
            <div class="col-md-4">
              <input type="text" name="title" class="form-control" placeholder="Title" required>
            </div>
            <div class="col-md-3">
              <select name="audience" class="form-select">
                <option value="All">All</option>
                <option value="Scholars">All Scholars</option>
                <option value="Applicants">All Applicants</option>
              </select>
            </div>
            <div class="col-12 mt-2">
              <textarea name="content" rows="3" class="form-control" placeholder="Write your announcement..." required></textarea>
            </div>
            <div class="col-12 mt-2">
              <button type="submit" name="create_announcement" class="btn btn-primary">Publish</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- List Announcements -->
    <div class="card mb-4">
      <div class="card-header bg-secondary text-white">Recent Announcements</div>
      <div class="card-body">
        <?php if ($announcements && mysqli_num_rows($announcements) > 0): ?>
          <?php while($a = mysqli_fetch_assoc($announcements)): ?>
            <div class="announcement-box mb-3 p-3 border rounded">
              <div class="d-flex justify-content-between">
                <h5><?= htmlspecialchars($a['title']) ?></h5>
                <small><?= $a['created_at'] ?></small>
              </div>
              <p><?= nl2br(htmlspecialchars($a['content'])) ?></p>
              <form method="POST" onsubmit="return confirm('Delete this announcement?')" class="text-end">
                <input type="hidden" name="announcement_id" value="<?= $a['announcement_id'] ?>">
                <button type="submit" name="delete_announcement" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="text-muted">No announcements yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
