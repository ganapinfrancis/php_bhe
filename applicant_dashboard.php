<?php
session_start();
include 'db_connect.php';

// Redirect kung hindi naka-login
if (!isset($_SESSION['applicant_email'])) {
    header("Location: index.html");
    exit;
}

// Kunin ang data mula sa session/email
$email = $_SESSION['applicant_email'];
$result = mysqli_query($conn, "SELECT * FROM applicants WHERE email='".mysqli_real_escape_string($conn, $email)."' LIMIT 1");
$data = $result ? mysqli_fetch_assoc($result) : null;

if (!$data) {
    session_destroy();
    header("Location: index.html?msg=" . urlencode("Session expired. Please login again."));
    exit;
}

$applicant_id = (int) $data['applicant_id'];

// Messages for alert
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Handle GWA image upload / delete...
// dito mo ilalagay ang code para sa gwa_image at result_image
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applicant Dashboard</title>
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
  </style>
</head>
<body>

<?php include 'navbar_applicant.php'; ?>

<!-- ✅ Dashboard Content -->
<div class="flex-grow-1 p-4">
    <h2 class="mb-4">Welcome, <?= htmlspecialchars($data['full_name']) ?>!</h2>

    <?php if ($message): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php include 'dashbar_applicant.php'; ?>
</div>



<!-- I-close ang main wrapper and d-flex -->
</div> <!-- close flex-grow-1 -->
</div> <!-- close d-flex -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


