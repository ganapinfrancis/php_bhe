<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $result = mysqli_query($conn, "SELECT * FROM applicants WHERE email = '$email'");

  if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {
      $_SESSION['applicant_email'] = $user['email'];
      $_SESSION['full_name'] = $user['full_name'];
      header("Location: applicant_dashboard.php");
      exit;
    } else {
      echo "<script>alert('Incorrect password!'); window.history.back();</script>";
    }
  } else {
    echo "<script>alert('No account found with that email!'); window.history.back();</script>";
  }
  
  
}
?>


