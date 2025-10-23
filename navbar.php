<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Smart Scholar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
    }
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
    .card {
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(17, 221, 248, 0.1);
    }
    .card h4 {
      font-weight: 600;
      margin-bottom: 10px;
    }
    .card h2 {
      font-size: 2.5rem;
      margin: 0;
    }
  </style>
</head>
<body>
<?php
  include 'db_connect.php';

  $current_page = basename($_SERVER['PHP_SELF']);

  $total_applicants = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM applicants"))['count'];

  $active_scholars = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM scholars WHERE status='Active'"))['count'];

  $risk_sql = "SELECT COUNT(*) AS risk_count FROM scholars WHERE scholar_standing = 'At Risk'";
  $risk_result = mysqli_query($conn, $risk_sql);
  $risk_count = mysqli_fetch_assoc($risk_result)['risk_count'];
?>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h4 class="text-white mb-4">Smart Scholar</h4>
    <a href="admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="announcements.php" class="<?= $current_page == 'announcements.php' ? 'active' : '' ?>">📢 Announcements</a>
    <a href="private_messages.php" class="<?= $current_page == 'private_messages.php' ? 'active' : '' ?>">✉️ Private Messages</a>
    <a href="dashboard_applicant_admin.php" class="<?= $current_page == 'dashboard_applicant_admin.php' ? 'active' : '' ?>">🧑‍🎓 Applicants</a>
    <a href="admin_scholars.php" class="<?= $current_page == 'admin_scholars.php' ? 'active' : '' ?>">🎓 Scholars</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <!-- Main Content -->
  <div class="flex-grow-1 p-4">
    <h2 class="mb-4">Welcome, Admin!</h2>

    <!-- Dashboard Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card p-3 text-center bg-primary text-white">
          <h4>Total Applicants</h4>
          <h2><?= $total_applicants ?></h2>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card p-3 text-center bg-success text-white">
          <h4>Active Scholars</h4>
          <h2><?= $active_scholars ?></h2>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3 text-center bg-danger text-white">
          <h4>At Risk Scholars</h4>
          <h2><?= $risk_count ?></h2>
        </div>
      </div>
    </div>

    <!-- Analytics / Charts -->
    <div class="card p-3">
      <h5>Analytics</h5>
      <canvas id="chart"></canvas>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chart');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Applicants', 'Active Scholars', 'At Risk Scholars'],
    datasets: [
      {
        label: 'Scholarship Data',
        data: [<?= $total_applicants ?>, <?= $active_scholars ?>, <?= $risk_count ?>],
        backgroundColor: ['#007bff', '#28a745', '#dc3545']
      }
    ]
  }
});
</script>
</body>
</html>
