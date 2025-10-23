<?php
// profile_scholar.php
session_start();
include 'db_connect.php';

// Require scholar login
if (!isset($_SESSION['scholar_id'])) {
    header("Location: scholar_login.php");
    exit;
}

$scholar_id = (int) $_SESSION['scholar_id'];

// Fetch scholar record
$result = mysqli_query($conn, "SELECT * FROM scholars WHERE scholar_id = $scholar_id LIMIT 1");
$data = $result ? mysqli_fetch_assoc($result) : null;

if (!$data) {
    session_destroy();
    header("Location: scholar_login.php?msg=" . urlencode("Session expired. Please login again."));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Scholar Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
body {
    background-color: #f0f2f5;
}
.profile-card {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.profile-header {
    background: linear-gradient(90deg, #007bff, #00c6ff);
    color: white;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 20px;
    text-align: center;
}
.profile-body {
    padding: 20px;
}
.profile-body p {
    font-size: 1.05rem;
    margin-bottom: 10px;
}
.profile-body i {
    color: #007bff;
    margin-right: 8px;
}
.small-muted {
    color: #6c757d;
    font-size: 0.9rem;
}
</style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card profile-card">
                <div class="profile-header">
                    <h3><?= htmlspecialchars($data['full_name'] ?? $data['name'] ?? 'No name') ?></h3>
                    <p class="small-muted">Scholar Profile</p>
                </div>
                <div class="profile-body">
                    <p><i class="bi bi-envelope"></i> <strong>Email:</strong> <?= htmlspecialchars($data['email'] ?? 'Not set') ?></p>
                    <p><i class="bi bi-calendar"></i> <strong>Age:</strong> <?= htmlspecialchars($data['age'] ?? 'Not set') ?></p>
                    <p><i class="bi bi-geo-alt"></i> <strong>Purok:</strong> <?= htmlspecialchars($data['purok'] ?? 'Not set') ?></p>

                   <!-- Back Button -->
    <div class="mt-4 text-center">
        <a href="scholar_dashboard.php" class="btn btn-primary">
            <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
        </a>
    </div>
</div>
</div>

<!-- Bootstrap JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
