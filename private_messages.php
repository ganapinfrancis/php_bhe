<?php
include 'db_connect.php';

// Initialize message para walang undefined variable
$message = '';

// =============================
// PRIVATE MESSAGE: CREATE
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $subject          = mysqli_real_escape_string($conn, $_POST['subject'] ?? '');
    $target_user_type = mysqli_real_escape_string($conn, $_POST['target_user_type'] ?? '');
    $target_user_id   = intval($_POST['target_user_id'] ?? 0);
    $content          = mysqli_real_escape_string($conn, $_POST['message'] ?? '');

    if (!empty($subject) && !empty($target_user_type) && !empty($target_user_id) && !empty($content)) {
        $sql = "INSERT INTO private_messages (subject, target_user_type, target_user_id, content, created_at) 
                VALUES ('$subject', '$target_user_type', $target_user_id, '$content', NOW())";
        if (mysqli_query($conn, $sql)) {
    $msg = urlencode("✅ Message sent successfully!");
    header("Location: private_messages.php?msg=$msg");
    exit;


        } else {
            $message = "❌ Error sending message: " . mysqli_error($conn);
        }
    } else {
        $message = "⚠️ Please fill all fields.";
    }
}

// =============================
// PRIVATE MESSAGE: DELETE
// =============================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM private_messages WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $message = "🗑️ Message deleted successfully!";
    } else {
        $message = "❌ Error deleting message: " . mysqli_error($conn);
    }
}

// =============================
// GET RECENT PRIVATE MESSAGES
// =============================
$result = mysqli_query($conn, "SELECT * FROM private_messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Private Messages | Smart Scholar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f4f6f9; }
    .sidebar {
      height: 100vh;
      background: #1e1e2d;
      color: white;
      padding-top: 20px;
    }
    .sidebar a {
      color: #cfd2da;
      text-decoration: none;
      display: block;
      padding: 10px 15px;
      margin-bottom: 5px;
      border-radius: 6px;
      transition: 0.3s;
    }
    .sidebar a:hover, .sidebar a.active {
      background: #007bff;
      color: white;
    }
    .card { border-radius: 10px; box-shadow: 0 2px 8px rgba(17, 221, 248, 0.1); }
  </style>
</head>
<body>
<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h4 class="text-white mb-4">Smart Scholar</h4>
    <a href="admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="announcements.php" class="<?= $current_page == 'announcements.php' ? 'active' : '' ?>">📢 Announcements</a>
    <a href="private_messages.php" class="<?= $current_page == 'private_messages.php' ? 'active' : '' ?>">✉️ Private Messages</a>
    <a href="applicants.php" class="<?= $current_page == 'applicants.php' ? 'active' : '' ?>">🧑‍🎓 Applicants</a>
    <a href="scholars.php" class="<?= $current_page == 'scholars.php' ? 'active' : '' ?>">🎓 Scholars</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <!-- Main Content -->
  <div class="flex-grow-1 p-4">
    <h2 class="mb-4">Send Private Message</h2>

    

    <!-- Compose Message -->
    <div class="card p-3 mb-4">
      <div class="card-header bg-dark text-white">Compose Message</div>
      <div class="card-body">
        <form method="POST" action="">
          <input type="text" name="subject" class="form-control mb-3" placeholder="Subject" required>
          
          <!-- Select Recipient Type -->
          <select name="target_user_type" id="target_user_type" class="form-select mb-3" required>
            <option value="">-- Select User Type --</option>
            <option value="Scholar">Scholar</option>
            <option value="Applicant">Applicant</option>
          </select>

          <!-- Select Specific User -->
          <select name="target_user_id" id="target_user_id" class="form-select mb-3" required>
            <option value="">-- Select Specific User --</option>
            <?php
              // Scholars
              $scholars = mysqli_query($conn, "SELECT scholar_id AS id, full_name FROM scholars");
              while ($s = mysqli_fetch_assoc($scholars)) {
                echo "<option value='{$s['id']}' data-type='Scholar'>Scholar - {$s['full_name']}</option>";
              }

              // Applicants
              $applicants = mysqli_query($conn, "SELECT applicant_id AS id, full_name FROM applicants");
              while ($a = mysqli_fetch_assoc($applicants)) {
                echo "<option value='{$a['id']}' data-type='Applicant'>Applicant - {$a['full_name']}</option>";
              }
            ?>
          </select>

          <textarea name="message" class="form-control mb-3" rows="4" placeholder="Write your private message..." required></textarea>
          <button type="submit" name="send_message" class="btn btn-primary">Send</button>
        </form>
      </div>
    </div>

    <!-- Recent Messages -->
    <h3 class="mb-3">📜 Recent Private Messages</h3>
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Subject</th>
          <th>Target</th>
          <th>Message</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['subject']) ?></td>
          <td><?= $row['target_user_type'] ?> (ID: <?= $row['target_user_id'] ?>)</td>
          <td><?= htmlspecialchars($row['content']) ?></td>
          <td><?= $row['created_at'] ?></td>
          <td>
            <a href="private_messages.php?delete=<?= $row['id'] ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('Delete this message?');">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
