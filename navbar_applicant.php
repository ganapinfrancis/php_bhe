<?php
// ✅ Detect current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h4 class="text-white mb-4">Smart Scholar</h4>
    <a href="applicant_dashboard.php" class="<?= $current_page == 'applicant_dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="profile_applicant.php?page=profile">👤 Profile</a>
    <a href="announcement_applicant.php" class="nav-link">📢 Announcements</a>
    <a href="private_mess_applicant.php">✉️Private Messages</a>
    <a href="logout_applicant.php">🚪 Logout</a>
  </div>

  <!-- Main Content Wrapper -->
  <div class="flex-grow-1 p-4">
