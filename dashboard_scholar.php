<?php
session_start();
include 'db_connect.php';

// Check kung naka-login ang scholar
if (!isset($_SESSION['scholar_id'])) {
    header("Location: login.php");
    exit();
}

$scholar_id = $_SESSION['scholar_id'];

// Kunin scholar info
$scholarQuery = "SELECT fullname FROM scholars WHERE id = $scholar_id";
$scholarResult = mysqli_query($conn, $scholarQuery);
$scholar = mysqli_fetch_assoc($scholarResult);

// Bilangin attendance
$attendanceQuery = "SELECT COUNT(*) AS total_attendance FROM attendance WHERE scholar_id = $scholar_id";
$attendanceResult = mysqli_query($conn, $attendanceQuery);
$attendance = mysqli_fetch_assoc($attendanceResult)['total_attendance'];

// Bilangin activity participation
$activityQuery = "SELECT COUNT(*) AS total_activities FROM activities WHERE scholar_id = $scholar_id";
$activityResult = mysqli_query($conn, $activityQuery);
$activities = mysqli_fetch_assoc($activityResult)['total_activities'];

// Bilangin messages
$messageQuery = "SELECT COUNT(*) AS total_messages FROM private_messages 
                 WHERE target_user_id = $scholar_id AND target_user_type = 'Scholar'";
$messageResult = mysqli_query($conn, $messageQuery);
$messages = mysqli_fetch_assoc($messageResult)['total_messages'];

// Kunin announcements
$announcementsQuery = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcementsResult = mysqli_query($conn, $announcementsQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scholar Dashboard | Smart Scholar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2 class="mb-4">🎓 Welcome, <?php echo $scholar['fullname']; ?>!</h2>

  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h5>📅 Attendance</h5>
          <h3><?php echo $attendance; ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h5>🎯 Activities</h5>
          <h3><?php echo $activities; ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h5>✉️ Messages</h5>
          <h3><?php echo $messages; ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <h5>📢 Announcements</h5>
          <h3><?php echo mysqli_num_rows($announcementsResult); ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Latest Announcements -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">📢 Latest Announcements</div>
    <div class="card-body">
      <?php if (mysqli_num_rows($announcementsResult) > 0): ?>
        <ul class="list-group">
          <?php while ($row = mysqli_fetch_assoc($announcementsResult)): ?>
            <li class="list-group-item">
              <strong><?php echo $row['title']; ?></strong> <br>
              <small class="text-muted"><?php echo $row['created_at']; ?></small>
              <p><?php echo $row['content']; ?></p>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No announcements yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Latest Attendance Records -->
  <div class="card shadow-sm">
    <div class="card-header bg-success text-white">📅 Your Attendance Records</div>
    <div class="card-body">
      <?php
      $attendanceListQuery = "SELECT * FROM attendance WHERE scholar_id = $scholar_id ORDER BY date DESC LIMIT 5";
      $attendanceListResult = mysqli_query($conn, $attendanceListQuery);
      ?>
      <?php if (mysqli_num_rows($attendanceListResult) > 0): ?>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($attendanceListResult)): ?>
              <tr>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo $row['status']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No attendance records yet.</p>
      <?php endif; ?>
    </div>
  </div>

</div>

</body>
</html>
