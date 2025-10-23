<?php
// scholar_register.php
session_start();
require 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name'] ?? '');
  $age       = intval($_POST['age'] ?? 0);
  $purok     = trim($_POST['purok'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $password  = $_POST['password'] ?? '';

  if ($full_name === '' || $age <= 0 || $purok === '' || $email === '' || $password === '') {
    $message = "<div class='alert alert-danger'>Please fill out all fields correctly.</div>";
  } else {
    // Check duplicate email
    $stmt = mysqli_prepare($conn, "SELECT scholar_id FROM scholars WHERE LOWER(email)=LOWER(?) LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) > 0) {
      $message = "<div class='alert alert-warning'>Email already exists.</div>";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = mysqli_prepare($conn, "INSERT INTO scholars (full_name, age, purok, activity_participation, status, email, password, scholar_standing)
                                    VALUES (?, ?, ?, 'Joined 0/4', 'Inactive', ?, ?, 'Safe')");
      mysqli_stmt_bind_param($ins, 'sisss', $full_name, $age, $purok, $email, $hash);
      if (mysqli_stmt_execute($ins)) {
        // Auto-login after register 
        $_SESSION['scholar_id'] = mysqli_insert_id($conn);
        header("Location: scholar_dashboard.php");
        exit;
      } else {
        $message = "<div class='alert alert-danger'>DB Error: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Scholar Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-4" style="max-width:600px;">
  <h3>Scholar Registration</h3>
  <?php if ($message) echo $message; ?>

  <form method="POST" class="card p-3">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" name="full_name" class="form-control" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Age</label>
      <input type="number" name="age" class="form-control" required min="10" max="100" />
    </div>
    <div class="mb-3">
      <label class="form-label">Purok</label>
      <input type="text" name="purok" class="form-control" placeholder="e.g., Purok 3" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required />
    </div>
    <button type="submit" class="btn btn-success w-100">Create Account</button>
  </form>

  <div class="mt-3">
    <a href="scholar_login.php">Already have an account? Login</a>
  </div>
</body>
</html>
