<?php
// announcement_scholar.php
session_start();
include 'db_connect.php';

// ✅ Require scholar login
if (!isset($_SESSION['scholar_id'])) {
    header("Location: scholar_login.php");
    exit;
}

$scholar_id = (int) $_SESSION['scholar_id'];

// ✅ Kunin announcements para sa Scholars (All, Scholars, or specific target)
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
  <title>📢 Announcements — Scholar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f9fafb, #eef2f7);
      font-family: "Segoe UI", sans-serif;
    }
    .announcement-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      margin-bottom: 1rem;
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .announcement-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .announcement-header {
      background: #0d6efd;
      color: white;
      font-weight: 600;
      border-radius: 12px 12px 0 0;
      padding: .75rem 1rem;
      font-size: 1.1rem;
    }
    .announcement-body {
      padding: 1rem 1.25rem;
    }
    .announcement-meta {
      font-size: 0.85rem;
      color: #6c757d;
      margin-bottom: .5rem;
    }
    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #6c757d;
      font-size: 1rem;
    }
  </style>
</head>
<body>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary">📢 Announcements</h3>
    <a href="scholar_dashboard.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
  </div>

  <?php if (empty($announcements)): ?>
    <div class="card announcement-card">
      <div class="empty-state">
        No announcements at the moment.
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($announcements as $a): ?>
      <div class="card announcement-card">
        <div class="announcement-header">
          <?= htmlspecialchars($a['title']) ?>
        </div>
        <div class="announcement-body">
          <div class="announcement-meta">
            📌 Audience: <strong><?= htmlspecialchars($a['audience']) ?></strong> 
            · 🕒 <?= htmlspecialchars(date('M d, Y h:i A', strtotime($a['created_at']))) ?>
            <?php if (!empty($a['created_by'])): ?>
              · ✍️ <?= htmlspecialchars($a['created_by']) ?>
            <?php endif; ?>
          </div>
          <div><?= nl2br(htmlspecialchars($a['content'])) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
</body>
</html>
