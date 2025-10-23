<?php
// scholar_login.php
session_start();
include 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email  = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
  $purok  = mysqli_real_escape_string($conn, $_POST['purok'] ?? '');
  $passIn = $_POST['password'] ?? '';

  $q = "SELECT * FROM scholars WHERE email='$email' AND purok='$purok' LIMIT 1";
  $res = mysqli_query($conn, $q);

  if ($res && mysqli_num_rows($res) === 1) {
    $sch = mysqli_fetch_assoc($res);

    //  passwords are hashed (recommended) need na use password_verify
    if (password_verify($passIn, $sch['password']) || $passIn === $sch['password']) {
      $_SESSION['scholar_id'] = $sch['scholar_id'];
      header("Location: scholar_dashboard.php");
      exit;
    } else {
      $message = "<div class='alert alert-danger'>❌ Incorrect password.</div>";
    }
  } else {
    $message = "<div class='alert alert-danger'>❌ Email & Purok not found.</div>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scholar Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h2>Scholar Login</h2>
  <?= $message ?>
  <form method="POST" class="mt-3">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Purok (e.g., Purok 1)</label>
      <input type="text" name="purok" class="form-control" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required />
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
    <a href="index.html" class="btn btn-link">Back</a>
  </form>
</body>
</html>
