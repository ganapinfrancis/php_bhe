<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];

  $check = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '$email'");
  if (mysqli_num_rows($check) > 0) {
    // Pwede kang mag-set ng random password or redirect sa reset page
    $new_password = substr(md5(time()), 0, 8); // Random 8-char password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    mysqli_query($conn, "UPDATE applicants SET password='$hashed' WHERE email='$email'");
    echo "Your new password is: <strong>$new_password</strong> <br><a href='index.html'>Go back to login</a>";
  } else {
    echo "Email not found.";
  }
}
?>

<!-- Forgot password form -->
<form method="post">
  <h3>Reset Password</h3>
  <input type="email" name="email" class="form-control mb-2" placeholder="Enter your email" required>
  <button type="submit" class="btn btn-warning">Reset Password</button>
</form>
