<?php
session_start();
include 'db_connect.php';

// Redirect kung hindi naka-login
if (!isset($_SESSION['applicant_email'])) {
    header("Location: index.html");
    exit;
}

$email = $_SESSION['applicant_email'];
$result = mysqli_query($conn, "SELECT * FROM applicants WHERE email='".mysqli_real_escape_string($conn, $email)."' LIMIT 1");
$data = $result ? mysqli_fetch_assoc($result) : null;

if (!$data) {
    session_destroy();
    header("Location: index.html?msg=" . urlencode("Session expired. Please login again."));
    exit;
}

$applicant_id = (int) $data['applicant_id'];

// ----------------------------
// Fetch Announcements
// ----------------------------
$announcements = [];
$aq = "
  SELECT announcement_id, title, content, audience, created_at, created_by, target_user_type, target_user_id
  FROM announcements
  WHERE audience IN ('All','Applicants')
     OR (target_user_type = 'Applicant' AND target_user_id = $applicant_id)
  ORDER BY created_at DESC, announcement_id DESC
";
$ar = mysqli_query($conn, $aq);
if ($ar) {
    while ($row = mysqli_fetch_assoc($ar)) {
        $announcements[] = $row;
    }
}

// ----------------------------
// Fetch Private Messages
// ----------------------------
$private_messages = [];
$pm_q = "SELECT * FROM private_messages WHERE target_user_type='Applicant' AND target_user_id=$applicant_id ORDER BY created_at DESC";
$pm_r = mysqli_query($conn, $pm_q);
if ($pm_r) {
    while ($row = mysqli_fetch_assoc($pm_r)) {
        $private_messages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applicant Announcements</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f4f6f9; }
    .card { margin-top: 20px; border-radius: 10px; }
  </style>
</head>
<body>

<div class="container py-4">
    <h3>Welcome, <?= htmlspecialchars($data['full_name']) ?>!</h3>

    <?php
    // ---------------------------- Announcements ----------------------------
    $announcements = [];
    $aq = "
        SELECT announcement_id, title, content, audience, created_at, created_by, target_user_type, target_user_id
        FROM announcements
        WHERE audience IN ('All','Applicants') 
           OR (target_user_type = 'Applicant' AND target_user_id = $applicant_id)
        ORDER BY created_at DESC, announcement_id DESC
    ";
    $ar = mysqli_query($conn, $aq);
    if ($ar) {
        while ($row = mysqli_fetch_assoc($ar)) {
            $announcements[] = $row;
        }
    } else {
        echo "Error sa announcements query: " . mysqli_error($conn);
    }
    ?>

    <div class="container py-4">
    <!-- Back Button -->
    <a href="applicant_dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>

    

    <?php
    // ---------------------------- Announcements ----------------------------
    $announcements = [];
    $aq = "
        SELECT announcement_id, title, content, audience, created_at, created_by, target_user_type, target_user_id
        FROM announcements
        WHERE audience IN ('All','Applicants') 
           OR (target_user_type = 'Applicant' AND target_user_id = $applicant_id)
        ORDER BY created_at DESC, announcement_id DESC
    ";
    $ar = mysqli_query($conn, $aq);
    if ($ar) {
        while ($row = mysqli_fetch_assoc($ar)) {
            $announcements[] = $row;
        }
    } else {
        echo "Error sa announcements query: " . mysqli_error($conn);
    }
    ?>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">Announcements</div>
        <div class="card-body">
            <?php if (empty($announcements)): ?>
                <div class="alert alert-secondary mb-0">No announcements yet.</div>
            <?php else: ?>
                <?php foreach ($announcements as $a): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-1"><?= htmlspecialchars($a['title']) ?></h6>
                        <div class="text-muted small mb-2">
                            Audience: <strong><?= htmlspecialchars($a['audience']) ?></strong> 
                            &middot; Posted: <?= htmlspecialchars(date('M d, Y h:i A', strtotime($a['created_at']))) ?>
                            <?php if (!empty($a['created_by'])): ?>
                                &middot; by <?= htmlspecialchars($a['created_by']) ?>
                            <?php endif; ?>
                        </div>
                        <div><?= nl2br(htmlspecialchars($a['content'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>


 