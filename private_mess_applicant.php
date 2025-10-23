<?php
session_start();
include 'db_connect.php';

// Redirect kung hindi naka-login
if (!isset($_SESSION['applicant_email'])) {
    header("Location: index.html");
    exit;
}

// Kunin ang data ng applicant mula sa session/email
$email = $_SESSION['applicant_email'];
$result = mysqli_query($conn, "SELECT * FROM applicants WHERE email='".mysqli_real_escape_string($conn, $email)."' LIMIT 1");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    session_destroy();
    header("Location: index.html?msg=" . urlencode("Session expired. Please login again."));
    exit;
}

$applicant_id = (int) $data['applicant_id'];

// Kunin ang private messages para sa applicant
$private_messages = [];
$pm_q = "SELECT * FROM private_messages WHERE target_user_type='Applicant' AND target_user_id=$applicant_id ORDER BY created_at DESC";
$pm_r = mysqli_query($conn, $pm_q);
if ($pm_r) {
    while ($pm = mysqli_fetch_assoc($pm_r)) {
        $private_messages[] = $pm;
    }
} else {
    echo "Error sa private messages query: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Private Messages | Applicant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">

    <!-- Back Button -->
    <a href="applicant_dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>

    <h3>📩 Private Messages for <?= htmlspecialchars($data['full_name']) ?></h3>

    <div class="card mt-3">
        <div class="card-header bg-warning text-dark">Messages</div>
        <div class="card-body">
            <?php if (!empty($private_messages)): ?>
                <?php foreach ($private_messages as $pm): ?>
                    <div class="border-bottom mb-3 pb-2">
                        <strong><?= htmlspecialchars($pm['subject']) ?></strong><br>
                        <small class="text-muted">From: <?= htmlspecialchars($pm['sender'] ?? 'Admin') ?></small><br>
                        <small class="text-muted"><?= htmlspecialchars(date('M d, Y h:i A', strtotime($pm['created_at']))) ?></small>
                        <p class="mt-2"><?= nl2br(htmlspecialchars($pm['content'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No private messages.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
