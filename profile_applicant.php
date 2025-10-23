<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['applicant_email'])) {
    header("Location: index.html");
    exit;
}

$email = $_SESSION['applicant_email'];
$result = mysqli_query($conn, "SELECT * FROM applicants WHERE email='".mysqli_real_escape_string($conn, $email)."' LIMIT 1");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    session_destroy();
    header("Location: index.html?msg=" . urlencode("Session expired. Please login again."));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Applicant Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    font-size: 1.1rem;
    margin-bottom: 10px;
}
.profile-body i {
    color: #007bff;
    margin-right: 8px;
}
</style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card profile-card">
                <div class="profile-header">
                    <h3><?= htmlspecialchars($data['full_name']) ?></h3>
                    <p>Applicant Profile</p>
                </div>
                <div class="profile-body">
                    <p><i class="bi bi-envelope"></i> <strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
                    <p><i class="bi bi-calendar"></i> <strong>Age:</strong> <?= htmlspecialchars($data['age']) ?></p>
                    <p><i class="bi bi-house"></i> <strong>Purok:</strong> <?= htmlspecialchars($data['purok']) ?></p>
                    <a href="applicant_dashboard.php" class="btn btn-primary w-100 mt-3">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
