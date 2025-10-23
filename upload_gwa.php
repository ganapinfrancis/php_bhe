<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['applicant_id'])) {
  header("Location: index.html");
  exit;
}

$applicant_id = $_SESSION['applicant_id'];

if (isset($_FILES['gwa_image']) && $_FILES['gwa_image']['error'] === 0) {
  $upload_dir = 'uploads/';
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir);
  }

  $file_name = time() . '_' . basename($_FILES["gwa_image"]["name"]);
  $target_path = $upload_dir . $file_name;

  if (move_uploaded_file($_FILES["gwa_image"]["tmp_name"], $target_path)) {
    $sql = "UPDATE applicants SET gwa_image = '$file_name' WHERE id = $applicant_id";
    mysqli_query($conn, $sql);
  }
}

header("Location: applicant_dashboard.php");
exit;
?>
