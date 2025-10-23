<?php 
// ✅ Detect current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
  <h4 class="text-white mb-4 text-center">Smart Scholar</h4>

 <a href="scholar_dashboard_main.php" class="<?= $current_page == 'scholar_dashboard_main.php' ? 'active' : '' ?>">
        📊 Dashboard
    </a>


  <a href="profile_scholar.php" 
     class="<?= $current_page == 'profile_scholar.php' ? 'active' : '' ?>">
     👤 Profile
  </a>

  <a href="announcement_scholar.php" 
     class="<?= $current_page == 'announcement_scholar.php' ? 'active' : '' ?>">
     📢 Announcements
  </a>

  <a href="private_mess_scholar.php">✉️ Private Messages</a>

  <!-- ✅ New Attendance sidebar link -->
  <a href="attendance_scholar.php">📥 Attendance</a>
  <a href="scholar_logout.php" class="logout">
     🚪 Logout
  </a>
</div>

<style>
/* Sidebar container */
.sidebar {
  width: 250px;
  background: #343a40;
  min-height: 100vh;
  padding: 20px 15px;
  position: fixed;
  top: 0;
  left: 0;
}

/* Sidebar title */
.sidebar h4 {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 25px;
}

/* Sidebar links */
.sidebar a {
  display: block;
  padding: 12px 15px;
  color: #ffffff;
  text-decoration: none;
  margin-bottom: 10px;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-weight: 500;
}

/* Hover effect */
.sidebar a:hover {
  background: #0d6efd;
  color: #fff;
  transform: translateX(5px);
}

/* Active state */
.sidebar a.active {
  background: #0d6efd;
  font-weight: bold;
  color: #fff;
  box-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
}

/* Logout button */
.sidebar a.logout {
  margin-top: 20px;
  color: #ff4d4d;
  font-weight: bold;
}

.sidebar a.logout:hover {
  background: #dc3545;
  color: #fff;
  transform: translateX(5px);
}
</style>
