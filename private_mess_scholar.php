<?php
// private_mess_scholar.php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db_connect.php';

// Require scholar to be logged in
if (!isset($_SESSION['scholar_id'])) {
    header("Location: scholar_login.php");
    exit;
}
$scholar_id = (int) $_SESSION['scholar_id'];

// ---------- Fetch messages for this scholar ----------
$messages = [];
$pm_q = "
    SELECT pm.*, 
           s.full_name AS sender_scholar_name, 
           a.full_name AS sender_applicant_name
    FROM private_messages pm
    LEFT JOIN scholars s ON pm.sender_type = 'Scholar' AND s.scholar_id = pm.sender_id
    LEFT JOIN applicants a ON pm.sender_type = 'Applicant' AND a.applicant_id = pm.sender_id
    WHERE pm.target_user_type = 'Scholar' 
      AND pm.target_user_id = $scholar_id
    ORDER BY pm.created_at DESC
";
$pm_r = mysqli_query($conn, $pm_q);
if ($pm_r) {
    while ($row = mysqli_fetch_assoc($pm_r)) {
        $messages[] = $row;
    }
} else {
    $pm_error = "Error loading messages: " . mysqli_error($conn);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>📩 Private Messages — Scholar</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#eef2f7; font-family: 'Segoe UI', sans-serif; }
    .header-bar { background:#0d6efd; color:#fff; padding:15px; border-radius:10px; }
    .header-bar h4 { margin:0; font-weight:600; }
    .message-card { border:1px solid #ddd; border-radius:12px; padding:15px; margin-bottom:15px; background:#fff; transition:0.2s; }
    .message-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    .message-subject { font-size:1.1rem; font-weight:600; color:#0d6efd; }
    .message-meta { font-size:0.85rem; color:#6c757d; }
    .message-body { margin-top:10px; line-height:1.5; }
  </style>
</head>
<body>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4 header-bar">
    <h4>📩 Private Messages</h4>
    <a href="scholar_dashboard.php" class="btn btn-light btn-sm">← Back to Dashboard</a>
  </div>

  <?php if (!empty($pm_error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($pm_error) ?></div>
  <?php endif; ?>

  <?php if (empty($messages)): ?>
    <div class="alert alert-secondary text-center">No private messages.</div>
  <?php else: ?>
    <?php foreach ($messages as $m): 
        // Determine sender display name
        $sender = 'Unknown';
        if (!empty($m['sender_type']) && $m['sender_type'] === 'Admin') {
            $sender = 'Admin';
        } elseif (!empty($m['sender_type']) && $m['sender_type'] === 'Scholar') {
            $sender = $m['sender_scholar_name'] ?? 'Scholar';
        } elseif (!empty($m['sender_type']) && $m['sender_type'] === 'Applicant') {
            $sender = $m['sender_applicant_name'] ?? 'Applicant';
        }
        $subject = $m['subject'] ?? 'No Subject';
        $content = $m['content'] ?? $m['message'] ?? '';
        $date = !empty($m['created_at']) ? date('M d, Y h:i A', strtotime($m['created_at'])) : '';
    ?>
      <div class="message-card">
        <div class="d-flex justify-content-between">
          <div>
            <div class="message-subject"><?= htmlspecialchars($subject) ?></div>
            <div class="message-meta">From: <strong><?= htmlspecialchars($sender) ?></strong> &middot; <?= htmlspecialchars($date) ?></div>
          </div>
        </div>
        <div class="message-body"><?= nl2br(htmlspecialchars($content)) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
</body>
</html>
