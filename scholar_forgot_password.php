<?php
// scholar_forgot_password.php
session_start();
include 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
  $purok    = mysqli_real_escape_string($conn, $_POST['purok'] ?? '');
  $pass1    = $_POST['new_password'] ?? '';
  $pass2    = $_POST['confirm_password'] ?? '';

  //  validations
  if (empty($email) || empty($purok) || empty($pass1) || empty($pass2)) {
    $message = "<div class='alert alert-danger'>Please fill in all fields.</div>";
  } elseif ($pass1 !== $pass2) {
    $message = "<div class='alert alert-danger'>Passwords do not match.</div>";
  } elseif (strlen($pass1) < 8) {
    $message = "<div class='alert alert-danger'>Password must be at least 8 characters.</div>";
  } else {
    //  scholar by email + purok
    $q = "SELECT scholar_id FROM scholars WHERE email='$email' AND purok='$purok' LIMIT 1";
    $r = mysqli_query($conn, $q);

    if ($r && mysqli_num_rows($r) === 1) {
      $row = mysqli_fetch_assoc($r);
      $scholar_id = (int)$row['scholar_id'];

      // Hash the new password
      $hash = password_hash($pass1, PASSWORD_DEFAULT);

      $upd = "UPDATE scholars SET password='$hash' WHERE scholar_id=$scholar_id";
      if (mysqli_query($conn, $upd)) {
        $message = "<div class='alert alert-success'>
                      ✅ Password updated successfully. You may now <a href='scholar_login.php'>log in</a>.
                    </div>";
      } else {
        $message = "<div class='alert alert-danger'>❌ Database error: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
      }
    } else {
      $message = "<div class='alert alert-danger'>❌ No scholar found with that Email & Purok.</div>";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scholar Forgot Password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5" style="max-width: 520px;">
  <h3 class="mb-3">Scholar – Forgot Password</h3>
  <?php if (!empty($message)) echo $message; ?>

  <form method="POST" class="card p-3">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Purok</label>
      <input type="text" name="purok" class="form-control" placeholder="e.g., Purok 1" required>
    </div>
    <div class="mb-3">
      <label class="form-label">New Password</label>
      <input type="password" name="new_password" class="form-control" placeholder="At least 8 characters" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm New Password</label>
      <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Reset Password</button>
    <a href="index.html" class="btn btn-link">Back</a>
  </form>
</body>
</html>
