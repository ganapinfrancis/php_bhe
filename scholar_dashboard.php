<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['scholar_id'])) {
  header("Location: scholar_login.php");
  exit;
}

$scholar_id = (int) $_SESSION['scholar_id'];

// Kunin ang scholar profile
$q = "SELECT * FROM scholars WHERE scholar_id = $scholar_id LIMIT 1";
$r = mysqli_query($conn, $q);
$scholar = $r ? mysqli_fetch_assoc($r) : null;

if (!$scholar) {
  session_destroy();
  header("Location: index.html?msg=" . urlencode("Session expired. Please log in again."));
  exit;
}

// Kunin ang announcements para sa Scholars
$announcements = [];
$aq = "
  SELECT announcement_id, title, content, audience, created_at, created_by, target_user_type, target_user_id
  FROM announcements
  WHERE 
    audience IN ('All','Scholars')
    OR (target_user_type = 'Scholar' AND target_user_id = $scholar_id)
  ORDER BY created_at DESC, announcement_id DESC
";
$ar = mysqli_query($conn, $aq);
if ($ar) {
  while ($row = mysqli_fetch_assoc($ar)) {
    $announcements[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scholar Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      display: flex;
      margin: 0;
    }
    .content {
      flex-grow: 1;
      padding: 20px;
      margin-left: 250px; /* para hindi matakpan ng sidebar */
    }
  </style>
</head>

<body>
  <!-- ✅ Sidebar (navbar_scholar.php ang filename) -->
  <?php include 'navbar_scholar.php'; ?> 

  <div class="content">
    <div class="welcome-card p-5 mb-4 text-center" style="
        background: linear-gradient(135deg, #ffffff, #e9f0ff);
        border-left: 6px solid #0d6efd;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    ">
        <h2 style="color: #0d6efd; font-size: 2.2rem; margin-bottom: 20px;">
            🎓 Maligayang Pagdating scholar ng bayan , <?= htmlspecialchars($scholar['full_name'] ?? 'Scholar') ?>!
        </h2>
        <p style="color: #495057; font-size: 1.15rem; line-height: 1.6;">
            Isang mainit na pagbati sa iyo! Ang dashboard na ito ay nilikha upang maging gabay mo sa iyong paglalakbay bilang isang iskolar. Dito mo makikita ang lahat ng mahahalagang impormasyon, updates, at mga hakbang upang patuloy mong maabot ang iyong mga pangarap.
        </p>
    </div>
</div>

<style>
.welcome-card {
      position: relative;
      padding: 60px 30px;
      border-radius: 15px;
      overflow: hidden;
      text-align: center;
      color: #fff;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      background: url('tao ng mayao.jpg') no-repeat center center/cover; /* <-- ilagay dito image path */
    }
    .welcome-card::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(245, 247, 251, 0.6); /* blue overlay */
      z-index: 0;
    }
    .welcome-card h2,
    .welcome-card p {
      position: relative;
      z-index: 1;
    }
    .welcome-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    }
  </style>
</head>

<body>
</style>
    
   <!-- ============ Scholar Update ============ -->
<!--
<div class="card mb-4">
  ... laman ng Private Message ...
</div>
-->

     <!-- ============ Announcements: Create ============ -->
<!--
<div class="card mb-4">
  ... laman ng Create Announcement ...
</div>
-->

    <!-- ============ Private Message: Create ============ -->
<!--
<div class="card mb-4">
  ... laman ng Private Message ...
</div>
-->

    <!-- ============ Attendance Upload ============ -->
<!--
<div class="card mb-4">
  ... laman ng Private Message ...
</div>
-->