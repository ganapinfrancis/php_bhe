<?php
session_start();
include 'db_connect.php';

// Check login
if (!isset($_SESSION['scholar_id'])) {
    header("Location: scholar_login.php");
    exit;
}

$scholar_id = (int) $_SESSION['scholar_id'];

// Get scholar profile
$q = "SELECT * FROM scholars WHERE scholar_id = $scholar_id LIMIT 1";
$r = mysqli_query($conn, $q);
$scholar = $r ? mysqli_fetch_assoc($r) : null;

if (!$scholar) {
    session_destroy();
    header("Location: scholar_login.php?msg=" . urlencode("Session expired. Please log in again."));
    exit;
}

// Get announcements
$announcements = [];
$aq = "
  SELECT announcement_id, title, content, audience, created_at, created_by, target_user_type, target_user_id
  FROM announcements
  WHERE audience IN ('All','Scholars') OR (target_user_type = 'Scholar' AND target_user_id = $scholar_id)
  ORDER BY created_at DESC, announcement_id DESC
";
$ar = mysqli_query($conn, $aq);
if ($ar) { while ($row = mysqli_fetch_assoc($ar)) { $announcements[] = $row; } }

// Count attendance
$attendanceQuery = "SELECT COUNT(*) AS total_attendance FROM attendance WHERE scholar_id = $scholar_id";
$attendanceResult = mysqli_query($conn, $attendanceQuery);
$attendance = mysqli_fetch_assoc($attendanceResult)['total_attendance'] ?? 0;

// Count private messages
$messageQuery = "SELECT COUNT(*) AS total_messages FROM private_messages 
                 WHERE target_user_type = 'Scholar' AND target_user_id = $scholar_id";
$messageResult = mysqli_query($conn, $messageQuery);
$messages = mysqli_fetch_assoc($messageResult)['total_messages'] ?? 0;

// Scholarship type
$scholarship_type = $scholar['scholarship_type'] ?? 'N/A';

// Detect current page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);
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
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4f6f8;
}
.content {
    flex-grow: 1;
    padding: 30px;
    margin-left: 250px; /* sidebar width */
}
h2 {
    color: #0d6efd;
}
/* Welcome Card Design */
.welcome-card {
    border-left: 5px solid #0d6efd; /* accent line */
    background: linear-gradient(145deg, #ffffff, #f1f5f9); /* subtle gradient */
    padding: 20px;
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    margin-bottom: 40px;
}
.welcome-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

/* Stats Cards Flex Layout */
.stats-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 40px;
}
.card {
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card h5 {
    font-weight: bold;
    color: #495057;
}
.card h3 {
    font-size: 2rem;
    margin-top: 10px;
    color: #0d6efd;
}
.announcements .card-header {
    font-weight: bold;
}
</style>
</head>
<body>

<?php include 'navbar_scholar.php'; ?>

<div class="content">
    <!-- ✅ Welcome Card -->
    <div class="welcome-card">
        <h2>🎓 Welcome, <?= htmlspecialchars($scholar['full_name']) ?>!</h2>
        <p class="mb-0 text-secondary">“Dito mo makikita ang lahat ng mahalagang updates tungkol sa iyong scholarship. Ang bilang ng messages ay awtomatikong nagco-count para madali mong masubaybayan kung may bago kang natanggap o hindi mo pa nababasa.”</p>
    </div>

    <!-- Stats Cards -->
<!-- Private Messages Card -->
<div class="mb-4" style="max-width: 18rem;">
    <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
            <h5>✉️ Private Messages</h5>
            <h3><?= $messages ?></h3>
        </div>
    </div>
</div>

<!-- Activity Participation Card -->
<?php if (!empty($scholar['activity_participation'])): ?>
<div class="mb-4" style="max-width: 18rem;">
    <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
            <h5>🏆 Activity Participation</h5>
            <p><?= htmlspecialchars($scholar['activity_participation']) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Status Card -->
<?php if (!empty($scholar['status'])): ?>
<div class="mb-4" style="max-width: 18rem;">
    <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
            <h5>👤 Status</h5>
            <span class="badge <?= ($scholar['status']==='Active')?'bg-success':'bg-secondary' ?>">
                <?= htmlspecialchars($scholar['status']) ?>
            </span>
        </div>
    </div>
</div>
<?php endif; ?>


</body>
</html>
